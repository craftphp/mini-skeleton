<?php
namespace Craft\Application\Drive\View;

use Craft\Application\Interfaces\ViewEngine;

class BladeDrive implements ViewEngine
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
        // TODO: Triển khai thực tế với package blade
        throw new \Exception('BladeDrive: Please install blade package and implement render()');
    }
}