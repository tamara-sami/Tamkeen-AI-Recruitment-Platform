<?php
$base_url = $base_url ?? "";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Tamkeen</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <link href="<?= $base_url ?>img/favicon.ico" rel="icon">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Rubik:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <link href="<?= $base_url ?>lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <link href="<?= $base_url ?>lib/animate/animate.min.css" rel="stylesheet">

    <link href="<?= $base_url ?>css/bootstrap.min.css" rel="stylesheet">

    <link href="<?= $base_url ?>css/style.css" rel="stylesheet">

    <?php
    if (!empty($extra_css)) {
        foreach ($extra_css as $css) {
            echo '<link href="' . $base_url . $css . '" rel="stylesheet">' . PHP_EOL;
        }
    }
    ?>

</head>

<body class="<?php echo $body_class ?? ''; ?>">