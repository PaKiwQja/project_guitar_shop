<?php
session_start();
include("../includes/connectdb.php");

if(!isset($_SESSION['user_id']) || $_SESSION['role']!='admin'){
    header("Location: ../login.php");
    exit();
}

if(!isset($_GET['id'])){
    header("Location: customers.php");
    exit();
}

$id = intval($_GET['id']);

/* ห้ามลบ admin */
$check = mysqli_query($conn,"SELECT role FROM users WHERE id='$id'");
$user = mysqli_fetch_assoc($check);

if($user['role'] == 'admin'){
    header("Location: customers.php");
    exit();
}

/* ลบลูกค้า */
mysqli_query($conn,"DELETE FROM users WHERE id='$id'");

header("Location: customers.php");
exit();
?>
