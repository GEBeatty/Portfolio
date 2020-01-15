<?php
include_once "db_config.php";
include_once "db_conn.php";
include_once "funcs.php";
session_start();

// If not logged in, send back to login
if(!isset($_SESSION['userId'])){
    header("Location: index.php");
}

$groupRow = '';
$groupOwner = '';
$itemResults = '';
$memberResults = '';
if(isset($_GET['group_id'])){
    // Get group info
    $q = "SELECT * FROM Groups WHERE group_id=".$_GET['group_id'];;
    $result = $conn->query($q);
    $groupRow = $result->fetch_assoc();
    // Get owner info
    $q = "SELECT * FROM Renters WHERE renter_id=".$groupRow['fk_group_owner'];
    $result = $conn->query($q);
    $groupOwner = $result->fetch_assoc();
    // Get item info
    $q = "SELECT * FROM Items JOIN Group_Items ON item_id=fk_item_id JOIN Renters ON fk_item_owner=renter_id WHERE fk_group_id=".$groupRow['group_id'];
    $itemResults = $conn->query($q);
    // Get member info
    $q = "SELECT * FROM Renters JOIN Group_Followers ON renter_id=fk_user_id WHERE fk_group_id=".$groupRow['group_id'];
    $memberResults = $conn->query($q);
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title><?php echo $groupRow['group_name'] ?> - Turn</title>
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
        <h2><?php echo $groupRow['group_name'] ?></h2>
        <!-- Group tabs! -->
        <div class="groupTab">
            <button class="gTabLinks" onclick="openTab(event, 'about')">About Us</button>
            <button class="gTabLinks" onclick="openTab(event, 'items')">Item List</button>
            <button class="gTabLinks" onclick="openTab(event, 'members')">Member List</button>
        </div>
        <!-- About tab -->
        <div id='about' class='gTabContent'>
            <h3>About Us</h3>
            <p><?php echo $groupRow['group_description']; ?></p>
            <br>
            <?php
                if(!userOfGroup($_SESSION['userId'],$groupRow['group_id'],$conn)){
                   echo "<a href='joinGroup.php?group_id=".$groupRow['group_id']."'>Request to join this Group!</a>";
                } else {
                    if($_SESSION['userId'] == $groupRow['fk_group_owner']){
                        echo "Group Owner: ".$groupOwner['renter_fName']." ".$groupOwner['renter_lName']."<br><br>";
                        echo "You are the owner of this group.<br>";
                        echo "<a href='editGroup.php?group_id=".$groupRow['group_id']."'>Edit Group</a>";
                        echo "<br><br>";
                        echo "<a href='deleteGroup.php?group_id=".$groupRow['group_id']."'>Delete Group</a>";
                    } else {
                        echo "You are a member of this group<br>";
                        echo "<a href='leaveGroup.php?group_id=".$groupRow['group_id']."'>Leave Group</a>";
                    }                    
                }
            ?>
        </div>
        <!-- Items tab -->
        <div id='items' class='gTabContent'>
            <h3>Group Items</h3>
            <?php
            // Test if user is part of group
            if(!userOfGroup($_SESSION['userId'],$groupRow['group_id'],$conn)){
                echo "You need to be part of the group to see the item list.";
            } else if($itemResults->num_rows == 0) {
                echo "There are no items posted yet.";
            } else {
                echo "<table>";
                // Display group items
                while($row = mysqli_fetch_assoc($itemResults)){
                    echo "<tr>";
                    echo "<td><a href='item.php?item_id=".$row['item_id']."'>".$row['item_name']."</a></td>";
                    echo "<td>".$row['renter_fName']." ".$row['renter_lName']."</td>";
                    if($row['item_status']==1){
                        echo "<td class='item_avail'>Available</td>";
                    } else {
                        echo "<td class='item_unavail'>Unavailable</td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
            }
            ?>
        </div>
        <!-- Members tab -->
        <div id='members' class='gTabContent'>
            <h3>Group Members</h3>
            <?php
            // Test if user is part of group
            if(!userOfGroup($_SESSION['userId'],$groupRow['group_id'],$conn)){
                echo "You need to be part of the group to see the member list.";
            } else {
                echo "<table>";
                // Display members
                while($row = mysqli_fetch_assoc($memberResults)){
                    echo "<tr>";
                    echo "<td>".$row['renter_fName']."</td>";
                    echo "<td>".$row['renter_lName']."</td>";
                    $starScore = getUserStarScore($row['renter_id'],$conn);
                    echo "<td>".$starScore."</td>";
                    if($_SESSION['userId'] == $groupRow['fk_group_owner'] && $_SESSION['userId'] != $row['renter_id']){
                        echo "<td><a href='removeMember.php?group_id=".$groupRow['group_id']."&member_id=".$row['renter_id']."'>Remove member</a></td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
            }
            ?>
        </div>
    </body>
</html>