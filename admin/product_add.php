<?php
session_start();
include("../includes/connectdb.php");

/* ตรวจสิทธิ์ */
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit();
}

/* ดึงหมวดหมู่ */
$cat_query = mysqli_query($conn,"SELECT * FROM categories");

/* บันทึกข้อมูล */
if(isset($_POST['submit'])){

    $product_name = mysqli_real_escape_string($conn,$_POST['product_name']);
    $description  = mysqli_real_escape_string($conn,$_POST['description']);
    $price        = $_POST['price'];
    $stock        = $_POST['stock'];
    $category_id  = $_POST['category_id'];
    $brand        = mysqli_real_escape_string($conn,$_POST['brand']);

    /* อัปโหลดรูป */
    $image_name = "";
    if($_FILES['image']['name'] != ""){
        $image_name = time() . "_" . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'],"../images/".$image_name);
    }

    $sql = "INSERT INTO products
            (product_name,description,price,stock,image,category_id,brand)
            VALUES
            ('$product_name','$description','$price','$stock',
             '$image_name','$category_id','$brand')";

    mysqli_query($conn,$sql);

    header("Location: products.php");
    exit();
}
?>

<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>เพิ่มสินค้า</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
body{font-family:'Kanit',sans-serif;background:#f4f7f6;}
.card-custom{background:#fff;border-radius:16px;border:none;box-shadow:0 5px 15px rgba(0,0,0,0.03);}
</style>
</head>

<body>

<div class="container py-5">
<div class="row justify-content-center">
<div class="col-md-8">

<div class="card-custom p-4">

<h3 class="fw-bold mb-4">เพิ่มสินค้าใหม่ 🛒</h3>

<form method="POST" enctype="multipart/form-data">

<div class="mb-3">
<label class="form-label">ชื่อสินค้า</label>
<input type="text" name="product_name" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">รายละเอียด</label>
<textarea name="description" class="form-control" rows="3"></textarea>
</div>

<div class="row">
<div class="col-md-6 mb-3">
<label class="form-label">ราคา</label>
<input type="number" step="0.01" name="price" class="form-control" required>
</div>

<div class="col-md-6 mb-3">
<label class="form-label">จำนวนคงเหลือ</label>
<input type="number" name="stock" class="form-control" required>
</div>
</div>

<div class="row">
<div class="col-md-6 mb-3">
<label class="form-label">หมวดหมู่</label>
<select name="category_id" class="form-select" required>
<option value="">เลือกหมวดหมู่</option>
<?php while($cat=mysqli_fetch_assoc($cat_query)){ ?>
<option value="<?=$cat['id']?>"><?=$cat['category_name']?></option>
<?php } ?>
</select>
</div>

<div class="col-md-6 mb-3">
<label class="form-label">แบรนด์</label>
<input type="text" name="brand" class="form-control">
</div>
</div>

<div class="mb-3">
<label class="form-label">รูปสินค้า</label>
<input type="file" name="image" class="form-control">
</div>

<div class="d-flex justify-content-between">
<a href="products.php" class="btn btn-secondary rounded-3">
<i class="bi bi-arrow-left me-2"></i>กลับ
</a>

<button type="submit" name="submit" class="btn btn-primary rounded-3">
<i class="bi bi-check-lg me-2"></i>บันทึกสินค้า
</button>
</div>

</form>

</div>
</div>
</div>
</div>

</body>
</html>
