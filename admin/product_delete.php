<?php
session_start();
include("../includes/connectdb.php");

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit();
}

if(!isset($_GET['id'])){
    header("Location: products.php");
    exit();
}

$id = intval($_GET['id']);

/* ลบรูปสินค้าใน product_images ก่อน */
mysqli_query($conn,"DELETE FROM product_images WHERE product_id = '$id'");

/* ลบสินค้า */
mysqli_query($conn,"DELETE FROM products WHERE id = '$id'");

header("Location: products.php");
exit();
?>
