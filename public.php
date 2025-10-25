<?php include('config.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Runner's Fuel Café Menu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="home.php">Runner's Fuel Café</a>
    <div class="navbar-nav">
      <a class="nav-link" href="home.php">Home</a>
      <a class="nav-link active" href="public.php">Menu</a>
      <a class="nav-link" href="private.php">Private</a>
    </div>
  </div>
</nav>

<div class="container">
  <h1 class="mb-4 text-center">Our Menu</h1>
  <table class="table table-striped table-bordered text-center align-middle">
    <thead class="table-dark">
      <tr>
        <th>Dish Name</th>
        <th>Category</th>
        <th>Description</th>
        <th>Price ($)</th>
        <th>Attributes</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $query = "
      SELECT
        mi.item_name,
        c.category_name,
        mi.description,
        mi.price,
        GROUP_CONCAT(DISTINCT ia.attribute ORDER BY ia.attribute SEPARATOR ', ') AS attributes
      FROM menu_items mi
      JOIN categories c ON mi.category_id = c.category_id
      LEFT JOIN item_attributes ia ON ia.item_id = mi.item_id
      GROUP BY mi.item_id, c.category_name
      ORDER BY c.category_name, mi.item_name;
      ";

      $result = $conn->query($query);

      if ($result && $result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
              echo "<tr>";
              echo "<td>{$row['item_name']}</td>";
              echo "<td>{$row['category_name']}</td>";
              echo "<td>{$row['description']}</td>";
              echo "<td>{$row['price']}</td>";
              echo "<td>{$row['attributes']}</td>";
              echo "</tr>";
          }
      } else {
          echo "<tr><td colspan='5'>No menu items available.</td></tr>";
      }

      $conn->close();
      ?>
    </tbody>
  </table>
</div>
</body>
</html>
