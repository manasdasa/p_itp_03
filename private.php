<?php
require_once __DIR__ . '/config.php';

function clean_text(string $value, mysqli $conn): string
{
    $value = trim($value);
    $value = strip_tags($value);
    $value = preg_replace('/[\x00-\x1F\x7F]+/u', '', $value);
    $value = preg_replace('/[\r\n\t]+/', ' ', $value);
    return $conn->real_escape_string($value);
}

$errors = [];
$successMessage = '';
$submitted = [
    'dish_name' => '',
    'dish_price' => '',
    'dish_description' => '',
    'category_id' => '',
    'location_id' => '',
    'attributes' => [],
];

$categories = [];
if ($categoryResult = $conn->query('SELECT category_id, category_name FROM categories ORDER BY category_name')) {
    while ($row = $categoryResult->fetch_assoc()) {
        $categories[(int) $row['category_id']] = $row['category_name'];
    }
    $categoryResult->free();
}

$locations = [];
if ($locationResult = $conn->query('SELECT location_id, name FROM locations ORDER BY name')) {
    while ($row = $locationResult->fetch_assoc()) {
        $locations[(int) $row['location_id']] = $row['name'];
    }
    $locationResult->free();
}

$attributeOptions = [];
if ($attributeResult = $conn->query('SELECT DISTINCT attribute FROM item_attributes ORDER BY attribute')) {
    while ($row = $attributeResult->fetch_assoc()) {
        $attributeOptions[] = $row['attribute'];
    }
    $attributeResult->free();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted['dish_name'] = $_POST['dish_name'] ?? '';
    $submitted['dish_price'] = $_POST['dish_price'] ?? '';
    $submitted['dish_description'] = $_POST['dish_description'] ?? '';
    $submitted['category_id'] = $_POST['category_id'] ?? '';
    $submitted['location_id'] = $_POST['location_id'] ?? '';
    $submitted['attributes'] = isset($_POST['attributes']) ? (array) $_POST['attributes'] : [];

    $dishNameRaw = $submitted['dish_name'];
    if (trim($dishNameRaw) === '') {
        $errors[] = 'Dish name is required.';
    } elseif (mb_strlen(trim($dishNameRaw)) > 120) {
        $errors[] = 'Dish name must be 120 characters or fewer.';
    } else {
        $dishNameClean = clean_text($dishNameRaw, $conn);
    }

    $priceValue = null;
    $priceRaw = trim($submitted['dish_price']);
    if ($priceRaw === '') {
        $errors[] = 'Dish price is required.';
    } else {
        $priceSanitized = filter_var($priceRaw, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        if ($priceSanitized === '' || !is_numeric($priceSanitized)) {
            $errors[] = 'Dish price must be a valid number.';
        } else {
            $priceValue = (float) $priceSanitized;
            if ($priceValue <= 0) {
                $errors[] = 'Dish price must be greater than zero.';
            }
        }
    }

    $descriptionRaw = $submitted['dish_description'];
    if (trim($descriptionRaw) === '') {
        $errors[] = 'Dish description is required.';
    } elseif (mb_strlen(trim($descriptionRaw)) > 500) {
        $errors[] = 'Dish description must be 500 characters or fewer.';
    } else {
        $descriptionClean = clean_text($descriptionRaw, $conn);
    }

    $categoryId = (int) $submitted['category_id'];
    if ($categoryId === 0 || !array_key_exists($categoryId, $categories)) {
        $errors[] = 'Please select a valid category.';
    }

    $locationId = (int) $submitted['location_id'];
    if ($locationId === 0 || !array_key_exists($locationId, $locations)) {
        $errors[] = 'Please select a valid location.';
    }

    $cleanAttributes = [];
    if (!empty($submitted['attributes'])) {
        foreach ($submitted['attributes'] as $attributeValue) {
            if (!in_array($attributeValue, $attributeOptions, true)) {
                $errors[] = 'An invalid attribute was provided.';
                break;
            }
            $cleanAttributes[] = clean_text($attributeValue, $conn);
        }
        $cleanAttributes = array_unique($cleanAttributes);
    }

    if (empty($errors) && $priceValue !== null) {
        $priceFormatted = number_format($priceValue, 2, '.', '');

        $insertItemSql = "INSERT INTO menu_items (location_id, category_id, item_name, price, description) VALUES ($locationId, $categoryId, '$dishNameClean', '$priceFormatted', '$descriptionClean')";
        if ($conn->query($insertItemSql) === true) {
            $itemId = $conn->insert_id;

            foreach ($cleanAttributes as $attributeCleaned) {
                $conn->query("INSERT INTO item_attributes (item_id, attribute) VALUES ($itemId, '$attributeCleaned')");
            }

            $successMessage = 'Dish successfully added to the menu.';
            $submitted = [
                'dish_name' => '',
                'dish_price' => '',
                'dish_description' => '',
                'category_id' => '',
                'location_id' => '',
                'attributes' => [],
            ];
        } else {
            $errors[] = 'There was a problem saving the dish. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Private Menu Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="home.php">Runner's Fuel Café</a>
    <div class="navbar-nav">
      <a class="nav-link" href="home.php">Home</a>
      <a class="nav-link" href="public.php">Menu</a>
      <a class="nav-link active" href="private.php">Private</a>
    </div>
  </div>
</nav>

<div class="container">
    <h1 class="mb-3">Add a New Dish</h1>
    <p class="text-muted">Use this form to add dishes to the Runner's Fuel Café menu. All fields are required.</p>

    <?php if (!empty($errors)) : ?>
        <div class="alert alert-danger" role="alert">
            <h2 class="h5">Please correct the following:</h2>
            <ul class="mb-0">
                <?php foreach ($errors as $error) : ?>
                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($successMessage !== '') : ?>
        <div class="alert alert-success" role="alert">
            <?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-5">
        <div class="card-body">
            <form method="post" novalidate>
                <div class="mb-3">
                    <label for="dish_name" class="form-label">Dish Name</label>
                    <input
                        type="text"
                        class="form-control"
                        id="dish_name"
                        name="dish_name"
                        maxlength="120"
                        value="<?= htmlspecialchars($submitted['dish_name'], ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label for="dish_price" class="form-label">Dish Price</label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        class="form-control"
                        id="dish_price"
                        name="dish_price"
                        value="<?= htmlspecialchars($submitted['dish_price'], ENT_QUOTES, 'UTF-8') ?>"
                        required
                    >
                    <div class="form-text">Enter a numeric value (e.g., 9.99).</div>
                </div>

                <div class="mb-3">
                    <label for="dish_description" class="form-label">Dish Description</label>
                    <textarea
                        class="form-control"
                        id="dish_description"
                        name="dish_description"
                        rows="4"
                        maxlength="500"
                        required
                    ><?= htmlspecialchars($submitted['dish_description'], ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="" disabled <?= $submitted['category_id'] === '' ? 'selected' : '' ?>>Select a category</option>
                            <?php foreach ($categories as $id => $name) : ?>
                                <option value="<?= $id ?>" <?= (string) $id === (string) $submitted['category_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="location_id" class="form-label">Location</label>
                        <select class="form-select" id="location_id" name="location_id" required>
                            <option value="" disabled <?= $submitted['location_id'] === '' ? 'selected' : '' ?>>Select a location</option>
                            <?php foreach ($locations as $id => $name) : ?>
                                <option value="<?= $id ?>" <?= (string) $id === (string) $submitted['location_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <fieldset class="mb-4">
                    <legend class="fs-6">Dish Attributes</legend>
                    <p class="text-muted">Select all attributes that apply.</p>
                    <?php if (!empty($attributeOptions)) : ?>
                        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-2">
                            <?php foreach ($attributeOptions as $attribute) : ?>
                                <?php
                                $attributeId = 'attr-' . preg_replace('/[^a-z0-9]+/i', '-', strtolower($attribute));
                                $isChecked = in_array($attribute, $submitted['attributes'], true);
                                ?>
                                <div class="col">
                                    <div class="form-check">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            id="<?= htmlspecialchars($attributeId, ENT_QUOTES, 'UTF-8') ?>"
                                            name="attributes[]"
                                            value="<?= htmlspecialchars($attribute, ENT_QUOTES, 'UTF-8') ?>"
                                            <?= $isChecked ? 'checked' : '' ?>
                                        >
                                        <label class="form-check-label" for="<?= htmlspecialchars($attributeId, ENT_QUOTES, 'UTF-8') ?>">
                                            <?= htmlspecialchars($attribute, ENT_QUOTES, 'UTF-8') ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <p class="text-muted fst-italic">No attributes are available yet. Add attributes via the database to enable selection.</p>
                    <?php endif; ?>
                </fieldset>

                <button type="submit" class="btn btn-primary">Add Dish</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
<?php
$conn->close();
