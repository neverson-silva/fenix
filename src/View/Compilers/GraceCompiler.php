<?php

namespace Fenix\View\Compilers;

use Fenix\View\GracePatterns;
use Fenix\Support\Collection;
use Fenix\View\Tool;

class GraceCompiler extends FenixCompiler
{
    use Tool;

    private $expressions;

    /**
     * Compiler constructor.
     * @param $views
     * @param $cache
     */
    public function __construct($views, $cache)
    {
        parent::__construct($views, $cache);
        $this->expressions = new Collection(GracePatterns::$expressions);
    }

    /**
     * Replace all patterns
     */
    public function replaceAll()
    {
        $this->expressions->map(function($values, $expression){
            $args = [];
            $method = 'compile';
            if ($expression == 'mixed') { 

                foreach ($values as $key => $value) {
                    if (count($value[0]) > 1) {
                        foreach ($value[0] as $newKey => $newValue){
                            $args[$newValue] = [$this, $method . ucfirst($key)];
                        }
                    } else {
                        $args[$value[0][0]] = [$this, $method . ucfirst($key)];
                    }
                }   
            } else {
                $method .=  ucfirst($expression);
                foreach($values[0] as $value) {
                    $args[$value] = [$this, $method];
                }
            }
            $this->replace($args);            
        });
    }
    
    /**
     * Replace
     * @param $search
     * @param $replace
     * @return bool
     * @throws \Fenix\Support\FileNotFoundException
     */
    public function replace($search, $replace = '') : bool
    {
        $original = $this->getViewContent();

        $content = preg_replace_callback_array($search, $original);

       $this->setContent($content);

        return $original != $content;
    }

    protected function compileIf($matches, $flag = 0)
    {
        $matches = $this->removeEmpty($matches);
        
        if ($this->isCommented($matches[0])) {
            return $matches[0];
        }
        if (stripos($matches[0], ':if=')) {
            return sprintf('<?php if(%s): ?>', $matches[1]);
        } elseif (stripos($matches[0], ':elseif=')) {
            return sprintf('<?php elseif(%s): ?> ', $matches[1]);
        } elseif (stripos($matches[0], ':else>')){
            return '<?php else: ?>';
        } else {
            return '<?php endif; ?>';
        }

    }

    /**
     * Compile foreach
     *
     * @param array $matches
     * @return string
     */
    protected function compileForeach($matches)
    {
        $matches = $this->removeEmpty($matches);

        if ($this->isCommented($matches[0])) {
            return $matches[0];
        }

        if (stripos($matches[0], 'foreach=')) {
            if (count($matches) == 4) {                
                return sprintf(
                    '<?php foreach(%s as %s => %s): ?>', $matches[3], $matches[2], $matches[1]
                );
            } else {
                return sprintf('<?php foreach(%s as %s): ?>', $matches[2], $matches[1]);
            }
        } elseif (stripos($matches[0], ':continue')){
            return '<?php continue; ?>';
        } else {
            return '<?php endforeach; ?>';
        }
    }

    /**
     * Compile for
     *
     * @param array $matches
     * @return string
     */
    protected function compileFor($matches)
    {
        $matches = $this->removeEmpty($matches);

        if ($this->isCommented($matches[0])) {
            return $matches[0];
        }

        if (count($matches) == 2) {
            return sprintf('<?php for(%s): ?>', $matches[1]);
        }
        return '<?php endfor; ?>';
    }

    /**
     * Compile while
     *
     * @param array $matches
     * @return string
     */
    protected function compileWhile($matches)
    {
        $matches = $this->removeEmpty($matches);

        if ($this->isCommented($matches[0])) {
            return $matches[0];
        }

        if (count($matches) == 2) {
            return sprintf('<?php while(%s): ?>', $matches[1]);
        }
        return '<?php endwhile; ?>';
    }

    /**
     * Compile switch
     *
     * @param array $matches
     * @return string
     */
    protected function compileSwitch($matches)
    {   
        $matches = $this->removeEmpty($matches);

        if ($this->isCommented($matches[0])) {
            return $matches[0];
        }

        if (stripos($matches[0], 'switch')) {
            if (count($matches) == 2) {
                return sprintf('<?php switch(%s) : ?>', $matches[1]);
            }
            return '<?php endswitch; ?>';
        } elseif (stripos($matches[0], 'case')) {
            if (count($matches) == 2) {
                $case = sprintf('<?php case %s : ?>', $matches[1]);
                return preg_replace('/^\s+/', '' , $case);
            }           
        } elseif (preg_match('/\<php:default\>/',$matches[0])) {
            return '<?php default : ?>';
        }
        return '<?php break; ?>';

    }

