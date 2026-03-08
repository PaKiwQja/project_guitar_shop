<?php
session_start();
include("includes/connectdb.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ดึงข้อมูลผู้ใช้
$sql = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn,$sql);
$user = mysqli_fetch_assoc($result);

// จัดการ page
$allowed_pages = ['info','edit','address','orders'];
$page = isset($_GET['page']) && in_array($_GET['page'],$allowed_pages)
        ? $_GET['page']
        : 'info';
?>

<?php include("includes/header.php"); ?>

<main class="main-content">
<div class="container my-5">
  <div class="row g-4">

    <div class="col-md-3">
      <div class="profile-sidebar">
        <h5 class="mb-4 fw-semibold">จัดการบัญชีผู้ใช้</h5>
        <a href="profile.php?page=info" class="profile-link <?=($page=='info'?'active':'')?>">ข้อมูลส่วนตัว</a>
        <a href="profile.php?page=edit" class="profile-link <?=($page=='edit'?'active':'')?>">จัดการข้อมูลส่วนตัว</a>
        <a href="profile.php?page=address" class="profile-link <?=($page=='address'?'active':'')?>">จัดการข้อมูลที่อยู่จัดส่ง</a>
        <a href="profile.php?page=orders" class="profile-link <?=($page=='orders'?'active':'')?>">ดูประวัติการซื้อ</a>
        <a href="logout.php" class="profile-link text-danger">ออกจากระบบ</a>
      </div>
    </div>

    <div class="col-md-9">
      <div class="profile-content">

      <?php if($page == 'info'){ ?>
        <h3 class="mb-4 fw-semibold">ข้อมูลส่วนตัว</h3>
        <div class="profile-card">
          <p><strong>ชื่อ:</strong> <?=$user['fullname']?></p>
          <p><strong>Email:</strong> <?=$user['email']?></p>
          <p><strong>เบอร์ติดต่อ:</strong> <?=!empty($user['phone']) ? $user['phone'] : '-'?></p>
          <p><strong>Username:</strong> <?=$user['username']?></p>
        </div>
      <?php } ?>

      <?php if($page == 'edit'){ ?>
        <h3 class="mb-4 fw-semibold">จัดการข้อมูลส่วนตัว</h3>
        <form method="POST" action="update_profile.php">
          <div class="mb-3">
            <label class="form-label">ชื่อ-นามสกุล</label>
            <input type="text" name="fullname" value="<?=$user['fullname']?>" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" value="<?=$user['email']?>" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">เบอร์ติดต่อ</label>
            <input type="text" name="phone" value="<?=$user['phone']?>" class="form-control">
          </div>
          <button class="btn btn-dark px-4">บันทึกข้อมูล</button>
        </form>
      <?php } ?>

      <?php if($page == 'address'){ ?>
        <?php
        $address_q = mysqli_query($conn,"SELECT * FROM user_addresses WHERE user_id = '$user_id'");
        ?>
        <h3 class="mb-4">จัดการข้อมูลที่อยู่จัดส่ง</h3>
        <a href="add_address.php" class="btn btn-dark mb-3">เพิ่มที่อยู่ใหม่</a>

        <?php if(mysqli_num_rows($address_q) == 0){ ?>
            <div class="alert alert-secondary">ยังไม่มีที่อยู่จัดส่ง</div>
        <?php } else { ?>
            <?php while($addr = mysqli_fetch_assoc($address_q)){ ?>
            <div class="card mb-3 p-3">
                <strong><?=$addr['full_name']?></strong><br>
                โทร: <?=$addr['phone']?><br>
                <?=$addr['address']?> <?=$addr['district']?> <?=$addr['province']?> <?=$addr['postal_code']?>
                <div class="mt-2">
                    <a href="edit_address.php?id=<?=$addr['id']?>" class="btn btn-sm btn-warning">แก้ไข</a>
                    <a href="delete_address.php?id=<?=$addr['id']?>" class="btn btn-sm btn-danger" onclick="return confirm('ลบที่อยู่นี้?')">ลบ</a>
                </div>
            </div>
            <?php } ?>
        <?php } ?>
      <?php } ?>

      <?php if($page == 'orders'){ ?>
        <h3 class="mb-4 fw-semibold">ดูประวัติการซื้อ</h3>

        <?php
        $status = isset($_GET['status']) ? $_GET['status'] : 'all';

        $allowed_status = ['all','shipped','completed','cancelled'];
        if(!in_array($status,$allowed_status)){
            $status = 'all';
        }

        $where = "WHERE user_id = '$user_id'";
        if($status != 'all'){
            $where .= " AND status = '$status'";
        }

        $order_sql = "SELECT * FROM orders $where ORDER BY id DESC";
        $order_result = mysqli_query($conn,$order_sql);

        $status_text = [
            'pending'   => 'รอตรวจสอบ/รอยืนยัน',
            'paid'      => 'เตรียมจัดส่ง',
            'shipped'   => 'ที่ต้องได้รับ',
            'completed' => 'สำเร็จแล้ว',
            'cancelled' => 'ยกเลิกแล้ว'
        ];
        ?>

        <div class="order-tabs mb-4 d-flex flex-wrap gap-2 pb-2">
          <a href="profile.php?page=orders&status=all"
             class="<?=($status=='all'?'active fw-bold text-dark text-decoration-none':'text-muted text-decoration-none')?> me-4">ดูคำสั่งซื้อทั้งหมด</a>

          <a href="profile.php?page=orders&status=shipped"
             class="<?=($status=='shipped'?'active fw-bold text-dark text-decoration-none':'text-muted text-decoration-none')?> me-4">ที่ต้องได้รับ</a>

          <a href="profile.php?page=orders&status=completed"
             class="<?=($status=='completed'?'active fw-bold text-dark text-decoration-none':'text-muted text-decoration-none')?> me-4">สำเร็จแล้ว</a>

          <a href="profile.php?page=orders&status=cancelled"
             class="<?=($status=='cancelled'?'active fw-bold text-dark text-decoration-none':'text-muted text-decoration-none')?>">ยกเลิก</a>
        </div>

        <?php if(mysqli_num_rows($order_result) > 0){ ?>

          <?php while($order = mysqli_fetch_assoc($order_result)){ 
              
              $badge_class = 'bg-secondary';
              if($order['status']=='pending') $badge_class = 'bg-warning text-dark';
              if($order['status']=='paid') $badge_class = 'bg-primary';
              if($order['status']=='shipped') $badge_class = 'bg-info text-dark';
              if($order['status']=='completed') $badge_class = 'bg-success';
              if($order['status']=='cancelled') $badge_class = 'bg-danger';

              $payment_txt = "";
              if($order['payment_method'] == 'เก็บเงินปลายทาง') {
                  if($order['status'] == 'completed') {
                      $payment_txt = '<span class="text-success small fw-bold"><i class="bi bi-check-circle"></i> ชำระเงินแล้ว</span>';
                  } else if($order['status'] == 'cancelled') {
                      $payment_txt = '<span class="text-danger small"><i class="bi bi-x-circle"></i> ยกเลิก</span>';
                  } else {
                      $payment_txt = '<span class="text-warning small fw-bold"><i class="bi bi-cash-stack"></i> รอชำระปลายทาง</span>';
                  }
              } else {
                  if($order['status'] == 'pending') {
                      $payment_txt = '<span class="text-warning small fw-bold"><i class="bi bi-hourglass-split"></i> รอตรวจสอบสลิป</span>';
                  } else if($order['status'] == 'cancelled') {
                      $payment_txt = '<span class="text-danger small"><i class="bi bi-x-circle"></i> ยกเลิก</span>';
                  } else {
                      $payment_txt = '<span class="text-success small fw-bold"><i class="bi bi-check-circle"></i> ชำระเงินแล้ว</span>';
                  }
              }
          ?>

            <div class="profile-card mb-3 p-4 border rounded shadow-sm bg-white">
              <div class="d-flex justify-content-between border-bottom pb-3 mb-3">
                <div>
                  <strong class="text-dark fs-5">#ORD-<?=$order['id']?></strong>
                  <span class="ms-2 badge <?=$badge_class?> px-2 py-1"><?=$status_text[$order['status']] ?? $order['status']?></span>
                </div>
                <div class="text-end">
                    <small class="text-muted d-block">สั่งซื้อเมื่อ: <?=date("d M Y H:i", strtotime($order['created_at']))?></small>
                </div>
              </div>

              <div class="d-flex justify-content-between align-items-center mt-2">
                  <div>
                    <small class="text-muted d-block mb-1">
                      <i class="bi bi-geo-alt-fill text-danger"></i> จัดส่ง: <?=$order['shipping_address']?>
                    </small>
                    <small class="text-muted d-block mb-1">
                      <i class="bi bi-wallet2 text-primary"></i> วิธีชำระเงิน: <?=$order['payment_method']?>
                    </small>
                    <div class="mt-2 p-2 bg-light d-inline-block rounded">
                        สถานะการเงิน: <?=$payment_txt?>
                    </div>
                  </div>

                  <div class="text-end">
                    <small class="text-muted">ยอดรวมทั้งสิ้น</small>
                    <h4 class="mt-1 mb-2 text-danger fw-bold">
                      ฿<?=number_format($order['total_price'],2)?>
                    </h4>
                    
                    <?php if($order['status'] == 'pending'){ ?>
                        <a href="cancel_order.php?id=<?=$order['id']?>" 
                           class="btn btn-outline-danger btn-sm px-3 rounded-pill"
                           onclick="return confirm('คุณต้องการยกเลิกคำสั่งซื้อ #ORD-<?=$order['id']?> ใช่หรือไม่?\n(ระบบจะยกเลิกและคืนสต็อกสินค้าให้ร้านค้าอัตโนมัติ)')">
                           ยกเลิกคำสั่งซื้อ
                        </a>
                    <?php } ?>

                  </div>
              </div>
            </div>

          <?php } ?>

        <?php } else { ?>
          <div class="profile-card text-center py-5 bg-white rounded border">
            <div style="font-size:60px;opacity:0.2;">📦</div>
            <p class="text-muted mt-3 mb-2">ยังไม่มีรายการสั่งซื้อในหมวดหมู่นี้</p>
            <a href="index.php" class="btn btn-dark mt-3 rounded-pill px-4">
              ช้อปสินค้าเพิ่มเติม
            </a>
          </div>
        <?php } ?>

      <?php } ?>

      </div>
    </div>

  </div>
</div>
</main>

<?php include("includes/footer.php"); ?>