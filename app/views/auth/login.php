<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?> | <?= e(SITE_NAME) ?></title>
    <link rel="stylesheet" href="<?= URLROOT ?>/css/style.css">
</head>
<body class="auth-body">
    <div class="auth-card card">
        <div class="sidebar-logo" style="margin-bottom:24px;">
            <span class="logo-mark">CC</span>
            <span class="logo-text">CampusConnect</span>
        </div>
        <h1 class="auth-title">Command Hub Sign In</h1>
        <p class="auth-subtitle">Administrators &amp; Moderators only</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $err): ?>
                    <p><?= e($err) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="<?= URLROOT ?>/auth/login" method="POST" novalidate>
            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required autofocus>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
        </form>
    </div>
</body>
</html>
