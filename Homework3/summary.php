<?php
    include_once 'db_config.php';
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Uncle Jack's Bear Honey | Order List</title>
        <link rel="stylesheet" href="shopstyle.css">
        <link href="https://fonts.googleapis.com/css?family=Rye&display=swap" rel="stylesheet"> 
    </head>
    <body class="order_list">
        <!-- Form processing -->
        <?php
            // Send data to the database
            $connect = mysqli_connect($host,$user,$pass,$dbname);
            if(!$connect){
                die("Connection failed: " . mysqli_connect_error());
            }
            echo "<h2>Order Tables<h2>";

            /* Newest first
            */
            echo "<h3>Newest Order First</h3>";
            $query = "SELECT * FROM orders ORDER BY order_date DESC";
            $result = $connect->query($query);
            if(!$result){
                echo "Something went wrong :(";
            }
            // Prepare tables
            echo "<table>";
            echo "<tr>";
                echo "<th>Name</th>";
                echo "<th>Address</th>";
                echo "<th>City</th>";
                echo "<th>State</th>";
                echo "<th>Zip</th>";
                echo "<th>Phone</th>";
                echo "<th>Quantity</th>";
                echo "<th>Delivery</th>";
                echo "<th>Subscribe</th>";
                echo "<th>Order Date</th>";
            echo "</tr>";
            // If there are rows to read, read them
            if($result->num_rows > 0){
                while($row = $result->fetch_assoc()){
                    echo "<tr>";
                    echo "<td>".$row["cust_name"]."</td>";
                    echo "<td>".$row["cust_address"]."</td>";
                    echo "<td>".$row["cust_city"]."</td>";
                    echo "<td>".$row["cust_state"]."</td>";
                    echo "<td>".$row["zip"]."</td>";
                    echo "<td>".$row["phone"]."</td>";
                    echo "<td>".$row["quantity"]."</td>";
                    echo "<td>".$row["delivery"]."</td>";
                    echo "<td>".$row["subscribe"]."</td>";
                    echo "<td>".$row["order_date"]."</td>";
                    echo "</tr>";
                }
            }
            echo "</table>";

            /* Oldest first
            */
            echo "<h3>Oldest Order First</h3>";
            $query = "SELECT * FROM orders ORDER BY order_date";
            // If the data got sent correctly, set variable to good
            $result = $connect->query($query);
            if(!$result){
                echo "Something went wrong :(";
            }
            // Prepare tables
            echo "<table>";
            echo "<tr>";
                echo "<th>Name</th>";
                echo "<th>Address</th>";
                echo "<th>City</th>";
                echo "<th>State</th>";
                echo "<th>Zip</th>";
                echo "<th>Phone</th>";
                echo "<th>Quantity</th>";
                echo "<th>Delivery</th>";
                echo "<th>Subscribe</th>";
                echo "<th>Order Date</th>";
            echo "</tr>";
            // If there are rows to read, read them
            if($result->num_rows > 0){
                while($row = $result->fetch_assoc()){
                    echo "<tr>";
                    echo "<td>".$row["cust_name"]."</td>";
                    echo "<td>".$row["cust_address"]."</td>";
                    echo "<td>".$row["cust_city"]."</td>";
                    echo "<td>".$row["cust_state"]."</td>";
                    echo "<td>".$row["zip"]."</td>";
                    echo "<td>".$row["phone"]."</td>";
                    echo "<td>".$row["quantity"]."</td>";
                    echo "<td>".$row["delivery"]."</td>";
                    echo "<td>".$row["subscribe"]."</td>";
                    echo "<td>".$row["order_date"]."</td>";
                    echo "</tr>";
                }
            }
            echo "</table>";

            /* Quantity first
            */
            echo "<h3>Largest Quantity First</h3>";
            $query = "SELECT * FROM orders ORDER BY quantity DESC";
            // If the data got sent correctly, set variable to good
            $result = $connect->query($query);
            if(!$result){
                echo "Something went wrong :(";
            }
            // Prepare tables
            echo "<table>";
            echo "<tr>";
                echo "<th>Name</th>";
                echo "<th>Address</th>";
                echo "<th>City</th>";
                echo "<th>State</th>";
                echo "<th>Zip</th>";
                echo "<th>Phone</th>";
                echo "<th>Quantity</th>";
                echo "<th>Delivery</th>";
                echo "<th>Subscribe</th>";
                echo "<th>Order Date</th>";
            echo "</tr>";
            // If there are rows to read, read them
            if($result->num_rows > 0){
                while($row = $result->fetch_assoc()){
                    echo "<tr>";
                    echo "<td>".$row["cust_name"]."</td>";
                    echo "<td>".$row["cust_address"]."</td>";
                    echo "<td>".$row["cust_city"]."</td>";
                    echo "<td>".$row["cust_state"]."</td>";
                    echo "<td>".$row["zip"]."</td>";
                    echo "<td>".$row["phone"]."</td>";
                    echo "<td>".$row["quantity"]."</td>";
                    echo "<td>".$row["delivery"]."</td>";
                    echo "<td>".$row["subscribe"]."</td>";
                    echo "<td>".$row["order_date"]."</td>";
                    echo "</tr>";
                }
            }
            echo "</table>";
        ?>
    </body>
</html>