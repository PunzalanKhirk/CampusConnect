<?php
/**
 * Base Controller
 * -------------------------------------------------
 * All app controllers extend this for model loading + view rendering.
 */
class Controller
{
    /** Instantiate a model by name, e.g. $this->model('UserModel') */
    public function model(string $model)
    {
        require_once dirname(__DIR__) . '/app/models/' . $model . '.php';
        return new $model();
    }

    /**
     * Render a view with data. $view uses dot notation, e.g. 'moderation/dashboard'.
     * Data is extracted into local vars; views must still escape with e() on output.
     */
    public function view(string $view, array $data = []): void
    {
        $viewFile = dirname(__DIR__) . '/app/views/' . $view . '.php';
        if (file_exists($viewFile)) {
            extract($data);
            require_once $viewFile;
        } else {
            http_response_code(404);
            die("View '{$view}' not found.");
        }
    }

    /** JSON response helper for the REST endpoints */
    public function json($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}

/** Global short alias for output escaping, used inside every view. */
function e(?string $value): string
{
    return sanitize_output($value);
}
