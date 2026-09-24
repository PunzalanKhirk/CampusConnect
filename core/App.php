<?php
/**
 * App (Router)
 * -------------------------------------------------
 * Parses the ?url= query string built by public/.htaccess into
 * controller / method / params, then dispatches to it.
 *
 * URL pattern: /controller/method/param1/param2
 * Defaults: controller = AuthController, method = login
 */
class App
{
    protected string $controllerName = 'AuthController';
    protected string $method = 'login';
    protected array $params = [];

    protected object $controller;

    public function __construct()
    {
        $url = $this->parseUrl();

        // ---- Controller ----
        if (!empty($url[0])) {
            $controllerName = ucfirst($this->clean($url[0])) . 'Controller';

            $controllerFile = dirname(__DIR__)
                . '/app/controllers/'
                . $controllerName
                . '.php';

            if (file_exists($controllerFile)) {
                $this->controllerName = $controllerName;
                unset($url[0]);
            }
        }

        // Load controller class
        require_once dirname(__DIR__)
            . '/app/controllers/'
            . $this->controllerName
            . '.php';

        // Create controller object
        $controllerClass = $this->controllerName;
        $this->controller = new $controllerClass();

        // ---- Method ----
        if (isset($url[1])) {
            $methodName = $this->clean($url[1]);

            if (method_exists($this->controller, $methodName)) {
                $this->method = $methodName;
                unset($url[1]);
            }
        }

        // ---- Params ----
        $this->params = $url
            ? array_values(array_map([$this, 'clean'], $url))
            : [];

        // ---- Dispatch ----
        call_user_func_array(
            [$this->controller, $this->method],
            $this->params
        );
    }

    private function parseUrl(): array
    {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);

            return explode('/', $url);
        }

        return [];
    }

    private function clean(string $value): string
    {
        return htmlspecialchars(
            strip_tags(trim($value)),
            ENT_QUOTES,
            'UTF-8'
        );
    }
}