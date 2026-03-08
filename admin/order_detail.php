<?php
session_start();
include("../includes/connectdb.php");

/* ===== ตรวจสอบสิทธิ์แอดมิน ===== */
if(!isset($_SESSION['user_id']) || $_SESSION['role']!='admin'){
    header("Location: ../login.php");
    exit();
}

/* ===== ตรวจสอบว่ามีการส่ง id มาหรือไม่ ===== */
if(!isset($_GET['id'])){
    header("Location: orders.php");
    exit();
}

$order_id = intval($_GET['id']);

/* ====== ดึงข้อมูลออเดอร์และลูกค้า ====== */
$order_sql = "
    SELECT orders.*, users.username, users.email, users.phone 
    FROM orders 
    LEFT JOIN users ON orders.user_id = users.id 
    WHERE orders.id = '$order_id'
";
$order_result = mysqli_query($conn, $order_sql);
if(mysqli_num_rows($order_result) == 0){
    // ถ้าไม่เจอบิลนี้ ให้เด้งกลับ
    header("Location: orders.php");
    exit();
}
$order = mysqli_fetch_assoc($order_result);

/* ====== ดึงรายการสินค้าในบิลนี้ ====== */
$items_sql = "
    SELECT order_items.*, products.product_name, products.image 
    FROM order_items 
    LEFT JOIN products ON order_items.product_id = products.id 
    WHERE order_items.order_id = '$order_id'
";
$items_result = mysqli_query($conn, $items_sql);

/* ====== ดึงรูปโปรไฟล์ admin ====== */
$admin_id = $_SESSION['user_id'];
$admin_q = mysqli_query($conn,"SELECT image FROM users WHERE id='$admin_id'");
$admin_data = mysqli_fetch_assoc($admin_q);
$profile_image = $admin_data['image'] ? $admin_data['image'] : "default.png";

// จัดการข้อความและสีสถานะ
$status_badge = 'bg-secondary';
$status_text = 'ไม่ทราบสถานะ';
if($order['status']=='pending') { $status_badge = 'bg-warning text-dark'; $status_text = 'รอชำระเงิน/รอยืนยัน'; }
if($order['status']=='paid') { $status_badge = 'bg-primary'; $status_text = 'เตรียมจัดส่ง'; }
if($order['status']=='shipped') { $status_badge = 'bg-info text-dark'; $status_text = 'ที่ต้องได้รับ (จัดส่งแล้ว)'; }
if($order['status']=='completed') { $status_badge = 'bg-success'; $status_text = 'สำเร็จแล้ว'; }
if($order['status']=='cancelled') { $status_badge = 'bg-danger'; $status_text = 'ยกเลิกแล้ว'; }
?>

