<?php
session_start();
include("includes/connectdb.php");

// ป้องกันคนแอบเข้า หรือไม่ได้ล็อกอิน
if(!isset($_SESSION['user_id']) || !isset($_GET['id'])){
    header("Location: index.php");
    exit();
}

$order_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// ดึงข้อมูลบิลมาเช็คก่อนว่าเป็นของ user คนนี้จริงๆ และสถานะต้องเป็น pending (รอตรวจสอบ) เท่านั้นถึงจะยกเลิกได้
$check_query = mysqli_query($conn, "SELECT id FROM orders WHERE id='$order_id' AND user_id='$user_id' AND status='pending'");

if(mysqli_num_rows($check_query) > 0){
    
    // 1. คืนสต็อกสินค้ากลับเข้าร้าน
    $items_query = mysqli_query($conn, "SELECT product_id, quantity FROM order_items WHERE order_id='$order_id'");
    while($item = mysqli_fetch_assoc($items_query)){
        $p_id = $item['product_id'];
        $qty = $item['quantity'];
        mysqli_query($conn, "UPDATE products SET stock = stock + $qty WHERE id='$p_id'");
    }

    // 2. เปลี่ยนสถานะบิลเป็น cancelled
    mysqli_query($conn, "UPDATE orders SET status='cancelled' WHERE id='$order_id'");
}

// เด้งกลับไปหน้าประวัติการสั่งซื้อ (แท็บยกเลิก)
header("Location: profile.php?page=orders&status=cancelled");
exit();
?>