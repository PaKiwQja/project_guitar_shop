<?php
session_start();
include("includes/connectdb.php");

// ถ้า login แล้ว ไม่ต้องเข้าหน้านี้
if(isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit();
}

$error = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username='$username' LIMIT 1";
    $result = mysqli_query($conn,$sql);

    if(mysqli_num_rows($result) == 1){

        $user = mysqli_fetch_assoc($result);

        if(password_verify($password, $user['password'])){

            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            if($user['role'] == 'admin'){
                header("Location: admin/index2.php");
            } else {
                header("Location: index.php");
            }
            exit();

        } else {
            $error = "รหัสผ่านไม่ถูกต้อง";
        }

    } else {
        $error = "ไม่พบชื่อผู้ใช้ในระบบ";
    }
}

include("includes/header.php");
?>

<div class="main-content">
    <div class="login-wrapper">

        <div class="login-card">

            <h3 class="login-title">เข้าสู่ระบบ</h3>

            <?php if($error != ""){ ?>
                <div class="alert alert-danger"><?=$error?></div>
            <?php } ?>

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">ชื่อผู้ใช้</label>
                    <input type="text" name="username"
                           class="form-control" required>
                </div>

                <div class="mb-4">
                    <label class="form-label">รหัสผ่าน</label>
                    <input type="password" name="password"
                           class="form-control" required>
                </div>

                <button type="submit"
                        class="btn btn-dark w-100 login-btn">
                    เข้าสู่ระบบ
                </button>

            </form>

            <div class="text-center mt-4">
                ยังไม่มีบัญชี?
                <a href="register.php" class="login-link text-dark">
                    สมัครสมาชิก
                </a>
            </div>

        </div>

    </div>
</div>

<?php include("includes/footer.php"); ?>
