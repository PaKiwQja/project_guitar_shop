<?php
session_start();
include("../includes/connectdb.php");

/* ===== ตรวจสอบสิทธิ์แอดมิน ===== */
if(!isset($_SESSION['user_id']) || $_SESSION['role']!='admin'){
    header("Location: ../login.php");
    exit();
}

/* ====== 1. ระบบอัปเดตสถานะออเดอร์ ====== */
if(isset($_POST['update_status'])){
    $order_id = intval($_POST['order_id']);
    $new_status = mysqli_real_escape_string($conn, $_POST['new_status']);
    mysqli_query($conn, "UPDATE orders SET status='$new_status' WHERE id='$order_id'");
    header("Location: orders.php?updated=1");
    exit();
}

/* ====== 2. ระบบลบคำสั่งซื้อ ====== */
if(isset($_POST['delete_order'])){
    $order_id = intval($_POST['order_id']);
    mysqli_query($conn, "DELETE FROM order_items WHERE order_id='$order_id'");
    mysqli_query($conn, "DELETE FROM orders WHERE id='$order_id'");
    header("Location: orders.php?deleted=1");
    exit();
}

/* ====== ดึงรูปโปรไฟล์ admin ====== */
$user_id = $_SESSION['user_id'];
$user_q = mysqli_query($conn,"SELECT image FROM users WHERE id='$user_id'");
$user_data = mysqli_fetch_assoc($user_q);
$profile_image = $user_data['image'] ? $user_data['image'] : "default.png";

/* ====== 3. ระบบกรองข้อมูล (Filter แบบเดิม) ====== */
$where = "WHERE 1=1";
$filter_date = isset($_GET['filter_date']) ? $_GET['filter_date'] : '';
$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';

if($filter_date != ''){
    $date_esc = mysqli_real_escape_string($conn, $filter_date);
    $where .= " AND DATE(orders.created_at) = '$date_esc'";
}

if($filter_status != ''){
    $status_esc = mysqli_real_escape_string($conn, $filter_status);
    $where .= " AND orders.status = '$status_esc'";
}

/* ===== ดึงข้อมูลออเดอร์ ===== */
$sql = "SELECT orders.*, users.username
        FROM orders
        LEFT JOIN users ON orders.user_id = users.id
        $where
        ORDER BY orders.id DESC";
$result = mysqli_query($conn,$sql);

/* ===== สถิติภาพรวม ===== */
$pending = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as total FROM orders WHERE status='pending'"))['total'];
$paid = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as total FROM orders WHERE status='paid'"))['total'];
$shipped = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as total FROM orders WHERE status='shipped'"))['total'];
$completed = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as total FROM orders WHERE status='completed'"))['total'];
?>

