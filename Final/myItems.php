<?php
include_once "db_config.php";
include_once "db_conn.php";
session_start();

// If not logged in, send back to login
if(!isset($_SESSION['userId'])){
    header("Location: index.php");
}
// Get list of user's items
$q = "SELECT * FROM Items WHERE fk_item_owner=".$_SESSION['userId'];
$userItems = mysqli_query($conn, $q);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>My Items - Turn</title>
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
        <h2>My Items</h2>
        <a href='createItem.php'>Add a new item</a>
        <br><br>
        <table id="sortTable">
            <thead>
            <tr>
                <th>Name</th>
                <th>Status</th>
                <th>Added Groups</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if($userItems->num_rows == 0){
                echo "You haven't added any items yet.";
            } else {
                while($row = mysqli_fetch_assoc($userItems)){
                    echo "<tr>";
                    // Get groups item is in
                    $q = "SELECT * FROM Items JOIN Group_Items ON item_id=fk_item_id JOIN Groups ON fk_group_id=group_id WHERE item_id=".$row['item_id'];
                    $itemGroups = mysqli_query($conn, $q);
                    if(!$itemGroups){echo mysqli_error($conn); }
                    
                    // List item data
                    echo "<td><a href='item.php?item_id=".$row['item_id']."'>".$row['item_name']."</a></td>";
                    if($row['item_status'] == 1){
                        echo "<td class='item_avail'>Available</td>";
                    } else {echo "<td class='item_unavail'>Unavailable</td>";}
                    echo "<td><ul>";
                        while($g = mysqli_fetch_assoc($itemGroups)){
                            echo "<li>".$g['group_name']."</li>";
                        }
                    echo "</ul></td>";
                    echo "</tr>";
                }
            }
            ?>
            </tbody>
        </table>
    </body>
</html>