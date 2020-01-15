<?php
include_once "db_config.php";
include_once "db_conn.php";
session_start();

// If not logged in, send back to login
if(!isset($_SESSION['userId'])){
    header("Location: index.php");
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>My Rentals - Turn</title>
        <link rel="stylesheet" href="style.css">

        <script>
            /**
                Learned this cool tab trick here: https://www.w3schools.com/howto/howto_js_tabs.asp
             */
            function openTab(evt, tabName) {
                var i, tabContent, tabLinks;
                // Hide all elements with class
                tabContent = document.getElementsByClassName("gTabContent");
                for(i=0; i<tabContent.length; i++){
                    tabContent[i].style.display = "none";
                }
                // Remove active from tab links
                tabLinks = document.getElementsByClassName("gTabLinks");
                for(i=0; i<tabLinks.length; i++){
                    tabLinks[i].className = tabLinks[i].className.replace(" active","");
                }
                // Show current tab only
                document.getElementById(tabName).style.display = "block";
                evt.currentTarget.className += " active";
            }
        </script>
    </head>
    <?php
    include "navbar.php";
    ?>
    <body>
        <h2>My Rentals</h2>
        <!-- Rental tab buttons -->
        <div class="groupTab">
            <button class="gTabLinks" onclick="openTab(event, 'renting')">I'm Renting</button>
            <button class="gTabLinks" onclick="openTab(event, 'rented')">I've Rented Out</button>
        </div>
        <!-- Things I'm renting -->
        <div id='renting' class='gTabContent'>
            <h3>Items I'm Renting</h3>
            <table>
                <tr>
                    <th>Item</th>
                    <th>Owner</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                </tr>
                <?php
                $rents = getRentals($_SESSION['userId'],$conn);
                while($row = mysqli_fetch_assoc($rents)){
                    echo "<tr>";
                    echo "<td>".$row['item_name']."</td>";
                    echo "<td>".$row['renter_fName']." ".$row['renter_lName']."</td>";
                    echo "<td>".$row['start_date']."</td>";
                    echo "<td>".$row['end_date']."</td>";
                    echo "</tr>";
                }
                ?>
            </table>
        </div>
        <!-- Things I've rented out -->
        <div id='rented' class='gTabContent'>
            <h3>Items I've rented out</h3>
            <table>
                <tr>
                    <th>Item</th>
                    <th>Renter</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                </tr>
                <?php
                $rentOuts = getRenteds($_SESSION['userId'],$conn);
                while($row = mysqli_fetch_assoc($rentOuts)){
                    echo "<tr>";
                    echo "<td>".$row['item_name']."</td>";
                    echo "<td>".$row['renter_fName']." ".$row['renter_lName']."</td>";
                    echo "<td>".$row['start_date']."</td>";
                    echo "<td>".$row['end_date']."</td>";
                    echo "</tr>";
                }
                ?>
            </table>
        </div>
    </body>
</html>