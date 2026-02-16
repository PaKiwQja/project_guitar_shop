<?php
session_start();
include("../includes/connectdb.php");

/* ===== ตรวจสอบสิทธิ์ ===== */
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit();
}

/* ====== ดึงรูปโปรไฟล์ admin ====== */
$user_id = $_SESSION['user_id'];
$user_q = mysqli_query($conn,"SELECT image FROM users WHERE id='$user_id'");
$user_data = mysqli_fetch_assoc($user_q);
$profile_image = $user_data['image'] ? $user_data['image'] : "default.png";

/* ===== รับค่ากรอง ===== */
$search   = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$brand    = $_GET['brand'] ?? '';
$page     = $_GET['page'] ?? 1;

$limit = 10;
$start = ($page - 1) * $limit;

$where = "WHERE 1=1";

if($search != ''){
    $search = mysqli_real_escape_string($conn,$search);
    $where .= " AND product_name LIKE '%$search%'";
}

if($category != ''){
    $category = mysqli_real_escape_string($conn,$category);
    $where .= " AND category_id='$category'";
}

if($brand != ''){
    $brand = mysqli_real_escape_string($conn,$brand);
    $where .= " AND brand='$brand'";
}

/* ===== นับจำนวนทั้งหมด ===== */
$count_sql = "SELECT COUNT(*) as total FROM products $where";
$count_res = mysqli_query($conn,$count_sql);
$total_rows = mysqli_fetch_assoc($count_res)['total'];
$total_pages = ceil($total_rows / $limit);

/* ===== ดึงสินค้า ===== */
$sql = "SELECT products.*, categories.category_name
        FROM products
        LEFT JOIN categories ON products.category_id = categories.id
        $where
        ORDER BY products.id DESC
        LIMIT $start,$limit";

$result = mysqli_query($conn,$sql);

/* ===== สถิติ ===== */
$count_q = mysqli_query($conn,"SELECT COUNT(*) as total FROM products");
$total_products = mysqli_fetch_assoc($count_q)['total'];

$value_q = mysqli_query($conn,"SELECT SUM(price * stock) as total FROM products");
$total_value = mysqli_fetch_assoc($value_q)['total'] ?? 0;

$low_q = mysqli_query($conn,"SELECT COUNT(*) as total FROM products WHERE stock <= 3");
$low_stock = mysqli_fetch_assoc($low_q)['total'];

/* ===== หมวดหมู่ ===== */
$cat_query = mysqli_query($conn,"SELECT * FROM categories");

/* ===== แบรนด์ ===== */
$brand_query = mysqli_query($conn,"SELECT DISTINCT brand FROM products WHERE brand IS NOT NULL AND brand != ''");
?>

