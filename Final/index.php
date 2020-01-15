<?php
include_once "db_config.php";
include_once "db_conn.php";
session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Turn - Lending Communities</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <?php
    include "navbar.php";
    if(isset($_SESSION['userId'])){
        header("Location: profile.php");
    }
    // Test email
    if($_POST){
        // Prepare SELECT statement to get row
        $stmt = $conn->prepare("SELECT * FROM Renters WHERE renter_email = ?");
        $email = htmlspecialchars(mysqli_escape_string($conn, $_POST['emailAddress']));
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        // Compare password hash
        $password = htmlspecialchars(mysqli_escape_string($conn, $_POST['password']));
        if(password_verify($password, $row['renter_pass'])){
            // Correct login, setup session, navigate to profile
            $_SESSION['userId'] = $row['renter_id'];
            header("Location: profile.php");
        } else {
            echo "Username or password may be incorrect";
        }
    }
    ?>
    <body>
        <!-- Login page: person must login to use app -->
        <form action='' method='post'>
            <h2>Login</h2>
            Email: <input type='email' name='emailAddress'><br>
            Password: <input type='password' name='password'><br>
            <input type='submit' value='Login'>
        </form>
    </body>
</html>