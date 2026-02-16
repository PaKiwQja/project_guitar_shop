<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MBS Guitar Shop</title>

<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="css/style.css">
</head>

<body>

<nav class="navbar navbar-light bg-white shadow-sm py-3 position-relative">
  <div class="container d-flex align-items-center">

    <!-- LEFT -->
    <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
        <img src="images/mbs_logo.png" style="height:55px;">
        <span class="fw-semibold">MBS Guitar Shop</span>
    </a>

    <!-- RIGHT -->
    <div class="ms-auto d-flex align-items-center gap-3">

      <?php if(isset($_SESSION['user_id'])){ ?>

        <a href="cart.php" class="icon-btn">
          <i class="bi bi-bag"></i>
        </a>

        <a href="profile.php" class="icon-btn">
          <i class="bi bi-person"></i>
        </a>

      <?php } else { ?>

        <a href="login.php" class="icon-btn">
          <i class="bi bi-person"></i>
        </a>

      <?php } ?>

    </div>

  </div>


  <!-- ✅ SEARCH CENTER ABSOLUTE -->
  <?php if(!in_array($current_page, ['login.php','register.php'])){ ?>

  <div class="search-center">

      <form method="GET" action="shop.php" class="position-relative">

          <i class="bi bi-search search-icon"></i>

          <input type="text"
                 id="searchInput"
                 name="search"
                 class="search-input"
                 placeholder="ค้นหาสินค้า">

          <div id="searchResult" class="search-result-box"></div>

      </form>

  </div>

  <?php } ?>

</nav>

