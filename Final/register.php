<?php
include_once "db_config.php";
include_once "db_conn.php";
session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Register : Turn - Lending Communities</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <?php
    include "navbar.php";

    if($_POST){
        // Does email exist?
        $stmt = $conn->prepare("SELECT * FROM Renters WHERE renter_email = ?");
        $email = htmlspecialchars(mysqli_escape_string($conn, $_POST['r_emailAddress']));
        $stmt->bind_param("s", $email);
        if(!$stmt){ echo "Issue registering email"; exit; }
        $stmt->execute();
        $result = $stmt->get_result();
        // None found
        if($result->num_rows === 0){
            $stmt = $conn->prepare("INSERT INTO Renters (renter_email, renter_pass, renter_fName, renter_lName) VALUES (?, ?, ?, ?)");
            $password = htmlspecialchars(mysqli_escape_string($conn, $_POST['r_password']));
            $hashpass = password_hash($password, PASSWORD_DEFAULT);
            $fName = htmlspecialchars(mysqli_escape_string($conn, $_POST['r_fName']));
            $lName = htmlspecialchars(mysqli_escape_string($conn, $_POST['r_lName']));
            $stmt->bind_param("ssss", $email, $hashpass, $fName, $lName);
            $stmt->execute();
            $stmt->close();
            // Set userId and go to profile
            $_SESSION['userId'] = mysqli_insert_id($conn);
            header("Location: profile.php");
        } else {
            echo "That email already exists";
        }
    }
    ?>
    <body>
        <!-- Login page: person must login to use app -->
        <form action='' method='post'>
            <h2>Register</h2>
            Email: <input type='email' name='r_emailAddress'><br>
            Password: <input type='password' name='r_password'><br>
            <!-- Profile information -->
            First Name: <input type='text' name='r_fName' pattern="^[a-zA-Z]+(([',. -][a-zA-Z ])?[a-zA-Z]*)*$"><br>
            Last Name: <input type='text' name='r_lName' pattern="^[a-zA-Z]+(([',. -][a-zA-Z ])?[a-zA-Z]*)*$"><br>
            <input type='submit' value='Create Account'>
        </form>
    </body>
</html>