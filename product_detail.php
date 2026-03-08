<?php
session_start();
include("includes/connectdb.php");

if(!isset($_GET['id'])){
    header("Location: index.php");
    exit();
}

$id = intval($_GET['id']);

/* ================== ดึงข้อมูลสินค้า ================== */
$product_q = mysqli_query($conn,"
    SELECT * FROM products WHERE id = '$id'
");

if(mysqli_num_rows($product_q) == 0){
    echo "ไม่พบสินค้า";
    exit();
}

$product = mysqli_fetch_assoc($product_q);

/* ================== ดึงรูปรอง ================== */
$images_q = mysqli_query($conn,"
    SELECT * FROM product_images 
    WHERE product_id = '$id'
");
?>

<?php include("includes/header.php"); ?>

<style>
body{
    background:#f8f9fa;
}

.product-box {
    background: #fff;
    padding: 50px;
    border-radius: 24px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.04); /* เงาบางๆ ให้กล่องดูลอยขึ้น */
}

/* ฝั่งรูปภาพ */
.main-img-container {
    background: #fdfdfd;
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
    aspect-ratio: 1 / 1;
}

.main-img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.thumb-gallery {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    padding-bottom: 5px;
}

.thumb-img {
    width: 75px;
    height: 75px;
    object-fit: cover;
    border-radius: 12px;
    cursor: pointer;
    border: 2px solid transparent;
    opacity: 0.5; /* รูปรองดูจางลง */
    transition: all 0.3s ease;
}

.thumb-img:hover, .thumb-img.active {
    opacity: 1;
    border: 2px solid #000;
}

/* ฝั่งรายละเอียด */
.price {
    font-size: 32px;
    font-weight: bold;
    color: #000;
    margin: 20px 0;
}

/* กลุ่มปุ่ม Action */
.action-area {
    display: flex;
    gap: 15px;
    align-items: center;
    flex-wrap: wrap; /* หน้าจอเล็กจะปัดตกบรรทัดใหม่อัตโนมัติ */
    margin-top: 30px;
}

.qty-input {
    width: 80px;
    text-align: center;
    border-radius: 30px;
    padding: 12px;
    border: 1px solid #ddd;
    box-shadow: none;
}

.btn-cart {
    border-radius: 30px;
    padding: 12px 30px;
    border: 1px solid #000;
    transition: 0.3s;
}

.btn-cart:hover {
    background: #f8f9fa;
}

.btn-buy {
    background: #000;
    color: #fff;
    border-radius: 30px;
    padding: 12px 35px;
    transition: 0.3s;
    border: 1px solid #000;
}

.btn-buy:hover {
    background: #333;
    color: #fff;
    transform: translateY(-2px);
}
</style>

<div class="container mt-5 mb-5">
    <div class="product-box">
        <div class="row">
            
            <div class="col-lg-6 col-md-12 mb-4 mb-lg-0">
                <?php $main_image = "images/" . $product['image']; ?>
                
                <div class="main-img-container">
                    <img src="<?=$main_image?>" class="main-img" id="mainImage" alt="<?=$product['product_name']?>">
                </div>

                <div class="thumb-gallery">
                    <img src="<?=$main_image?>" 
                         class="thumb-img active" 
                         onclick="changeImage(this)">

                    <?php while($img = mysqli_fetch_assoc($images_q)){ 
                        $sub_image = "images/" . $img['image'];
                    ?>
                        <img src="<?=$sub_image?>" 
                             class="thumb-img" 
                             onclick="changeImage(this)">
                    <?php } ?>
                </div>
            </div>

            <div class="col-lg-6 col-md-12 d-flex flex-column justify-content-center px-lg-4">
                
                <h2><?=$product['product_name']?></h2>
                <p class="text-muted"><?=$product['brand']?></p>
                
                <div class="price">
                    ฿<?=number_format($product['price'], 2)?>
                </div>
                
                <p><?=$product['description']?></p>
                
                <p class="mb-0">สถานะ: 
                    <?php if($product['stock'] > 0): ?>
                        <span class="text-success fw-bold">มีสินค้า (<?=$product['stock']?> ชิ้น)</span>
                    <?php else: ?>
                        <span class="text-danger fw-bold">สินค้าหมด</span>
                    <?php endif; ?>
                </p>

                <?php if($product['stock'] > 0): ?>
                <div class="action-area">
                    <form action="add_to_cart.php" method="POST" class="d-flex align-items-center gap-3 m-0">
                        <input type="hidden" name="product_id" value="<?=$product['id']?>">
                        
                        <input type="number" 
                               name="quantity" 
                               value="1" 
                               min="1" 
                               max="<?=$product['stock']?>"
                               class="form-control qty-input">

                        <button type="submit" class="btn btn-outline-dark btn-cart">
                            เพิ่มลงตะกร้า
                        </button>
                    </form>

                    <a href="checkout.php?id=<?=$product['id']?>" class="btn btn-buy">
                        ซื้อเลย
                    </a>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<script>
function changeImage(element){
    // เปลี่ยนรูปหลัก
    document.getElementById('mainImage').src = element.src;
    
    // ลบคลาส active ออกจากรูปทั้งหมด
    let thumbs = document.querySelectorAll('.thumb-img');
    thumbs.forEach(thumb => thumb.classList.remove('active'));
    
    // เพิ่มคลาส active ให้กับรูปที่ถูกคลิก
    element.classList.add('active');
}
</script>

<?php include("includes/footer.php"); ?>