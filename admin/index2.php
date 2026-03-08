<?php
session_start();
include("../includes/connectdb.php");

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit();
}

/* ====== ดึงรูปโปรไฟล์ admin ====== */
$user_id = $_SESSION['user_id'];
$user_q = mysqli_query($conn,"SELECT image FROM users WHERE id='$user_id'");
$user_data = mysqli_fetch_assoc($user_q);
$profile_image = $user_data['image'] ? $user_data['image'] : "default.png";

/* ====== ดึงข้อมูลจากฐานข้อมูล ====== */

// ยอดขายรวม (เฉพาะออเดอร์สำเร็จ)
$total_sales_q = mysqli_query($conn,"
    SELECT SUM(total_price) as total 
    FROM orders 
    WHERE status='completed'
");
$total_sales = mysqli_fetch_assoc($total_sales_q)['total'];
$total_sales = $total_sales ? $total_sales : 0;

// ออเดอร์ใหม่ (pending)
$new_orders_q = mysqli_query($conn,"
    SELECT COUNT(*) as total 
    FROM orders 
    WHERE status='pending'
");
$new_orders = mysqli_fetch_assoc($new_orders_q)['total'];

// จำนวนสินค้า
$product_q = mysqli_query($conn,"
    SELECT COUNT(*) as total 
    FROM products
");
$total_products = mysqli_fetch_assoc($product_q)['total'];

// จำนวนลูกค้า
$user_count_q = mysqli_query($conn,"
    SELECT COUNT(*) as total 
    FROM users 
    WHERE role='customer'
");
$total_users = mysqli_fetch_assoc($user_count_q)['total'];

// ออเดอร์ล่าสุด 5 รายการ
$latest_orders = mysqli_query($conn,"
    SELECT orders.*, users.username 
    FROM orders
    LEFT JOIN users ON orders.user_id = users.id
    ORDER BY orders.id DESC
    LIMIT 5
");

/* ====== ดึงข้อมูลสำหรับทำกราฟ (ยอดขาย 7 วันย้อนหลัง) ====== */
$chart_sql = "
    SELECT DATE(created_at) as order_date, SUM(total_price) as daily_total 
    FROM orders 
    WHERE status = 'completed' 
    GROUP BY DATE(created_at) 
    ORDER BY DATE(created_at) DESC 
    LIMIT 7
";
$chart_result = mysqli_query($conn, $chart_sql);

$dates = [];
$totals = [];

if(mysqli_num_rows($chart_result) > 0){
    while($row = mysqli_fetch_assoc($chart_result)){
        $dates[] = date("d M", strtotime($row['order_date']));
        $totals[] = $row['daily_total'];
    }
    $dates = array_reverse($dates);
    $totals = array_reverse($totals);
}

$dates_json = json_encode($dates);
$totals_json = json_encode($totals);
?>

<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Dashboard - MBS Guitar Shop</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body{font-family:'Kanit',sans-serif;background:#f4f7f6;}
.sidebar{min-height:100vh;background:#fff;border-right:1px solid #eee;}
.nav-link{color:#666;padding:12px 20px;border-radius:10px;margin:5px 15px;display:flex;align-items:center;transition:.3s;}
.nav-link:hover,.nav-link.active{background:#f0f2f5;color:#2563eb;font-weight:500;}
.nav-link i{margin-right:12px;}
.main-content{padding:30px;}
.welcome-banner{background:linear-gradient(135deg,#2563eb 0%,#8b5cf6 100%);
color:#fff;border-radius:20px;padding:40px 30px;margin-bottom:30px;
box-shadow:0 10px 20px rgba(37,99,235,.2);}
.stat-card{background:#fff;border-radius:16px;padding:25px;
box-shadow:0 5px 15px rgba(0,0,0,.03);}
.icon-box{width:50px;height:50px;border-radius:12px;
display:flex;align-items:center;justify-content:center;font-size:1.5rem;}
.bg-purple-light{background:#f3e8ff;color:#9333ea;}
.bg-blue-light{background:#e0f2fe;color:#0284c7;}
.bg-green-light{background:#dcfce7;color:#16a34a;}
.bg-orange-light{background:#ffedd5;color:#ea580c;}
.chart-container{background:#fff;border-radius:16px;padding:25px;
box-shadow:0 5px 15px rgba(0,0,0,.03);}
</style>
</head>

<body>

<div class="container-fluid">
<div class="row">

<nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
<div class="p-4 text-center border-bottom mb-4">
<img src="../images/admins/<?=$profile_image?>"
     class="rounded-circle mb-2" width="80" height="80" style="object-fit:cover;"
     onerror="this.src='../images/default.png'">
<h6 class="mt-2 mb-0 fw-bold"><?=$_SESSION['username']?></h6>
<small class="text-muted">Admin</small>
</div>

<div class="nav flex-column">
    <a href="index2.php" class="nav-link active">
        <i class="bi bi-grid-1x2-fill"></i> แดชบอร์ด
    </a>
    <a href="products.php" class="nav-link">
        <i class="bi bi-box-seam"></i> จัดการสินค้า
    </a>
    
    <a href="categories.php" class="nav-link">
        <i class="bi bi-tags"></i> จัดการประเภทสินค้า
    </a>

    <a href="orders.php" class="nav-link">
        <i class="bi bi-cart3"></i> จัดการออเดอร์
    </a>
    <a href="customers.php" class="nav-link">
        <i class="bi bi-people"></i> จัดการลูกค้า
    </a>
    <div class="mt-5 px-3">
        <a href="../logout.php" class="btn btn-danger w-100 rounded-3">
            <i class="bi bi-box-arrow-right me-2"></i> ออกจากระบบ
        </a>
    </div>
</div>
</nav>

<main class="col-md-9 ms-sm-auto col-lg-10 main-content">

<div class="welcome-banner">
<h2 class="fw-bold mb-1">
สวัสดี, คุณ <?=$_SESSION['username']?> 👋
</h2>
<p class="mb-0 opacity-75">
ภาพรวมร้าน MBS Guitar Shop ของวันนี้
</p>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between">
                <div>
                    <p class="text-muted small mb-1">ยอดขายรวม</p>
                    <h3 class="fw-bold text-dark">฿<?=number_format($total_sales,2)?></h3>
                </div>
                <div class="icon-box bg-purple-light"><i class="bi bi-currency-dollar"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between">
                <div>
                    <p class="text-muted small mb-1">ออเดอร์รอตรวจสอบ</p>
                    <h3 class="fw-bold text-dark"><?=$new_orders?> รายการ</h3>
                </div>
                <div class="icon-box bg-blue-light"><i class="bi bi-bag-check"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between">
                <div>
                    <p class="text-muted small mb-1">สินค้าทั้งหมด</p>
                    <h3 class="fw-bold text-dark"><?=$total_products?> ชิ้น</h3>
                </div>
                <div class="icon-box bg-orange-light"><i class="bi bi-box"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between">
                <div>
                    <p class="text-muted small mb-1">สมาชิกทั้งหมด</p>
                    <h3 class="fw-bold text-dark"><?=$total_users?> คน</h3>
                </div>
                <div class="icon-box bg-green-light"><i class="bi bi-people"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    
    <div class="col-lg-8">
        <div class="chart-container h-100">
            <h5 class="fw-bold mb-4"><i class="bi bi-graph-up-arrow text-primary me-2"></i>ยอดขาย 7 วันย้อนหลัง (ออเดอร์ที่สำเร็จแล้ว)</h5>
            
            <?php if(empty($dates)){ ?>
                <div class="alert alert-light border text-center py-5 text-muted">
                    <i class="bi bi-bar-chart fs-1 d-block mb-2"></i>
                    ยังไม่มีข้อมูลยอดขายที่สำเร็จแล้วใน 7 วันนี้
                </div>
            <?php } else { ?>
                <canvas id="salesChart" height="100"></canvas>
            <?php } ?>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="chart-container h-100">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold m-0"><i class="bi bi-clock-history text-warning me-2"></i>ออเดอร์ล่าสุด</h5>
                <a href="orders.php" class="small text-decoration-none">ดูทั้งหมด</a>
            </div>

            <table class="table align-middle table-hover">
                <tbody>
                <?php if(mysqli_num_rows($latest_orders)>0){ ?>
                    <?php while($row=mysqli_fetch_assoc($latest_orders)){ 
                        $badge_class = 'bg-secondary';
                        if($row['status']=='pending') $badge_class = 'bg-warning text-dark';
                        if($row['status']=='paid') $badge_class = 'bg-primary';
                        if($row['status']=='shipped') $badge_class = 'bg-info text-dark';
                        if($row['status']=='completed') $badge_class = 'bg-success';
                        if($row['status']=='cancelled') $badge_class = 'bg-danger';
                    ?>
                    <tr>
                        <td>
                            <div class="fw-bold text-primary">#ORD-<?=$row['id']?></div>
                            <small class="text-muted"><?=$row['username'] ?? 'No user'?></small>
                        </td>
                        <td class="text-center">
                            <span class="badge <?=$badge_class?>" style="font-size: 0.7rem;"><?=$row['status']?></span>
                        </td>
                        <td class="text-end fw-bold">
                            ฿<?=number_format($row['total_price'],2)?>
                        </td>
                    </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">
                            ยังไม่มีออเดอร์
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</main>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php if(!empty($dates)){ ?>
<script>
    const ctx = document.getElementById('salesChart').getContext('2d');
    
    const salesChart = new Chart(ctx, {
        type: 'line', 
        data: {
            labels: <?= $dates_json ?>, 
            datasets: [{
                label: 'ยอดขาย (บาท)',
                data: <?= $totals_json ?>, 
                borderColor: '#2563eb', 
                backgroundColor: 'rgba(37,99,235,0.1)', 
                borderWidth: 3,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#2563eb',
                pointBorderWidth: 2,
                pointRadius: 4,
                fill: true,
                tension: 0.4 
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false 
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ' ฿' + context.parsed.y.toLocaleString(); 
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value, index, values) {
                            return '฿' + value.toLocaleString(); 
                        }
                    }
                }
            }
        }
    });
</script>
<?php } ?>

</body>
</html>