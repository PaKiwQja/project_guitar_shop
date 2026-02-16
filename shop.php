<?php
session_start();
include("includes/connectdb.php");

$where = [];
$search = "";
$brand = "";
$category = "";

// ค้นหาจากช่อง search
if(isset($_GET['search']) && $_GET['search'] != ""){
    $search = mysqli_real_escape_string($conn,$_GET['search']);
    $where[] = "product_name LIKE '%$search%'";
}

// กรองแบรนด์
if(isset($_GET['brand']) && $_GET['brand'] != ""){
    $brand = mysqli_real_escape_string($conn,$_GET['brand']);
    $where[] = "brand='$brand'";
}

// กรองประเภท
if(isset($_GET['category']) && $_GET['category'] != ""){
    $category = mysqli_real_escape_string($conn,$_GET['category']);
    $where[] = "category_id='$category'";
}

$sql = "SELECT * FROM products";

if(count($where) > 0){
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY id DESC";

$result = mysqli_query($conn,$sql);

include("includes/header.php");
?>

<div class="main-content container mt-5">

    <h3 class="mb-4">สินค้าทั้งหมด</h3>

    <!-- ===== FILTER ===== -->
    <form method="GET" class="row g-3 mb-4">

        <div class="col-md-4">
            <input type="text" name="search"
                   value="<?=$search?>"
                   class="form-control"
                   placeholder="ค้นหาสินค้า...">
        </div>

        <div class="col-md-3">
            <select name="brand" class="form-select">
                <option value="">ทุกแบรนด์</option>
                <option value="Yamaha">Yamaha</option>
                <option value="Taylor">Taylor</option>
                <option value="Gibson">Gibson</option>
                <option value="Kazuki">Kazuki</option>
            </select>
        </div>

        <div class="col-md-3">
            <select name="category" class="form-select">
                <option value="">ทุกประเภท</option>
                <option value="1">กีต้าร์โปร่ง</option>
                <option value="2">โปร่งไฟฟ้า</option>
                <option value="3">กีต้าร์ไฟฟ้า</option>
                <option value="4">กีต้าร์คลาสสิค</option>
                <option value="5">ไซเลนท์กีตาร์</option>
            </select>
        </div>

        <div class="col-md-2">
            <button class="btn btn-dark w-100">
                กรอง
            </button>
        </div>

    </form>

    <!-- ===== PRODUCTS ===== -->
    <div class="row">

    <?php if(mysqli_num_rows($result) > 0){ ?>

        <?php while($row = mysqli_fetch_assoc($result)){ ?>

            <div class="col-md-3 mb-4">
                <div class="card h-100 shadow-sm">

                    <img src="images/<?=$row['image']?>"
                         class="card-img-top"
                         style="height:200px;object-fit:cover;"
                         onerror="this.src='https://via.placeholder.com/300x200';">

                    <div class="card-body d-flex flex-column">
                        <h6><?=$row['product_name']?></h6>
                        <small class="text-muted"><?=$row['brand']?></small>

                        <h5 class="text-dark mt-2">
                            ฿<?=number_format($row['price'],2)?>
                        </h5>

                        <a href="product_detail.php?id=<?=$row['id']?>"
                           class="btn btn-outline-dark btn-sm mt-auto">
                           ดูรายละเอียด
                        </a>
                    </div>

                </div>
            </div>

        <?php } ?>

    <?php } else { ?>

        <div class="col-12">
            <div class="alert alert-warning">
                ไม่พบสินค้าที่ค้นหา
            </div>
        </div>

    <?php } ?>

    </div>

</div>

<?php include("includes/footer.php"); ?>
