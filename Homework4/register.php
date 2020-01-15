<?php
    include_once "db_config.php";
    session_start();
?>
<html>
    <head>
        <meta charset="utf-8">
        <title>Community Forums | Register</title>
        <link rel='stylesheet' href='forum_style.css'>

        <script>
            function register_success(){
                window.location.replace("index.php");
            }
        </script>   
    </head>
    <body>
        <?php include "navbar.php";
        ?>
        Register to post topics and replies:<br><br>
        <?php
        if($_POST){
            // Create connection
            $conn = mysqli_connect($host,$user,$pass,$dbname);
            if(mysqli_connect_error()){
                echo "Sorry, our servers aren't working right now.";
            } else {
                // Test for username availability
                $username = htmlspecialchars($_POST['reg_user']);
                $username = mysqli_real_escape_string($conn, $username);
                $q = "SELECT * FROM Users WHERE username='$username'";
                $result = mysqli_query($conn,$q);
                if (!$result){
                    die("Database query failed.");
                    echo "Sorry, our servers aren't working right now.";
                } else {
                    if(mysqli_num_rows($result) > 0){
                        echo "That username is already in use";
                    } else {
                        $password = htmlspecialchars($_POST['reg_pass']);
                        $password = mysqli_real_escape_string($conn, $password);
                        // Insert new user into database
                        $q = "INSERT INTO Users (username, password) VALUES ('$username', '$password')";
                        $result = mysqli_query($conn,$q);
                        if(!$result){
                            die("Database update failed: ".mysqli_error($conn));
                            echo "Sorry, our servers aren't working right now.";
                        } else {
                            // Success, start session and redirect.
                            $q = "SELECT * FROM Users WHERE username='$username'";
                            $result = mysqli_query($conn,$q);
                            if (!$result){
                                die("Database query failed.");
                                echo "Sorry, our servers aren't working right now.";
                            } else {
                                $row = mysqli_fetch_assoc($result);
                                $_SESSION['username'] = $row['username'];
                                $_SESSION['user_id'] = $row['userID'];
                                echo "<script>register_success()</script>";
                            }      
                        }
                    }
                }
            }
        }
        ?>
        <form action="" method="post">
            Username: <input type="text" name="reg_user" <?php  if(isset($_POST['reg_user'])) echo "value='$username'";?> pattern="^[A-Za-z0-9]+$" required><br><br>
            Password: <input type="password" name="reg_pass" pattern="^[A-Za-z0-9]+$" required><br><br>
            <input type="submit" value="Submit">
        </form><br>
        *Please use letters and numbers only
    </body>
</html>