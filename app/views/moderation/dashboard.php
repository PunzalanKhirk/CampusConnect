<?php $active = 'home'; require dirname(__DIR__) . '/inc/header.php'; ?>
    <?php require dirname(__DIR__) . '/inc/sidebar.php'; ?>

    <div class="main-col">
        <!-- ===== Top Header ===== -->
        <header class="topbar">
            <div class="search-bar">
                <span class="search-icon" aria-hidden="true">&#128269;</span>
                <input type="text" id="queueSearch" placeholder="Search reported posts or reporter ID...">
            </div>
            <div class="user-indicator">
                <span class="user-name"><?= e($userName) ?></span>
                <span class="user-role-badge">
                    <?= $userRole === 'system_admin' ? 'System Administrator' : 'Student Moderator' ?>
                </span>
                <span class="avatar"><?= e(strtoupper(substr($userName, 0, 1))) ?></span>
            </div>
        </header>

        <!-- ===== Main Content ===== -->
        <main class="content">
            <div class="content-header">
                <div>
                    <h1 class="page-title">Moderation Queue</h1>
                    <p class="page-subtitle">Review AI-flagged and reported content. Reporter and content
                        identities are masked to protect anonymity where applicable.</p>
                </div>

                <div class="filter-tabs">
                    <?php
                    $tabs = ['pending' => 'Pending', 'approved' => 'Approved', 'hidden' => 'Hidden', 'all' => 'All'];
                    foreach ($tabs as $key => $label):
                        $isActive = $activeFilter === $key;
                    ?>
                        <a href="<?= URLROOT ?>/moderation/dashboard?status=<?= e($key) ?>"
                           class="filter-tab <?= $isActive ? 'active' : '' ?>">
                            <?= e($label) ?>
                            <?php if ($key === 'pending'): ?>
                                <span class="badge-count"><?= (int) $pendingCount ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ===== Queue Card / Data List ===== -->
            <section class="card">
                <div class="queue-list" id="queueList">
                    <?php if (empty($queue)): ?>
                        <div class="empty-state">
                            <p>No reports in this view. The queue is clear. 🎉</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($queue as $row): ?>
                            <?php
                                $status = $row['status'];
                                $snippet = mb_strimwidth($row['content'], 0, 140, '...');
                            ?>
                            <div class="queue-row" data-report-id="<?= (int) $row['id'] ?>">
                                <div class="queue-main">
                                    <span class="status-dot <?= e($status) ?>" title="<?= e(ucfirst($status)) ?>"></span>
                                    <div class="queue-content">
                                        <p class="snippet">
                                            <?= e($row['is_anonymous'] ? '[Anonymous Post] ' : '') ?><?= e($snippet) ?>
                                        </p>
                                        <div class="queue-meta">
                                            <span class="reporter-id">Reporter: <?= e($row['reporter_display_id']) ?></span>
                                            <span class="reason-pill">Reason: <?= e($row['reason']) ?></span>
                                            <?php if ((int) $row['ai_flagged'] === 1): ?>
                                                <span class="ai-flag-badge">&#9888; AI-Flagged</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="queue-actions">
                                    <?php if ($status === 'pending'): ?>
                                        <button class="btn btn-approve" data-action="approve" data-id="<?= (int) $row['id'] ?>">Approve</button>
                                        <button class="btn btn-hide" data-action="hide" data-id="<?= (int) $row['id'] ?>">Hide</button>
                                        <button class="btn btn-delete" data-action="delete" data-id="<?= (int) $row['id'] ?>">Delete</button>
                                    <?php else: ?>
                                        <span class="reason-pill">Resolved: <?= e(ucfirst($status)) ?></span>
                                    <?php endif; ?>
                                    <button class="btn-ellipsis" data-action="more" data-id="<?= (int) $row['id'] ?>" aria-label="More options">&#8942;</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>

    <!-- ===== Confirmation Modal ===== -->
    <div class="modal-backdrop" id="confirmModal">
        <div class="modal">
            <h2 class="modal-title" id="modalTitle">Confirm Action</h2>
            <p class="modal-subtitle" id="modalSubtitle">Are you sure you want to proceed? This action is logged.</p>
            <div class="modal-actions">
                <button class="btn btn-secondary" id="modalCancel">Cancel</button>
                <button class="btn btn-primary" id="modalConfirm">Confirm</button>
            </div>
        </div>
    </div>

    <div class="toast" id="toast"></div>

    <script>
        // Passed from PHP -> JS. URLROOT/CSRF are needed by main.js for all fetch() calls.
        window.CampusConnect = {
            urlRoot: <?= json_encode(URLROOT) ?>,
            csrfToken: <?= json_encode($csrf_token) ?>
        };
    </script>

<?php require dirname(__DIR__) . '/inc/footer.php'; ?>
