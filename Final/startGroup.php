<?php
include_once "db_config.php";
include_once "db_conn.php";
session_start();

// If not logged in, send back to login
if(!isset($_SESSION['userId'])){
    header("Location: index.php");
}

// Get cities
$q = "SELECT * FROM Cities";
$cityResult = mysqli_query($conn, $q);
if(!$cityResult){ die("Error querying database"); }
// Get types
$q = "SELECT * FROM Types";
$typeResult = mysqli_query($conn, $q);
if(!$typeResult){ die("Error querying database"); }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Create Group - Turn</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <?php
    include "navbar.php";

    if($_POST){
        // Grab all values
        $newName = $_POST['newGroupName'];
        $newLoc = $_POST['newGroupCity'];
        $newDes = $_POST['newGroupDes'];
        $newType = $_POST['newGroupType'];
        // Create prepared statement for Groups
        $stmt = $conn->prepare("INSERT INTO Groups (group_name,group_location, group_description, fk_group_type, fk_group_owner) VALUES (?, ?, ?, ?, ?)");
        if(!$stmt){ echo mysqli_error($conn); }
        $stmt->bind_param("sisii", $newName, $newLoc, $newDes, $newType, $_SESSION['userId']);
        $stmt->execute();
        $stmt->close();
        // Get new group's id
        $newId = mysqli_insert_id($conn);
        // Update group members
        $stmt = $conn->prepare("INSERT INTO Group_Followers (fk_user_id, fk_group_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $_SESSION['userId'], $newId);
        $stmt->execute();
        $stmt->close();
        header("Location: group.php?group_id=".$newId);
    }
    ?>
    <body>
        <h2>Create Group</h2>
        <form id='createGroupForm' method='post'>
            Group Name: <input type='text' name='newGroupName' required><br>
            Group Location: 
            <select name='newGroupCity' form='createGroupForm'>
                <?php
                while($row = mysqli_fetch_assoc($cityResult)) {
                    echo "<option value=".$row['loc_id'].">".$row['loc_city']." ".$row['loc_state']."</option>";
                }
                ?>
            </select><br>
            Group Description:<br>
            <textarea name='newGroupDes' rows=4 cols=50 form='createGroupForm'></textarea><br>
            Group Category:
            <select name='newGroupType' id='newGroupType' form='createGroupForm'>
                <?php
                while($row = mysqli_fetch_assoc($typeResult)) {
                    echo "<option value=".$row['type_id'].">".$row['type_name']."</option>";
                }
                ?><br>
            </select><br>
            <input type='submit' name='createSubmit' value="Create Group">
        </form>
    </body>
</html>