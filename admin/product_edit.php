<?php
session_start();
include("../includes/connectdb.php");

/* ===== ตรวจสอบสิทธิ์ ===== */
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit();
}

if(!isset($_GET['id'])){
    header("Location: products.php");
    exit();
}

$id = intval($_GET['id']);

/* ===== 1. ระบบลบรูปภาพเพิ่มเติม (ลบทีละรูป) ===== */
if(isset($_GET['delete_img'])){
    $del_img_id = intval($_GET['delete_img']);
    
    // ดึงชื่อไฟล์รูปภาพมาลบออกจากโฟลเดอร์
    $get_img_q = mysqli_query($conn, "SELECT image FROM product_images WHERE id='$del_img_id'");
    if(mysqli_num_rows($get_img_q) > 0){
        $img_row = mysqli_fetch_assoc($get_img_q);
        $file_path = "../images/" . $img_row['image'];
        
        // เช็คว่ามีไฟล์อยู่จริงไหม ถ้ามีให้ลบไฟล์ออก
        if(file_exists($file_path)){
            unlink($file_path); 
        }
        
        // ลบข้อมูลออกจากฐานข้อมูล
        mysqli_query($conn, "DELETE FROM product_images WHERE id='$del_img_id'");
    }
    
    // เด้งกลับมาหน้าเดิมเพื่อรีเฟรชข้อมูล (เอาพารามิเตอร์ลบออก)
    header("Location: product_edit.php?id=$id");
    exit();
}

/* ===== ดึงข้อมูลสินค้า ===== */
$product_q = mysqli_query($conn,"SELECT * FROM products WHERE id='$id'");
if(mysqli_num_rows($product_q) == 0){
    header("Location: products.php");
    exit();
}
$product = mysqli_fetch_assoc($product_q);

/* ===== ดึงหมวดหมู่ ===== */
$categories = mysqli_query($conn,"SELECT * FROM categories");

/* ===== ดึงรูปภาพเพิ่มเติมที่มีอยู่ ===== */
$extra_images_q = mysqli_query($conn, "SELECT * FROM product_images WHERE product_id='$id'");

