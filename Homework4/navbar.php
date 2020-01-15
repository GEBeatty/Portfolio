<header>
<h1>Community Forums</h1>
</header>

<nav>
    <?php
    if(isset($_GET['logout'])){
        if($_GET['logout']==1){
            session_unset();
        }
        unset ($_GET['logout']);
    }
    if(isset($_SESSION['username'])){
        echo "<span class='welcome'>Hello, ".$_SESSION['username']."!</span>";
    }
    ?>
    <ul>
    <?php
        echo "<li><a href='index.php' class='navi'>Home</a></li>";
        if (isset($_SESSION['username'])){
            // echo "<li><a href='javascript:logout()'>Logout</a></li>";
            echo "<li><a href='index.php?logout=1' class='navi'>Logout</a></li>";
        } else {
            echo "<li><a href='login.php' class='navi'>Login</a></li>";
            echo "<li><a href='register.php' class='navi'>Register</a></li>";
        }
        ?>
    </ul>
</nav>