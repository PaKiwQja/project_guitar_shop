<?php
session_start();
include("includes/connectdb.php");

/* ================= FILTER & PAGINATION ================= */
$brand = isset($_GET['brand']) ? $_GET['brand'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'cheap';
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// กำหนดจำนวนสินค้าต่อหน้า (8 ชิ้น)
$limit = 8; 
$start = ($page - 1) * $limit;

$where = "WHERE 1=1";

// กรองแบรนด์
if($brand != ''){
    $brand_esc = mysqli_real_escape_string($conn, $brand);
    $where .= " AND p.brand='$brand_esc'";
}

// กรองหมวดหมู่
if($category != ''){
    $category_esc = mysqli_real_escape_string($conn, $category);
    $where .= " AND p.category_id='$category_esc'";
}

// เรียงลำดับราคา
$order = "ORDER BY p.price ASC";
if($sort == "expensive"){
    $order = "ORDER BY p.price DESC";
}

/* ===== คำนวณจำนวนหน้าทั้งหมด ===== */
$count_sql = "SELECT COUNT(*) as total FROM products p $where";
$count_res = mysqli_query($conn, $count_sql);
$total_rows = mysqli_fetch_assoc($count_res)['total'];
$total_pages = ceil($total_rows / $limit);

/* ===== ดึงข้อมูลสินค้า (ใช้ LIMIT แบ่งหน้า และ ORDER BY) ===== */
$sql = "
SELECT p.*, c.category_name 
FROM products p
LEFT JOIN categories c ON p.category_id = c.id
$where
$order
LIMIT $start, $limit
";
$result = mysqli_query($conn, $sql);

include("includes/header.php");
?>

<section class="hero text-center mb-5">
  <div class="container">
    <h1 class="fw-bold">สินค้าทั้งหมด</h1>
    <p class="text-light">ค้นหากีต้าร์ที่ใช่ ในสไตล์ของคุณ</p>
  </div>
</section>

<div class="container mb-4">
    <h2 class="text-center section-title">เลือกแบรนด์สินค้า</h2>
    <div class="row g-4 text-center justify-content-center">
    
    <?php
        $brands = ["Yamaha", "Taylor", "Gibson", "Kazuki"];
        foreach($brands as $b){
            // เช็คว่าแบรนด์นี้ถูกเลือกอยู่หรือเปล่า
            $active = ($brand == $b) ? "border-primary border-3" : "";
    ?>
        <div class="col-6 col-md-3">
            <a href="all_products.php?brand=<?=$b?><?=($category!='')?'&category='.$category:''?><?=($sort!='cheap')?'&sort='.$sort:''?>" class="text-decoration-none text-dark">
                <div class="brand-card p-3 shadow-sm <?=$active?>" style="transition:0.3s;">
                    <img src="images/brands/<?=strtolower($b)?>.jpg" 
                         class="img-fluid mb-2" 
                         style="height:60px;object-fit:contain;" 
                         onerror="this.src='https://via.placeholder.com/120x60?text=<?=$b?>';">
                    <div class="brand-name fw-semibold"><?=$b?></div>
                </div>
            </a>
        </div>
    <?php } ?>

    </div>
</div>

<div class="container mt-4 mb-5">
  <form method="GET" class="d-flex justify-content-center gap-3 flex-wrap align-items-center">

    <?php if($brand!=''){ ?>
      <input type="hidden" name="brand" value="<?=$brand?>">
    <?php } ?>

    <select name="category" class="form-select w-auto">
      <option value="">ทุกประเภท</option>
      <?php
      $cat_sql = mysqli_query($conn,"SELECT * FROM categories");
      while($cat = mysqli_fetch_assoc($cat_sql)){
      ?>
        <option value="<?=$cat['id']?>" <?=($category==$cat['id']?'selected':'')?>>
          <?=$cat['category_name']?>
        </option>
      <?php } ?>
    </select>

    <select name="sort" class="form-select w-auto">
      <option value="cheap" <?=($sort=='cheap'?'selected':'')?>>ราคาถูก → แพง</option>
      <option value="expensive" <?=($sort=='expensive'?'selected':'')?>>ราคาแพง → ถูก</option>
    </select>

    <button class="btn btn-dark px-4">กรอง</button>
    
  </form>
</div>


<div class="container mb-5">
    <div class="row justify-content-center">

    <?php if(mysqli_num_rows($result) > 0){ ?>
        <?php while($row = mysqli_fetch_assoc($result)){ ?>

        <div class="col-6 col-md-4 col-lg-3 mb-4">
            <div class="card border-0 shadow-sm h-100 text-center p-3 product-card">
                <a href="product_detail.php?id=<?=$row['id']?>" class="text-decoration-none text-dark">
                    <img src="images/<?=$row['image']?>" 
                         class="img-fluid mb-3" 
                         style="height:180px;object-fit:contain;" 
                         onerror="this.src='https://via.placeholder.com/300x200';">
                    <div class="fw-semibold"><?=$row['product_name']?></div>
                    <div class="text-muted small"><?=$row['brand']?></div>
                    <div class="small text-secondary"><?=$row['category_name']?></div>
                    <div class="fw-bold mt-2 price" style="font-size:18px;">
                        ฿<?=number_format($row['price'], 2)?>
                    </div>
                </a>

                <div class="mt-3">
                <?php if(!isset($_SESSION['user_id'])){ ?>
                    <a href="login.php" class="btn btn-dark w-100 mb-2 rounded-pill">ซื้อเลย</a>
                    <a href="login.php" class="btn btn-outline-dark w-100 rounded-pill">เพิ่มลงรถเข็น</a>
                <?php } else { ?>
                    <a href="checkout.php?id=<?=$row['id']?>" class="btn btn-dark w-100 mb-2 rounded-pill">ซื้อเลย</a>
                    <form action="add_to_cart.php" method="POST">
                        <input type="hidden" name="product_id" value="<?=$row['id']?>">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="btn btn-outline-dark w-100 rounded-pill">เพิ่มลงรถเข็น</button>
                    </form>
                <?php } ?>
                </div>
            </div>
        </div>

        <?php } ?>
    <?php } else { ?>
        <div class="text-center py-5">
            <h4 class="text-muted"><i class="bi bi-box-seam display-4 d-block mb-3"></i>ไม่มีสินค้า</h4>
        </div>
    <?php } ?>

    </div>

    <?php if($total_pages > 1){ 
        // สร้าง String สำหรับต่อท้าย URL เพื่อให้การกรองยังคงอยู่เมื่อเปลี่ยนหน้า
        $query_str = "";
        if($brand != '') $query_str .= "&brand=".$brand;
        if($category != '') $query_str .= "&category=".$category;
        if($sort != 'cheap') $query_str .= "&sort=".$sort;
    ?>
    <nav class="mt-5">
        <ul class="pagination justify-content-center">
            <?php for($i = 1; $i <= $total_pages; $i++){ ?>
                <li class="page-item <?=($i == $page) ? 'active' : ''?>">
                    <a class="page-link shadow-sm px-3" href="all_products.php?page=<?=$i?><?=$query_str?>">
                       <?=$i?>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </nav>
    <?php } ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php if(isset($_GET['added'])){ ?>
<script>
Swal.fire({
    icon: 'success',
    title: 'เพิ่มสินค้าลงตะกร้าแล้ว',
    showConfirmButton: false,
    timer: 1500
});
window.history.replaceState(null, null, window.location.pathname);
</script> 
<?php } ?>

<?php include("includes/footer.php"); ?>