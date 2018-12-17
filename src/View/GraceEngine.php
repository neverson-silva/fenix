<?php

namespace Fenix\View;

use Fenix\View\Compilers\GraceCompiler;

class GraceEngine extends FenixEngine
{

    public function __construct(string $views, string $cache, Patterns $patterns = null)
    {
        parent::__construct($views, $cache, new GracePatterns(), new GraceCompiler($views, $cache));

    }

    /**
     * Renders a view
     *
     * @param string $view
     * @param array $params
     * @param boolean $include
     * @return stream
     */
    public function render(string $view, array $params = [], $include = true)
    {
        if (!empty($params)) extract($params);

        if (!$include) {
            return $this->goCompile($view, '/\<\/php:section\>\b/');
        }
        include $this->goCompile($view, '/\<\/php:section\>\b/');
    }
}