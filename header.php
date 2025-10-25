<?php
if (!isset($page)) { $page = ''; }
if (!isset($title)) { $title = 'Site'; }

// Compute a path prefix relative to where the script is running.
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$to = function ($file) use ($base) {
  return $base . '/' . ltrim($file, '/');
};
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($title); ?></title>
  <!-- Framework (grid) stylesheet -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Your site stylesheet -->
  <link href="<?php echo $to('css/styles.css'); ?>" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="<?php echo $to('home.php'); ?>">My PHP Site</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div id="nav" class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link <?php echo $page==='home'?'active':''; ?>" href="<?php echo $to('home.php'); ?>">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo $page==='public'?'active':''; ?>" href="<?php echo $to('public.php'); ?>">Public</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo $page==='private'?'active':''; ?>" href="<?php echo $to('private.php'); ?>">Private</a>
        </li>
      </ul>
      <button id="discountBtn" class="btn btn-warning ms-3" type="button">Check Discount</button>
    </div>
  </div>
</nav>

<main class="py-5">
  <div class="container">
