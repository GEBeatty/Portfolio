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
        <title>My Requests - Turn</title>
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

    if($_POST){
        $decision = $_POST['decide_yn'];
        // request id and decision
        $decision = preg_split("~_~", $decision);
        $table = '';
        if($decision[0]=='r'){
            $table = 'RentRequest';
        } else {
            $table = 'JoinRequest';
        }
        // echo $table;
        $q = "UPDATE $table SET ".$decision[0]."_status=? WHERE ".$decision[0]."_request_id=?";
        // update request id status
        $stmt = $conn->prepare($q);
        $stmt->bind_param("ii",$decision[2],$decision[1]);
        $stmt->execute();
        $stmt->close();
        // if 1, add user to the group/create rental
        if($decision[2]==1){
            // Fetch request
            $q = "SELECT * FROM $table WHERE ".$decision[0]."_request_id=".$decision[1];
            $result = mysqli_query($conn, $q);
            $j_req = $result->fetch_assoc();
            //update Group Followers or Rentals
            if($decision[0]=='j'){
                $q = "INSERT INTO Group_Followers (fk_user_id, fk_group_id) VALUES (".$j_req['fk_joiner_id'].",".$j_req['fk_group_id'].")";
                $result = mysqli_query($conn, $q);
                if(!$result) { echo mysqli_error($conn); }
            } else {
                $q = "INSERT INTO Rentals (start_date, end_date, fk_rent_item, fk_rent_renter, fk_rent_owner) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($q);
                $stmt->bind_param("ssiii",$j_req['start_date'],$j_req['end_date'],$j_req['fk_item_id'],$j_req['fk_renter_id'],$j_req['fk_rentee_id']);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
    ?>
    <body>
        <h2>My Requests</h2>
        <!-- Request tab buttons -->
        <div class="groupTab">
            <button class="gTabLinks" onclick="openTab(event, 'to_me')">Incoming Requests</button>
            <button class="gTabLinks" onclick="openTab(event, 'from_me')">Requests I've Sent</button>
        </div>
        <!-- Requests to me -->
        <div id='to_me' class='gTabContent'>
            <h3>Requests to Me</h3>
            <!-- Rent requests -->
            <h4>Rent Requests</h4>
            <table>
                <tr>
                    <th>Item</th>
                    <th>Member</th>
                    <th>StarScore</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                    <th></th>
                </tr>
                <?php 
                $rentToMe = getRentRequests($_SESSION['userId'],$conn);
                while($row = mysqli_fetch_assoc($rentToMe)){
                    echo "<tr>";
                    echo "<td>".$row['item_name']."</td>";
                    echo "<td>".$row['renter_fName']." ".$row['renter_lName']."</td>";
                    $ss = getUserStarScore($row['renter_id'],$conn);
                    echo "<td>".$ss."</td>";
                    echo "<td>".$row['start_date']."</td>";
                    echo "<td>".$row['end_date']."</td>";
                    $stat = getStatus($row['r_status']);
                    echo "<td>".$stat."</td>";
                    // Decide whether to accept rental
                    echo "<td>";
                    echo "<form method='post'>";
                    echo "<input type='radio' name='decide_yn' value='r_".$row['r_request_id']."_1'>Accept ";
                    echo "<input type='radio' name='decide_yn' value='r_".$row['r_request_id']."_2'>Reject ";
                    echo "<input type='submit' name='submit' value='Submit'>";
                    echo "</form>";
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </table>
            <!-- Join requests -->
            <h4>Join Requests</h4>
            <table>
                <tr>
                    <th>Name</th>
                    <th>StarScore</th>
                    <th>Group</th>
                    <th>Status</th>
                </tr>
            <?php 
            $joinToMe = getJoinRequests($_SESSION['userId'],$conn);
            while($row = mysqli_fetch_assoc($joinToMe)){
                echo "<tr>";
                echo "<td>".$row['renter_fName']." ".$row['renter_lName']."</td>";
                $ss = getUserStarScore($row['renter_id'],$conn);
                echo "<td>$ss</td>";
                echo "<td>".$row['group_name']."</td>";
                $stat = getStatus($row['j_status']);
                echo "<td>$stat</td>";
                // Decide whether to accept user
                echo "<td>";
                echo "<form method='post'>";
                echo "<input type='radio' name='decide_yn' value='j_".$row['j_request_id']."_1'>Accept ";
                echo "<input type='radio' name='decide_yn' value='j_".$row['j_request_id']."_2'>Reject ";
                echo "<input type='submit' name='submit' value='Submit'>";
                echo "</form>";
                echo "</td>";
                echo "</tr>";
            }
            ?>
            </table>
        </div>
        <!-- Requests I've sent -->
        <div id='from_me' class='gTabContent'>
            <h3>Requests from Me</h3>
            <!-- Rent requests -->
            <h4>Rent Requests</h4>
            <table>
                <tr>
                    <th>Item</th>
                    <th>Owner</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                </tr>
                <?php
                $rentFromMe = getRentalStatuses($_SESSION['userId'],$conn);
                while($row = mysqli_fetch_assoc($rentFromMe)){
                    echo "<tr>";
                    echo "<td>".$row['item_name']."</td>";
                    echo "<td>".$row['renter_fName']." ".$row['renter_lName']."</td>";
                    echo "<td>".$row['start_date']."</td>";
                    echo "<td>".$row['end_date']."</td>";
                    $stat = getStatus($row['r_status']);
                    echo "<td>".$stat."</td>";
                    echo "</tr>";
                }
                ?>
            </table><br>
            <!-- Join requests -->
            <h4>Join Requests</h4>
            <table>
                <tr>
                    <th>Group Name</th>
                    <th>Status</th>
                </tr>
                <?php
                $joinFromMe = getJoinStatuses($_SESSION['userId'],$conn);
                while($row = mysqli_fetch_assoc($joinFromMe)){
                    echo "<tr>";
                    echo "<td>".$row['group_name']."</td>";
                    $stat = getStatus($row['j_status']);
                    echo "<td>$stat</td>";
                    echo "</tr>";
                }
                ?>
            </table>
        </div>
    </body>
</html>