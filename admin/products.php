<?php
session_start();
include("../includes/connectdb.php");

/* ===== ตรวจสอบสิทธิ์แอดมิน ===== */
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit();
}

/* ====== ดึงรูปโปรไฟล์ admin ====== */
$user_id = $_SESSION['user_id'];
$user_q = mysqli_query($conn,"SELECT image FROM users WHERE id='$user_id'");
$user_data = mysqli_fetch_assoc($user_q);
$profile_image = $user_data['image'] ? $user_data['image'] : "default.png";

/* ===== ดึงสินค้าทั้งหมด (ให้ DataTables จัดการ Search/Pagination หน้าบ้าน) ===== */
$sql = "SELECT products.*, categories.category_name
        FROM products
        LEFT JOIN categories ON products.category_id = categories.id
        ORDER BY products.id ASC";
$result = mysqli_query($conn,$sql);

/* ===== คำนวณสถิติภาพรวม ===== */
$total_products = mysqli_num_rows($result);
$value_q = mysqli_query($conn,"SELECT SUM(price * stock) as total FROM products");
$total_value = mysqli_fetch_assoc($value_q)['total'] ?? 0;
$low_stock_q = mysqli_query($conn,"SELECT COUNT(*) as total FROM products WHERE stock <= 3");
$low_stock = mysqli_fetch_assoc($low_stock_q)['total'];
?>

