<?php
include("includes/connectdb.php");

if(isset($_GET['keyword'])){

    $keyword = mysqli_real_escape_string($conn,$_GET['keyword']);

    $sql = "SELECT id, product_name, price, image 
            FROM products 
            WHERE product_name LIKE '%$keyword%'
            LIMIT 5";

    $result = mysqli_query($conn,$sql);

    if(mysqli_num_rows($result) > 0){

        while($row = mysqli_fetch_assoc($result)){ ?>

            <div class="search-item"
                 onclick="window.location='product_detail.php?id=<?=$row['id']?>'">

                <img src="images/<?=$row['image']?>">

                <div>
                    <div><?=$row['product_name']?></div>
                    <div class="search-price">
                        ฿<?=number_format($row['price'],2)?>
                    </div>
                </div>

            </div>

        <?php }

    } else {
        echo "<div class='p-3 text-muted'>ไม่พบสินค้า</div>";
    }

}
?>
