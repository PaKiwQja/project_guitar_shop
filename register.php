<?php
session_start();
include("includes/connectdb.php");

if(isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit();
}

$error = "";
$success = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    if($password !== $confirm){
        $error = "รหัสผ่านไม่ตรงกัน";
    } else {

        // เช็ค username ซ้ำ
        $check = mysqli_query($conn, "SELECT id FROM users WHERE username='$username' OR email='$email'");
        
        if(mysqli_num_rows($check) > 0){
            $error = "ชื่อผู้ใช้หรืออีเมลนี้มีในระบบแล้ว";
        } else {

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (fullname,email,username,password,role)
                    VALUES ('$fullname','$email','$username','$hashedPassword','customer')";

            if(mysqli_query($conn,$sql)){
                $success = "สมัครสมาชิกสำเร็จ สามารถเข้าสู่ระบบได้เลย";
            } else {
                $error = "เกิดข้อผิดพลาด กรุณาลองใหม่";
            }
        }
    }
}

include("includes/header.php");
?>

<div class="main-content">
    <div class="login-wrapper">

        <div class="login-card">

            <h3 class="login-title">สมัครสมาชิก</h3>

            <?php if($error != ""){ ?>
                <div class="alert alert-danger"><?=$error?></div>
            <?php } ?>

            <?php if($success != ""){ ?>
                <div class="alert alert-success"><?=$success?></div>
            <?php } ?>

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">ชื่อ-นามสกุล</label>
                    <input type="text" name="fullname"
                           class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">อีเมล</label>
                    <input type="email" name="email"
                           class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">ชื่อผู้ใช้</label>
                    <input type="text" name="username"
                           class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">รหัสผ่าน</label>
                    <input type="password" name="password"
                           class="form-control" required>
                </div>

                <div class="mb-4">
                    <label class="form-label">ยืนยันรหัสผ่าน</label>
                    <input type="password" name="confirm_password"
                           class="form-control" required>
                </div>

                <button type="submit"
                        class="btn btn-dark w-100 login-btn">
                    สมัครสมาชิก
                </button>

            </form>

            <div class="text-center mt-4">
                มีบัญชีแล้ว?
                <a href="login.php" class="login-link text-dark">
                    เข้าสู่ระบบ
                </a>
            </div>

        </div>

    </div>
</div>

<?php include("includes/footer.php"); ?>
