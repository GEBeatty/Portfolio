<?php
include_once "db_config.php";
include_once "db_conn.php";
session_start();

// If not logged in, send back to login
if(!isset($_SESSION['userId'])){
    header("Location: index.php");
}

$itemRow = '';
// Get item by id
if(isset($_GET['item_id'])){
    $q = "SELECT * FROM Items WHERE item_id=".$_GET['item_id'];
    // Should the user be seeing this item?
        // Let's worry about that later...
    $result = mysqli_query($conn, $q);
    $itemRow = $result->fetch_assoc();
}

// Get groups that have item in them
$q = "SELECT * FROM Group_Items WHERE fk_item_id=".$itemRow['item_id'];
$result = mysqli_query($conn, $q);
$itemGroupsArr = array();
while($row = mysqli_fetch_assoc($result)){
    array_push($itemGroupsArr, $row['fk_group_id']);
}

// Get groups that user is part of
$q = "SELECT * FROM Groups JOIN Group_Followers ON group_id=fk_group_id WHERE fk_user_id=".$_SESSION['userId'];
$groupsResult = mysqli_query($conn, $q);
if(!$groupsResult){ die("Something went wrong with the connection."); }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Edit Item - Turn</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <?php
    include "navbar.php";

    if($_POST){
        if(isset($_POST['cancelSubmit'])){
            header("Location: item.php?item_id=".$itemRow['item_id']);
        }
        if(isset($_POST['editSubmit'])){
            // Update existing item profile
            $stmt = $conn->prepare("UPDATE Items SET item_name=?, item_des=?, item_status=? WHERE item_id=".$itemRow['item_id']);
            $stmt->bind_param("ssi",$_POST['editItemName'],$_POST['editItemDes'],$_POST['editItemStatus']);
            $stmt->execute();
            $stmt->close();
            // Remove existing item-group placements
            $stmt = $conn->prepare("DELETE FROM Group_Items WHERE fk_item_id=?");
            $stmt->bind_param("i",$itemRow['item_id']);
            $stmt->execute();
            $stmt->close();
            // Add new item-group placements
            if(isset($_POST['item_groups'])){
                foreach($_POST['item_groups'] as $check) {
                    $q = "INSERT INTO Group_Items (fk_item_id, fk_group_id) VALUES (".$itemRow['item_id'].", ".$check.")";
                    $result = mysqli_query($conn, $q);
                    if(!$result){ echo mysqli_error($conn); die("Could not connect item groups."); }
                }
            }            
            header("Location: item.php?item_id=".$itemRow['item_id']);
        }
    }
    ?>
    <body>
        <h2>Edit Item</h2>
        <form id='editItemForm' method='post'>
            Name: <br>
            <input type="text" name='editItemName' value='<?php if(isset($_POST['editItemName'])) { echo $_POST['editItemName']; } else { echo $itemRow['item_name']; } ?>' required>
            <br>
            Description: <br>
            <textarea name='editItemDes' id='editItemDes' form='editItemForm' rows=4 cols=50 required><?php if(isset($_POST['editItemDes'])) { echo $_POST['editItemDes']; } else { echo $itemRow['item_des']; } ?></textarea>
            <br>
            <select name='editItemStatus' form='editItemForm'>
                <option value=1 
                <?php 
                if(isset($_POST['editItemStatus'])){
                    if($_POST['editItemStatus']==1){
                        echo "selected='selected'"; 
                    } else { echo ""; }
                } else {
                    if($itemRow['item_status']==1){
                        echo "selected='selected'";
                    } else { echo ""; }
                }?>>Available</option>
                <option value=0 
                <?php 
                if(isset($_POST['editItemStatus'])){
                    if($_POST['editItemStatus']==0){
                        echo"selected='selected'"; 
                    } else { echo ""; } 
                } else {
                    if($itemRow['item_status']==0){
                        echo "selected='selected'";
                    } else { echo ""; }
                }?>>Unavailable</option>
            </select>
            <br>
            Groups that can see item:<br>
            <?php
            while($row = mysqli_fetch_assoc($groupsResult)){
                $checkedVal = '';
                if(isset($_POST['item_groups'])){
                    $checkedVal = (in_array($row['group_id'],$_POST['item_groups']) ? "checked='checked'" : '');
                } else {
                    if(!empty($itemGroupsArr)){
                        $checkedVal = (in_array($row['group_id'],$itemGroupsArr)? "checked='checked'" : '');
                    }                    
                }
                echo "<input type='checkbox' name='item_groups[]' value=".$row['group_id']." ".$checkedVal.">".$row['group_name']."<br>";
            }
            ?>
            <br>
            <input type='submit' name='editSubmit' value='Save Item'><br>
            <input type='submit' name='cancelSubmit' value='Cancel'><br>
        </form>
    </body>
</html>