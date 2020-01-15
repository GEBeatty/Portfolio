/**
 * Functions required:
 * - Setup new category
 * - Delete category
 * - Add transaction
 * - Remove transaction
 * 
 * Page must update data upon each function
 */

function setup() {
    document.getElementById("catButton").addEventListener("click", createCat, true);
    document.getElementById("purButton").addEventListener("click", createPur, true);
    // Populate page
    reset();
}

/**
 * AJAX Boilerplate
 */
function makeRec(method, target, retCode, handlerAction, data) {
    var httpRequest = new XMLHttpRequest();
    if(!httpRequest) {
        alert("Couldn't create XMLHTTP instance");
        return false;
    }

    httpRequest.onreadystatechange = makeHandler(httpRequest, retCode, handlerAction);
    httpRequest.open(method, target);

    if(data) {
        httpRequest.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        httpRequest.send(data);
    } else {
        httpRequest.send();
    }
}

function makeHandler(httpRequest, retCode, action) {
    console.log("making handler!");
    function handler() {
        if (httpRequest.readyState === XMLHttpRequest.DONE) {
            if (httpRequest.status === retCode) {
                console.log("recieved response text: " + httpRequest.responseText);
                action(httpRequest.responseText);
            } else {
                alert("There was a problem with the request. Please refresh the page.");
            }
        }
    }
    return handler;
}

/**
 * Main JS logic
 */

function reset() {
    d = new Date();
    y = d.getYear() + 1900
    m = d.getMonth() + 1;
    d = d.getDay() + 1;
    data = "year="+y+"&month="+m+"&day="+d;
    makeRec("POST", '/date', 200, dateSet, data);
    makeRec("GET", '/cats', 200, repopulateCat);
    makeRec("GET", '/purchases', 200, repopulatePur);
}

function dateSet(responseText) {
    var title = document.getElementById("monthTitle");
    var mon = "";
    var d = JSON.parse(responseText);
    var num = d['month'];
    switch(num){
        case 1: mon = "January"; break;
        case 2: mon = "February"; break;
        case 3: mon = "March"; break;
        case 4: mon = "April"; break;
        case 5: mon = "May"; break;
        case 6: mon = "June"; break;
        case 7: mon = "July"; break;
        case 8: mon = "August"; break;
        case 9: mon = "September"; break;
        case 10: mon = "October"; break;
        case 11: mon = "November"; break;
        case 12: mon = "December"; break;
    }
    title.innerText = "Budget for " + mon;
}

function createCat() {
    var newName = document.getElementById("catName").value.trim();
    var newLimit = document.getElementById("catLimit").value.trim();
    var data;
    if(newName == "" || newLimit == "" || newLimit == 0){
        alert("Please provide a name and limit");
    } else {
        data = "name="+newName+"&limit="+newLimit+"&diff="+newLimit;
        makeRec("POST", "/cats", 201, reset, data);
        document.getElementById('catName').value = "";
        document.getElementById('catLimit').value = "";
    }
}

function deleteCat(cat_id) {
    makeRec("DELETE", "/cats/"+cat_id, 204, reset);
}

function createPur() {
    var newDes = document.getElementById("purName").value.trim();
    var newCat = document.getElementById("purCat").value.trim();
    var newAmt = document.getElementById("purAmt").value.trim();
    var newDate = document.getElementById("purDate").value.trim();
    var data;
    if(newDes == "" || newCat == "" || newAmt == "" || newAmt == 0 || newDate == ""){
        alert("Please fill in all the purchase requirements");
    } else {
        data = "des="+newDes+"&cat="+newCat+"&amt="+newAmt+"&date="+newDate;
        makeRec("POST", "/purchases", 201, reset, data);
        // Reset the form values
        document.getElementById("purName").value="";
        document.getElementById("purCat").value="";
        document.getElementById("purAmt").value="";
        document.getElementById("purDate").value="";
    }    
}


function addCell(row, text) {
    var newCell = row.insertCell();
    var newText = document.createTextNode(text);
    newCell.appendChild(newText);
}

function addCell(row, text, style) {
    var newCell = row.insertCell();
    var newText = document.createTextNode(text);
    newCell.appendChild(newText);
    newCell.className = style;
}

function repopulateCat(responseText) {
    console.log("repopulating categories!");
    // First, fix up the categories
    var categories = JSON.parse(responseText);
    var catList = document.getElementById("catList");
    var uncat = document.getElementById("uncat");
    var purSelect = document.getElementById("purCat");
    // console.log(categories)
    var newRow, newCell, c, cat, newButton, option;

    while(catList.rows.length > 0){
        catList.deleteRow(0);
    }
    purSelect.options.length=0;

    // catList header
    newRow = catList.insertRow();
    addCell(newRow, "Name");
    addCell(newRow, "Money Left / Limit");
    addCell(newRow, "Status");

    for (c in categories) {
        // Reset options dropdown
        // console.log(c);
        option = document.createElement("option");
        option.text = categories[c]['name'];
        option.value = categories[c]['name'];
        purSelect.add(option);

        // Reset category list
        if(categories[c]['name'] == 'Uncategorized'){
            // addCell(newRow, categories[c]['name']);
            // addCell(newRow, "Total:");
            // addCell(newRow, "$"+categories[c]['total']);
            uncat.innerHTML = "Uncategorized total: $"+categories[c]['total'];
        } else {
            newRow = catList.insertRow();
            addCell(newRow, categories[c]['name']);
            monUsed = "$"+categories[c]['diff']+' / '+"$"+ categories[c]['limit'];
            addCell(newRow, monUsed);
            if(categories[c]['diff'] > 0){
                addCell(newRow, "Good", "status-good");
            } else if (categories[c]['diff'] == 0){
                addCell(newRow, "Limit Reached", "status-limit");
            } else if (categories[c]['diff'] < 0) {
                abso = Math.abs(categories[c]['diff'])
                status = "$"+abso+" over budget!";
                addCell(newRow, status, "status-over");
            }
        }

        newCell = newRow.insertCell();
        // Can't delete uncategorized
        if(c != "cat_1"){
            let b = c;
            newButton = document.createElement("input");
            newButton.type = "button";
            newButton.value = "Delete category";
            newButton.addEventListener("click", function() { deleteCat(b); });
            newCell.appendChild(newButton);
        }
    }
}

function repopulatePur(responseText) {
    console.log("repopulating purchases!");
    var purchases = JSON.parse(responseText);
    var purList = document.getElementById("purList");
    var newRow, newCell, p, pur, newButton;

    while(purList.rows.length>0){
        purList.deleteRow(0);
    }

    for (p in purchases) {
        newRow = purList.insertRow(0);
        for(pur in purchases[p]){
            addCell(newRow, purchases[p][pur]);
        }
    }
}

// Setup window
window.addEventListener("load", setup, true);