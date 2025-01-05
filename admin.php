<?php

require_once './config/base_url.php';
require_once './controllers/protect.php';
require_once 'controllers/lastVisitedPage.php';
require_once 'controllers/profileValidation.php';
require_once './controllers/adminValidation.php';

// Check if the logged-in user has the "admin" role
if($_SESSION['user']['role'] !== "admin") {
    // If the user is not an admin, redirect to the home page
    header("Location: " . BASE_URL);
    exit();
}

$title = "Admin";
ob_start();

?>
<main class="main-admin">
    <form action="" method="get" class="form" id="admin-category">
        <label <?= $category === 0 ? "class='selected'" : "" ?>>
            <input type="radio" name="cat" value="0" onchange="this.form.submit()">
            <p>Films</p>
        </label>
        <label <?= $category === 1 ? "class='selected'" : "" ?>>
            <input type="radio" name="cat" value="1" onchange="this.form.submit()">
            <p>Utilisateurs</p>
        </label>
        <label <?= $category === 2 ? "class='selected'" : "" ?>>
            <input type="radio" name="cat" value="2" onchange="this.form.submit()">
            <p>Avis</p>
        </label>
    </form>
    <div>
        <?= $content ?>
    </div>

    <?php
    // Prepare parameters for the "Show more" link
    $loadingMoreParam = [
        'cat' => $category == 0 ? null : $category,
        'ia' => $itemsShown < $itemsAmount ? $itemsLoad + 1 : null
    ];

    // Filter out null values from the parameters
    $loadingMoreParam = array_filter($loadingMoreParam, fn($values) => $values !== null);

    // Convert parameters into a query string
    $loadingMoreParamString = http_build_query($loadingMoreParam);

    // Generate the URL for the "Show more" button
    $loadingMoreLink = BASE_URL . "admin.php" . ($loadingMoreParamString ? "?" . $loadingMoreParamString : "");
    
    // If there are more items to display, show the "Show more" button
    if($itemsShown < $itemsAmount): ?>
        <a class="show-more-btn" href="<?= $loadingMoreLink ?>">Show more</a>
    <?php endif; ?>
</main>

<?php $content = ob_get_clean(); ?>

<?php include_once 'template.php'; ?>