<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?> | <?= e(SITE_NAME) ?></title>
    <link rel="stylesheet" href="<?= URLROOT ?>/css/style.css?v=<?= filemtime(__DIR__ . '/../../../public/css/style.css') ?>">
</head>
<body class="auth-body">
    <div class="auth-card card">
        <div class="auth-brand">
            <img class="auth-logo" src="<?= URLROOT ?>/images/1000223978-removebg-preview.png" alt="CampusConnect">
            <span class="auth-wordmark">CampusConnect</span>
        </div>
        <p class="auth-subtitle">Connect, Share, and Learn Together</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $err): ?>
                    <p><?= e($err) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form class="auth-form" action="<?= URLROOT ?>/auth/login" method="POST" novalidate>
            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
            <label for="email">Institutional Email</label>
            <input type="email" id="email" name="email" required autofocus>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" class="auth-submit">Login</button>
        </form>
    </div>
</body>
</html>
