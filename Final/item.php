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
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title><?php echo $itemRow['item_name'] ?> - Turn</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <?php
    include "navbar.php";
    ?>
    <body>
        <h2>Item Description</h2>
        Name: <?php echo $itemRow['item_name'] ?><br>
        Description: <?php echo $itemRow['item_des'] ?><br>
        Status: <?php
            if($itemRow['item_status'] == 1){
                echo "Available";
            } else {
                echo "Unavailable";
            }
        ?>
        <br>
        Groups listed: <br>
        <ul>
            <?php
            $q2 = "SELECT * FROM Groups JOIN Group_Items ON group_id=fk_group_id WHERE fk_item_id=".$itemRow['item_id'];
            $result2 = mysqli_query($conn, $q2);
            while($row = mysqli_fetch_assoc($result2)){
                echo "<li>".$row['group_name']."</li>";
            }
            ?>
        </ul>
        <br> 
        <?php
        if($_SESSION['userId'] != $itemRow['fk_item_owner']){
            if($itemRow['item_status'] != 0){
                echo "<a href='newRentRequest.php?item_id=".$itemRow['item_id']."'>Send a Rent Request</a>";
            }
        }
        if($_SESSION['userId'] == $itemRow['fk_item_owner']){
            // Option to delete or edit item
            echo "<a href='editItem.php?item_id=".$itemRow['item_id']."'>Edit Item</a><br><br>";
            echo "<a href='deleteItem.php?item_id=".$itemRow['item_id']."'>Delete Item</a><br><br>";
        }
        ?>
    </body>
</html>