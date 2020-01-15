<?php
include_once "db_config.php";
include_once "db_conn.php";
include_once "funcs.php";
session_start();

// If not logged in, send back to login
if(!isset($_SESSION['userId'])){
    header("Location: index.php");
}

$memRow = '';
$groupRow = '';
if(isset($_GET['group_id']) && isset($_GET['member_id'])){
    // Make sure member is part of group
    if(userOfGroup($_GET['member_id'],$_GET['group_id'],$conn)){
        $q = "SELECT * FROM Renters WHERE renter_id=".$_GET['member_id'];
        $result = mysqli_query($conn, $q);
        $memRow = $result->fetch_assoc();
    } else {
        http_response_code(404);
        include('my_404.php');
        die();
    }
    // Make sure current user is leader of group
    $q = "SELECT * FROM Groups WHERE group_id=".$_GET['group_id'];
    $result = mysqli_query($conn, $q);
    $groupRow = $result->fetch_assoc();
    if($_SESSION['userId'] != $groupRow['fk_group_owner']){
        http_response_code(404);
        include('my_404.php');
        die();
    }
    if($_GET['member_id']==$groupRow['fk_group_owner']){
        http_response_code(404);
        include('my_404.php');
        die();
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Remove Member - Turn</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <?php
    include "navbar.php";

    if(isset($_POST['remove_n'])){
        // Go back to group home
        header("Location: group.php?group_id=".$groupRow['group_id']);
    }
    if(isset($_POST['remove_y'])){
        // Remove user from group list
        $q = "DELETE FROM Group_Followers WHERE fk_user_id=".$memRow['renter_id']." AND fk_group_id=".$groupRow['group_id'];
        $result = mysqli_query($conn, $q);
        if(!$result){die("Could not reach database");}
        // Remove user's items from group
        $q = "DELETE FROM Group_Items WHERE fk_group_id=".$groupRow['group_id']." AND fk_item_id IN (SELECT item_id FROM Items JOIN Renters ON fk_item_owner=renter_id WHERE renter_id=".$memRow['renter_id'].")";
        $result = mysqli_query($conn, $q);
        if(!$result){die("Could not reach database");}
        // Return to group page
        header("Location: group.php?group_id=".$groupRow['group_id']);
    }
    ?>
    <body>
        <h2>Remove Member</h2>
        <?php
        echo "Are you sure you want to remove ".$memRow['renter_fName']." ".$memRow['renter_lName']." from ".$groupRow['group_name']."?<br>";
        ?>
        <form method='post'>
            <input type='submit' name="remove_y" value='Yes'>
            <input type='submit' name="remove_n" value='No'>
        </form>
    </body>
</html>