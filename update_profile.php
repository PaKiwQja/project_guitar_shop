<?php
session_start();
include("includes/connectdb.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

if(isset($_POST['fullname'])){

    $user_id = $_SESSION['user_id'];
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    $sql = "UPDATE users 
            SET fullname='$fullname',
                email='$email',
                phone='$phone'
            WHERE id='$user_id'";

    mysqli_query($conn,$sql);

    header("Location: profile.php?page=info");
}
?>
