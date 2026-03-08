<?php
session_start();
include("includes/connectdb.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

if(!isset($_GET['id'])){
    header("Location: profile.php?page=address");
    exit();
}

$address_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

/* ลบเฉพาะของ user ตัวเอง */
mysqli_query($conn,"
    DELETE FROM user_addresses
    WHERE id='$address_id' AND user_id='$user_id'
");

header("Location: profile.php?page=address&deleted=1");
exit();
?>