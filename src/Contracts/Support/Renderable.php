<?php

namespace Fenix\Contracts\Support;

/**
 * Render views
 *
 * @author Neverson Silva
 */
interface Renderable
{
    /**
     * Render a view
     * @param string $view The view name
     * @param array $args Array of variables
     * @param boolean $include Include
     */
    public function render(string $view, array $params = [], $include = true);

}
