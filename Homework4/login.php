<?php
    include_once "db_config.php";
    session_start();
?>
<html>
    <head>
        <meta charset="utf-8">
        <title>Community Forums | Login</title>
        <link rel='stylesheet' href='forum_style.css'>

        <script>
            function login_success(){
                window.location.replace("index.php");
            }
        </script>  
    </head>
    <body>
        <?php
        include "navbar.php";

        if($_POST){
            $username = htmlspecialchars($_POST['log_user']);
            // Establish connection
            $conn = mysqli_connect($host,$user,$pass,$dbname);
            if(mysqli_connect_error()){
                die("Database connection error");
            } else {
                $q = "SELECT * FROM Users WHERE username='$username'";
                $result = mysqli_query($conn,$q);
                if(!$result){
                    die("Database query failed");
                } else if (mysqli_num_rows($result) == 0){
                    echo "Username or password is incorrect.";
                } else {
                    $password = htmlspecialchars($_POST['log_pass']);
                    $row = mysqli_fetch_assoc($result);
                    if($password != $row['password']){
                        echo "Username or password is incorrect.";
                    } else {
                        // Successful login
                        $_SESSION['username'] = $username;
                        $_SESSION['user_id'] = $row['userID'];
                        echo "<script>login_success()</script>";
                    }
                }
            }
        }
        ?>
        Login:<br><br>
        <form action="" method="post">
            Username: <input type="text" name="log_user" <?php  if(isset($_POST['log_user'])) echo "value='$username'";?> pattern="^[A-Za-z0-9]+$" required><br><br>
            Password: <input type="password" name="log_pass" pattern="^[A-Za-z0-9]+$" required><br><br>
            <input type="submit" value="Submit">
        </form><br>
    </body>
</html>