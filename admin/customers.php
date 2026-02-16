<?php
session_start();
include("../includes/connectdb.php");

if(!isset($_SESSION['user_id']) || $_SESSION['role']!='admin'){
    header("Location: ../login.php");
    exit();
}

/* ====== ดึงรูปโปรไฟล์ admin ====== */
$user_id = $_SESSION['user_id'];
$user_q = mysqli_query($conn,"SELECT image FROM users WHERE id='$user_id'");
$user_data = mysqli_fetch_assoc($user_q);
$profile_image = !empty($user_data['image']) ? $user_data['image'] : "default.png";

/* ===== ดึงลูกค้าทั้งหมด (role = customer) ===== */
$sql = "SELECT * FROM users WHERE role='customer' ORDER BY id DESC";
$result = mysqli_query($conn,$sql);

if(!$result){
    die("Query Error: " . mysqli_error($conn));
}

/* ===== สถิติ ===== */
$total_users_q = mysqli_query($conn,
"SELECT COUNT(*) as total FROM users WHERE role='customer'");
$total_users = mysqli_fetch_assoc($total_users_q)['total'];

$new_this_month_q = mysqli_query($conn,
"SELECT COUNT(*) as total FROM users 
 WHERE role='customer' 
 AND MONTH(created_at)=MONTH(CURRENT_DATE()) 
 AND YEAR(created_at)=YEAR(CURRENT_DATE())");
$new_this_month = mysqli_fetch_assoc($new_this_month_q)['total'];
?>


<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>จัดการลูกค้า</title>

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
.card-custom{background:white;border-radius:16px;box-shadow:0 5px 15px rgba(0,0,0,.03);}
.avatar-img{width:45px;height:45px;border-radius:50%;object-fit:cover;}
.badge-soft-success{background:#dcfce7;color:#166534;}
.badge-soft-danger{background:#fee2e2;color:#991b1b;}
.badge-soft-gray{background:#f3f4f6;color:#374151;}
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
<a href="orders.php" class="nav-link">
<i class="bi bi-cart3"></i>จัดการออเดอร์
</a>
<a href="customers.php" class="nav-link active">
<i class="bi bi-people-fill"></i>จัดการลูกค้า
</a>
<div class="mt-5 px-3">
<a href="../logout.php" class="btn btn-danger w-100 rounded-3">
<i class="bi bi-box-arrow-right me-2"></i>ออกจากระบบ
</a>
</div>
</div>
</nav>

<main class="col-md-9 ms-sm-auto col-lg-10 main-content">

<h2 class="fw-bold mb-4">ฐานข้อมูลลูกค้า 👥</h2>

<!-- ===== STAT CARDS ===== -->
<div class="row mb-4 g-3">

<div class="col-md-6">
<div class="card-custom p-3 d-flex align-items-center">
<div class="me-3 text-primary fs-3">
<i class="bi bi-people"></i>
</div>
<div>
<small class="text-muted">ลูกค้าทั้งหมด</small>
<h5 class="fw-bold mb-0"><?=$total_users?> คน</h5>
</div>
</div>
</div>

<div class="col-md-6">
<div class="card-custom p-3 d-flex align-items-center">
<div class="me-3 text-success fs-3">
<i class="bi bi-person-plus"></i>
</div>
<div>
<small class="text-muted">สมัครใหม่เดือนนี้</small>
<h5 class="fw-bold mb-0">+<?=$new_this_month?> คน</h5>
</div>
</div>
</div>

</div>

<!-- ===== TABLE ===== -->
<div class="card-custom p-0 overflow-hidden">
<div class="table-responsive">
<table class="table table-hover mb-0">
<thead>
<tr>
<th class="ps-4">ชื่อผู้ใช้</th>
<th>Email</th>
<th>เบอร์โทร</th>
<th>วันที่สมัคร</th>
<th class="text-center">จัดการ</th>
</tr>
</thead>

<tbody>

<?php if(mysqli_num_rows($result)>0){ ?>
<?php while($row=mysqli_fetch_assoc($result)){ ?>

<tr>
<td class="ps-4">
<div class="d-flex align-items-center">
<img src="https://ui-avatars.com/api/?name=<?=$row['username']?>&background=random"
class="avatar-img me-3">
<div>
<div class="fw-bold"><?=$row['username']?></div>
<small class="text-muted"><?=$row['fullname']?></small>
</div>
</div>
</td>

<td><?=$row['email']?></td>

<td><?=$row['phone']?></td>

<td><?=date("d M Y",strtotime($row['created_at']))?></td>

<td class="text-center">
<a href="customer_edit.php?id=<?=$row['id']?>"
class="btn btn-sm btn-light text-primary me-1">
<i class="bi bi-pencil-square"></i>
</a>

<a href="customer_delete.php?id=<?=$row['id']?>"
class="btn btn-sm btn-light text-danger"
onclick="return confirm('ลบลูกค้านี้?')">
<i class="bi bi-trash"></i>
</a>
</td>

</tr>

<?php } ?>
<?php } else { ?>

<tr>
<td colspan="5" class="text-center py-4 text-muted">
ยังไม่มีลูกค้า
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
