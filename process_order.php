<?php
session_start();
include("includes/connectdb.php");

// ป้องกันคนแอบเข้าหน้านี้
if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    
    $user_id = $_SESSION['user_id'];
    $address = $_POST['address']; 
    $payment_method = $_POST['payment_method'];
    $total_price = $_POST['total_price'];
    
    // กำหนดให้สถานะเริ่มต้นเป็น pending เสมอ ไม่ว่าจะจ่ายเงินแบบไหน
    $status = 'pending'; 
    $payment_slip = NULL; 

    // เช็คว่ามาจาก "ซื้อเลย" หรือมาจาก "ตะกร้า"
    $buy_now_id = isset($_POST['buy_now_id']) ? intval($_POST['buy_now_id']) : 0;
    
    $items_to_process = [];
    if($buy_now_id > 0){
        $items_to_process[$buy_now_id] = 1; // ชิ้นเดียว
    } else {
        if(empty($_SESSION['cart'])) { header("Location: index.php"); exit(); }
        $items_to_process = $_SESSION['cart']; // หลายชิ้นจากตะกร้า
    }

    // ==========================================
    // 1. จัดการอัปโหลดไฟล์สลิป 
    // ==========================================
    if($payment_method == 'พร้อมเพย์' && isset($_FILES['payment_slip']) && $_FILES['payment_slip']['error'] == 0){
        $ext = pathinfo($_FILES['payment_slip']['name'], PATHINFO_EXTENSION);
        $new_filename = 'slip_' . time() . '_' . $user_id . '.' . $ext; 
        $upload_path = 'images/slips/' . $new_filename; 

        if(move_uploaded_file($_FILES['payment_slip']['tmp_name'], $upload_path)){
            $payment_slip = $new_filename;
            // ลบคำสั่ง $status = 'paid' ออกไปแล้ว เพื่อให้บิลเป็น pending รอแอดมินมาตรวจสลิป
        }
    }

    // ==========================================
    // 2. บันทึกข้อมูลลงตาราง orders (หัวบิล)
    // ==========================================
    $stmt = $conn->prepare("
        INSERT INTO orders (user_id, total_price, status, shipping_address, payment_method, payment_slip) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("idssss", $user_id, $total_price, $status, $address, $payment_method, $payment_slip);
    $stmt->execute();
    
    $order_id = $stmt->insert_id; 
    $stmt->close();

    // ==========================================
    // 3. บันทึกสินค้าลงตาราง order_items
    // ==========================================
    foreach($items_to_process as $product_id => $qty){
        
        $p_query = mysqli_query($conn, "SELECT price FROM products WHERE id='$product_id'");
        $p_data = mysqli_fetch_assoc($p_query);
        $price = $p_data['price'];

        $stmt_item = $conn->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt_item->bind_param("iiid", $order_id, $product_id, $qty, $price);
        $stmt_item->execute();
        $stmt_item->close();

        mysqli_query($conn, "UPDATE products SET stock = stock - $qty WHERE id='$product_id'");
    }

    // ==========================================
    // 4. ล้างตะกร้า (เฉพาะกรณีที่สั่งซื้อจากตะกร้า)
    // ==========================================
    if($buy_now_id == 0){
        unset($_SESSION['cart']); 
    }
    
    header("Location: profile.php?page=orders");
    exit();
}
?>