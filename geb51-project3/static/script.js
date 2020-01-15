var timeoutID;
var timeout = 1000;

function setup() {
    timeoutID = window.setTimeout(poller, timeout);
}

function sendPost() {
    // Create a new request to send data
    var httpRequest = new XMLHttpRequest();
    if (!httpRequest) {
        alert('Cannot create XMLHTTP instance');
        return false;
    }
    // Setup data
    var postText = document.getElementById("message_text").value;
    var user = document.getElementById("user_name").value;
    var now = new Date();
    // var time = now.toISOString();
    var time_sql = now.getFullYear()+"-"+(now.getMonth()+1)+"-"+now.getDate()+" "+now.getHours()+":"+now.getMinutes()+":"+now.getSeconds()+"."+now.getMilliseconds();
    var row = [user, postText, time_sql];
    httpRequest.onreadystatechange = function() { handlePost(httpRequest, row) };

    var chatid = document.getElementById("chat_id").value;
    httpRequest.open("POST", "/newpost");
    httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    var data = "message_text=" + postText + "&message_time=" + time_sql;
    httpRequest.send(data);
}

function handlePost(httpRequest, row) {
    if (httpRequest.readyState === XMLHttpRequest.DONE) {
        if (httpRequest.status === 200) {
            // Add row data to HTML file
            // addPost(row);
            clearInput();
        } else {
            alert("There was a problem with the post request.");
        }
    }
}

function poller() {
    var httpRequest = new XMLHttpRequest();
    if (!httpRequest) {
        alert('Cannot create XMLHTTP instance');
        return false;
    }
    httpRequest.onreadystatechange = function() { handlePoll(httpRequest) };
    httpRequest.open("GET", "/allposts");
    httpRequest.send();
}

function handlePoll(httpRequest) {
    if (httpRequest.readyState === XMLHttpRequest.DONE) {
        if (httpRequest.status === 200) {
            var postTab = document.getElementById("post_table");

            if (httpRequest.responseText[0] !== "["){
                if (httpRequest.responseText === "no_new"){
                    // Do nothing
                } else {
                    document.getElementById("above_table").innerHTML = httpRequest.responseText;
                    if (httpRequest.responseText[0] === "W"){
                        // User deleted chat, need timed redirect
                        setTimeout("location.href = '/chatrooms';",3500);
                    }
                }                
            } else {
                if (document.getElementById("above_table").innerHTML != null){
                    document.getElementById("above_table").innerHTML = "";
                }
                var rows=JSON.parse(httpRequest.responseText);
                for (var i = (rows.length-1); i > -1; i--) {
                    console.log(rows[i]['text'])
                    arr = [rows[i]['name'], rows[i]['text'], rows[i]['time']]
                    addPost(arr);
                }            
            }
            timeoutID = window.setTimeout(poller, timeout);
        } else {
            alert("There was a problem with the poll request. Please refresh the page to recieve updates.");
        }
    }
}

function addPost(row) {
    // Grab table
    var postTab = document.getElementById("post_table");
    var newRow = postTab.insertRow(0);
    row[2] = row[2].substring(0,19)
    // Insert post username, message, and date
    var newCell, newText;
    console.log(row.length)
    for (var i = 0; i < row.length; i++) {
        newCell = newRow.insertCell();
        newText = document.createTextNode(row[i]);
        console.log(newText);
        newCell.appendChild(newText);
    }
}

function clearInput() {
    document.getElementById('message_text').value = "";
}

window.addEventListener("load", setup, true);