<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>จัดการสินค้า</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
body{font-family:'Kanit',sans-serif;background:#f4f7f6;}
.sidebar{min-height:100vh;background:#fff;border-right:1px solid #eee;}
.nav-link{color:#666;padding:12px 20px;border-radius:10px;margin:5px 15px;display:flex;align-items:center;}
.nav-link:hover,.nav-link.active{background:#f0f2f5;color:#2563eb;font-weight:500;}
.nav-link i{margin-right:12px;}
.main-content{padding:30px;}
.card-custom{background:#fff;border-radius:16px;border:none;box-shadow:0 5px 15px rgba(0,0,0,0.03);margin-bottom:20px;padding:20px;}
.product-img{width:60px;height:60px;object-fit:cover;border-radius:10px;}
.table-custom th{background:#f9fafb;color:#6b7280;font-weight:500;}
.badge-soft-success{background:#dcfce7;color:#166534;}
.badge-soft-warning{background:#fef9c3;color:#854d0e;}
.badge-soft-danger{background:#fee2e2;color:#991b1b;}
</style>
</head>

<body>

<div class="container-fluid">
<div class="row">

<!-- SIDEBAR -->
<nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
<div class="p-4 text-center border-bottom mb-4">

<!--  ใช้รูปจากฐานข้อมูล -->
<img src="../images/admins/<?=$profile_image?>"
     class="rounded-circle mb-2"
     width="80"
     height="80"
     style="object-fit:cover;"
     onerror="this.src='../images/default.png'">

<h6 class="mt-2 mb-0 fw-bold"><?=$_SESSION['username']?></h6>
<small class="text-muted">Admin</small>
</div>

<div class="nav flex-column">
<a href="index2.php" class="nav-link">
<i class="bi bi-grid-1x2"></i> แดชบอร์ด
</a>
<a href="products.php" class="nav-link active">
<i class="bi bi-box-seam-fill"></i> จัดการสินค้า
</a>
<a href="orders.php" class="nav-link">
<i class="bi bi-cart3"></i> จัดการออเดอร์
</a>
<a href="customers.php" class="nav-link">
<i class="bi bi-people"></i> จัดการลูกค้า
</a>
<div class="mt-5 px-3">
<a href="../logout.php" class="btn btn-danger w-100 rounded-3">
<i class="bi bi-box-arrow-right me-2"></i>ออกจากระบบ
</a>
</div>
</nav>

<!-- CONTENT -->
<main class="col-md-9 ms-sm-auto col-lg-10 main-content">

<h2 class="fw-bold mb-4">คลังสินค้า 📦</h2>

<!-- กล่องสรุป -->
<div class="row g-3 mb-4">
<div class="col-md-4">
<div class="card-custom">
<small class="text-muted">สินค้าทั้งหมด</small>
<h5 class="fw-bold"><?=$total_products?> รายการ</h5>
</div>
</div>

<div class="col-md-4">
<div class="card-custom">
<small class="text-muted">มูลค่าสินค้ารวม</small>
<h5 class="fw-bold">฿<?=number_format($total_value,2)?></h5>
</div>
</div>

<div class="col-md-4">
<div class="card-custom">
<small class="text-muted">สินค้าใกล้หมด</small>
<h5 class="fw-bold"><?=$low_stock?> รายการ</h5>
</div>
</div>
</div>

<!-- Filter -->
<form method="GET" class="mb-4">
    <div class="row g-2 align-items-center">

        <!-- ค้นหา -->
        <div class="col-md-4">
            <input type="text" name="search"
                   value="<?=isset($_GET['search']) ? $_GET['search'] : ''?>"
                   class="form-control"
                   placeholder="ค้นหาสินค้า...">
        </div>

        <!-- หมวดหมู่ -->
        <div class="col-md-3">
            <select name="category" class="form-select">
                <option value="">ทุกหมวดหมู่</option>
                <?php
                $cat_q = mysqli_query($conn,"SELECT * FROM categories");
                while($cat=mysqli_fetch_assoc($cat_q)){
                ?>
                <option value="<?=$cat['id']?>"
                    <?= (isset($_GET['category']) && $_GET['category']==$cat['id'])?'selected':'' ?>>
                    <?=$cat['category_name']?>
                </option>
                <?php } ?>
            </select>
        </div>

        <!-- แบรนด์ -->
        <div class="col-md-3">
            <select name="brand" class="form-select">
                <option value="">ทุกแบรนด์</option>
                <?php
                $brand_q = mysqli_query($conn,"SELECT DISTINCT brand FROM products WHERE brand IS NOT NULL AND brand != ''");
                while($b=mysqli_fetch_assoc($brand_q)){
                ?>
                <option value="<?=$b['brand']?>"
                    <?= (isset($_GET['brand']) && $_GET['brand']==$b['brand'])?'selected':'' ?>>
                    <?=$b['brand']?>
                </option>
                <?php } ?>
            </select>
        </div>

        <!-- ปุ่มกรอง -->
        <div class="col-md-1 d-grid">
            <button class="btn btn-dark">กรอง</button>
        </div>

        <!-- ปุ่มเพิ่มสินค้า -->
        <div class="col-md-1 d-grid">
            <a href="product_add.php" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i>
            </a>
        </div>

    </div>
</form>


<!-- ตารางสินค้า -->
<div class="card-custom">
<div class="table-responsive">
<table class="table table-hover table-custom">

<thead>
<tr>
<th>สินค้า</th>
<th>หมวดหมู่</th>
<th>ราคา</th>
<th>คงเหลือ</th>
<th>สถานะ</th>
<th class="text-center">จัดการ</th>
</tr>
</thead>

<tbody>

<?php if(mysqli_num_rows($result)>0){ ?>
<?php while($row=mysqli_fetch_assoc($result)){ ?>
<tr>

<td>
<div class="d-flex align-items-center">
<img src="../images/<?=$row['image']?>" class="product-img me-3">
<div>
<div class="fw-bold"><?=$row['product_name']?></div>
<small class="text-muted">ID: <?=$row['id']?></small>
</div>
</div>
</td>

<td><?=$row['category_name']?></td>

<td class="fw-bold text-primary">
฿<?=number_format($row['price'],2)?>
</td>

<td><?=$row['stock']?> ชิ้น</td>

<td>
<?php if($row['stock']==0){ ?>
<span class="badge badge-soft-danger">สินค้าหมด</span>
<?php } elseif($row['stock']<=3){ ?>
<span class="badge badge-soft-warning">ใกล้หมด</span>
<?php } else { ?>
<span class="badge badge-soft-success">พร้อมขาย</span>
<?php } ?>
</td>

<td class="text-center">
<a href="product_edit.php?id=<?=$row['id']?>" class="btn btn-sm btn-light text-primary">
<i class="bi bi-pencil-square"></i>
</a>
<a href="product_delete.php?id=<?=$row['id']?>"
class="btn btn-sm btn-light text-danger"
onclick="return confirm('ยืนยันการลบ?')">
<i class="bi bi-trash"></i>
</a>
</td>

</tr>
<?php } ?>
<?php } else { ?>
<tr>
<td colspan="6" class="text-center py-4 text-muted">
ไม่พบสินค้า
</td>
</tr>
<?php } ?>

</tbody>
</table>
</div>

<!-- Pagination -->
<?php if($total_pages > 1){ ?>
<nav class="mt-3">
<ul class="pagination justify-content-end">
<?php for($i=1;$i<=$total_pages;$i++){ ?>
<li class="page-item <?=($i==$page?'active':'')?>">
<a class="page-link"
href="?search=<?=$search?>&category=<?=$category?>&brand=<?=$brand?>&page=<?=$i?>">
<?=$i?>
</a>
</li>
<?php } ?>
</ul>
</nav>
<?php } ?>

</div>

</main>
</div>
</div>

</body>
</html>
