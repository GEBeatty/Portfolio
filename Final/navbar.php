<header>
<h1>Turn</h1>
</header>
    <?php
    include_once "funcs.php";

    if(isset($_GET['logout'])){
        if($_GET['logout']==1){
            session_unset();
        }
        unset ($_GET['logout']);
    }
    if(isset($_SESSION['userId'])){
        $q = "SELECT * FROM Renters WHERE renter_id=".$_SESSION['userId'];
        $result = mysqli_query($conn, $q);
        if(!$result){
            die("Error querying database");
        } else {
            $row = mysqli_fetch_assoc($result);
            echo "<a href='profile.php' class='welcome'>Hello ".$row['renter_fName']."!</a></span><br>";
        }
    }
    ?>
<nav>
    <ul>
    <?php
        if (isset($_SESSION['userId'])){
            // Count number of current requests
            $reqs = 0;
            $jreqStr = '';
            $reqs = $reqs + countJoinRequests($_SESSION['userId'],$conn);
            if($reqs > 0){
                $jreqStr = "($reqs)";
            }
            /**
             * PAGES:
             * Search Groups
             * Profile
             * My Items
             * My Groups
             * Logout
             */
            echo "<li><a href='searchGroups.php' class='navi'>Group Search</a></li>";
            // echo "<li><a href='profile.php' class='navi'>My Info</a></li>";
            echo "<li><a href='myRequests.php' class='navi'>My Requests</a></li>";
            echo "<li><a href='myRentals.php' class='navi'>My Rentals</a></li>";
            echo "<li><a href='myItems.php' class='navi'>My Items</a></li>";
            echo "<li><a href='myGroups.php' class='navi'>My Groups</a></li>";
            echo "<li><a href='index.php?logout=1' class='navi'>Logout</a></li>";
        } else {
            echo "<li><a href='register.php' class='navi'>Register</a></li>";
        }
        ?>
    </ul>
</nav>