<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>จัดการออเดอร์ - MBS Guitar Shop</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

    <style>
        body{font-family:'Kanit',sans-serif;background:#f4f7f6; color:#333;}
        .sidebar{min-height:100vh;background:#fff;border-right:1px solid #eee;}
        .nav-link{color:#666;padding:12px 20px;border-radius:10px;margin:5px 15px;display:flex;align-items:center;transition:all 0.3s ease;}
        .nav-link:hover,.nav-link.active{background:#f0f2f5;color:#2563eb;font-weight:500;}
        .nav-link i{margin-right:12px;}
        .main-content{padding:30px;}
        .card-custom{background:#fff;border-radius:16px;border:none;box-shadow:0 4px 15px rgba(0,0,0,0.03);margin-bottom:24px;padding:24px;}
        
        /* 🔥 Fix DataTables Overlap */
        .dataTables_length label { display: flex !important; align-items: center !important; gap: 10px !important; white-space: nowrap; color: #666; font-size: 0.9rem; }
        .dataTables_length select { width: 80px !important; padding: 6px 10px !important; border-radius: 8px !important; border: 1px solid #ddd !important; }
        .dataTables_filter input { border-radius: 10px !important; border: 1px solid #ddd !important; padding: 8px 15px !important; outline: none; margin-left: 10px !important; }

        .table-custom th { background: #f8fafc !important; color: #64748b; font-weight: 600; padding: 15px; border-bottom: 2px solid #e2e8f0 !important; }
        .table-custom td { padding: 15px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
        .status-select { font-weight: 500; border-radius: 30px; padding: 5px 15px; cursor: pointer; border: 1px solid transparent; }
        .status-pending { background-color: #fef9c3; color: #ca8a04; }
        .status-paid { background-color: #e0f2fe; color: #0284c7; }
        .status-shipped { background-color: #ede9fe; color: #7e22ce; }
        .status-completed { background-color: #dcfce7; color: #166534; }
        .status-cancelled { background-color: #fee2e2; color: #991b1b; }
    </style>
</head>

<body>

<div class="container-fluid">
<div class="row">

<nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
    <div class="p-4 text-center border-bottom mb-4">
        <img src="../images/admins/<?=$profile_image?>" class="rounded-circle mb-2" width="80" height="80" style="object-fit:cover;" onerror="this.src='../images/default.png'">
        <h6 class="mt-2 mb-0 fw-bold"><?=$_SESSION['username'] ?? 'Admin'?></h6>
        <small class="text-muted">Admin</small>
    </div>
    <div class="nav flex-column">
        <a href="index2.php" class="nav-link"><i class="bi bi-grid-1x2"></i>แดชบอร์ด</a>
        <a href="products.php" class="nav-link"><i class="bi bi-box-seam"></i>จัดการสินค้า</a>
        <a href="categories.php" class="nav-link"><i class="bi bi-tags"></i> จัดการประเภทสินค้า</a>
        <a href="orders.php" class="nav-link active"><i class="bi bi-cart3-fill"></i>จัดการออเดอร์</a>
        <a href="customers.php" class="nav-link"><i class="bi bi-people"></i>จัดการลูกค้า</a>
        <div class="mt-5 px-3">
            <a href="../logout.php" class="btn btn-danger w-100 rounded-3 text-white fw-bold shadow-sm">
                <i class="bi bi-box-arrow-right me-2"></i>ออกจากระบบ
            </a>
        </div>
    </div>
</nav>

<main class="col-md-9 ms-sm-auto col-lg-10 main-content">

    <h2 class="fw-bold mb-4">รายการสั่งซื้อ 🛒</h2>

    <div class="row mb-4 g-3">
        <div class="col-6 col-lg-3">
            <div class="card-custom p-3 d-flex align-items-center m-0 shadow-sm">
                <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-2 me-3 fs-3"><i class="bi bi-hourglass-split"></i></div>
                <div><small class="text-muted">รอชำระ/ยืนยัน</small><h5 class="fw-bold mb-0"><?=$pending?> รายการ</h5></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-custom p-3 d-flex align-items-center m-0 shadow-sm">
                <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-2 me-3 fs-3"><i class="bi bi-box-seam"></i></div>
                <div><small class="text-muted">เตรียมจัดส่ง</small><h5 class="fw-bold mb-0"><?=$paid?> รายการ</h5></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-custom p-3 d-flex align-items-center m-0 shadow-sm">
                <div class="bg-info bg-opacity-10 text-info rounded-3 p-2 me-3 fs-3"><i class="bi bi-truck"></i></div>
                <div><small class="text-muted">ที่ต้องได้รับ</small><h5 class="fw-bold mb-0"><?=$shipped?> รายการ</h5></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card-custom p-3 d-flex align-items-center m-0 shadow-sm">
                <div class="bg-success bg-opacity-10 text-success rounded-3 p-2 me-3 fs-3"><i class="bi bi-check-circle"></i></div>
                <div><small class="text-muted">สำเร็จแล้ว</small><h5 class="fw-bold mb-0"><?=$completed?> รายการ</h5></div>
            </div>
        </div>
    </div>

    <div class="card-custom p-3 mb-4 shadow-sm">
        <form method="GET" class="row g-3 align-items-center m-0">
            <div class="col-auto"><label class="col-form-label fw-bold"><i class="bi bi-funnel"></i> ตัวกรอง:</label></div>
            <div class="col-auto"><input type="date" name="filter_date" class="form-control" value="<?=$filter_date?>"></div>
            <div class="col-auto">
                <select name="filter_status" class="form-select">
                    <option value="">ทุกสถานะ</option>
                    <option value="pending" <?=($filter_status=='pending'?'selected':'')?>>รอชำระ/รอยืนยัน</option>
                    <option value="paid" <?=($filter_status=='paid'?'selected':'')?>>เตรียมจัดส่ง</option>
                    <option value="shipped" <?=($filter_status=='shipped'?'selected':'')?>>ที่ต้องได้รับ</option>
                    <option value="completed" <?=($filter_status=='completed'?'selected':'')?>>สำเร็จแล้ว</option>
                    <option value="cancelled" <?=($filter_status=='cancelled'?'selected':'')?>>ยกเลิก</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-dark px-4">กรองข้อมูล</button>
                <a href="orders.php" class="btn btn-light border px-3 text-danger">ล้างค่า</a>
            </div>
        </form>
    </div>

    <div class="card-custom p-0 overflow-hidden shadow-sm">
        <div class="table-responsive p-4">
            <table id="orderTable" class="table table-custom table-hover mb-0 w-100">
                <thead>
                    <tr>
                        <th class="ps-4">Order</th>
                        <th>ลูกค้า</th>
                        <th>วันที่</th>
                        <th>ยอดรวม</th>
                        <th>สถานะ</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row=mysqli_fetch_assoc($result)){ 
                        $select_class = "status-pending";
                        if($row['status'] == 'paid') $select_class = "status-paid";
                        if($row['status'] == 'shipped') $select_class = "status-shipped";
                        if($row['status'] == 'completed') $select_class = "status-completed";
                        if($row['status'] == 'cancelled') $select_class = "status-cancelled";
                    ?>
                    <tr>
                        <td class="ps-4 fw-bold text-primary">#ORD-<?=$row['id']?></td>
                        <td><div class="fw-bold text-dark"><?=$row['username'] ?? 'ไม่มีผู้ใช้'?></div></td>
                        <td>
                            <small class="text-dark fw-semibold"><?=date("d M Y",strtotime($row['created_at']))?></small><br>
                            <small class="text-muted"><?=date("H:i",strtotime($row['created_at']))?></small>
                        </td>
                        <td class="fw-bold text-dark">฿<?=number_format($row['total_price'],2)?></td>
                        <td>
                            <form method="POST" class="m-0">
                                <input type="hidden" name="order_id" value="<?=$row['id']?>">
                                <select name="new_status" class="form-select form-select-sm status-select <?=$select_class?>" onchange="this.form.submit()">
                                    <option value="pending" <?=($row['status']=='pending'?'selected':'')?>>รอชำระ/ยืนยัน</option>
                                    <option value="paid" <?=($row['status']=='paid'?'selected':'')?>>เตรียมจัดส่ง</option>
                                    <option value="shipped" <?=($row['status']=='shipped'?'selected':'')?>>ที่ต้องได้รับ</option>
                                    <option value="completed" <?=($row['status']=='completed'?'selected':'')?>>สำเร็จแล้ว</option>
                                    <option value="cancelled" <?=($row['status']=='cancelled'?'selected':'')?>>ยกเลิก</option>
                                </select>
                                <input type="hidden" name="update_status" value="1">
                            </form>
                        </td>
                        <td class="text-center">
                            <a href="order_detail.php?id=<?=$row['id']?>" class="btn btn-sm btn-light border shadow-sm me-1" title="ดูรายละเอียด"><i class="bi bi-eye"></i></a>
                            <form method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบคำสั่งซื้อ #ORD-<?=$row['id']?> ?');">
                                <input type="hidden" name="order_id" value="<?=$row['id']?>">
                                <input type="hidden" name="delete_order" value="1">
                                <button type="submit" class="btn btn-sm btn-outline-danger shadow-sm"><i class="bi bi-trash"></i></button>
                            </form>
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

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    $('#orderTable').DataTable({
        "language": { "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/th.json" },
        "order": [[0, "desc"]],
        "columnDefs": [{ "orderable": false, "targets": [5] }]
    });
});
</script>

<?php if(isset($_GET['updated'])){ ?>
<script>Swal.fire({ icon: 'success', title: 'อัปเดตสถานะสำเร็จ', showConfirmButton: false, timer: 1500 });</script>
<?php } ?>
<?php if(isset($_GET['deleted'])){ ?>
<script>Swal.fire({ icon: 'success', title: 'ลบคำสั่งซื้อเรียบร้อย', showConfirmButton: false, timer: 1500 });</script>
<?php } ?>

</body>
</html>