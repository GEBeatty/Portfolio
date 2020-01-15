<?php
include_once "db_config.php";
include_once "db_conn.php";
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
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Delete Group - Turn</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <?php
    include "navbar.php";

    if($_POST){
        if(isset($_POST['delGroup_n'])){
            // Go back to group page
            header("Location: group.php?group_id=".$groupRow['group_id']);
        }
        if(isset($_POST['delGroup_y'])){
            // Delete group (item and member relations should cascade)
            $stmt = $conn->prepare("DELETE FROM Groups WHERE group_id=?");
            $stmt->bind_param("i",$groupRow['group_id']);
            $stmt->execute();
            $stmt->close();
            header("Location: myGroups.php");
        }
    }
    ?>
    <body>
        <h2>Delete Group</h2>
        <?php echo "Are you sure you want to delete ".$groupRow['group_name']."?" ?>
        <br>
        <form method='post'>
            <input type='submit' name='delGroup_y' value="Yes"> 
            <input type='submit' name='delGroup_n' value="No">
        </form>
    </body>
</html>