<?php
session_start();
include("../includes/connectdb.php");

/* ===== ตรวจสอบสิทธิ์ ===== */
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit();
}

/* ====== 1. ระบบเพิ่มหมวดหมู่ ====== */
if(isset($_POST['add_category'])){
    $cat_name = mysqli_real_escape_string($conn, $_POST['category_name']);
    mysqli_query($conn, "INSERT INTO categories (category_name) VALUES ('$cat_name')");
    header("Location: categories.php?added=1");
    exit();
}

/* ====== 2. ระบบแก้ไขหมวดหมู่ ====== */
if(isset($_POST['edit_category'])){
    $cat_id = intval($_POST['category_id']);
    $cat_name = mysqli_real_escape_string($conn, $_POST['category_name']);
    mysqli_query($conn, "UPDATE categories SET category_name='$cat_name' WHERE id='$cat_id'");
    header("Location: categories.php?updated=1");
    exit();
}

/* ====== 3. ระบบลบหมวดหมู่ ====== */
if(isset($_POST['delete_category'])){
    $cat_id = intval($_POST['category_id']);
    mysqli_query($conn, "DELETE FROM categories WHERE id='$cat_id'");
    header("Location: categories.php?deleted=1");
    exit();
}

/* ====== ดึงรูปโปรไฟล์ admin ====== */
$user_id = $_SESSION['user_id'];
$user_q = mysqli_query($conn,"SELECT image FROM users WHERE id='$user_id'");
$user_data = mysqli_fetch_assoc($user_q);
$profile_image = $user_data['image'] ? $user_data['image'] : "default.png";

