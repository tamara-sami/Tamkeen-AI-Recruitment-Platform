<?php


$base_url = $base_url ?? "/tamkeentest/";

if (!str_ends_with($base_url, "/")) {
    $base_url .= "/";
}

$page_title = $page_title ?? "Tamkeen Dashboard";
$body_class = $body_class ?? "task-dashboard-body";

$default_css = [
    "css/bootstrap.min.css",
    "css/style.css",
    "css/task-dashboard.css",
];

$extra_css = $extra_css ?? [];

$all_css = array_values(array_unique(array_merge($default_css, $extra_css)));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">

    <title><?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?></title>

    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <link href="<?= htmlspecialchars($base_url . 'img/favicon.ico', ENT_QUOTES, 'UTF-8') ?>" rel="icon">

    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Inter:wght@400;600;700;800;900&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <?php foreach ($all_css as $css): ?>
        <?php
        $css = ltrim($css, "/");

        if (str_starts_with($css, "http://") || str_starts_with($css, "https://")) {
            $css_href = $css;
        } else {
            $css_href = $base_url . $css;
        }
        ?>
        <link href="<?= htmlspecialchars($css_href, ENT_QUOTES, 'UTF-8') ?>" rel="stylesheet">
    <?php endforeach; ?>

</head>

<body class="<?= htmlspecialchars($body_class, ENT_QUOTES, 'UTF-8') ?>">
