<?php
include_once "db_config.php";
include_once "db_conn.php";
include_once "funcs.php";
session_start();

// If not logged in, send back to login
if(!isset($_SESSION['userId'])){
    header("Location: index.php");
}

// Get group info
$groupRow = '';
if(isset($_GET['group_id'])){
    $q = "SELECT * FROM Groups WHERE group_id=".$_GET['group_id'];;
    $result = $conn->query($q);
    $groupRow = $result->fetch_assoc();
}
// Is user part of group?
if(!userOfGroup($_SESSION['userId'],$groupRow['group_id'],$conn)){
    http_response_code(404);
    include('my_404.php');
    die();
}
// User can't leave if they're the owner
if($_SESSION['userId']==$groupRow['fk_group_owner']){
    http_response_code(404);
    include('my_404.php');
    die();
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Leave Group - Turn</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <?php
    include "navbar.php";

    if($_POST){
        if(isset($_POST['leave_n'])){
            // Go back to group home
            header("Location: group.php?group_id=".$groupRow['group_id']);
        }
        if(isset($_POST['leave_y'])){
            // Remove user from group list
            $stmt = $conn->prepare("DELETE FROM Group_Followers WHERE fk_user_id=? AND fk_group_id=?");
            $stmt->bind_param("ii",$_SESSION['userId'],$groupRow['group_id']);
            $stmt->execute();
            $stmt->close();
            // Remove user's items from group list
            $stmt = $conn->prepare("DELETE FROM Group_Items WHERE fk_group_id=? AND fk_item_id IN (SELECT item_id FROM Items JOIN Renters ON fk_item_owner=renter_id WHERE renter_id=?)");
            $stmt->bind_param("ii",$groupRow['group_id'],$_SESSION['userId']);
            $stmt->execute();
            $stmt->close();
            header("Location: myGroups.php");
        }
    }
    ?>
    <body>
        <h2>Leave Group</h2>
        <?php echo "Are you sure you want to leave ".$groupRow['group_name']."?"; ?><br>
        <form method='post'>
            <input type='submit' name="leave_y" value="Yes">
            <input type='submit' name="leave_n" value="No">
        </form>
    </body>
</html>