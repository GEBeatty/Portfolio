<?php
include_once "db_config.php";
include_once "db_conn.php";
session_start();

// If not logged in, send back to login
if(!isset($_SESSION['userId'])){
    header("Location: index.php");
}

$groupRow = '';
if(isset($_GET['group_id'])){
    // Get group info
    $q = "SELECT * FROM Groups WHERE group_id=".$_GET['group_id'];;
    $result = $conn->query($q);
    $groupRow = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Send Join Request - Turn</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <?php
    include "navbar.php";
    if(isset($_POST['join_n'])){
        header("Location: group.php?group_id=".$groupRow['group_id']);
    }
    if(isset($_POST['join_y'])){
        // Create new join request
        $stmt = $conn->prepare("INSERT INTO JoinRequest (fk_joiner_id, fk_owner_id, fk_group_id, j_read, j_status) VALUES (?,?,?,0,0)");
        if(!$stmt){ echo mysqli_error($conn);}
        $stmt->bind_param("iii", $_SESSION['userId'], $groupRow['fk_group_owner'], $groupRow['group_id']);
        $stmt->execute();
        $stmt->close();
        header("Location: myRequests.php");
    }
    ?>
    <body>
        <h2>Send Join Request</h2>
        <?php echo "Send request to join ".$groupRow['group_name']."?" ?><br>
        <form method='post'>
            <input type='submit' name='join_y' value='Yes'> 
            <input type='submit' name='join_n' value='No'>
        </form>
    </body>
</html>