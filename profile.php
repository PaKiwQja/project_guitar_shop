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

    <!-- Sidebar -->
    <div class="col-md-3">
      <div class="profile-sidebar">
        <h5 class="mb-4 fw-semibold">จัดการบัญชีผู้ใช้</h5>

        <a href="profile.php?page=info"
           class="profile-link <?=($page=='info'?'active':'')?>">ข้อมูลส่วนตัว</a>

        <a href="profile.php?page=edit"
           class="profile-link <?=($page=='edit'?'active':'')?>">จัดการข้อมูลส่วนตัว</a>

        <a href="profile.php?page=address"
           class="profile-link <?=($page=='address'?'active':'')?>">จัดการข้อมูลที่อยู่จัดส่ง</a>

        <a href="profile.php?page=orders"
           class="profile-link <?=($page=='orders'?'active':'')?>">ดูประวัติการซื้อ</a>

        <a href="logout.php" class="profile-link text-danger">ออกจากระบบ</a>
      </div>
    </div>

    <!-- Content -->
    <div class="col-md-9">
      <div class="profile-content">

      <!-- INFO -->
      <?php if($page == 'info'){ ?>

        <h3 class="mb-4 fw-semibold">ข้อมูลส่วนตัว</h3>

        <div class="profile-card">
          <p><strong>ชื่อ:</strong> <?=$user['fullname']?></p>
          <p><strong>Email:</strong> <?=$user['email']?></p>
          <p><strong>เบอร์ติดต่อ:</strong> <?=!empty($user['phone']) ? $user['phone'] : '-'?></p>
          <p><strong>Username:</strong> <?=$user['username']?></p>
        </div>

      <?php } ?>


      <!-- EDIT -->
      <?php if($page == 'edit'){ ?>

        <h3 class="mb-4 fw-semibold">จัดการข้อมูลส่วนตัว</h3>

        <form method="POST" action="update_profile.php">

          <div class="mb-3">
            <label class="form-label">ชื่อ-นามสกุล</label>
            <input type="text" name="fullname"
                   value="<?=$user['fullname']?>"
                   class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email"
                   value="<?=$user['email']?>"
                   class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">เบอร์ติดต่อ</label>
            <input type="text" name="phone"
                   value="<?=$user['phone']?>"
                   class="form-control">
          </div>

          <button class="btn btn-dark px-4">บันทึกข้อมูล</button>
        </form>

      <?php } ?>


      <!-- ADDRESS -->
      <?php if($page == 'address'){ ?>

        <h3 class="mb-4 fw-semibold">จัดการข้อมูลที่อยู่จัดส่ง</h3>

        <div class="profile-card text-center py-5">
          <p class="text-muted mb-3">ยังไม่มีที่อยู่จัดส่ง</p>
          <button class="btn btn-outline-dark">เพิ่มที่อยู่ใหม่</button>
        </div>

      <?php } ?>


      <!-- ORDERS -->
      <?php if($page == 'orders'){ ?>

        <h3 class="mb-4 fw-semibold">ดูประวัติการซื้อ</h3>

        <?php
        $status = isset($_GET['status']) ? $_GET['status'] : 'all';

        $allowed_status = ['all','pending','paid','shipped','completed'];
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
            'pending'   => 'รอชำระเงิน',
            'paid'      => 'ชำระเงินแล้ว',
            'shipped'   => 'กำลังจัดส่ง',
            'completed' => 'สำเร็จแล้ว'
        ];
        ?>

        <!-- Tabs -->
        <div class="order-tabs mb-4">
          <a href="profile.php?page=orders&status=all"
             class="<?=($status=='all'?'active':'')?>">ดูคำสั่งซื้อทั้งหมดของฉัน</a>

          <a href="profile.php?page=orders&status=pending"
             class="<?=($status=='pending'?'active':'')?>">ที่ต้องชำระ</a>

          <a href="profile.php?page=orders&status=paid"
             class="<?=($status=='paid'?'active':'')?>">ชำระเงินแล้ว</a>

          <a href="profile.php?page=orders&status=shipped"
             class="<?=($status=='shipped'?'active':'')?>">ที่ต้องได้รับ</a>

          <a href="profile.php?page=orders&status=completed"
             class="<?=($status=='completed'?'active':'')?>">คำสั่งซื้อที่สำเร็จแล้ว</a>
        </div>

        <!-- Order List -->
        <?php if(mysqli_num_rows($order_result) > 0){ ?>

          <?php while($order = mysqli_fetch_assoc($order_result)){ ?>

            <div class="profile-card mb-3">
              <div class="d-flex justify-content-between">
                <div>
                  <strong>รหัสคำสั่งซื้อ:</strong> #<?=$order['id']?><br>
                  <small class="text-muted">
                    วันที่สั่งซื้อ: <?=$order['created_at']?>
                  </small><br>
                  <small>
                    ที่อยู่จัดส่ง: <?=$order['shipping_address']?>
                  </small>
                </div>

                <div class="text-end">
                  <span class="badge bg-dark">
                    <?=$status_text[$order['status']]?>
                  </span>
                  <h6 class="mt-2 mb-0">
                    ฿<?=number_format($order['total_price'],2)?>
                  </h6>
                </div>
              </div>
            </div>

          <?php } ?>

        <?php } else { ?>

          <div class="profile-card text-center py-5">
            <div style="font-size:60px;opacity:0.2;">📦</div>
            <p class="text-muted mt-3 mb-2">ยังไม่มีรายการสั่งซื้อ</p>
            <a href="index.php" class="fw-semibold text-decoration-none">
              ช้อปสินค้าอื่นๆ
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
