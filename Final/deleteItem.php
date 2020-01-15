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
        <title>Delete Item - Turn</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <?php
    include "navbar.php";

    if($_POST){
        if(isset($_POST['delete_n'])){
            // Go back to previous page
            $str = "Location: item.php?item_id=".$itemRow['item_id'];
            header($str);
        } else if (isset($_POST['delete_y'])){
            // Delete the item from database
            $q = "DELETE FROM Items WHERE item_id=".$itemRow['item_id'];
            $result = mysqli_query($conn, $q);
            if(!$result){ echo mysqli_error($conn); }
            header("Location: myItems.php");
        }
    }
    ?>
    <body>
        <h2>Delete Item</h2>
        Are you sure you would like to delete <?php echo $itemRow['item_name']; ?>?
        <form method='post'>
            <input type='submit' name='delete_y' value="Yes">
            <input type='submit' name='delete_n' value="No">
        </form>
    </body>
</html>