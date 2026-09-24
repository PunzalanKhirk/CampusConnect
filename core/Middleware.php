<?php
/**
 * Middleware
 * -------------------------------------------------
 * Central RBAC + session guard. Every protected controller method calls
 * one of these at the very top, before touching any model or view.
 *
 * Roles:
 *   system_admin        -> RBAC management, system analytics
 *   student_moderator    -> moderation queue, AI-flagged review, enforcement
 */
class Middleware
{
    /** Must simply be logged in (any role). */
    public static function requireLogin(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
            self::denyAndRedirect('Please log in to continue.');
        }

        // Idle timeout, defense-in-depth on top of the cookie's own lifetime
        if (!empty($_SESSION['last_activity']) &&
            (time() - $_SESSION['last_activity']) > SESSION_LIFETIME) {
            session_unset();
            session_destroy();
            self::denyAndRedirect('Your session expired. Please log in again.');
        }
        $_SESSION['last_activity'] = time();
    }

    /** Must be logged in AND hold one of the given roles. */
    public static function requireRole(array $allowedRoles): void
    {
        self::requireLogin();

        if (!in_array($_SESSION['role'], $allowedRoles, true)) {
            http_response_code(403);
            require dirname(__DIR__) . '/app/views/inc/403.php';
            exit;
        }
    }

    public static function requireAdmin(): void
    {
        self::requireRole(['system_admin']);
    }

    public static function requireModerator(): void
    {
        // Admins can also access moderator tooling; moderators cannot access admin-only tooling.
        self::requireRole(['student_moderator', 'system_admin']);
    }

    /** Lightweight CSRF token issue/check, used by every state-changing form/AJAX call. */
    public static function csrfToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf(?string $token): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($token) || empty($_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(419);
            die('Invalid or expired security token. Please refresh and try again.');
        }
    }

    private static function denyAndRedirect(string $message): void
    {
        $_SESSION['flash_error'] = $message;
        header('Location: ' . URLROOT . '/auth/login');
        exit;
    }
}
