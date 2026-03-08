<?php
session_start();
include("includes/connectdb.php");

if(!isset($_SESSION['cart'])){
    $_SESSION['cart'] = [];
}

/* ===== ลบสินค้า ===== */
if(isset($_GET['remove'])){
    $remove_id = intval($_GET['remove']);
    unset($_SESSION['cart'][$remove_id]);
    header("Location: cart.php");
    exit();
}

/* ===== AJAX Update ===== */
if(isset($_POST['ajax_update'])){
    $id  = intval($_POST['id']);
    $qty = max(1, intval($_POST['qty']));
    $_SESSION['cart'][$id] = $qty;

    $total = 0;
    foreach($_SESSION['cart'] as $pid => $q){
        $p = mysqli_fetch_assoc(mysqli_query($conn,"SELECT price FROM products WHERE id='$pid'"));
        $total += $p['price'] * $q;
    }

    echo number_format($total,2);
    exit();
}
?>

<?php include("includes/header.php"); ?>

<style>
body{ background:#f6f7fb; }

.cart-container{
    background:#fff;
    border-radius:20px;
    padding:40px;
    box-shadow:0 10px 30px rgba(0,0,0,0.05);
}

.cart-table{
    table-layout:fixed;
}

.cart-table th{
    background:#f1f3f6;
    font-weight:600;
    padding:18px 10px;
}

.cart-table td{
    padding:18px 10px;
    vertical-align:middle;
}

/* กำหนดความกว้างตรงกัน */
.cart-table th:nth-child(1),
.cart-table td:nth-child(1){
    width:45%;
    text-align:left;
}

.cart-table th:nth-child(2),
.cart-table td:nth-child(2){
    width:15%;
    text-align:center;
}

.cart-table th:nth-child(3),
.cart-table td:nth-child(3){
    width:15%;
    text-align:center;
}

.cart-table th:nth-child(4),
.cart-table td:nth-child(4){
    width:15%;
    text-align:center;
}

.cart-table th:nth-child(5),
.cart-table td:nth-child(5){
    width:10%;
    text-align:center;
}

.product-img{
    width:70px;
    height:70px;
    object-fit:cover;
    border-radius:12px;
}

.qty-input{
    width:70px;
    text-align:center;
    margin:auto;
}

/* ปุ่มลบแดง */
.remove-btn{
    background:#ff4d4f;
    color:#fff;
    padding:6px 14px;
    border-radius:8px;
    text-decoration:none;
    font-size:14px;
    transition:0.2s;
}

.remove-btn:hover{
    background:#d9363e;
    color:#fff;
}

.cart-bottom{
    display:flex;
    justify-content:flex-end;
    align-items:center;
    gap:25px;
    margin-top:30px;
}

.cart-total{
    font-size:20px;
}

.cart-total span{
    font-weight:700;
    font-size:22px;
}

.checkout-btn{
    background:#111;
    color:#fff;
    border-radius:30px;
    padding:12px 32px;
}

.checkout-btn:hover{
    background:#333;
}
</style>

<div class="container mt-5 mb-5">
<div class="cart-container">

<h3 class="mb-4">🛒 รถเข็นสินค้า</h3>

<?php if(empty($_SESSION['cart'])){ ?>

<div class="text-center py-5">
    <h5 class="text-muted">ยังไม่มีสินค้าในรถเข็น</h5>
    <a href="index.php" class="btn btn-dark rounded-pill mt-3 px-4">
        เลือกสินค้า
    </a>
</div>

<?php } else { ?>

<div class="table-responsive">
<table class="table cart-table">

<thead>
<tr>
    <th>สินค้า</th>
    <th>ราคา</th>
    <th>จำนวน</th>
    <th>รวม</th>
    <th></th>
</tr>
</thead>

<tbody>

<?php
$total = 0;

foreach($_SESSION['cart'] as $id => $qty){

    $product_q = mysqli_query($conn,"SELECT * FROM products WHERE id='$id'");
    $product = mysqli_fetch_assoc($product_q);

    $subtotal = $product['price'] * $qty;
    $total += $subtotal;
?>

<tr>

<td>
<div class="d-flex align-items-center">
<img src="images/<?=$product['image']?>" class="product-img me-3">
<div>
<div class="fw-semibold"><?=$product['product_name']?></div>
<small class="text-muted"><?=$product['brand']?></small>
</div>
</div>
</td>

<td class="fw-semibold">
฿<?=number_format($product['price'],2)?>
</td>

<td>
<input type="number"
       value="<?=$qty?>"
       min="1"
       data-id="<?=$id?>"
       class="form-control qty-input qty-change">
</td>

<td class="fw-semibold">
฿<?=number_format($subtotal,2)?>
</td>

<td>
<a href="cart.php?remove=<?=$id?>" class="remove-btn">
ลบ
</a>
</td>

</tr>

<?php } ?>

</tbody>
</table>
</div>

<div class="cart-bottom">
    <div class="cart-total">
        รวมทั้งหมด:
        <span id="cartTotal">
            ฿<?=number_format($total,2)?>
        </span>
    </div>

    <a href="checkout.php" class="checkout-btn btn">
        ไปชำระเงิน
    </a>
</div>

<?php } ?>

</div>
</div>

<script>
document.querySelectorAll(".qty-change").forEach(input => {
    input.addEventListener("change", function(){

        let id  = this.dataset.id;
        let qty = this.value;

        fetch("cart.php",{
            method:"POST",
            headers:{ "Content-Type":"application/x-www-form-urlencoded" },
            body:"ajax_update=1&id="+id+"&qty="+qty
        })
        .then(res => res.text())
        .then(total => {
            document.getElementById("cartTotal").innerHTML = "฿"+total;
            location.reload(); // อัปเดต subtotal ให้ตรง
        });

    });
});
</script>

<?php include("includes/footer.php"); ?>