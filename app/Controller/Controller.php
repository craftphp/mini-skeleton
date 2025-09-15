<?php
namespace App\Controller;

use Craft\Application\View;
use Exception;

class Controller
{
    protected $viewEngine = null;

    /**
     * Dispatch method to call the specified action with parameters.
     * @param string $action 
     * @param array $params
     * @return mixed
     */
    public function dispatch(string $action, array $params = [])
    {
        if (method_exists($this, $action)) {
            return call_user_func_array([$this, $action], $params);
        } else {
            View::abort(404, View::resource('error/404.php'));
        }
    }

    /**
     * Render view with data.
     * @param string $view View name to render (directory at: resource/view/)
     * @param array $data Data to pass to the view
     * @throws Exception if view file not found
     * @return void (echoes the rendered view)
     */
    public function render(string $view, array $data = [])
    {
        $viewObj = new View(null);
        echo $viewObj->view($view, $data);
    }
}
