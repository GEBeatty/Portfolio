<?php
    include_once "db_config.php";
    include_once "db_conn.php";
    include_once "funcs.php";
    session_start();
    if(isset($_GET['cat_id'])){
        $q = "SELECT * FROM Categories WHERE cat_id=".$_GET['cat_id'];
        $result = mysqli_query($conn,$q);
        if(!$result){
            die("Error querying database");
        } else {
            $row = mysqli_fetch_assoc($result);
            $_SESSION['cat_id'] = $row['cat_id'];
            $_SESSION['cat_name'] = $row['cat_name'];
        }
    }
?>
<html>
    <head>
        <meta charset="utf-8">
        <title><?php echo $_SESSION['cat_name'] ?> | Community Forums</title>
        <link rel='stylesheet' href='forum_style.css'>

        <script>
            function create_topic() {
                window.location.href = "create_topic.php"
            }
        </script>
    </head>
    <body>
        <?php include 'navbar.php';
        // List breadcrumb trail
        echo "<h2><a href='index.php'>Categories</a> > ".$_SESSION['cat_name']."</h2>";
        // Get list of topics
        $q = "SELECT * FROM Topics WHERE fk_cat_id=".$_SESSION['cat_id'];
        $results = mysqli_query($conn,$q);
        if(!$results){
            die("Error querying forums");
        } else {            
            // If logged in, can create new topic
            if(isset($_SESSION['username'])){
                echo "<input type='button' value='Create new Topic' onclick='create_topic()'><br><br>";
            }
            echo "<table>";
            echo "<tr>";
            echo "<th>Name</th>";
            echo "<th>Author</th>";
            echo "<th>Description</th>";
            echo "<th>Created</th>";
            echo "<th>Replies</th>";
            echo "</tr>";
            // Print out each topic
            while($row = mysqli_fetch_assoc($results)){
                echo "<tr>";
                echo "<td><a href='topic.php?top_id=".$row['top_id']."'>".$row['top_name']."</td>";
                $author = findUser($conn,$row['fk_user_id']);
                echo "<td>".$author."</td>";
                $top_des = substr($row['top_description'],0,25);
                if(strlen($top_des) > 24){
                    echo "<td>".$top_des."...</td>";
                } else {
                    echo "<td>".$top_des."</td>";
                }
                echo "<td>".$row['date_created']."</td>";
                echo "<td>".$row['num_replies']."</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        ?>
    </body>
</html>