<?php

namespace Fenix\View\Compilers;

use Fenix\Contracts\View\Compilator;
use Fenix\View\Components\Compiled;
use Fenix\View\CompileException;
use Fenix\View\Patterns;
use Fenix\Support\File;

/**
 * Compilador
 */
class FenixCompiler implements Compilator
{
    /**
     * @var File
     */
    protected $views;
    /**
     * @var File
     */
    protected $cache;
    /**
     * @var string
     */
    protected $expiration = '20 seconds';
    /**
     * Compiler constructor.
     * @param $views
     * @param $cache
     */
    public function __construct($views, $cache)
    {
        $this->views = new File($views, '', 'php');
        $this->cache = new File($cache, '', 'php');
    }

    /**
     * Get Views Object
     * @return File
     */
    public function getViews()
    {
        return $this->views;
    }

    /**
     * Name a view
     *
     * @param $name
     * @param bool $cache
     */
    public function nameView(string $name, $cache = false)
    {
        if ($cache) {
            $this->cache->setName($name);
        } else {
            $this->views->setName($name);
        }
    }

    /**
     * Rename a view
     *
     * @param string $name
     * @return void
     */
    public function renameView(string $name)
    {
        $this->nameView($name);
    }

    /**
     * Set expiration time for views
     *
     * @param $time
     */
    public function setExpiration($time)
    {
        $this->expiration = $time;
    }

    /**
     * Get expiration time for views
     *
     * @param $time
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * Get cache directory
     *
     * @return string
     */
    public function getCachePath()
    {
        return $this->cache->getPath();
    }
    /**
     * Get views directory
     *
     * @return string
     */
    public function getViewsPath()
    {
        return $this->views->getPath();
    }


    /**
     * Check if the view is still valid
     *
     * If the view had an edition in the last 20 seconds it will be expired
     *
     * @param string $what Check from what directory get the file to check
     * @return bool
     * @throws \Exception
     */
    public function isExpired($name, $what = false)
    {
        try {
            if ($what === true) {
                $this->cache->newName($name);
                return $this->cache->lastEditedIn($this->getExpiration());
            }
            $this->views->setName($name);

            return $this->views->lastEditedIn($this->getExpiration());
        } catch (\Exception $e){
            $file = $what ? $this->cache->fullName() : $this->views->fullName();
            throw new CompileException(sprintf("File %s doesn't not exist.", $file));
        }
    }


     /**
     * Check if the view has php keywords
     *
     * @param $keyword
     * @param null $content
     * @return false|int
     */
    public function hasKeyWord($keyword, $content = null)
    {
        return preg_match($keyword, $content ?? $this->getViewContent());
    }

    /**
     * Compile the view
     *
     * @return mixed
     */
    public function compile()
    {
        $this->replaceAll();
        return $this;
    }


    /**
     * Replace all expressions
     *
     * @return void
     */
    public function replaceAll()
    {
        array_map(function($expression){
            return $this->replace(...$expression);
        }, Patterns::$expressions);
    }
    /**
     * Replace
     * @param $search
     * @param $replace
     * @return bool
     * @throws \Fenix\Support\FileNotFoundException
     */
    public function replace($search, $replace) : bool
    {
        $original = $this->getViewContent();

        $content = preg_replace($search, $replace, $original);

        $this->views->setContent($content);

        return $original != $content;
    }

    /**
     * Clear views
     */
    public function clear()
    {
        $this->views->clear();
    }
 
    /**
     * Save a compiled view in the cache
     *
     * @param $name File name
     * @param $content the content to be written
     * @return bool|void
     */
    public function saveInCache($name, $content)
    {
        return $this->cache->save($name, true, $content);
    }

    /**
     * Check if the view is already compiled
     *
     * @param $compiled
     * @return bool If the view is already compiled in saved in cache
     */
    public function isCompiled(string $compiled)
    {
        return Compiled::isCompiled($this->cache, $compiled);
    }

    /**
     * Return the full filename cached
     *
     * @return string
     */
    public function cached()
    {
        return $this->cache->fullNewName();
    }

    /**
     * Read the view content
     *
     * @return void
     */
    public function readView($view)
    {
      
        if ($this->views->noContent()) {
            if ($this->views->noName()) {
                $this->views->setName($view);
            }
            $this->views->read();
        }  else {
            $this->clear();  
            $this->views->setName($view);
            $this->views->read();
        }
        return $this;
    }

    /**
     * Get the view read content
     *
     * @return string
     */
    public function getViewContent() : string
    {
        return $this->views->getContent();
    }

    /**
     * Get View read content
     *
     * @return string
     */
    public function getContent() : string
    {
        return $this->getViewContent();
    } 


    /**
     * Set view content
     *
     * @param string $content
     * @return void
     */
    public function setContent(string $content)
    {
        $this->views->setContent($content);
    }
}