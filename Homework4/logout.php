<!-- NO longer in use -->

<?php
    include_once "db_config.php";
    session_start();
?>
<html>
    <head>
        <meta charset="utf-8">
        <title>Community Forums | Logout</title>

        <script>
        function logout() {
            window.location.replace("index.php?logout=1")
        }

        function stayin() {
            history.go(-1)
        }
        </script>
    </head>
    <body>
        <?php include "navbar.php";
        ?>

        Are you sure you want to log out?
        <button onclick="logout()">Yes</button>
        <button onclick="stayin()">No</button>
    </body>
</html>