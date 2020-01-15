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
        <title>Edit Group - Turn</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <?php
    include "navbar.php";
    if($_POST){
        if(isset($_POST['edit_n'])){
            // Go back to group
            header("Location: group.php?group_id=".$groupRow['group_id']);
        }
        if(isset($_POST['edit_y'])){
            // Update existing profile
            $stmt = $conn->prepare("UPDATE Groups SET group_name=?, group_location=?, group_description=?, fk_group_type=? WHERE group_id=".$groupRow['group_id']);
            if(!$stmt){ echo $conn->error; }
            $stmt->bind_param("sisi",$_POST['editGroupName'],$_POST['editGroupCity'],$_POST['editGroupDes'],$_POST['editGroupType']);
            if(!$stmt){ echo $conn->error; }
            $stmt->execute();
            if(!$stmt){ echo $conn->error; }
            $stmt->close();
            // Go back to group
            header("Location: group.php?group_id=".$groupRow['group_id']);
        }
    }
    ?>
    <body>
        <h2>Edit Group</h2>
        <form id='editGroupForm' method='post'>
            Group Name: <input type='text' name='editGroupName' value='<?php if(isset($_POST['editGroupName'])){ echo $_POST['editGroupName'];} else { echo $groupRow['group_name']; } ?>' required><br>
            Group Location: 
            <select name='editGroupCity' form='editGroupForm'>
                <?php
                while($row = mysqli_fetch_assoc($cityResult)) {
                    // Check if checked
                    $check = '';
                    if(isset($_POST['editGroupCity'])){
                        if($_POST['editGroupCity']==$row['loc_id']){
                            $check = "selected='selected'";
                        } else { echo ""; }
                    } else {
                        if($groupRow['group_location']==$row['loc_id']){
                            $check = "selected='selected'";
                        } else { echo ""; }
                    }
                    echo "<option value=".$row['loc_id']." ".$check.">".$row['loc_city']." ".$row['loc_state']."</option>";
                }
                ?>
            </select><br>
            Group Description:<br>
            <textarea name='editGroupDes' form='editGroupForm' rows=4 cols=50><?php if(isset($_POST['editGroupDes'])){ echo $_POST['editGroupDes']; } else { echo $groupRow['group_description']; } ?></textarea><br>
            Group Category:
            <select name='editGroupType' form='editGroupForm'>
                <?php
                while($row = mysqli_fetch_assoc($typeResult)) {
                    // Check if checked
                    $check = '';
                    if(isset($_POST['editGroupType'])){
                        if($_POST['editGroupType']==$row['type_id']){
                            $check = "selected='selected'";
                        } else { echo ""; }
                    } else {
                        if($groupRow['fk_group_type']==$row['type_id']){
                            $check = "selected='selected'";
                        } else { echo ""; }
                    }

                    echo "<option value=".$row['type_id']." ".$check.">".$row['type_name']."</option>";
                }
                ?>
            </select><br>
            <input type='submit' name='edit_y' value='Save Group'><br>
            <input type='submit' name='edit_n' value='Cancel'>
        </form>
    </body>
</html>