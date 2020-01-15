<?php
    include_once "db_config.php";
    include_once "db_conn.php";
    include_once "funcs.php";
    session_start();
?>
<html>
    <head>
        <meta charset="utf-8">
        <title>Community Forums | Home</title>
        <link rel='stylesheet' href='forum_style.css'>
    </head>
    <body>
        <?php
        include "navbar.php";
        // Establish connection
        $conn = mysqli_connect($host,$user,$pass,$dbname);
        if(mysqli_connect_error()){
            die("Error connecting to forums");
        } else {
            // Get list of categories
            $q = "SELECT * FROM Categories";
            $results = mysqli_query($conn,$q);
            if(!$results){
                die("Error querying forums");
            } else {
                echo "<h2>Categories</h2>";
                echo "<table>";
                echo "<tr>";
                echo "<th>Name</th>";
                echo "<th>Description</th>";
                echo "<th>Topics</th>";
                echo "</tr>";
                // Print out each row
                while($row = mysqli_fetch_assoc($results)){
                    echo "<tr>";
                    echo "<td><a href='category.php?cat_id=".$row['cat_id']."'>".$row['cat_name']."</td>";
                    echo "<td>".$row['cat_description']."</td>";
                    echo "<td>".$row['num_topics']."</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        }
        ?>
    </body>
</html>