    /**
     * Compile php tag
     *
     * @param array $matches
     * @return string
     */
    protected function compilePhp($matches)
    {
        $matches = $this->removeEmpty($matches);

        if ($this->isCommented($matches[0])) {
            return $matches[0];
        }
        if(stripos($matches[0], '/')) {
            return '?>';
        }
        return '<?php';
    }

    /**
     * Compile include
     *
     * @param array $matches
     * @return string
     */
    protected function compileInclude($matches)
    {
        $matches = $this->removeEmpty($matches);

        if ($this->isCommented($matches[0])) {
            return $matches[0];
        }

        if (count($matches) == 3){
            return sprintf('<?php $this->includePartial("%s", %s) ?>', $matches[1], $matches[2]);
        }
        return sprintf('<?php $this->includePartial("%s") ?>', $matches[1]);
    }

    private function isCommented($string)
    {
        return false;
    }

    /**
     * Compile compile scaped echo 
     *
     * @param array $matches
     * @return string
     */
    protected function compileEcho($matches)
    {
        $matches = $this->removeEmpty($matches);
        if (preg_match('/{{/', $matches[0])) {
            return sprintf('<?php echo $this->escapeHtmlChars(%s) ?>', $matches[1]);
        } elseif (preg_match('/{!!/', $matches[0])) {
            return sprintf('<?php echo %s ?>', $matches[1]);
        } elseif (preg_match('/<@/', $matches[0])) {
            return sprintf('{{ %s }}', $matches[1]);
        } elseif (preg_match('/<%/', $matches[0])) {
            return sprintf('{{ %s }}', $matches[1]);
        }
        return sprintf('<?php $this->escapeHtmlChars(%s) ?>', $matches[1]);
    }

    /**
     * Compile include with a void tag
     *
     * @param array $matches
     * @return string
     */
    protected function compileIncludeSlot($matches)
    {
        $matches = $this->removeEmpty($matches);

        if ($this->hasVariable($matches)) {
            return $this->compileIncludeWithVars($matches);
        }
        if ($this->isCommented($matches[0])) {
            return $matches[0];
        }
        return sprintf('<?php $this->includePartial("%s") ?>', $matches[1]);
    }

    /**
     * Check if include tag has variables
     *
     * @param array $matches
     * @return boolean
     */
    protected function hasVariable($matches)
    {
        return preg_match('/(.*)include(.*)var(.*)/', $matches[0] ?? null);
    }

    /**
     * Compile with vars
     *
     * @param array $matches
     * @return string
     */
    protected function compileIncludeWithVars($matches)
    {

        $pattern = '/\<php:include([\s])?=([\s])?\"(.*)\"([\s])?var([\s])?=([\s])?\"(.*)\"/';
        preg_match($pattern, $matches[0], $matches);

        if(!isset($matches[3])) {
            return '';
        }
        $matches = $this->removeEmpty($matches);
        $includeFile = $matches[1];
        $variables = $matches[3];
        if ($this->isCompactable($variables)) {
            $args = explode(',', str_replace([', ', ',', ' , '], ',', $variables));
            $colunized = array_map(function($arg){
                return "'" . $arg ."'";
            }, $args);

            return sprintf('<?php $this->includePartial("%s", compact(%s)) ?>', $includeFile, implode(', ', $colunized));
        }
        $compacts = explode(',', str_replace([', ', ',', ' , '], ',', $variables));
        $aliased = $this->getAliased($compacts);
        return sprintf('<?php $this->includePartial("%s", %s) ?>', $includeFile, $aliased); 
    }

    /**
     * Check if variable can be compacted
     * That hasn't 'as' words
     *
     * @param string $parameters
     * @return boolean
     */
    protected function isCompactable(string $parameters)
    {
        return stripos($parameters, ' to ') === false;
    }

    /**
     * Get Aliased to a variable parent
     *
     * @param array $compacts
     * @return string
     */
    protected function getAliased($compacts)
    {
        $aliased = [];

        foreach ($compacts as $compact) {
            $cifrao = preg_match('/\'(.*)\'/', $compact) ? '' : '$';
            if ($this->isCompactable($compact) ) {
                $aliased[] = sprintf("'%s' => %s%s", trim($compact), $cifrao, trim($compact));
            } else {
                $args = explode(' to ', str_replace(['to ', ' to '], ' to ', $compact));
                $aliased[] = sprintf("'%s' => %s%s", trim($args[1]), $cifrao, trim($args[0])); //"'{$args[0]}' => ".  '$' ."{$args[1]}";
            }
        }
        return "[" . implode(', ', $aliased) . "]";
    }

}