<?php
include_once "db_config.php";
include_once "db_conn.php";
include_once "funcs.php";
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
        <title>Search Groups - Turn</title>
        <link rel='stylesheet' href='style.css'>
    </head>
    <?php
    include "navbar.php";
    ?>
    <body>
        <h2>Group Search</h2>
        <!-- Display option to start a new group -->
        <a href="startGroup.php">Start new Group</a>
        <br><br>
        <!-- Select city/type to search groups -->
        <form id='searchForm' method='post'>
            City: 
            <select name='cityList' id='cityList' form='searchForm'>
                <option value='all'>No Preference</option>
                <?php
                while($row = mysqli_fetch_assoc($cityResult)) {
                    echo "<option value=".$row['loc_id'].">".$row['loc_city']." ".$row['loc_state']."</option>";
                }
                ?>
            </select>
            Category: 
            <select name='typeList' id='typeList' form='searchForm'>
                <option value='all'>No Preference</option>
                <?php
                while($row = mysqli_fetch_assoc($typeResult)) {
                    echo "<option value=".$row['type_id'].">".$row['type_name']."</option>";
                }
                ?>
            </select>
            <input type='submit' name='searchSubmit' value='Search'>
        </form>
        <br>
        <?php
        if($_POST){
            // Pull ids from form
            $cityVal = $_POST['cityList'];
            $typeVal = $_POST['typeList'];
            $q = 'SELECT * FROM Groups JOIN Types ON fk_group_type=type_id JOIN Cities ON group_location=loc_id';
            // Different queries depending on values
            // No preferences
            if($cityVal=='all' && $typeVal=='all'){
                // Already good to go
            } else if ($cityVal=='all'){
                $q = $q.' WHERE fk_group_type='.$typeVal;
            } else if ($typeVal=='all'){
                $q = $q.' WHERE group_location='.$cityVal;
            } else {
                $q = $q.' WHERE fk_group_type='.$typeVal.' AND group_location='.$cityVal;
            }
            // Grab groups from database
            $result = mysqli_query($conn, $q);
            if(!$result){ die("Trouble connecting to groups"); }
            else {
                if($result->num_rows == 0){
                    echo "Sorry, we couldn't find any groups with those criteria";
                } else {
                    echo "<table>";
                    echo "<tr>";
                    echo "<th>Name</th>";
                    echo "<th>Location</th>";
                    echo "<th>Category</th>";
                    echo "<th>Member Count</th>";
                    echo "</tr>";
                    // Print out each of the groups
                    while($row = mysqli_fetch_assoc($result)){
                        // Get number of members
                        $q2 = "SELECT COUNT(*) FROM Group_Followers WHERE fk_group_id=".$row['group_id'];
                        $result2 = mysqli_query($conn, $q2);
                        $num_members = $result2->fetch_row();
                        // Create row
                        echo "<tr>";
                        echo "<td><a href='group.php?group_id=".$row['group_id']."' target='_blank'>".$row['group_name']."</a></td>";
                        echo "<td>".$row['loc_city']." ".$row['loc_state']."</td>";
                        echo "<td>".$row['type_name']."</td>";
                        echo "<td><img class='num_mem_icon' src='icons/num_members.png'>".$num_members[0]."</td>";
                        // Note if user is a current member
                        if(userOfGroup($_SESSION['userId'],$row['group_id'],$conn)){
                            echo "<td class='item_avail'>Current Member</td>";
                        }
                        echo "</tr>";
                    }
                    echo "</table>";
                }
            }
        }
        ?>
    </body>
</html>