<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>จัดการสินค้า - MBS Guitar Shop</title>

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
        
        /* 🔥 แก้ไขบั๊กตัวหนังสือซ้อนกัน (DataTables Styling) */
        .dataTables_length label { 
            display: flex !important; 
            align-items: center !important; 
            gap: 12px !important; 
            white-space: nowrap; 
            color: #666;
            font-size: 0.9rem;
        }
        .dataTables_length select { 
            width: 100px !important; 
            padding: 6px 12px !important;
            border-radius: 8px !important;
            border: 1px solid #ddd !important;
            appearance: auto !important;
        }
        .dataTables_filter input { 
            border-radius: 10px !important; 
            border: 1px solid #ddd !important; 
            padding: 8px 15px !important; 
            outline: none; 
            margin-left: 10px !important; 
        }
        
        /* ดีไซน์การ์ดและตาราง */
        .card-custom { background: #fff; border-radius: 16px; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.03); margin-bottom: 24px; padding: 24px; }
        .table-custom th { background: #f8fafc !important; color: #64748b; font-weight: 600; padding: 16px; border-bottom: 2px solid #e2e8f0 !important; }
        .table-custom td { padding: 16px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }

        /* ปุ่ม Action ทรงสี่เหลี่ยมมน */
        .btn-action { width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; transition: all 0.2s; border: none; background: transparent; text-decoration: none; }
        .btn-action:hover { transform: translateY(-2px); }
        .btn-edit { background: #f0f9ff; color: #0284c7; }
        .btn-edit:hover { background: #0284c7; color: #fff; }
        .btn-delete { background: #fef2f2; color: #dc2626; }
        .btn-delete:hover { background: #dc2626; color: #fff; }

        .product-img { width: 50px; height: 50px; object-fit: cover; border-radius: 10px; border: 1px solid #eee; }
        .badge-soft-success { background: #dcfce7; color: #166534; }
        .badge-soft-warning { background: #fef9c3; color: #854d0e; }
        .badge-soft-danger { background: #fee2e2; color: #991b1b; }
    </style>
</head>

<body>

<div class="container-fluid">
<div class="row">

    <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
        <div class="p-4 text-center border-bottom mb-4">
            <img src="../images/admins/<?=$profile_image?>" class="rounded-circle mb-2" width="80" height="80" style="object-fit:cover;" onerror="this.src='../images/default.png'">
            <h6 class="mt-2 mb-0 fw-bold"><?=$_SESSION['username']?></h6>
            <small class="text-muted">Admin</small>
        </div>

        <div class="nav flex-column">
            <a href="index2.php" class="nav-link"><i class="bi bi-grid-1x2"></i> แดชบอร์ด</a>
            <a href="products.php" class="nav-link active"><i class="bi bi-box-seam-fill"></i> จัดการสินค้า</a>
            <a href="categories.php" class="nav-link"><i class="bi bi-tags"></i> จัดการประเภทสินค้า</a>
            <a href="orders.php" class="nav-link"><i class="bi bi-cart3"></i> จัดการออเดอร์</a>
            <a href="customers.php" class="nav-link"><i class="bi bi-people"></i> จัดการลูกค้า</a>
            <div class="mt-5 px-3">
                <a href="../logout.php" class="btn btn-danger w-100 rounded-3 text-white fw-bold shadow-sm">
                    <i class="bi bi-box-arrow-right me-2"></i>ออกจากระบบ
                </a>
            </div>
        </div>
    </nav>

    <main class="col-md-9 ms-sm-auto col-lg-10 main-content">

        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <h2 class="fw-bold m-0"><i class="bi bi-box-seam text-primary me-2"></i>คลังสินค้า</h2>
            <a href="product_add.php" class="btn btn-dark rounded-pill px-4 shadow-sm fw-semibold">
                <i class="bi bi-plus-lg me-2"></i>เพิ่มสินค้าใหม่
            </a>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="bg-white p-4 rounded-4 shadow-sm d-flex align-items-center" style="border: 1px solid #f0f2f5;">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px; font-size: 1.8rem;">
                        <i class="bi bi-boxes"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">สินค้าทั้งหมด</div>
                        <h4 class="fw-bold mb-0"><?=$total_products?> รายการ</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="bg-white p-4 rounded-4 shadow-sm d-flex align-items-center" style="border: 1px solid #f0f2f5;">
                    <div class="bg-success bg-opacity-10 text-success rounded-3 d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px; font-size: 1.8rem;">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">มูลค่าสินค้ารวม</div>
                        <h4 class="fw-bold mb-0">฿<?=number_format($total_value,2)?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="bg-white p-4 rounded-4 shadow-sm d-flex align-items-center" style="border: 1px solid #f0f2f5;">
                    <div class="bg-danger bg-opacity-10 text-danger rounded-3 d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px; font-size: 1.8rem;">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">สินค้าใกล้หมด</div>
                        <h4 class="fw-bold mb-0"><?=$low_stock?> รายการ</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-custom p-0 overflow-hidden shadow-sm">
            <div class="table-responsive p-4">
                <table id="productTable" class="table table-custom mb-0 w-100">
                    <thead>
                        <tr>
                            <th width="30%">สินค้า</th>
                            <th width="20%">หมวดหมู่</th>
                            <th width="15%">ราคา</th>
                            <th class="text-center" width="10%">คงเหลือ</th>
                            <th class="text-center" width="15%">สถานะ</th>
                            <th class="text-center" width="10%">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result)){ ?>
                        <tr>
                            <td data-order="<?=$row['id']?>">
                                <div class="d-flex align-items-center">
                                    <img src="../images/<?=$row['image']?>" class="product-img me-3 border">
                                    <div>
                                        <div class="fw-bold text-dark"><?=$row['product_name']?></div>
                                        <small class="text-muted">ID: <?=$row['id']?></small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="text-secondary"><?=$row['category_name']?></span></td>
                            <td class="fw-bold text-dark">฿<?=number_format($row['price'],2)?></td>
                            <td class="text-center fw-semibold text-secondary"><?=$row['stock']?> ชิ้น</td>
                            <td class="text-center">
                                <?php if($row['stock']==0){ ?>
                                    <span class="badge badge-soft-danger rounded-pill px-3 py-2">สินค้าหมด</span>
                                <?php } elseif($row['stock']<=3){ ?>
                                    <span class="badge badge-soft-warning rounded-pill px-3 py-2">ใกล้หมด</span>
                                <?php } else { ?>
                                    <span class="badge badge-soft-success rounded-pill px-3 py-2">พร้อมขาย</span>
                                <?php } ?>
                            </td>
                            <td class="text-center">
                                <a href="product_edit.php?id=<?=$row['id']?>" class="btn-action btn-edit me-1" title="แก้ไข"><i class="bi bi-pencil-square"></i></a>
                                <a href="product_delete.php?id=<?=$row['id']?>" class="btn-action btn-delete" onclick="return confirm('ยืนยันการลบสินค้า [<?=$row['product_name']?>] ?')" title="ลบ"><i class="bi bi-trash"></i></a>
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

<script>
$(document).ready(function() {
    $('#productTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/th.json"
        },
        "pageLength": 10,
        "order": [[0, "asc"]], // เรียงตาม ID (คอลัมน์แรก) จากน้อยไปมาก
        "columnDefs": [
            { "orderable": false, "targets": [5] } // ปิดการเรียงลำดับในปุ่มจัดการ
        ]
    });
});
</script>

</body>
</html>