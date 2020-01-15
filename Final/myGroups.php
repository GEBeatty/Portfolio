<?php
include_once "db_config.php";
include_once "db_conn.php";
session_start();

// If not logged in, send back to login
if(!isset($_SESSION['userId'])){
    header("Location: index.php");
}

// Get user's groups
$q = "SELECT * FROM Groups JOIN Group_Followers ON group_id=fk_group_id JOIN Types ON fk_group_type=type_id JOIN Cities ON group_location=loc_id WHERE fk_user_id=".$_SESSION['userId'];
$groupsResult = mysqli_query($conn, $q);
if(!$groupsResult){ die("Something went wrong with the connection."); }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>My Groups - Turn</title>
        <link rel='stylesheet' href='style.css'>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

        <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.js"></script>

        <script>
            $(document).ready( function () {
                $('#sortTable').DataTable( {
                    "paging": false
                });
            } );
        </script>
    </head>
    <?php
    include "navbar.php";
    ?>
    <body>
        <h2>My Groups</h2>
        <table id='sortTable'>
            <thead>
            <tr>
                <th>Name</th>
                <th>Location</th>
                <th>Category</th>
                <th>Member Count</th>
                <th></th>
            </tr>
            </thead>
            <?php
            if($groupsResult->num_rows == 0){
                echo "You haven't joined any groups yet!";
            } else {
                echo "<tbody>";
                while($row = mysqli_fetch_assoc($groupsResult)){
                    // Get number of members
                    $q2 = "SELECT COUNT(*) FROM Group_Followers WHERE fk_group_id=".$row['group_id'];
                    $result2 = mysqli_query($conn, $q2);
                    $num_members = $result2->fetch_row();
    
                    echo "<tr>";
                    echo "<td><a href='group.php?group_id=".$row['group_id']."'>".$row['group_name']."</a></td>";
                    echo "<td>".$row['loc_city']." ".$row['loc_state']."</td>";
                    echo "<td>".$row['type_name']."</td>";
                    echo "<td><img class='num_mem_icon' src='icons/num_members.png'>".$num_members[0]."</td>";
                    if($_SESSION['userId']==$row['fk_group_owner']){
                        echo "<td class='item_avail'>Owner</td>";
                    }
                    echo "</tr>";
                }
                echo "</tbody>";
            }
            ?>
        </table>
    </body>
</html>