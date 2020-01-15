<?php
    include_once "db_config.php";
    include_once "db_conn.php";
    include_once "funcs.php";
    session_start();
    if(isset($_GET['top_id'])){
        $q = "SELECT * FROM Topics WHERE top_id=".$_GET['top_id'];
        $result = mysqli_query($conn,$q);
        if(!$result){
            echo mysqli_error($conn);
            die("Error querying database");
        } else {
            $row = mysqli_fetch_assoc($result);
            $_SESSION['top_id'] = $row['top_id'];
            $_SESSION['top_name'] = $row['top_name'];
        }
    }
    // if(!isset($_GET['top_created'])){

    // }
?>
<html>
    <head>
        <meta charset="utf-8">
        <title><?php echo $_SESSION['top_name'] ?> | <?php echo $_SESSION['cat_name'] ?></title>
        <link rel='stylesheet' href='forum_style.css'>
    </head>
    <body>
        <?php include 'navbar.php';
        // If reply posted, add to database
        if($_POST){
            $reply = htmlspecialchars($_POST['reply']);
            $reply = mysqli_real_escape_string($conn, $reply);
            $top_id = $_POST['top_id'];
            $user_id = $_POST['user_id'];
            $date_time = $_POST['date_time'];
            // Create reply
            $q = "INSERT INTO Replies (rep_text, fk_top_id, fk_user_id, date_written) VALUES ('$reply', '$top_id', '$user_id', '$date_time')";
            $result = mysqli_query($conn, $q);
            if(!$result){
                echo "There was an error creating your reply.";
            } else {
                // Update reply count in topics
                $q = "UPDATE Topics SET num_replies = num_replies + 1 WHERE top_id = '$top_id'";
                $result = mysqli_query($conn, $q);
                if(!$result){
                    echo "There was an error updating our database.";
                } else {
                    // Success! Yay!
                }
            }
        }
        // Post breadcrumb trail
        echo "<h2><a href='index.php'>Categories</a> > <a href='category.php?cat_id=".$_SESSION['cat_id']."'>".$_SESSION['cat_name']."</a> > ".$_SESSION['top_name']."</h2>";
        // Post full topic description
        $q = "SELECT * FROM Topics WHERE top_id=".$_SESSION['top_id'];
        $results = mysqli_query($conn, $q);
        if(!$results){
            die("Error querying topic");
        } else {
            $row = mysqli_fetch_assoc($results);
            echo "<h3>".findUser($conn, $row['fk_user_id'])."</h3>";
            echo "<h4>".$row['top_description']."</h4>";
        }
        // Get list of replies
        $q = "SELECT * FROM Replies WHERE fk_top_id=".$_SESSION['top_id'];
        $results = mysqli_query($conn,$q);
        if(!$results){
            die("Error querying forums");
        } else {
            echo "<table>";
            echo "<tr>";
            echo "<th>User</th>";
            echo "<th>Message</th>";
            echo "<th>Date</th>";
            echo "</tr>";
            // Print out each topic
            while($row = mysqli_fetch_assoc($results)){
                echo "<tr>";                
                $username = findUser($conn,$row['fk_user_id']);
                echo "<td>".$username."</td>";
                echo "<td>".$row['rep_text']."</td>";
                echo "<td>".$row['date_written']."</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        echo "<br><br>";
        // If user is logged in, they can reply to topic
        if (isset($_SESSION['username'])){
            echo "<form action='topic.php?top_id=".$_SESSION['top_id']."' method='post' id='reply_form'>";
            echo '<textarea name="reply" form="reply_form" placeholder="Type reply here..."></textarea><br>';
            echo '<input type="hidden" name="top_id" value="'.$_SESSION['top_id'].'">';
            echo '<input type="hidden" name="user_id" value="'.$_SESSION['user_id'].'">';
            echo '<input type="hidden" name="date_time" value="'.date('Y-m-d H:i:s', time()).'">';
            echo '<input type="submit" value="Post">';
            echo "</form>";
        }
        ?>
    </body>
</html>