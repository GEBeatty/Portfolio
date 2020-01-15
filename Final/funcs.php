<?php
// Test if user is part of a group
function userOfGroup($userId, $groupId, $conn){
    $q = "SELECT * FROM Group_Followers WHERE fk_user_id=$userId AND fk_group_id=$groupId";
    $result = $conn->query($q);
    if($result->num_rows == 0){
        // User is not part of group
        return false;
    }
    // User is part of group
    return true;
}

// Get others' requests to join
function getJoinRequests($userId, $conn){
    $q = "SELECT * FROM JoinRequest JOIN Renters ON fk_joiner_id=renter_id JOIN Groups ON fk_group_id=group_id WHERE fk_owner_id=".$userId." AND j_status=0";
    $result = mysqli_query($conn, $q);
    // if(!$result){ echo mysqli_error($conn);}
    return $result;
}
// Count unread requests
function countJoinRequests($userId, $conn){
    $q = "SELECT COUNT(*) FROM JoinRequest WHERE fk_owner_id=".$userId." AND j_status=0";
    $result = mysqli_query($conn, $q);
    $num = $result->fetch_row();
    return $num[0];
}
// Get own request statuses
function getJoinStatuses($userId, $conn){
    $q = "SELECT * FROM JoinRequest JOIN Groups ON fk_group_id=group_id WHERE fk_joiner_id=".$userId." ORDER BY j_request_id DESC";
    $result = mysqli_query($conn, $q);
    return $result;
}

// Get my rentals
function getRentRequests($userId, $conn){
    $q = "SELECT * FROM RentRequest JOIN Renters ON fk_renter_id=renter_id JOIN Items ON fk_item_id=item_id WHERE fk_rentee_id=".$userId." AND r_status=0";
    $result = mysqli_query($conn, $q);
    // if(!$result){echo mysqli_error($conn);}
    return $result;
}
function countRentRequests($userId, $conn){
    $q = "SELECT COUNT(*) FROM RentRequest WHERE fk_rentee_id=".$userId." AND r_status=0";
    $result = mysqli_query($conn, $q);
    $num = $result->fetch_row();
    return $num[0];
}
// Get own request statuses
function getRentalStatuses($userId, $conn){
    $q = "SELECT * FROM RentRequest JOIN Items ON fk_item_id=item_id JOIN Renters ON fk_rentee_id=renter_id WHERE fk_renter_id=".$userId;
    $result = mysqli_query($conn, $q);
    // if(!$result){echo mysqli_error($conn);}
    return $result;
}

// Get things I'm renting
function getRentals($userId, $conn){
    $q = "SELECT * FROM Rentals JOIN Renters ON fk_rent_owner=renter_id JOIN Items ON fk_rent_item=item_id WHERE fk_rent_renter=".$userId." ORDER BY end_date";
    $result = mysqli_query($conn,$q);
    return $result;
}
// Get things I've rented out
function getRenteds($userId, $conn){
    $q = "SELECT * FROM Rentals JOIN Renters ON fk_rent_renter=renter_id JOIN Items ON fk_rent_item=item_id WHERE fk_rent_owner=".$userId." ORDER BY end_date";
    $result = mysqli_query($conn,$q);
    return $result;
}

function getUserStarScore($userId, $conn){
    $q = "SELECT * FROM Renters WHERE renter_id=".$userId;
    $result = mysqli_query($conn, $q);
    $row = $result->fetch_assoc();
    if($row['num_reviews']==0){
        return 0;
    }
    $starScore = $row['num_stars'] / $row['num_reviews'];
    return $starScore;
}

function getStatus($stat){
    if($stat==0){
        return "<td>Pending</td>";
    } else if ($stat==1){
        return "<td class='item_avail'>Approved</td>";
    } else if ($stat==2){
        return "<td class='item_unavail'>Rejected</td>";
    }
}
?>