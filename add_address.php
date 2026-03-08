<?php
session_start();
include("includes/connectdb.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == "POST"){
    $user_id = $_SESSION['user_id'];
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $district = $_POST['district'];
    $province = $_POST['province'];
    $postal_code = $_POST['postal_code'];

    // ปรับปรุงความปลอดภัย: ใช้ Prepared Statement ป้องกัน SQL Injection
    $stmt = $conn->prepare("
        INSERT INTO user_addresses 
        (user_id, full_name, phone, address, district, province, postal_code) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("issssss", $user_id, $full_name, $phone, $address, $district, $province, $postal_code);
    $stmt->execute();
    $stmt->close();

    header("Location: profile.php?page=address");
    exit();
}
?>

<?php include("includes/header.php"); ?>

<style>
body {
    background: #f8f9fa;
}

.address-box {
    background: #fff;
    padding: 40px;
    border-radius: 24px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.04);
}

.form-label {
    font-weight: 500;
    color: #333;
    margin-bottom: 8px;
}

.form-control {
    border-radius: 12px;
    padding: 12px 15px;
    border: 1px solid #ddd;
    box-shadow: none;
    transition: 0.3s;
}

.form-control:focus {
    border-color: #000;
    box-shadow: 0 0 0 0.2rem rgba(0, 0, 0, 0.05);
}

.btn-save {
    background: #000;
    color: #fff;
    border-radius: 30px;
    padding: 12px 35px;
    font-weight: 500;
    transition: 0.3s;
    border: 1px solid #000;
}

.btn-save:hover {
    background: #333;
    color: #fff;
    transform: translateY(-2px);
}

.btn-cancel {
    border-radius: 30px;
    padding: 12px 35px;
    font-weight: 500;
    color: #555;
    border: 1px solid #ddd;
    transition: 0.3s;
    text-decoration: none;
}

.btn-cancel:hover {
    background: #f8f9fa;
    color: #000;
    border-color: #ccc;
}
</style>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            
            <div class="address-box">
                <h3 class="mb-4 text-center">เพิ่มที่อยู่จัดส่ง</h3>

                <form method="POST">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ชื่อผู้รับ</label>
                            <input type="text" name="full_name" class="form-control" placeholder="ชื่อ - นามสกุล" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">เบอร์โทรศัพท์</label>
                            <input type="text" name="phone" class="form-control" placeholder="08X-XXX-XXXX" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ที่อยู่ (บ้านเลขที่, ซอย, ถนน)</label>
                        <textarea name="address" class="form-control" rows="3" placeholder="กรอกรายละเอียดที่อยู่..." required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">เขต / อำเภอ</label>
                            <input type="text" name="district" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">จังหวัด</label>
                            <input type="text" name="province" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">รหัสไปรษณีย์</label>
                            <input type="text" name="postal_code" class="form-control" required>
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-center gap-3">
                        <a href="profile.php?page=address" class="btn btn-cancel">ยกเลิก</a>
                        <button type="submit" class="btn btn-save">บันทึก</button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>

<?php include("includes/footer.php"); ?>