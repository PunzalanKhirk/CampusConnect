<?php $active = 'home'; require dirname(__DIR__) . '/inc/header.php'; ?>
    <?php require dirname(__DIR__) . '/inc/sidebar.php'; ?>

    <div class="main-col">
        <header class="topbar">
            <div class="search-bar">
                <span class="search-icon" aria-hidden="true">&#128269;</span>
                <input type="text" placeholder="Search users, reports, logs...">
            </div>
            <div class="user-indicator">
                <span class="user-name"><?= e($userName) ?></span>
                <span class="user-role-badge">System Administrator</span>
                <span class="avatar"><?= e(strtoupper(substr($userName, 0, 1))) ?></span>
            </div>
        </header>

        <main class="content">
            <h1 class="page-title">System Analytics &amp; RBAC Management</h1>
            <div class="card" style="padding:24px;">
                <p>Welcome, <?= e($userName) ?>. This is the System Administrator workspace
                   (RBAC management + analytics widgets go here).</p>
            </div>
        </main>
    </div>

<?php require dirname(__DIR__) . '/inc/footer.php'; ?>
