<?php
include_once "db_config.php";
include_once "db_conn.php";
session_start();

// If not logged in, send back to login
if(!isset($_SESSION['userId'])){
    header("Location: index.php");
// Otherwise set up user
$row = '';
$starScore;
} else {
    $stmt = $conn->prepare("SELECT * FROM Renters WHERE renter_id = ?");
    $stmt->bind_param("i", $_SESSION['userId']);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows === 0){ header("Location: index.php "); }
    $row = $result->fetch_assoc();
    if($row['num_reviews'] == 0){
        $starScore = 0;
    } else {
        $starScore = $row['num_stars'] / $row['num_reviews'];
    }
}
$stmt->close();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>My Profile : Turn - Lending Communities</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <?php
    include "navbar.php";
    ?>
    <body>
        <h2>My Info</h2>
        <br>
        Name: <?php echo $row['renter_fName']." ".$row['renter_lName']; ?>
        <br>
        Current star score: <?php echo $starScore; ?>
    </body>
</html>