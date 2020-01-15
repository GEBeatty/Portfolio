<?php
    include_once 'db_config.php';
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Uncle Jack's Bear Honey | Store</title>
        <link rel="stylesheet" href="shopstyle.css">
        <link href="https://fonts.googleapis.com/css?family=Rye&display=swap" rel="stylesheet"> 
    </head>
    <body>
        <!-- Form processing -->
        <?php
            if($_POST){
                $cust_name  = htmlspecialchars($_POST['cust_name']);
                $add_street = htmlspecialchars($_POST['add_street']);
                $add_city   = htmlspecialchars($_POST['add_city']);
                $add_state  = htmlspecialchars($_POST['add_state']);
                $add_zip    = htmlspecialchars($_POST['add_zip']);
                $phone      = htmlspecialchars($_POST['phone']);
                $quantity   = htmlspecialchars($_POST['quantity']);
                $delivery   = htmlspecialchars($_POST['delivery']);
                $subscribe  = "0";
                if(isset($_POST['subscribe'])){
                    $subscribe  = "1";
                }
                // Send data to the database
                $connect = mysqli_connect($host,$user,$pass,$dbname);
                if(!$connect){
                    die("Connection failed: " . mysqli_connect_error());
                }
                $query = "INSERT INTO orders (cust_name, cust_address, cust_city, cust_state, zip, phone, quantity, delivery, subscribe, order_date) ".
                        "VALUES ('$cust_name','$add_street','$add_city','$add_state','$add_zip','$phone',$quantity,'$delivery','$subscribe', NOW() )";
                // If the data got sent correctly, set variable to good
                $success = $connect->query($query);
                if($success){
                    echo "<span class='note'>Thank you for your order!</span>";
                    // Clear entry for next order
                    $_POST = array();
                    // I think this is important so people don't accidentally
                    // submit multiple orders. It doesn't clear the $_POST unless
                    // the order goes through.
                } else {
                    echo "<span class='note'>There was an error processing your order.</span>";
                }
                // Else, set variable to not good
            }
        ?>
        <!-- Product page html -->
        <div class="productContainer">
            <div class="bothSides productSide">
                <h1 style="font-family: 'Rye', cursive;">UNCLE JACK'S BEAR HONEY</h1>
                <span style="font-family: 'Rye', cursive;">Made by REAL bears!</span><br><br>
                <img class="productImg" src="bearhoney.png">
                <div class="productDescription"><br>
                    <p>Uncle Jack is back with his classic Bona-Fide Bear Honey! 
                        For years, Uncle Jack and his troop of honey-loving bears 
                        have brought smiles to families all across the sweet globe, 
                        and now's the perfect chance for you to grab a bottle of 
                        your own!</p>
                    <p>Uncle Jack's Bear Honey is made of all-natural bee honey and
                        blended with a host of delicious spices by bears, 
                        for bears (and other mammals too)</p>
                </div>
            </div>
            <div class="bothSides formSide">
                <h2 style="font-family: 'Rye', cursive;">Order now!</h2>
                Please use letters, numbers, spaces, and apostrophes only.
                <!-- Form information goes here -->
                <form action="store.php" method="post">
                    <table name="address">
                        <tr><td>Name:</td>
                        <td><input type="text" name="cust_name" <?php  if(isset($_POST['cust_name'])) echo "value='$cust_name'";?> 
                            pattern="^[A-Za-z ']+$" required></td></tr>
                        <tr><td>Address:</td>
                        <td><input type="text" name="add_street" <?php  if(isset($_POST['add_street'])) echo "value='$add_street'";?> 
                            pattern="^[0-9A-Za-z .'-]+$" required></td></tr>
                        <tr><td>City:</td>
                        <td><input type="text" name="add_city" <?php  if(isset($_POST['add_city'])) echo "value='$add_city'";?> 
                            pattern="^[A-Za-z .'-]+$" required></td></tr>
                        <tr><td>State:</td>
                        <td><select name="add_state">
                            <option value="AL">Alabama</option>
                            <option value="AK">Alaska</option>
                            <option value="AZ">Arizona</option>
                            <option value="AR">Arkansas</option>
                            <option value="CA">California</option>
                            <option value="CO">Colorado</option>
                            <option value="CT">Connecticut</option>
                            <option value="DE">Delaware</option>
                            <option value="DC">District Of Columbia</option>
                            <option value="FL">Florida</option>
                            <option value="GA">Georgia</option>
                            <option value="HI">Hawaii</option>
                            <option value="ID">Idaho</option>
                            <option value="IL">Illinois</option>
                            <option value="IN">Indiana</option>
                            <option value="IA">Iowa</option>
                            <option value="KS">Kansas</option>
                            <option value="KY">Kentucky</option>
                            <option value="LA">Louisiana</option>
                            <option value="ME">Maine</option>
                            <option value="MD">Maryland</option>
                            <option value="MA">Massachusetts</option>
                            <option value="MI">Michigan</option>
                            <option value="MN">Minnesota</option>
                            <option value="MS">Mississippi</option>
                            <option value="MO">Missouri</option>
                            <option value="MT">Montana</option>
                            <option value="NE">Nebraska</option>
                            <option value="NV">Nevada</option>
                            <option value="NH">New Hampshire</option>
                            <option value="NJ">New Jersey</option>
                            <option value="NM">New Mexico</option>
                            <option value="NY">New York</option>
                            <option value="NC">North Carolina</option>
                            <option value="ND">North Dakota</option>
                            <option value="OH">Ohio</option>
                            <option value="OK">Oklahoma</option>
                            <option value="OR">Oregon</option>
                            <option value="PA">Pennsylvania</option>
                            <option value="RI">Rhode Island</option>
                            <option value="SC">South Carolina</option>
                            <option value="SD">South Dakota</option>
                            <option value="TN">Tennessee</option>
                            <option value="TX">Texas</option>
                            <option value="UT">Utah</option>
                            <option value="VT">Vermont</option>
                            <option value="VA">Virginia</option>
                            <option value="WA">Washington</option>
                            <option value="WV">West Virginia</option>
                            <option value="WI">Wisconsin</option>
                            <option value="WY">Wyoming</option>
                        </select></td></tr>
                        <tr><td>Zip:</td>
                        <td><input type="text" name="add_zip" pattern="[0-9]{5}" 
                            <?php  if(isset($_POST['add_zip'])) echo "value='$add_zip'";?> required></td></tr>
                    </table>
                    Phone (format: 123-456-7890):<br>
                    <input type="tel" name="phone" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}"
                        <?php  if(isset($_POST['phone'])) echo "value='$phone'";?> required><br><br>
                    Quantity:<br>
                    <input type="number" name="quantity" min="1" <?php  if(isset($_POST['quantity'])) echo "value='$quantity'";?> required><br>
                    Delivery Type*:<br>
                        <input type="radio" name="delivery" value="usps" required>USPS First Class<br>
                        <input type="radio" name="delivery" value="ups_stan">UPS Standard<br>
                        <input type="radio" name="delivery" value="pidgeon">Carrier Pidgeon<br>
                        <input type="radio" name="delivery" value="bm_exp">Bear Mail Express<br>
                        <input type="radio" name="delivery" value="grizz_par">Grizzly Parcels<br>
                        <br>
                    <input type="checkbox" name="subscribe" 
                        <?php echo (isset($_POST['subscribe'])? 'checked="checked"':'')?>> 
                    Subscribe to Uncle Jack's Grizzly Newsletter?<br><br>
                    <input type="submit" name="submit" value="Submit Order">
                </form>
                <span class="shipping">*Shipping only availible within US</span>
            </div>
        </div>
    </body>
</html>