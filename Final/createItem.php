<?php
include_once "db_config.php";
include_once "db_conn.php";
session_start();

// If not logged in, send back to login
if(!isset($_SESSION['userId'])){
    header("Location: index.php");
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
        <title>Create Item - Turn</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <?php
    include "navbar.php";

    if($_POST){
        // Add item to Items table
        $stmt = $conn->prepare("INSERT INTO Items (item_name, item_des, fk_item_owner, item_status) VALUES (?, ?, ?, ?)");
        $itemName = $_POST['newItemName'];
        $itemDes = $_POST['newItemDes'];
        $itemStatus = $_POST['newItemStatus'];
        $stmt->bind_param("ssii", $itemName, $itemDes, $_SESSION['userId'], $itemStatus);
        $stmt->execute();
        $stmt->close();
        $lastItemId = mysqli_insert_id($conn);
        // Connect item with groups
        if(isset($_POST['item_groups'])){
            foreach($_POST['item_groups'] as $check) {
                $q = "INSERT INTO Group_Items (fk_item_id, fk_group_id) VALUES (".$lastItemId.", ".$check.")";
                $result = mysqli_query($conn, $q);
                if(!$result){ echo mysqli_error($conn); die("Could not create item."); }
            }
        }
        header("Location: myItems.php");
    }
    ?>
    <body>
        <h2>Create New Item</h2>
        <form id='CreateItemForm' method='post'>
            Name: <br>
            <input type="text" name='newItemName' value='<?php if(isset($_POST['newItemName'])) { echo $_POST['newItemName']; } ?>'  required>
            <br>
            Description: <br>
            <textarea name='newItemDes' id='newItemDes' form='CreateItemForm' rows=4 cols=50 required><?php if(isset($_POST['newItemDes'])) { echo $_POST['newItemDes']; } ?></textarea>
            <br>
            Status:
            <select name='newItemStatus' form='CreateItemForm'>
                <option value=1 
                <?php 
                if(isset($_POST['newItemStatus'])){
                    if($_POST['newItemStatus']==1){
                        echo "selected='selected'"; 
                    } else { echo ""; }
                }?>>Available</option>
                <option value=0 
                <?php 
                if(isset($_POST['newItemStatus'])){
                    if($_POST['newItemStatus']==0){
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
                }
                echo "<input type='checkbox' name='item_groups[]' value=".$row['group_id']." ".$checkedVal.">".$row['group_name']."<br>";
            }
            ?>
            <br>
            <input type='submit' name='newItemSubmit' value='Create Item'>
        </form>
    </body>
</html>