/* ====== ดึงข้อมูลหมวดหมู่ (เรียงจาก ID น้อยไปมากตามที่ต้องการ) ====== */
$sql = "
    SELECT c.*, COUNT(p.id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id 
    GROUP BY c.id 
    ORDER BY c.id ASC
";
$result = mysqli_query($conn, $sql);
?>

<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>จัดการประเภทสินค้า - MBS Guitar Shop</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

    <style>
        body { font-family: 'Kanit', sans-serif; background: #f4f7f6; color: #333; }
        .sidebar{min-height:100vh;background:#fff;border-right:1px solid #eee;}
        .nav-link{color:#666;padding:12px 20px;border-radius:10px;margin:5px 15px;display:flex;align-items:center;transition:all 0.3s ease;}
        .nav-link:hover,.nav-link.active{background:#f0f2f5;color:#2563eb;font-weight:500;}
        .nav-link i{margin-right:12px;}
        .main-content { padding: 30px; }

        /* 🔥 แก้ไขบั๊กตัวหนังสือซ้อนกัน */
        .dataTables_length label { display: flex !important; align-items: center !important; gap: 10px !important; white-space: nowrap; color: #666; font-size: 0.9rem; }
        .dataTables_length select { width: 80px !important; padding: 6px 10px !important; border-radius: 8px !important; border: 1px solid #ddd !important; }
        .dataTables_filter input { border-radius: 10px !important; border: 1px solid #ddd !important; padding: 8px 15px !important; outline: none; margin-left: 10px !important; }

        .card-custom { background: #fff; border-radius: 16px; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.03); margin-bottom: 24px; padding: 24px; }
        .table-custom th { background: #f8fafc !important; color: #64748b; font-weight: 600; padding: 16px; border-bottom: 2px solid #e2e8f0 !important; }
        .table-custom td { padding: 16px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }

        .btn-action { width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; transition: all 0.2s; border: none; background: transparent; }
        .btn-action:hover { transform: translateY(-2px); }
        .btn-edit { background: #f0f9ff; color: #0284c7; }
        .btn-edit:hover { background: #0284c7; color: #fff; }
        .btn-delete { background: #fef2f2; color: #dc2626; }
        .btn-delete:hover { background: #dc2626; color: #fff; }
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
    <a href="products.php" class="nav-link"><i class="bi bi-box-seam"></i> จัดการสินค้า</a>
    <a href="categories.php" class="nav-link active"><i class="bi bi-tags-fill"></i> จัดการประเภทสินค้า</a>
    <a href="orders.php" class="nav-link"><i class="bi bi-cart3"></i> จัดการออเดอร์</a>
    <a href="customers.php" class="nav-link"><i class="bi bi-people"></i> จัดการลูกค้า</a>
    <div class="mt-5 px-3">
        <a href="../logout.php" class="btn btn-danger w-100 rounded-3 text-white fw-bold shadow-sm"><i class="bi bi-box-arrow-right me-2"></i>ออกจากระบบ</a>
    </div>
</div>
</nav>

<main class="col-md-9 ms-sm-auto col-lg-10 main-content">

    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <h2 class="fw-bold m-0"><i class="bi bi-tags text-primary me-2"></i>จัดการประเภทสินค้า</h2>
        <button class="btn btn-dark rounded-pill px-4 shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="bi bi-plus-lg me-2"></i>เพิ่มประเภทสินค้า
        </button>
    </div>

    <div class="card-custom p-0 overflow-hidden shadow-sm">
        <div class="table-responsive p-4">
            <table id="categoryTable" class="table table-custom mb-0 w-100">
                <thead>
                    <tr>
                        <th width="10%">ID</th>
                        <th width="50%">ชื่อประเภทสินค้า</th>
                        <th class="text-center" width="20%">จำนวนสินค้า</th>
                        <th class="text-center" width="20%">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row=mysqli_fetch_assoc($result)){ ?>
                    <tr>
                        <td class="fw-bold text-secondary">#<?=$row['id']?></td>
                        <td><div class="fw-bold text-dark fs-6"><?=$row['category_name']?></div></td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                                <?=$row['product_count']?> รายการ
                            </span>
                        </td>
                        <td class="text-center">
                            <button class="btn-action btn-edit me-1" data-bs-toggle="modal" data-bs-target="#editCategoryModal<?=$row['id']?>"><i class="bi bi-pencil-square"></i></button>
                            <form method="POST" class="d-inline" onsubmit="return confirm('ยืนยันการลบประเภทสินค้า?');">
                                <input type="hidden" name="category_id" value="<?=$row['id']?>">
                                <input type="hidden" name="delete_category" value="1">
                                <button type="submit" class="btn-action btn-delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>

                    <div class="modal fade" id="editCategoryModal<?=$row['id']?>" tabindex="-1" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow">
                          <div class="modal-header border-bottom-0 pb-0">
                            <h5 class="modal-title fw-bold">แก้ไขประเภทสินค้า</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                          </div>
                          <form method="POST">
                              <div class="modal-body">
                                  <input type="hidden" name="category_id" value="<?=$row['id']?>">
                                  <div class="mb-3">
                                      <label class="form-label text-muted small fw-bold">ชื่อประเภทสินค้า</label>
                                      <input type="text" name="category_name" class="form-control" value="<?=$row['category_name']?>" required>
                                  </div>
                              </div>
                              <div class="modal-footer border-top-0 pt-0">
                                  <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">ยกเลิก</button>
                                  <button type="submit" name="edit_category" class="btn btn-primary rounded-pill">บันทึก</button>
                              </div>
                          </form>
                        </div>
                      </div>
                    </div>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

</main>
</div>
</div>

<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom-0 pb-0">
        <h5 class="modal-title fw-bold">เพิ่มประเภทสินค้าใหม่</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
          <div class="modal-body">
              <div class="mb-3">
                  <label class="form-label text-muted small fw-bold">ชื่อประเภทสินค้า</label>
                  <input type="text" name="category_name" class="form-control" placeholder="เช่น สายกีต้าร์..." required>
              </div>
          </div>
          <div class="modal-footer border-top-0 pt-0">
              <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">ยกเลิก</button>
              <button type="submit" name="add_category" class="btn btn-dark rounded-pill">บันทึก</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    $('#categoryTable').DataTable({
        "language": { "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/th.json" },
        "order": [[0, "asc"]],
        "columnDefs": [{ "orderable": false, "targets": [3] }]
    });
});
</script>

</body>
</html>