<?php
session_start();
include("includes/connectdb.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

// ==========================================
// แยกระบบ ซื้อเลย (Buy Now) กับ ตะกร้า (Cart)
// ==========================================
$buy_now_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$items_to_checkout = [];

if ($buy_now_id > 0) {
    // โหมด "ซื้อเลย": ดึงมาแค่สินค้าที่กด มีจำนวน 1 ชิ้น
    $items_to_checkout[$buy_now_id] = 1; 
} else {
    // โหมด "ตะกร้า": ถ้าไม่ได้กดซื้อเลย แล้วตะกร้าดันว่าง ค่อยเด้งกลับไปหน้า cart
    if(empty($_SESSION['cart'])){
        header("Location: cart.php");
        exit();
    }
    // ดึงของทั้งหมดในตะกร้ามาแสดง
    $items_to_checkout = $_SESSION['cart'];
}

$user_id = $_SESSION['user_id'];
$total = 0;

/* ดึงข้อมูลที่อยู่ของผู้ใช้ */
$addr_q = mysqli_query($conn, "SELECT * FROM user_addresses WHERE user_id = '$user_id'");
$has_address = mysqli_num_rows($addr_q) > 0;
?>

<?php include("includes/header.php"); ?>

<style>
body { background: #f8f9fa; }
.checkout-box { background: #fff; padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); margin-bottom: 20px; }
.section-title { font-size: 1.25rem; font-weight: 600; margin-bottom: 20px; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; }
.option-card { border: 2px solid #eee; border-radius: 12px; padding: 15px; cursor: pointer; transition: all 0.3s ease; height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center; }
.option-card:hover { border-color: #ccc; background: #fafafa; }
.option-input:checked + .option-card { border-color: #000; background: #fffaf0; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
.product-item { display: flex; justify-content: space-between; align-items: center; padding: 15px 0; border-bottom: 1px dashed #eee; }
.product-item:last-child { border-bottom: none; }
.btn-confirm { background: #000; color: #fff; border-radius: 30px; padding: 15px; font-weight: bold; font-size: 1.1rem; width: 100%; transition: 0.3s; border: none; }
.btn-confirm:hover { background: #333; transform: translateY(-2px); }
.payment-logo { height: 35px; object-fit: contain; }
</style>

<div class="container mt-5 mb-5">
    <h2 class="mb-4 fw-bold">ทำการสั่งซื้อ</h2>

    <form action="process_order.php" method="POST" enctype="multipart/form-data">
        <div class="row">
            
            <div class="col-lg-8">
                
                <div class="checkout-box">
                    <h4 class="section-title">📦 ที่อยู่จัดส่ง</h4>
                    <?php if(!$has_address): ?>
                        <div class="alert alert-warning text-center rounded-4 py-4">
                            <i class="bi bi-exclamation-circle fs-1 text-warning mb-2 d-block"></i>
                            <p class="mb-3">คุณยังไม่มีข้อมูลที่อยู่สำหรับจัดส่งสินค้า</p>
                            <a href="profile.php?page=address" class="btn btn-dark rounded-pill px-4">+ เพิ่มที่อยู่จัดส่ง</a>
                        </div>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php 
                            $is_first = true;
                            while($a = mysqli_fetch_assoc($addr_q)): 
                                $full_addr_string = "ชื่อ: " . $a['full_name'] . " โทร: " . $a['phone'] . "\n" . 
                                                    $a['address'] . " อ." . $a['district'] . " จ." . $a['province'] . " " . $a['postal_code'];
                            ?>
                            <div class="col-md-6">
                                <label class="w-100 h-100 m-0">
                                    <input type="radio" name="address" value="<?=$full_addr_string?>" class="d-none option-input" <?=($is_first ? 'checked' : '')?> required>
                                    <div class="option-card align-items-start text-start">
                                        <div class="fw-bold mb-1"><?=$a['full_name']?></div>
                                        <div class="text-muted small mb-2"><i class="bi bi-telephone"></i> <?=$a['phone']?></div>
                                        <p class="small mb-0 text-secondary"><?=$a['address']?> อ.<?=$a['district']?> จ.<?=$a['province']?> <?=$a['postal_code']?></p>
                                    </div>
                                </label>
                            </div>
                            <?php 
                            $is_first = false;
                            endwhile; 
                            ?>
                        </div>
                        <div class="mt-3 text-end">
                            <a href="profile.php?page=address" class="text-decoration-none small text-muted">+ จัดการที่อยู่จัดส่ง</a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="checkout-box">
                    <h4 class="section-title">💳 ช่องทางการชำระเงิน</h4>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="w-100 h-100">
                                <input type="radio" name="payment_method" value="พร้อมเพย์" class="d-none option-input" onchange="togglePayment(this)" required>
                                <div class="option-card py-3">
                                    <img src="images/Prompt_logo.png" alt="พร้อมเพย์" class="payment-logo">
                                </div>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="w-100 h-100">
                                <input type="radio" name="payment_method" value="เก็บเงินปลายทาง" class="d-none option-input" onchange="togglePayment(this)" required>
                                <div class="option-card py-3">
                                    <div class="fw-bold">🚚 เก็บเงินปลายทาง</div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="checkout-box sticky-top" style="top: 20px;">
                    <h4 class="section-title">รายการสินค้า</h4>
                    
                    <div class="mb-4">
                        <?php
                        // เปลี่ยนมาวนลูปจาก $items_to_checkout แทนตะกร้า
                        foreach($items_to_checkout as $id => $qty){
                            $product = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM products WHERE id='$id'"));
                            $subtotal = $product['price'] * $qty;
                            $total += $subtotal;
                        ?>
                        <div class="product-item">
                            <div>
                                <div class="fw-bold text-truncate" style="max-width: 150px;"><?=$product['product_name']?></div>
                                <small class="text-muted">จำนวน: <?=$qty?></small>
                            </div>
                            <div class="fw-bold text-end">฿<?=number_format($subtotal, 2)?></div>
                        </div>
                        <?php } ?>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="fs-5 text-muted">ยอดรวมทั้งสิ้น</span>
                        <span class="fs-3 fw-bold text-danger">฿<?=number_format($total, 2)?></span>
                    </div>

                    <input type="hidden" name="total_price" value="<?=$total?>">
                    
                    <?php if($buy_now_id > 0): ?>
                        <input type="hidden" name="buy_now_id" value="<?=$buy_now_id?>">
                    <?php endif; ?>

                    <div id="payment-slip-section" class="mt-4 pt-4 border-top text-center" style="display: none;">
                        <h6 class="fw-bold mb-3">สแกน QR Code เพื่อชำระเงิน</h6>
                        <img src="images/QR.jpg" alt="QR Code" class="img-fluid rounded mb-3 border p-2" style="max-width: 200px;">
                        <div class="text-start mt-2">
                            <label class="form-label fw-bold text-dark small mb-2">📸 แนบสลิปการโอนเงิน (บังคับ)</label>
                            <input type="file" name="payment_slip" id="slip-input" class="form-control" accept="image/*">
                        </div>
                    </div>

                    <div class="mt-4">
                        <?php if($has_address): ?>
                            <button type="submit" class="btn btn-confirm">สั่งสินค้า</button>
                        <?php else: ?>
                            <button type="button" class="btn btn-confirm" style="background:#ccc; cursor:not-allowed;" disabled>กรุณาเพิ่มที่อยู่ก่อนสั่งซื้อ</button>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

        </div>
    </form>
</div>

<script>
function togglePayment(radio) {
    const slipSection = document.getElementById('payment-slip-section');
    const slipInput = document.getElementById('slip-input');
    if (radio.value === 'พร้อมเพย์') {
        slipSection.style.display = 'block';
        slipInput.required = true;
    } else {
        slipSection.style.display = 'none';
        slipInput.required = false;
        slipInput.value = ''; 
    }
}
</script>

<?php include("includes/footer.php"); ?>