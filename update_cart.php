<?php
session_start();

$product_id = intval($_POST['product_id']);
$quantity = intval($_POST['quantity']);

if($quantity <= 0){
    unset($_SESSION['cart'][$product_id]);
}else{
    $_SESSION['cart'][$product_id] = $quantity;
}

header("Location: cart.php");
exit();
?>