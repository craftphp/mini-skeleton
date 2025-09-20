<?php
namespace App\Controller;

use Craft\Application\View;
use Exception;

class Controller
{
    protected $viewEngine = null;

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
