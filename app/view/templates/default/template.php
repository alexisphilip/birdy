<!DOCTYPE html>
<html lang="<?= $language_code ?>">
<head>
    <!-- Title -->
    <title><?= $page_title ?></title>

    <!-- File encoding -->
    <meta charset="UTF-8">

    <!-- Viewport -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Meta elements -->
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="author" content="">

    <!-- CSS files -->
    <?php foreach ($css_urls as $css_url) { ?>
        <link href="<?= css_url($css_url) ?>" rel="stylesheet">
    <?php } ?>
</head>
<body>

    <?php include($view_path) ?>

    <!-- JS files -->
    <?php foreach ($js_urls as $js_url) { ?>
        <script src="<?= js_url($js_url) ?>"></script>
    <?php } ?>

</body>
</html>
