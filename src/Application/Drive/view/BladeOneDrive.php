<?php
namespace Craft\Application\Drive\View;

use Craft\Application\Interfaces\ViewEngine;

class BladeOneDrive implements ViewEngine
{
    protected $viewPath;
    protected $options;

    public function __construct($viewPath, $options = [])
    {
        $this->viewPath = $viewPath;
        $this->options = $options;
    }

    public function render(string $template, array $data = []): string
    {
        // TODO: Implement rendering logic using a templating engine
        throw new \Exception('BladeOneDrive: Please install a templating engine and implement render()');
    }
}