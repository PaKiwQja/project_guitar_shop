<?php
session_start();
include("includes/connectdb.php");

if(!isset($_POST['product_id'])){
    header("Location: index.php");
    exit();
}

$id  = intval($_POST['product_id']);
$qty = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

if($qty < 1){
    $qty = 1;
}

if(!isset($_SESSION['cart'])){
    $_SESSION['cart'] = [];
}

if(isset($_SESSION['cart'][$id])){
    $_SESSION['cart'][$id] += $qty;
}else{
    $_SESSION['cart'][$id] = $qty;
}

/* กลับหน้าเดิม */
$redirect = $_SERVER['HTTP_REFERER'];

if(strpos($redirect, '?') !== false){
    $redirect .= "&added=1";
}else{
    $redirect .= "?added=1";
}

header("Location: " . $redirect);
exit();