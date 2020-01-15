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
        <title>New Rental Request - Turn</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <?php
    include "navbar.php";

    if($_POST){
        // Create a new rental request
        $stmt = $conn->prepare("INSERT INTO RentRequest (fk_item_id, fk_renter_id, fk_rentee_id, start_date, end_date) VALUES (?,?,?,?,?)");
        // if(!$stmt){echo mysqli_error($conn);}
        $stmt->bind_param("iiiss",$itemRow['item_id'],$_SESSION['userId'],$itemRow['fk_item_owner'],$_POST['start'],$_POST['end']);
        $stmt->execute();
        $stmt->close();
        header("Location: myRequests.php");
    }
    ?>
    <body>
        <h2>New Rental Request</h2>
        <form method='post'>
            Item: <?php echo $itemRow['item_name'] ?><br>
            Rent start: <input type='date' name='start'><br>
            Rent end: <input type='date' name='end'><br>
            <input type='submit' name='submit' value='Create Request'>
        </form>
    </body>
</html>