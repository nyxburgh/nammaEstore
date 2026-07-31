<?php
// app/core/Controller.php
namespace App\Core;

abstract class Controller
{
    /**
     * Each panel controller sets this to its own views path constant.
     * e.g.  protected string $viewsPath = ADMIN_VIEWS;
     */
    protected string $viewsPath = '';

    // ── Rendering ─────────────────────────────────────────────

    protected function view(string $view, array $data = [], string $layout = 'main'): void
    {
        extract($data);

        // Convert dot notation → directory path
        // e.g. 'dashboard.index' → '{viewsPath}/dashboard/index.php'
        $viewFile = $this->viewsPath . '/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            die("View not found: <code>{$viewFile}</code>");
        }

        // Capture the view content
        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        // Wrap in layout
        if ($layout) {
            $layoutFile = $this->viewsPath . '/layouts/' . $layout . '.php';
            if (file_exists($layoutFile)) {
                include $layoutFile;
                return;
            }
        }

        echo $content;
    }

    protected function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    protected function back(): void
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? APP_URL;
        $this->redirect($ref);
    }

    // ── Input ─────────────────────────────────────────────────

    protected function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function inputs(): array
    {
        return array_merge($_GET, $_POST);
    }

    protected function isPost(): bool
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
    }

    // ── Validation ────────────────────────────────────────────

    protected function validate(array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $rule) {
            $value = $this->input($field);
            foreach (explode('|', $rule) as $check) {
                if ($check === 'required' && ($value === null || $value === '')) {
                    $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
                    break;
                }
                if (str_starts_with($check, 'min:') && strlen((string) $value) < (int) substr($check, 4)) {
                    $errors[$field] = ucfirst($field) . ' must be at least ' . substr($check, 4) . ' characters.';
                    break;
                }
                if ($check === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = 'Please enter a valid email address.';
                    break;
                }
                if ($check === 'numeric' && !is_numeric($value)) {
                    $errors[$field] = ucfirst($field) . ' must be a number.';
                    break;
                }
            }
        }
        return $errors;
    }

    // ── Flash messages ────────────────────────────────────────

    protected function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }
}