<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>รายละเอียดคำสั่งซื้อ #ORD-<?=$order_id?></title>

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
.card-custom{background:#fff;border-radius:16px;box-shadow:0 5px 15px rgba(0,0,0,.03); padding: 25px;}
.slip-img { max-width: 100%; border-radius: 10px; cursor: pointer; transition: 0.3s; border: 1px solid #ddd; }
.slip-img:hover { opacity: 0.8; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
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
        <h6 class="mt-2 mb-0 fw-bold"><?=$_SESSION['username'] ?? 'Admin'?></h6>
        <small class="text-muted">Admin</small>
    </div>

    <div class="nav flex-column">
        <a href="index2.php" class="nav-link"><i class="bi bi-grid-1x2"></i>แดชบอร์ด</a>
        <a href="products.php" class="nav-link"><i class="bi bi-box-seam"></i>จัดการสินค้า</a>
        <a href="orders.php" class="nav-link active"><i class="bi bi-cart3-fill"></i>จัดการออเดอร์</a>
        <a href="customers.php" class="nav-link"><i class="bi bi-people"></i>จัดการลูกค้า</a>
        <div class="mt-5 px-3">
            <a href="../logout.php" class="btn btn-danger w-100 rounded-3">
                <i class="bi bi-box-arrow-right me-2"></i>ออกจากระบบ
            </a>
        </div>
    </div>
</nav>

<main class="col-md-9 ms-sm-auto col-lg-10 main-content">

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold m-0">
        <a href="orders.php" class="text-dark text-decoration-none me-2"><i class="bi bi-arrow-left-circle"></i></a>
        รายละเอียดคำสั่งซื้อ
    </h2>
    <span class="badge <?=$status_badge?> fs-6 px-3 py-2"><?=$status_text?></span>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        
        <div class="card-custom mb-4">
            <h5 class="fw-bold mb-3 border-bottom pb-2"><i class="bi bi-truck text-primary me-2"></i>ข้อมูลการจัดส่ง</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <p class="mb-1 text-muted small">ชื่อบัญชีผู้สั่งซื้อ</p>
                    <div class="fw-semibold"><i class="bi bi-person me-2"></i><?=$order['username'] ?? 'ไม่มีข้อมูล'?></div>
                </div>
                <div class="col-md-6 mb-3">
                    <p class="mb-1 text-muted small">วันที่สั่งซื้อ</p>
                    <div class="fw-semibold"><i class="bi bi-calendar-event me-2"></i><?=date("d M Y H:i", strtotime($order['created_at']))?></div>
                </div>
                <div class="col-12">
                    <p class="mb-1 text-muted small">ที่อยู่สำหรับจัดส่ง</p>
                    <div class="bg-light p-3 rounded border">
                        <?=nl2br($order['shipping_address'])?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-custom">
            <h5 class="fw-bold mb-3 border-bottom pb-2"><i class="bi bi-box-seam text-primary me-2"></i>รายการสินค้า (<?=mysqli_num_rows($items_result)?> รายการ)</h5>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="80">ภาพ</th>
                            <th>ชื่อสินค้า</th>
                            <th class="text-center">ราคา/ชิ้น</th>
                            <th class="text-center">จำนวน</th>
                            <th class="text-end">รวม</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($item = mysqli_fetch_assoc($items_result)){ ?>
                        <tr>
                            <td>
                                <img src="../images/<?=$item['image']?>" alt="Product" class="img-fluid rounded border" style="width: 60px; height: 60px; object-fit: cover;" onerror="this.src='https://via.placeholder.com/60';">
                            </td>
                            <td class="fw-semibold"><?=$item['product_name'] ?? 'สินค้าถูกลบ'?></td>
                            <td class="text-center">฿<?=number_format($item['price'],2)?></td>
                            <td class="text-center">x<?=$item['quantity']?></td>
                            <td class="text-end fw-bold">฿<?=number_format($item['price'] * $item['quantity'], 2)?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="4" class="text-end fw-bold fs-5">ยอดรวมทั้งสิ้น</td>
                            <td class="text-end fw-bold fs-5 text-danger">฿<?=number_format($order['total_price'],2)?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>

    <div class="col-lg-4">
        <div class="card-custom sticky-top" style="top: 20px;">
            <h5 class="fw-bold mb-3 border-bottom pb-2"><i class="bi bi-wallet2 text-success me-2"></i>การชำระเงิน</h5>
            
            <div class="mb-3">
                <p class="mb-1 text-muted small">ช่องทางการชำระเงิน</p>
                <div class="fw-bold fs-5">
                    <?php if($order['payment_method'] == 'พร้อมเพย์'){ ?>
                        <span class="text-primary"><img src="../images/Prompt_logo.png" height="24" class="me-2">พร้อมเพย์</span>
                    <?php } else { ?>
                        <span class="text-warning text-dark"><i class="bi bi-cash-stack me-2"></i>เก็บเงินปลายทาง</span>
                    <?php } ?>
                </div>
            </div>

            <?php if($order['payment_method'] == 'พร้อมเพย์' && !empty($order['payment_slip'])){ ?>
                <div class="mt-4">
                    <p class="mb-2 text-muted small fw-bold">หลักฐานการโอนเงิน (คลิกเพื่อขยาย)</p>
                    <img src="../images/slips/<?=$order['payment_slip']?>" 
                         class="slip-img w-100" 
                         data-bs-toggle="modal" 
                         data-bs-target="#slipModal"
                         alt="Payment Slip"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                    <div class="alert alert-danger p-2 text-center" style="display: none;">
                        <i class="bi bi-exclamation-triangle"></i> ไม่พบไฟล์สลิปในระบบ
                    </div>
                </div>
            <?php } elseif ($order['payment_method'] == 'พร้อมเพย์' && empty($order['payment_slip'])) { ?>
                <div class="alert alert-warning mt-3 small"><i class="bi bi-exclamation-circle"></i> ลูกค้ายังไม่ได้แนบสลิป</div>
            <?php } ?>

            <button class="btn btn-outline-dark w-100 mt-4" onclick="window.print()">
                <i class="bi bi-printer me-2"></i>พิมพ์ใบสั่งซื้อ
            </button>
        </div>
    </div>
</div>

</main>
</div>
</div>

<div class="modal fade" id="slipModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content bg-transparent border-0">
      <div class="modal-header border-0 pb-0">
        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center pt-0">
        <?php if(!empty($order['payment_slip'])){ ?>
            <img src="../images/slips/<?=$order['payment_slip']?>" class="img-fluid rounded shadow-lg" style="max-height: 85vh;">
        <?php } ?>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style type="text/css" media="print">
    body { background: #fff; }
    .sidebar, .btn, .badge { display: none !important; }
    .main-content { margin-left: 0 !important; width: 100% !important; padding: 0 !important; }
    .card-custom { box-shadow: none !important; border: 1px solid #ddd; margin-bottom: 20px; }
    a[href]:after { content: none !important; }
</style>

</body>
</html>