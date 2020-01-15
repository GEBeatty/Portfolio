<?php
    include_once "db_config.php";
    include_once "db_conn.php";
    include_once "funcs.php";
    session_start();
?>
<html>
    <head>
        <meta charset="utf-8">
        <title><?php echo $_SESSION['cat_name'] ?> | New Topic</title>
        <link rel='stylesheet' href='forum_style.css'>

        <script>
            function post_topic(top_id){
                window.location.replace("topic.php?top_id="+top_id+"&top_created=1");
            }

            function cancel_topic() {
                history.go(-1)
            }
        </script>
    </head>
    <body>
        <?php include 'navbar.php';
        if(!isset($_SESSION['user_id'])){ // Must be logged in
            header("Location: index.php");
            exit();
        }
        if($_POST){
            // Prepare variables
            $title = htmlspecialchars($_POST['topic_name']);
            $title = mysqli_real_escape_string($conn, $title);
            $description = htmlspecialchars($_POST['topic_description']);
            $description = mysqli_real_escape_string($conn, $description);
            $cat_id = $_POST['cat_id'];
            $user_id = $_POST['user_id'];
            $date_time = $_POST['date_time'];
            // Create topic in database
            $q = "INSERT INTO Topics (top_name, top_description, fk_cat_id, fk_user_id, date_created, num_replies) VALUES ('$title', '$description', '$cat_id', '$user_id', '$date_time', 0);";
            $result = mysqli_query($conn, $q);
            if(!$result){
                echo "Sorry, there was an error creating your topic.";
            } else {
                $last_id = mysqli_insert_id($conn);
                // // Create first description for topic
                // $q = "INSERT INTO Replies (rep_text, fk_top_id, fk_user_id, date_written) VALUES ('$description', '$last_id', '$user_id', '$date_time');";
                // $result = mysqli_query($conn, $q);
                // if(!$result){
                //     echo "Sorry, there was an error posting your description. ";
                //     // echo mysqli_error($conn);
                // } else {
                    // Update topic count for category
                    $q = "UPDATE Categories SET num_topics = (SELECT COUNT(*) FROM Topics WHERE fk_cat_id='$cat_id') WHERE cat_id = '$cat_id'";
                    $result = mysqli_query($conn, $q);
                    if(!$result){
                        echo "Sorry, there was an error updating our database.".mysqli_error($conn);
                    } else {
                        // Go to topic page
                        echo "<script>post_topic($last_id)</script>";
                    }
                //}
            }
        }
        ?>
        <!-- Information to send: topic name, category id, user id, date created, set replies to 0 -->
        <h2><?php echo "<a href='index.php'>Categories</a> > <a href='category.php?cat_id=".$_SESSION['cat_id']."'>".$_SESSION['cat_name']."</a>"; ?> > New Topic</h2>
        <form action="" method="post" id="topic_form">
            <!-- Information for topic -->
            <input type="text" name="topic_name" placeholder="Topic name"><br>
            <!-- Information for first description -->
            <textarea name="topic_description" form="topic_form" placeholder="Enter body here..."></textarea><br>
            <!-- Other info -->
            <input type="hidden" name="cat_id" <?php echo "value=".$_SESSION['cat_id'] ?> >
            <input type="hidden" name="user_id" <?php echo "value=".$_SESSION['user_id'] ?> >
            <input type="hidden" name="date_time" <?php $date = date('Y-m-d H:i:s', time()); echo "value=".$date;
             ?> >
            <input type="submit" value="Submit">
            <input type="button" value="Cancel" onclick="cancel_topic()">
        </form>
    </body>
</html>