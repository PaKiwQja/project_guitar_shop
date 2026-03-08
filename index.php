<?php
session_start();
include("includes/connectdb.php");
/* ================= FILTER ================= */

$brand = isset($_GET['brand']) ? $_GET['brand'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'cheap';

$where = "WHERE 1=1";

if($brand != ''){
    $brand = mysqli_real_escape_string($conn,$brand);
    $where .= " AND p.brand='$brand'";
}

if($category != ''){
    $category = mysqli_real_escape_string($conn,$category);
    $where .= " AND p.category_id='$category'";
}

/* เรียงราคาถูกที่สุดเป็นค่าเริ่มต้น */
$order = "ORDER BY p.price ASC";

if($sort == "expensive"){
    $order = "ORDER BY p.price DESC";
}

/* JOIN categories เพื่อดึงชื่อประเภท */
$sql = "
SELECT p.*, c.category_name 
FROM products p
LEFT JOIN categories c ON p.category_id = c.id
$where
$order
LIMIT 8
";

$result = mysqli_query($conn,$sql);

include("includes/header.php");
?>

<!-- HERO -->
<style>
  .btn-hero-transparent {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    background-color: transparent !important;
    border: 1px solid #ffffff !important; /* ลดขอบเหลือ 1px ให้ดูบางและมินิมอล */
    color: #ffffff !important;
    padding: 6px 24px !important; /* ลดความกว้างและความสูงของปุ่ม */
    font-size: 0.9rem !important; /* ลดขนาดตัวอักษรลง */
    font-weight: 300 !important; /* ให้ตัวอักษรบางลง */
    border-radius: 50px !important;
    text-decoration: none !important;
    transition: all 0.3s ease !important;
  }
  
  .btn-hero-transparent:hover {
    background-color: #ffffff !important;
    color: #000000 !important;
    transform: translateY(-3px) !important; /* ลอยขึ้นเบาๆ ไม่กระโดดมาก */
    box-shadow: 0 5px 15px rgba(0,0,0,0.2) !important;
  }
</style>

<section class="hero text-center">
  <div class="container">
    <h1 class="fw-bold">กีต้าร์คุณภาพ ราคาดีที่สุด</h1>
    <p class="text-light">เลือกสินค้าที่ใช่สำหรับคุณ</p>
    
    <div class="mt-4">
      <a href="all_products.php" class="btn-hero-transparent">
        <i class="bi bi-music-note-list me-2"></i>สินค้าทั้งหมด
      </a>
    </div>
  </div>
</section>

<!-- BRANDS -->
<div class="container mt-5 mb-5">
  <h2 class="text-center section-title">เลือกแบรนด์สินค้า</h2>

  <div class="row g-4 text-center">

  <?php
    $brands = ["Yamaha","Taylor","Gibson","Kazuki"];
    foreach($brands as $b){

      $active = ($brand==$b) ? "border-primary border-3" : "";
  ?>

    <div class="col-6 col-md-3">
      <a href="index.php?brand=<?=$b?>
      <?php if($category!=''){ ?>&category=<?=$category?><?php } ?>
      <?php if($sort!=''){ ?>&sort=<?=$sort?><?php } ?>"
         class="text-decoration-none text-dark">

        <div class="brand-card p-3 shadow-sm <?=$active?>"
             style="transition:0.3s;">

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



<!-- FILTER BAR -->
<div class="container mt-4">
  <form method="GET" class="d-flex justify-content-center gap-3 flex-wrap">

    <?php if($brand!=''){ ?>
      <input type="hidden" name="brand" value="<?=$brand?>">
    <?php } ?>

    <!-- Category -->
    <select name="category" class="form-select w-auto">
      <option value="">ทุกประเภท</option>
      <?php
      $cat_sql = mysqli_query($conn,"SELECT * FROM categories");
      while($cat = mysqli_fetch_assoc($cat_sql)){
      ?>
        <option value="<?=$cat['id']?>"
          <?=($category==$cat['id']?'selected':'')?>>
          <?=$cat['category_name']?>
        </option>
      <?php } ?>
    </select>

    <!-- Price Sort -->
    <select name="sort" class="form-select w-auto">
      <option value="cheap" <?=($sort=='cheap'?'selected':'')?>>
        ราคาถูก → แพง
      </option>
      <option value="expensive" <?=($sort=='expensive'?'selected':'')?>>
        ราคาแพง → ถูก
      </option>
    </select>

    <button class="btn btn-dark px-4">กรอง</button>

  </form>
</div>


<!-- PRODUCT SECTION -->
<div class="container mt-5">

  <h3 class="text-center mb-4 fw-semibold">
    สินค้าแนะนำ
  </h3>

  <div class="row justify-content-center">

  <?php if(mysqli_num_rows($result) > 0){ ?>
    <?php while($row = mysqli_fetch_assoc($result)){ ?>

      <div class="col-6 col-md-4 col-lg-3 mb-4">

        <div class="card border-0 shadow-sm h-100 text-center p-3">

          <a href="product_detail.php?id=<?=$row['id']?>"
             class="text-decoration-none text-dark">

            <img src="images/<?=$row['image']?>"
                 class="img-fluid mb-3"
                 style="height:180px;object-fit:contain;"
                 onerror="this.src='https://via.placeholder.com/300x200';">

            <div class="fw-semibold">
              <?=$row['product_name']?>
            </div>

            <div class="text-muted small">
              <?=$row['brand']?>
            </div>

            <div class="small text-secondary">
              <?=$row['category_name']?>
            </div>

            <div class="fw-bold mt-2" style="font-size:18px;">
              ฿<?=number_format($row['price'],2)?>
            </div>

          </a>

          <div class="mt-3">

          <?php if(!isset($_SESSION['user_id'])){ ?>

            <a href="login.php"
            class="btn btn-dark w-100 mb-2 rounded-pill">
            ซื้อเลย
            </a>

            <a href="login.php"
            class="btn btn-outline-dark w-100 rounded-pill">
            เพิ่มลงรถเข็น
            </a>

          <?php } else { ?>

            <a href="checkout.php?id=<?=$row['id']?>"
            class="btn btn-dark w-100 mb-2 rounded-pill">
            ซื้อเลย
            </a>

          <form action="add_to_cart.php" method="POST">
            <input type="hidden" name="product_id" value="<?=$row['id']?>">
            <input type="hidden" name="quantity" value="1">
            <button type="submit"
              class="btn btn-outline-dark w-100 rounded-pill">
              เพิ่มลงรถเข็น
            </button>
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
</script> 
<?php } ?>

<?php include("includes/footer.php"); ?>
