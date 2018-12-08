<?php

namespace Fenix\View\Components;

use Fenix\View\Compilers\FenixCompiler as Compiler;

class Content extends Component
{
    /**
     * Get compiled view
     * @return array
     */
    public function content(Compiler $compiler, string $view, $add = false)
    {

        $compiler->readView($view);

        $compiler->compile();
        
        $name = $compiler->getViews()->fullName();

        $content = $compiler->getViewContent();

        if ($add) {
            $this->setValues(compact('name', 'content'));
            return;
        }
        return compact('name', 'content');
    }

    /**
     * Add half compiled content to value
     *
     * @param Compiler $compiler
     * @param string $view
     * @return |arrayvoid
     */
    public function addContent(Compiler $compiler, string $view)
    {
        return $this->content($compiler, $view, true);
    }

    /**
     * Get Half compiled content
     *
     * @param Compiler $compiler
     * @param string $view
     * @return array
     */
    public function getContent(Compiler $compiler, string $view)
    {
        return $this->content($compiler, $view);
    }
}