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
.product-box{
    background:#fff;
    padding:40px;
    border-radius:20px;
}
.main-img{
    width:100%;
    border-radius:15px;
    object-fit:contain;
}
.thumb-img{
    width:80px;
    height:80px;
    object-fit:cover;
    border-radius:10px;
    cursor:pointer;
    margin:5px;
    border:2px solid transparent;
    transition:0.2s;
}
.thumb-img:hover{
    border:2px solid #000;
}
.price{
    font-size:28px;
    font-weight:bold;
}
.btn-buy{
    background:#000;
    color:#fff;
    border-radius:30px;
    padding:10px 25px;
}
.btn-cart{
    border-radius:30px;
    padding:10px 25px;
}
</style>

<div class="container mt-5 mb-5">
<div class="product-box">

<div class="row">

<!-- ================== รูปสินค้า ================== -->
<div class="col-md-6">

<?php
// เตรียม path รูปหลัก
$main_image = "images/" . $product['image'];
?>

<img src="<?=$main_image?>"
     class="main-img mb-3"
     id="mainImage">

<div class="d-flex flex-wrap">

<!-- รูปหลักเป็น thumbnail -->
<img src="<?=$main_image?>"
     class="thumb-img"
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

<!-- ================== รายละเอียด ================== -->
<div class="col-md-6">

<h2><?=$product['product_name']?></h2>
<p class="text-muted"><?=$product['brand']?></p>

<p class="price">
฿<?=number_format($product['price'],2)?>
</p>

<p><?=$product['description']?></p>

<p>คงเหลือ <?=$product['stock']?> ชิ้น</p>

<div class="mt-4">

<a href="checkout.php?id=<?=$product['id']?>"
   class="btn btn-buy me-2">
ซื้อเลย
</a>

<a href="add_to_cart.php?id=<?=$product['id']?>"
   class="btn btn-outline-dark btn-cart">
เพิ่มลงตะกร้า
</a>

</div>

</div>

</div>
</div>
</div>

<script>
function changeImage(img){
    document.getElementById('mainImage').src = img.src;
}
</script>

<?php include("includes/footer.php"); ?>
