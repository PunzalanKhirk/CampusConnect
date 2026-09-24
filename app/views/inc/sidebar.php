    <aside class="sidebar">
        <div class="sidebar-logo">
            <span class="logo-mark">CC</span>
            <span class="logo-text">CampusConnect</span>
        </div>
        <nav class="sidebar-nav">
            <a href="<?= URLROOT ?>/<?= ($userRole ?? '') === 'system_admin' ? 'admin/dashboard' : 'moderation/dashboard' ?>"
               class="nav-item <?= ($active ?? '') === 'home' ? 'active' : '' ?>">
                <span class="nav-icon" aria-hidden="true">&#8962;</span>
                <span class="nav-label">Home</span>
            </a>
            <a href="#" class="nav-item <?= ($active ?? '') === 'profile' ? 'active' : '' ?>">
                <span class="nav-icon" aria-hidden="true">&#128100;</span>
                <span class="nav-label">Profile</span>
            </a>
            <a href="#" class="nav-item <?= ($active ?? '') === 'notifications' ? 'active' : '' ?>">
                <span class="nav-icon" aria-hidden="true">&#128276;</span>
                <span class="nav-label">Notifications</span>
            </a>
            <a href="#" class="nav-item <?= ($active ?? '') === 'settings' ? 'active' : '' ?>">
                <span class="nav-icon" aria-hidden="true">&#9881;</span>
                <span class="nav-label">Settings</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="<?= URLROOT ?>/auth/logout" class="nav-item nav-logout">
                <span class="nav-icon" aria-hidden="true">&#9211;</span>
                <span class="nav-label">Logout</span>
            </a>
        </div>
    </aside>
