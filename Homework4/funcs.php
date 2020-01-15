<?php
    /* Given a user id, find and return the username
     */
    function findUser($conn, $id){
        $q = "SELECT * FROM Users WHERE userID=".$id;
        $result = mysqli_query($conn,$q);
        $username="";
        if(!$result){
            $username="Unknown";
        } else {
            $row = mysqli_fetch_assoc($result);
            $username = $row['username'];
        }
        return $username;
    }
?>