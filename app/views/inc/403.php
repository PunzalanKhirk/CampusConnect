<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>403 - Access Denied</title>
    <link rel="stylesheet" href="<?= defined('URLROOT') ? URLROOT : '' ?>/css/style.css">
</head>
<body>
    <div style="display:flex;align-items:center;justify-content:center;height:100vh;flex-direction:column;font-family:sans-serif;">
        <h1 style="font-size:2rem;color:#e63946;">403 - Access Denied</h1>
        <p>Your role does not have permission to view this page.</p>
        <a href="<?= defined('URLROOT') ? URLROOT : '' ?>/auth/login">Return to login</a>
    </div>
</body>
</html>
