<?php

namespace Fenix\View;

use Fenix\View\Components\ParentComponent;
use Fenix\View\Components\YieldComponent;
use Fenix\Contracts\Support\Renderable;
use Fenix\Contracts\View\Compilator;
use Fenix\View\Components\Component;
use Fenix\View\Components\Compiled;
use Fenix\View\Components\Content;
use Fenix\View\Components\Section;
use Fenix\Support\File;
use Exception;

class FenixEngine implements Renderable
{
    use Tool;
    /**
     *
     * @var Compiler
     */
    private $compiler;

    /**
     *
     * @var YieldComponent
     */
    private $yield;

    /**
     *
     * @var Content
     */
    private $content;

    /**
     *
     * @var ParentComponent
     */
    private $parent;

    /**
     *
     * @var Section
     */
    private $section;

    /**
     *
     * @var Compiled
     */
    private $compiled;

    /**
     * @var Pattern
     */
    private $pattern;

    public function __construct(string $views, string $cache, Patterns $patterns = null, Compilator $compilator = null)
    {
        $this->compiler = $compilator ? $compilator : new Compilers\FenixCompiler($views, $cache);
        $this->yield = new Components\YieldComponent;
        $this->content = new Components\Content;
        $this->parent = new Components\ParentComponent;
        $this->section = new Components\Section;
        $this->compiled = new Components\Compiled;
        $this->pattern = $patterns ? $patterns : new Patterns();
    }

    /**
     * Compile and render a view
     *
     * @param $view
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public function render(string $view, array $params = [], $include = true)
    {
        if (!empty($params)) extract($params);

        if (!$include) {
            return $this->goCompile($view);
        }
        include $this->goCompile($view);
    }
    
    /**
     * Run the compiler
     * @param $view
     * @return bool
     * @throws \Fenix\Filesystem\FileNotFoundException
     */
    public function goCompile($view, $endsection = '/\#endsection\b/')
    {
        try {
            $viewExpired = $this->compiler->isExpired($view);
            $parentExpired = $this->compiler->readView($view);
        } catch(Exception $e) {
            throw new \Fenix\View\CompileException(
                sprintf("Unable to find view ('%s').", $this->compiler->getViews()->fullName())
            );
        }
        preg_match($this->pattern::EXTEND_VALUE, $parentExpired->getViewContent(), $matches);
        $matches = $this->removeEmpty($matches);
        $parentExpired = isset($matches[1]) ? $this->compiler->isExpired($matches[1]) : false;
        if ($viewExpired == false && $parentExpired == false) {
            if ($this->compiler->isCompiled($view)) {
               return $this->compiler->cached();
            }
        }
        return $this->toRender($view)
                    ->findParent()
                    ->findSection()
                    ->findYield()
                    ->assemble($endsection)
                    ->saveInCache($view, $this->compiled->getValue());
    }



    /**
     * The read and get the content to be rendered
     */
    public function toRender(string $view)
    {
        $this->getCompiled($view, true);
        return $this;
    }

    /**
     * Check if the view has parent content
     * If the view has parent content, it will get it
     *
     * @return $this
     */
    public function findParent()
    {
        if ($this->compiler->hasKeyWord($this->pattern::FIND_EXTEND)) {
            preg_match($this->pattern::EXTEND_VALUE, $this->getHeritage($this->content, 'content'), $matches);
            $matches = $this->removeEmpty($matches);
            $extendedCompiled = $this->getCompiled($matches[1]);
            $this->setHeritage($this->parent,  $extendedCompiled['name'], $extendedCompiled['content']);
        }
        return $this;
    }

    /**
     * Get the view sections
     *
     * @return $this
     */
    public function findSection()
    {
        $found = preg_match_all(
            $this->pattern::FIND_SECTION, $this->getHeritage($this->content, 'content'), $matches
        );
        $this->section->findSections($found, $this->removeEmpty($matches), $this->pattern::SECTION_VALUE, $this->content);
        return $this;
    }

    /**
     * Get the yield from parent
     * @return $this
     */
    public function findYield()
    {
        $this->yield->findYields($this->section, $this->parent, $this->pattern);

        return $this;
    }

   /**
     * Assemble all content together
     * @return $this
     */
    public function assemble($endsection = '/\#endsection\b/' )
    {
        $this->compiled->glueCompiled($this->parent, $this->section, $this->content, $this->pattern, $endsection);
        
        return $this;
    }

    /**
     * Get Compiled Value
     * @param string $view
     * @param boolean $add
     */
    public function getCompiled(string $view, $add = false)
    {
        return $add
               ? $this->content->addContent($this->compiler, $view)
               : $this->content->getContent($this->compiler, $view);
    }

    /**
     * Escape html special chars
     * s
     * @param $print
     * @return string
     */
    public function escapeHtmlChars($print)
    {
        return htmlspecialchars($print, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Include partials
     *
     * @param string $view
     * @param array $params
     * @return void
     * @throws Exception
     */
    public function includePartial($view, $params = [])
    {
        if (!empty($params)) {
            extract($params);
        }
        $content = $this->compiler->readView($view)->compile()->getViewContent();
        $file = $this->compiler->saveInCache($view, $content);
        include $file;
        unlink($file);
    }

     /**
     * Get inherit values
     * @param Component $component
     * @param $name
     * @param null $second
     * @return mixed
     */
    public function getHeritage(Component $component, $second = null)
    {
        if ($second !== null) {
            if ($component->isArray()) {
                return $component->getValues()[$second];
            } elseif ($component->isObject()) {
                return $component->getValue()->{$second};
            }
        }
        return $component->getValue();
    }

    
     /**
     * Set inherity values
     *
     * @param Component $component
     * @param string $name
     * @param string $filename
     * @param string $value
     * @param bool $isSection
     */
    public function setHeritage(Component $component, string $filename, 
                                string $value, bool $isSection = null)
    {
        if ($isSection) {
            if (!$component->isArray()) {
                $component->initWithArray();
            }
            $value = is_null($value) ?: (object) ['name' => $filename, 'content' => $value];
            $component->push($value);
        } else {
            $value = is_null($value) ?: (object) ['filename' => $filename, 'content' => $value];
            $component->setValue($value);
        }
    }


    /**
     * Get full path to cached file
     * @param $view
     * @return string
     */
    public function getCached($view)
    {
        return $this->compiler->getCachePath() . DIRECTORY_SEPARATOR . $view . '.php';
    }

        /**
     * Save the compiled file in cache
     *
     * @param $name
     * @return string The full name where the file is located
     */
    public function saveInCache($name, $content)
    {
        return $this->compiler->saveInCache($name, $content);
    }

}