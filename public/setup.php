<?php
/**
 * CampusConnect - One-Time Setup / Installer
 * -------------------------------------------------
 * Visit this file once in your browser:
 *   http://localhost/CampusConnect/public/setup.php
 *
 * It will:
 *   1. Create the `campusconnect` database if it doesn't exist
 *   2. Create all tables from config/schema.sql
 *   3. Seed one System Administrator and one Student Moderator account,
 *      with REAL password_hash() values (not placeholders)
 *   4. Seed a few demo posts/reports so the Moderation Queue isn't empty
 *   5. Write a lock file so it can't accidentally be run twice
 *
 * SECURITY: Delete this file (or at least remove it from public/) once
 * setup is complete. It is intentionally blocked from running again by
 * config/.installed, but a setup script should never be left reachable
 * in a real deployment.
 */

require_once dirname(__DIR__) . '/config/config.php';

$lockFile = dirname(__DIR__) . '/config/.installed';
$errors = [];
$result = null;

function h(?string $s): string { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

if (file_exists($lockFile)) {
    $installedAt = trim(file_get_contents($lockFile));
    $alreadyInstalled = true;
} else {
    $alreadyInstalled = false;

    try {
        // Step 1: connect WITHOUT a database name yet, so we can create it
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET,
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `" . DB_NAME . "`");

        // Step 2: run schema.sql (strip comments, split into individual statements)
        $schemaPath = dirname(__DIR__) . '/config/schema.sql';
        $sql = file_get_contents($schemaPath);
        $sql = preg_replace('/^--.*$/m', '', $sql); // strip -- comment lines
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        foreach ($statements as $stmt) {
            if ($stmt !== '') {
                $pdo->exec($stmt);
            }
        }

        // Step 3: seed real accounts with genuinely hashed passwords
        $demoAdminPass = 'Admin@12345';
        $demoModPass   = 'Moderator@12345';

        $adminHash = password_hash($demoAdminPass, PASSWORD_DEFAULT);
        $modHash   = password_hash($demoModPass, PASSWORD_DEFAULT);

        $insertUser = $pdo->prepare(
            'INSERT INTO users (full_name, email, password_hash, role, is_active)
             VALUES (:name, :email, :hash, :role, 1)
             ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)'
        );
        $insertUser->execute([':name' => 'Ada Lovelace', ':email' => 'admin@campusconnect.test', ':hash' => $adminHash, ':role' => 'system_admin']);
        $adminId = (int) $pdo->lastInsertId() ?: (int) $pdo->query("SELECT id FROM users WHERE email='admin@campusconnect.test'")->fetchColumn();

        $insertUser->execute([':name' => 'Sam Rivera', ':email' => 'mod@campusconnect.test', ':hash' => $modHash, ':role' => 'student_moderator']);
        $modId = (int) $pdo->lastInsertId() ?: (int) $pdo->query("SELECT id FROM users WHERE email='mod@campusconnect.test'")->fetchColumn();

        // Step 4: seed a handful of demo posts + reports so the queue has content
        $postCount = (int) $pdo->query('SELECT COUNT(*) FROM posts')->fetchColumn();
        if ($postCount === 0) {
            $demoPosts = [
                ['content' => 'Does anyone have last year\'s notes for Organic Chemistry 202? Final is in two weeks.', 'anon' => 0],
                ['content' => 'Honestly the cafeteria food this week has been inedible, someone needs to say something.', 'anon' => 1],
                ['content' => 'I think a classmate is struggling and posted something concerning in the study group chat. Flagging for a wellness check, not punishment.', 'anon' => 1],
                ['content' => 'Selling my old calculus textbook, DM me an offer. Also here\'s my personal cell number: 555-0192.', 'anon' => 0],
                ['content' => 'This is spam spam spam buy followers now at totally-real-followers.example', 'anon' => 0],
            ];
            $insertPost = $pdo->prepare('INSERT INTO posts (content, is_anonymous, status) VALUES (:content, :anon, "visible")');
            $insertReport = $pdo->prepare(
                'INSERT INTO reports (post_id, reporter_display_id, reason, ai_flagged, status)
                 VALUES (:post_id, :reporter, :reason, :flagged, "pending")'
            );

            $reasons = [
                ['reporter' => 'R-1042', 'reason' => 'Off-topic', 'flagged' => 0],
                ['reporter' => 'R-2291', 'reason' => 'Inappropriate content', 'flagged' => 1],
                ['reporter' => 'R-0087', 'reason' => 'Self-harm concern', 'flagged' => 1],
                ['reporter' => 'R-3355', 'reason' => 'Shared personal contact info', 'flagged' => 1],
                ['reporter' => 'R-1042', 'reason' => 'Spam / scam link', 'flagged' => 1],
            ];

            foreach ($demoPosts as $i => $p) {
                $insertPost->execute([':content' => $p['content'], ':anon' => $p['anon']]);
                $postId = (int) $pdo->lastInsertId();
                $r = $reasons[$i];
                $insertReport->execute([
                    ':post_id' => $postId,
                    ':reporter' => $r['reporter'],
                    ':reason' => $r['reason'],
                    ':flagged' => $r['flagged'],
                ]);
            }
        }

        // Step 5: lock so this can't run twice
        file_put_contents($lockFile, date('Y-m-d H:i:s'));

        $result = [
            'admin' => ['email' => 'admin@campusconnect.test', 'password' => $demoAdminPass],
            'moderator' => ['email' => 'mod@campusconnect.test', 'password' => $demoModPass],
        ];
    } catch (PDOException $e) {
        $errors[] = 'Database error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusConnect Setup</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-body">
    <div class="auth-card card" style="width:480px;">
        <div class="sidebar-logo" style="margin-bottom:20px;">
            <span class="logo-mark">CC</span>
            <span class="logo-text">CampusConnect Setup</span>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $err): ?><p><?= h($err) ?></p><?php endforeach; ?>
            </div>
            <p style="font-size:13px;color:var(--text-secondary);">
                Check that MySQL is running in XAMPP and that <code>config/config.php</code>
                has the correct <code>DB_HOST</code> / <code>DB_USER</code> / <code>DB_PASS</code>.
            </p>

        <?php elseif ($alreadyInstalled): ?>
            <div class="alert alert-success">
                <p><strong>Already installed</strong> (<?= h($installedAt) ?>).</p>
            </div>
            <p style="font-size:13px;color:var(--text-secondary);">
                Delete <code>config/.installed</code> if you intentionally want to re-run setup,
                or just log in below.
            </p>
            <a href="<?= URLROOT ?>/auth/login" class="btn btn-primary btn-block" style="display:block;text-align:center;margin-top:16px;">Go to Login</a>

        <?php else: ?>
            <div class="alert alert-success">
                <p><strong>Setup complete!</strong> Database, tables, and demo accounts were created.</p>
            </div>

            <p style="font-size:13px;font-weight:600;margin-top:18px;">System Administrator</p>
            <p style="font-size:13px;color:var(--text-secondary);margin:2px 0;">
                Email: <code><?= h($result['admin']['email']) ?></code><br>
                Password: <code><?= h($result['admin']['password']) ?></code>
            </p>

            <p style="font-size:13px;font-weight:600;margin-top:14px;">Student Moderator</p>
            <p style="font-size:13px;color:var(--text-secondary);margin:2px 0;">
                Email: <code><?= h($result['moderator']['email']) ?></code><br>
                Password: <code><?= h($result['moderator']['password']) ?></code>
            </p>

            <div class="alert alert-error" style="margin-top:18px;">
                <p><strong>Important:</strong> these are demo credentials. Change both passwords
                after logging in, and delete or restrict access to <code>setup.php</code> now
                that installation is complete.</p>
            </div>

            <a href="<?= URLROOT ?>/auth/login" class="btn btn-primary btn-block" style="display:block;text-align:center;margin-top:16px;">Go to Login</a>
        <?php endif; ?>
    </div>
</body>
</html>
