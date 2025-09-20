<?php

namespace Craft\Application;

use \Exception;

/**
 * #### Class View
 * 
 *  View class for rendering views with data and redirecting to a given URL or route name.
 */
#region View
class View
{
    /**
     * @var string The path to the view files.
     */
    private $viewPath;
    private $engine;
    private $engineInstance;

    /**
     * Constructor to set the view path.
     * @param string|null $viewPath The path to the view files. If null, uses config or default.
     */
    public function __construct($viewPath = null)
    {
        $configPath = ROOT_DIR . 'config/view.php';
        $config = file_exists($configPath) ? require $configPath : [];
        $viewPath = $viewPath ?? ($config['view_path'] ?? ROOT_DIR . 'resource/view/');
        $this->viewPath = rtrim($viewPath, '/');
        $this->engine = $config['engine'] ?? 'php';
        
        if ($this->engine !== 'php' && isset($config['drives'][$this->engine]['class'])) {
            $class = $config['drives'][$this->engine]['class'];
            $options = $config['drives'][$this->engine]['options'] ?? [];
            if (class_exists($class)) {
                $this->engineInstance = new $class($this->viewPath, $options);
            } else {
                throw new Exception("View engine class not found: $class");
            }
        }
    }

    /**
     * Render a view file with optional data.
     * @param string $view The view file to render, relative to the view path.
     * @param array $data Data to be passed to the view.
     * @return string
     */
    public static function render($view, $data = [])
    {
        $instance = new self();
        return $instance->view($view, $data);
    }

    /**
     * Render a view file with optional data.
     * @param string $view The view file to render, relative to the view path.
     * @param array $data Data to be passed to the view.
     * @return string
     */
    public function view($view, $data = [])
    {
        if ($this->engine === 'php' || !$this->engineInstance) {
            $view = str_replace(['..', '\\'], '', $view);
            if (strpos($view, '.') !== false) {
                $view = str_replace('.', '/', $view);
            }
            $filePath = $this->viewPath . '/' . $view . '.php';
            if (!file_exists($filePath)) {
                throw new Exception("View file not found: " . $filePath);
            }
            extract($data);
            ob_start();
            require $filePath;
            return ob_get_clean();
        } else {
            if (method_exists($this->engineInstance, 'render')) {
                return $this->engineInstance->render($view, $data);
            } else {
                throw new Exception("View engine does not support render method");
            }
        }
    }

    /**
     * Abort the request with a given status code and optional custom error template.
     * @param int $statusCode
     * @param string|null $errorTemplate
     * @return never
     */
    public static function abort($statusCode, $errorTemplate = null)
    {
        http_response_code($statusCode);
        $instance = new self();

        $errorTemplate = $errorTemplate ?? "errors/$statusCode";
        $filePath = $instance->viewPath . '/' . $errorTemplate . '.php';

        if (file_exists($filePath)) {
            echo $instance->view($errorTemplate, ['statusCode' => $statusCode]);
        } else {
            echo "Error $statusCode: " . http_response_code($statusCode);
        }
        exit();
    }
}
#endregion