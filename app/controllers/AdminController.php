<?php
/**
 * AdminController
 * -------------------------------------------------
 * Accessible to: system_admin ONLY (see Middleware::requireAdmin).
 * Handles RBAC management (creating/editing moderator & admin accounts)
 * and system-wide analytics.
 */
class AdminController extends Controller
{
    private UserModel $userModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        Middleware::requireAdmin(); // Strict RBAC gate: admins only
        $this->userModel = $this->model('UserModel');
    }

    /** GET /admin/dashboard */
    public function dashboard(): void
    {
        $this->view('admin/dashboard', [
            'title' => 'System Analytics',
            'userName' => $_SESSION['full_name'] ?? 'Administrator',
            'userRole' => $_SESSION['role'] ?? '',
            'csrf_token' => Middleware::csrfToken(),
        ]);
    }

    // Further RBAC-management endpoints (createUser, updateRole, deactivateUser, etc.)
    // would follow this same pattern: verifyCsrf() -> validate input -> UserModel call -> json().
}
