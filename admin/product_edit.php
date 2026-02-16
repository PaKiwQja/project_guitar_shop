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

$product_q = mysqli_query($conn,"SELECT * FROM products WHERE id='$id'");
$product = mysqli_fetch_assoc($product_q);

if(isset($_POST['update'])){
    $name = $_POST['product_name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $desc = $_POST['description'];

    mysqli_query($conn,"
        UPDATE products SET
        product_name='$name',
        price='$price',
        stock='$stock',
        description='$desc'
        WHERE id='$id'
    ");

    header("Location: products.php");
    exit();
}
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>แก้ไขสินค้า</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="p-5">

<h3>แก้ไขสินค้า</h3>

<form method="POST">
    <div class="mb-3">
        <label>ชื่อสินค้า</label>
        <input type="text" name="product_name"
               value="<?=$product['product_name']?>"
               class="form-control">
    </div>

    <div class="mb-3">
        <label>ราคา</label>
        <input type="number" name="price"
               value="<?=$product['price']?>"
               class="form-control">
    </div>

    <div class="mb-3">
        <label>จำนวน</label>
        <input type="number" name="stock"
               value="<?=$product['stock']?>"
               class="form-control">
    </div>

    <div class="mb-3">
        <label>รายละเอียด</label>
        <textarea name="description"
                  class="form-control"><?=$product['description']?></textarea>
    </div>

    <button type="submit" name="update" class="btn btn-primary">
        บันทึก
    </button>

    <a href="products.php" class="btn btn-secondary">
        ยกเลิก
    </a>
</form>

</body>
</html>