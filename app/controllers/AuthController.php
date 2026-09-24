<?php
class AuthController extends Controller
{
    private UserModel $userModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->userModel = $this->model('UserModel');
    }

    public function login(): void
    {
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Middleware::verifyCsrf($_POST['csrf_token'] ?? null);

            $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
            $password = $_POST['password'] ?? '';

            if (!$email) {
                $errors[] = 'Please enter a valid email address.';
            }
            if (empty($password)) {
                $errors[] = 'Password is required.';
            }

            if (empty($errors)) {
                $user = $this->userModel->findByEmail($email);

                // password_verify() guards against timing attacks & rainbow tables.
                // Deliberately generic error message - don't reveal whether the email exists.
                if ($user && (int)$user['is_active'] === 1 && password_verify($password, $user['password_hash'])) {
                    session_regenerate_id(true); // prevent session fixation
                    $_SESSION['user_id'] = (int) $user['id'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['last_activity'] = time();

                    $redirect = $user['role'] === 'system_admin'
                        ? URLROOT . '/admin/dashboard'
                        : URLROOT . '/moderation/dashboard';
                    header('Location: ' . $redirect);
                    exit;
                }
                $errors[] = 'Invalid credentials, or your account is inactive.';
            }
        }

        $this->view('auth/login', [
            'title' => 'Sign In',
            'errors' => $errors,
            'csrf_token' => Middleware::csrfToken(),
        ]);
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'],
                $params['secure'], $params['httponly']);
        }
        session_destroy();
        header('Location: ' . URLROOT . '/auth/login');
        exit;
    }
}
