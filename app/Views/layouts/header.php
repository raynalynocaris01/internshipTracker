<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Internship Tracker' ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <?php if (isset($extraCSS)): ?>
        <style><?= $extraCSS ?></style>
    <?php endif; ?>
</head>
<body>