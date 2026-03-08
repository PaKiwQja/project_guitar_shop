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

    /* 1. อัปโหลดรูปภาพหลัก (Main Image) */
    $image_name = "";
    if($_FILES['image']['name'] != ""){
        $image_name = time() . "_" . uniqid() . "_" . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'],"../images/".$image_name);
    }

    /* บันทึกข้อมูลสินค้าลงตาราง products */
    $sql = "INSERT INTO products
            (product_name,description,price,stock,image,category_id,brand)
            VALUES
            ('$product_name','$description','$price','$stock',
             '$image_name','$category_id','$brand')";

    if(mysqli_query($conn,$sql)){
        
        /* ดึง ID ของสินค้าที่เพิ่งเพิ่มเข้าไปเมื่อกี้ */
        $new_product_id = mysqli_insert_id($conn);

        /* 2. จัดการรูปภาพเพิ่มเติม (Gallery Images) */
        if(isset($_FILES['extra_images']['name']) && $_FILES['extra_images']['name'][0] != ""){
            
            // นับจำนวนรูปภาพที่ถูกเลือกมา
            $total_files = count($_FILES['extra_images']['name']);
            
            for($i = 0; $i < $total_files; $i++) {
                $ext_img_name = $_FILES['extra_images']['name'][$i];
                $ext_tmp_name = $_FILES['extra_images']['tmp_name'][$i];
                $ext_error    = $_FILES['extra_images']['error'][$i];

                if($ext_error === 0 && $ext_img_name != ""){
                    // ตั้งชื่อไฟล์ไม่ให้ซ้ำกัน
                    $new_ext_name = time() . "_" . uniqid() . "_" . $ext_img_name;
                    $upload_path  = "../images/" . $new_ext_name;

                    // ถ้าย้ายไฟล์สำเร็จ ให้บันทึกลงตาราง product_images
                    if(move_uploaded_file($ext_tmp_name, $upload_path)){
                        $img_sql = "INSERT INTO product_images (product_id, image) VALUES ('$new_product_id', '$new_ext_name')";
                        mysqli_query($conn, $img_sql);
                    }
                }
            }
        }

        header("Location: products.php?added=1");
        exit();
    }
}
?>

<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>เพิ่มสินค้า - MBS Guitar Shop</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
body { font-family: 'Kanit', sans-serif; background: #f4f7f6; }
.card-custom { background: #fff; border-radius: 16px; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.03); }
.form-label { font-weight: 500; color: #444; }
</style>
</head>

<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">

            <div class="card-custom p-4 p-md-5">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="fw-bold m-0"><i class="bi bi-box-seam text-primary me-2"></i>เพิ่มสินค้าใหม่</h3>
                </div>

                <form method="POST" enctype="multipart/form-data">

                    <div class="mb-3">
                        <label class="form-label">ชื่อสินค้า</label>
                        <input type="text" name="product_name" class="form-control" required placeholder="เช่น Yamaha F310">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">รายละเอียด</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="กรอกรายละเอียดสินค้า..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ราคา (บาท)</label>
                            <input type="number" step="0.01" name="price" class="form-control" required placeholder="0.00">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">จำนวนคงเหลือ</label>
                            <input type="number" name="stock" class="form-control" required placeholder="0">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">หมวดหมู่</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">-- เลือกหมวดหมู่ --</option>
                                <?php while($cat=mysqli_fetch_assoc($cat_query)){ ?>
                                    <option value="<?=$cat['id']?>"><?=$cat['category_name']?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">แบรนด์</label>
                            <input type="text" name="brand" class="form-control" placeholder="เช่น Yamaha, Gibson">
                        </div>
                    </div>

                    <hr class="my-4 text-muted">

                    <h5 class="fw-bold mb-3"><i class="bi bi-images text-secondary me-2"></i>เพิ่มรูปภาพ</h5>
                    
                    <div class="mb-3">
                        <label class="form-label text-primary">รูปภาพหน้าปก (Main Image)</label>
                        <input type="file" name="image" class="form-control" accept="image/*" required>
                        <small class="text-muted">รูปภาพหลักที่จะแสดงในหน้าแรกของร้าน</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">รูปภาพเพิ่มเติม (Gallery Images)</label>
                        <input type="file" name="extra_images[]" class="form-control" accept="image/*" multiple>
                        <small class="text-muted"><i class="bi bi-info-circle me-1"></i>เลือกได้เฉพาะไฟล์รูปภาพ (เช่น .jpg, .png, .webp)</small>
                    </div>

                    <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                        <a href="products.php" class="btn btn-light border px-4 rounded-pill">
                            <i class="bi bi-arrow-left me-2"></i>กลับ
                        </a>
                        <button type="submit" name="submit" class="btn btn-dark px-4 rounded-pill shadow-sm">
                            <i class="bi bi-save me-2"></i>บันทึกสินค้า
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>