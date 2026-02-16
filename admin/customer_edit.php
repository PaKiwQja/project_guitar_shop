<?php
session_start();
include("../includes/connectdb.php");

if(!isset($_SESSION['user_id']) || $_SESSION['role']!='admin'){
    header("Location: ../login.php");
    exit();
}

if(!isset($_GET['id'])){
    header("Location: customers.php");
    exit();
}

$id = intval($_GET['id']);

$user_q = mysqli_query($conn,"SELECT * FROM users WHERE id='$id'");
$user = mysqli_fetch_assoc($user_q);

if(!$user){
    header("Location: customers.php");
    exit();
}

/* ===== บันทึกการแก้ไข ===== */
if(isset($_POST['update'])){

    $fullname = $_POST['fullname'];
    $email    = $_POST['email'];
    $phone    = $_POST['phone'];

    mysqli_query($conn,"
        UPDATE users SET
        fullname='$fullname',
        email='$email',
        phone='$phone'
        WHERE id='$id'
    ");

    header("Location: customers.php");
    exit();
}
?>

<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>แก้ไขลูกค้า</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
body{font-family:'Kanit',sans-serif;background:#f4f7f6;}
.card-box{background:white;padding:30px;border-radius:15px;max-width:600px;margin:auto;margin-top:50px;}
</style>
</head>

<body>

<div class="card-box">
<h4 class="mb-4">แก้ไขข้อมูลลูกค้า</h4>

<form method="POST">

<div class="mb-3">
<label>ชื่อผู้ใช้</label>
<input type="text" class="form-control"
value="<?=$user['username']?>" disabled>
</div>

<div class="mb-3">
<label>ชื่อ-นามสกุล</label>
<input type="text" name="fullname"
class="form-control"
value="<?=$user['fullname']?>" required>
</div>

<div class="mb-3">
<label>Email</label>
<input type="email" name="email"
class="form-control"
value="<?=$user['email']?>" required>
</div>

<div class="mb-3">
<label>เบอร์โทร</label>
<input type="text" name="phone"
class="form-control"
value="<?=$user['phone']?>">
</div>

<button type="submit" name="update"
class="btn btn-primary">
บันทึก
</button>

<a href="customers.php"
class="btn btn-secondary">
ยกเลิก
</a>

</form>
</div>

</body>
</html>
