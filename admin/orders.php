<?php
session_start();
include("../includes/connectdb.php");

/* ===== ตรวจสอบสิทธิ์แอดมิน ===== */
if(!isset($_SESSION['user_id']) || $_SESSION['role']!='admin'){
    header("Location: ../login.php");
    exit();
}

/* ====== ดึงรูปโปรไฟล์ admin ====== */
$user_id = $_SESSION['user_id'];
$user_q = mysqli_query($conn,"SELECT image FROM users WHERE id='$user_id'");
$user_data = mysqli_fetch_assoc($user_q);
$profile_image = $user_data['image'] ? $user_data['image'] : "default.png";

/* ===== ดึงข้อมูลออเดอร์ ===== */
$sql = "SELECT orders.*, users.username
        FROM orders
        LEFT JOIN users ON orders.user_id = users.id
        ORDER BY orders.id DESC";

$result = mysqli_query($conn,$sql);

/* ===== สถิติ ===== */
$pending = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) as total FROM orders WHERE status='pending'"))['total'];

$paid = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) as total FROM orders WHERE status='paid'"))['total'];

$completed = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) as total FROM orders WHERE status='completed'"))['total'];

$cancelled = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) as total FROM orders WHERE status='cancelled'"))['total'];
?>

<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>จัดการออเดอร์</title>

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
.card-custom{background:#fff;border-radius:16px;box-shadow:0 5px 15px rgba(0,0,0,.03);}
.stat-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;margin-right:15px;}
.table-custom th{background:#f9fafb;color:#6b7280;font-weight:500;padding:15px;}
.table-custom td{padding:15px;vertical-align:middle;}
.badge-soft-primary{background:#e0f2fe;color:#0284c7;}
.badge-soft-warning{background:#fef9c3;color:#ca8a04;}
.badge-soft-success{background:#dcfce7;color:#166534;}
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
<i class="bi bi-grid-1x2"></i>แดชบอร์ด
</a>
<a href="products.php" class="nav-link">
<i class="bi bi-box-seam"></i>จัดการสินค้า
</a>
<a href="orders.php" class="nav-link active">
<i class="bi bi-cart3-fill"></i>จัดการออเดอร์
</a>
<a href="customers.php" class="nav-link">
<i class="bi bi-people"></i>จัดการลูกค้า
</a>
<div class="mt-5 px-3">
<a href="../logout.php" class="btn btn-danger w-100 rounded-3">
<i class="bi bi-box-arrow-right me-2"></i>ออกจากระบบ
</a>
</div>
</div>
</nav>

<main class="col-md-9 ms-sm-auto col-lg-10 main-content">

<h2 class="fw-bold mb-4">รายการสั่งซื้อ 🛒</h2>

<!-- ===== STAT CARDS ===== -->
<div class="row mb-4 g-3">

<div class="col-6 col-lg-3">
<div class="card-custom p-3 d-flex align-items-center">
<div class="stat-icon bg-warning bg-opacity-10 text-warning">
<i class="bi bi-hourglass-split"></i>
</div>
<div>
<small class="text-muted">รอชำระเงิน</small>
<h5 class="fw-bold mb-0"><?=$pending?> รายการ</h5>
</div>
</div>
</div>

<div class="col-6 col-lg-3">
<div class="card-custom p-3 d-flex align-items-center">
<div class="stat-icon bg-primary bg-opacity-10 text-primary">
<i class="bi bi-box-seam"></i>
</div>
<div>
<small class="text-muted">เตรียมจัดส่ง</small>
<h5 class="fw-bold mb-0"><?=$paid?> รายการ</h5>
</div>
</div>
</div>

<div class="col-6 col-lg-3">
<div class="card-custom p-3 d-flex align-items-center">
<div class="stat-icon bg-success bg-opacity-10 text-success">
<i class="bi bi-check-circle"></i>
</div>
<div>
<small class="text-muted">สำเร็จแล้ว</small>
<h5 class="fw-bold mb-0"><?=$completed?> รายการ</h5>
</div>
</div>
</div>

<div class="col-6 col-lg-3">
<div class="card-custom p-3 d-flex align-items-center">
<div class="stat-icon bg-danger bg-opacity-10 text-danger">
<i class="bi bi-x-circle"></i>
</div>
<div>
<small class="text-muted">ยกเลิก</small>
<h5 class="fw-bold mb-0"><?=$cancelled?> รายการ</h5>
</div>
</div>
</div>

</div>

<!-- ===== TABLE ===== -->
<div class="card-custom p-0 overflow-hidden">
<div class="table-responsive">
<table class="table table-custom table-hover mb-0">
<thead>
<tr>
<th class="ps-4">Order</th>
<th>ลูกค้า</th>
<th>วันที่</th>
<th>ยอดรวม</th>
<th>การชำระเงิน</th>
<th>สถานะ</th>
<th class="text-center">ดู</th>
</tr>
</thead>

<tbody>

<?php if(mysqli_num_rows($result)>0){ ?>
<?php while($row=mysqli_fetch_assoc($result)){ ?>

<tr>
<td class="ps-4 fw-bold text-primary">
#ORD-<?=$row['id']?>
</td>

<td><?=$row['username']?></td>

<td>
<?=date("d M Y",strtotime($row['order_date']))?><br>
<small><?=date("H:i",strtotime($row['order_date']))?></small>
</td>

<td class="fw-bold">
฿<?=number_format($row['total_amount'],2)?>
</td>

<td><?=$row['payment_method']?></td>

<td>

<?php
if($row['status']=='pending'){
echo '<span class="badge badge-soft-warning rounded-pill px-3">รอชำระเงิน</span>';
}
elseif($row['status']=='paid'){
echo '<span class="badge badge-soft-primary rounded-pill px-3">เตรียมจัดส่ง</span>';
}
elseif($row['status']=='completed'){
echo '<span class="badge badge-soft-success rounded-pill px-3">สำเร็จแล้ว</span>';
}
else{
echo '<span class="badge badge-soft-danger rounded-pill px-3">ยกเลิก</span>';
}
?>

</td>

<td class="text-center">
<a href="order_detail.php?id=<?=$row['id']?>"
class="btn btn-sm btn-light">
<i class="bi bi-eye"></i>
</a>
</td>

</tr>

<?php } ?>
<?php } else { ?>

<tr>
<td colspan="7" class="text-center py-4 text-muted">
ยังไม่มีออเดอร์
</td>
</tr>

<?php } ?>

</tbody>

</table>
</div>
</div>

</main>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
