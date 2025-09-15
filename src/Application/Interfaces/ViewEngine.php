<?php
namespace Craft\Application\Interfaces;

/**
 * ViewEngine interface for rendering templates.
 * 
 * This interface defines the contract for view engines in the Craft application.
 */
interface ViewEngine
{
    public function render(string $template, array $data = []): string;
}