/* ===== อัปเดตข้อมูล ===== */
if(isset($_POST['update'])){

    $name = mysqli_real_escape_string($conn,$_POST['product_name']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $desc = mysqli_real_escape_string($conn,$_POST['description']);
    $category_id = intval($_POST['category_id']);
    $brand = mysqli_real_escape_string($conn,$_POST['brand']);

    /* จัดการรูปภาพหลัก (Main Image) */
    $image_name = $product['image'];

    if(!empty($_FILES['image']['name'])){
        // ถ้ามีการอัปโหลดรูปใหม่ ให้ลบรูปเก่าทิ้งด้วย (ถ้าต้องการลบรูปหลักอันเก่าออก)
        if($image_name != "" && file_exists("../images/".$image_name)){
            unlink("../images/".$image_name);
        }
        $image_name = time() . "_" . uniqid() . "_" . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "../images/" . $image_name);
    }

    /* อัปเดตข้อมูลลงตาราง products */
    mysqli_query($conn,"
        UPDATE products SET
        product_name='$name',
        price='$price',
        stock='$stock',
        description='$desc',
        category_id='$category_id',
        brand='$brand',
        image='$image_name'
        WHERE id='$id'
    ");

    /* จัดการเพิ่มรูปภาพเพิ่มเติม (Gallery Images) */
    if(isset($_FILES['extra_images']['name']) && $_FILES['extra_images']['name'][0] != ""){
        $total_files = count($_FILES['extra_images']['name']);
        
        for($i = 0; $i < $total_files; $i++) {
            $ext_img_name = $_FILES['extra_images']['name'][$i];
            $ext_tmp_name = $_FILES['extra_images']['tmp_name'][$i];
            $ext_error    = $_FILES['extra_images']['error'][$i];

            if($ext_error === 0 && $ext_img_name != ""){
                $new_ext_name = time() . "_" . uniqid() . "_" . $ext_img_name;
                $upload_path  = "../images/" . $new_ext_name;

                if(move_uploaded_file($ext_tmp_name, $upload_path)){
                    $img_sql = "INSERT INTO product_images (product_id, image) VALUES ('$id', '$new_ext_name')";
                    mysqli_query($conn, $img_sql);
                }
            }
        }
    }

    header("Location: products.php?updated=1");
    exit();
}
?>

<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>แก้ไขสินค้า - MBS Guitar Shop</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
body { font-family: 'Kanit', sans-serif; background: #f4f7f6; }
.form-box { max-width: 900px; margin: 40px auto; background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,.05); }
.preview-img { width: 120px; height: 120px; object-fit: cover; border-radius: 12px; margin-bottom: 15px; border: 1px solid #ddd; }
.extra-img-box { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd; }
.form-label { font-weight: 500; color: #444; }

/* ดีไซน์ปุ่มกากบาทลบรูปภาพ */
.btn-delete-img {
    width: 24px;
    height: 24px;
    padding: 0;
    line-height: 20px;
    font-size: 14px;
    font-weight: bold;
    position: absolute;
    top: -8px;
    right: -8px;
    background-color: #dc3545;
    color: white;
    border: 2px solid white;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    border-radius: 50%;
    text-align: center;
    text-decoration: none;
    transition: 0.2s;
    z-index: 10;
}
.btn-delete-img:hover {
    background-color: #bb2d3b;
    color: white;
    transform: scale(1.1);
}
</style>
</head>

<body>

<div class="container">
    <div class="form-box">

        <h3 class="fw-bold mb-4"><i class="bi bi-pencil-square text-primary me-2"></i>แก้ไขสินค้า</h3>

        <form method="POST" enctype="multipart/form-data">

            <div class="mb-3">
                <label class="form-label">ชื่อสินค้า</label>
                <input type="text" name="product_name" value="<?=$product['product_name']?>" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">รายละเอียด</label>
                <textarea name="description" class="form-control" rows="4"><?=$product['description']?></textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">ราคา</label>
                    <input type="number" step="0.01" name="price" value="<?=$product['price']?>" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">จำนวนคงเหลือ</label>
                    <input type="number" name="stock" value="<?=$product['stock']?>" class="form-control" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">หมวดหมู่</label>
                    <select name="category_id" class="form-select" required>
                    <?php while($cat=mysqli_fetch_assoc($categories)){ ?>
                        <option value="<?=$cat['id']?>" <?=($cat['id']==$product['category_id'])?'selected':''?>>
                            <?=$cat['category_name']?>
                        </option>
                    <?php } ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">แบรนด์</label>
                    <input type="text" name="brand" value="<?=$product['brand']?>" class="form-control">
                </div>
            </div>

            <hr class="my-4 text-muted">

            <h5 class="fw-bold mb-3"><i class="bi bi-images text-secondary me-2"></i>จัดการรูปภาพ</h5>

            <div class="row mb-4">
                <div class="col-md-5">
                    <label class="form-label text-primary">รูปภาพหน้าปกปัจจุบัน</label><br>
                    <img src="../images/<?=$product['image']?>" class="preview-img" onerror="this.src='../images/default.png'">
                    <div class="mt-2">
                        <label class="form-label">อัปโหลดรูปหน้าปกใหม่</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <small class="text-muted mt-1 d-block"><i class="bi bi-info-circle me-1"></i>เลือกได้เฉพาะไฟล์รูปภาพ (เช่น .jpg, .png, .webp)</small>
                    </div>
                </div>

                <div class="col-md-7 border-start pl-4">
                    <label class="form-label text-primary">รูปภาพเพิ่มเติม</label><br>
                    <div class="d-flex flex-wrap gap-3 mb-3 pt-2">
                        <?php 
                        if(mysqli_num_rows($extra_images_q) > 0){
                            while($ext_img = mysqli_fetch_assoc($extra_images_q)){
                                ?>
                                <div class="position-relative d-inline-block">
                                    <img src="../images/<?=$ext_img['image']?>" class="extra-img-box" onerror="this.src='../images/default.png'">
                                    
                                    <a href="product_edit.php?id=<?=$id?>&delete_img=<?=$ext_img['id']?>" 
                                       class="btn-delete-img" 
                                       onclick="return confirm('ยืนยันที่จะลบรูปภาพนี้ใช่หรือไม่?');" 
                                       title="ลบรูปภาพนี้">
                                       <i class="bi bi-x"></i>
                                    </a>
                                </div>
                                <?php
                            }
                        } else {
                            echo "<span class='text-muted small'>ไม่มีรูปภาพเพิ่มเติม</span>";
                        }
                        ?>
                    </div>
                    
                    <div class="mt-3 bg-light p-3 rounded">
                        <label class="form-label fw-bold">อัปโหลดรูปเพิ่มเติม</label>
                        <input type="file" name="extra_images[]" class="form-control" accept="image/*" multiple>
                        <small class="text-muted mt-1 d-block"><i class="bi bi-info-circle me-1"></i>เลือกได้เฉพาะไฟล์รูปภาพ (เช่น .jpg, .png, .webp)</small>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between pt-3 border-top mt-4">
                <a href="products.php" class="btn btn-light border px-4 rounded-pill">
                    <i class="bi bi-arrow-left me-2"></i>กลับ
                </a>
                <button type="submit" name="update" class="btn btn-dark px-4 rounded-pill shadow-sm">
                    <i class="bi bi-save me-2"></i>บันทึกการแก้ไข
                </button>
            </div>

        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>