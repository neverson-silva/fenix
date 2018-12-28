<?php

namespace Fenix\View\Compilers;

use Fenix\View\GracePatterns;
use Fenix\Support\Collection;
use Fenix\Support\Strin;
use Fenix\Support\Arra;
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

    /**
     * Compile if
     *
     * @param array $matches
     * @param integer $flag
     * @return string
     */
    protected function compileIf($matches, $flag = 0)
    {
        $matches = $this->removeEmpty($matches);
        
        if ($this->isCommented( $matches->first() )) {
            return $matches->first();
        }
        if ($matches->first()->position(':if=')) {
            return Strin::format('<?php if(%s): ?>', $matches[1]);
        } elseif ($matches->first()->position(':elseif=')) {
            return Strin::format('<?php elseif(%s): ?>', $matches[1]);
        } elseif (Strin::create($matches[0])->position(':else>')){
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

        if ($this->isCommented( $matches->first() )) {
            return $matches->first();
        }

        if ($matches->first()->position('foreach=')) {
            if ($matches->countEquals(4)) {  
                return Strin::format(
                    '<?php foreach(%s as %s => %s): ?>', 
                    $matches->remove(0)->reverse()->getItems()
                );

            } else {
                return Strin::format(
                    '<?php foreach(%s as %s): ?>', 
                    $matches->remove(0, 3)->reverse()->getItems()
                );
           }
        } elseif ($matches->first()->position(':continue')){
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

        if ($this->isCommented( $matches->first() )) {
            return $matches->first();
        }

        if ($matches->countEquals(2)) {
            return Strin::format('<?php for(%s): ?>', $matches[1]);
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

        if ($this->isCommented( $matches->first() )) {
            return $matches->first();
        }

        if ($matches->countEquals(2)) {
            return Strin::format('<?php while(%s): ?>', $matches[1]);
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

        if ($this->isCommented( $matches->first() )) {
            return $matches->first();
        }

        $first = $matches->first();

        if ($first->position('switch')) {
            if ($matches->countEquals(2)) {
                return Strin::format('<?php switch(%s) : ?>', $matches[1]);
            }
            return '<?php endswitch; ?>';
        } elseif ($first->position('case')) {
            if ($matches->countEquals(2)) {
                return (string) Strin::create('<?php case %s : ?')
                                     ->formatWith($matches[1])
                                     ->replaceNew('/^\s+/', '');
            }           
        } elseif ($first->match('/\<php:default\>/')) {
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

        $first = $matches->first();

        if ($this->isCommented( $first )) {
            return $first;
        }
        if($first->position('/')) {
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

        $first = $matches->first();

        if ($this->isCommented( $first )) {
            return $first;
        }

        if ($matches->countEquals(3)){
            return Strin::format(
                '<?php $this->includePartial("%s", %s) ?>',
                $matches->remove(0)->getItems()
            );
        }
        return  Strin::format('<?php $this->includePartial("%s") ?>', $matches[1]);
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
        return  Strin::format('<?php $this->includePartial("%s") ?>', $matches[1]);
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

        $matches = $this->removeEmpty(
            $matches->first()->match($pattern, null, true)
        );

        if(!isset($matches[2])) {
            return '';
        }

        $includeFile = $matches[1];

        $variables = $matches[2];
        
        if ($this->isCompactable($variables)) {

            $args = Strin::explode(',', 
               (string) $variables->replaceNew([', ', ',', ' , '], ',')
            );

            $colunized = $args->map(function($arg){
                return  "'" . (string) $arg ."'";
            });

            return Strin::format(
                '<?php $this->includePartial("%s", compact(%s)) ?>',
                $includeFile, (string) $colunized->implode(', ')
            );
        }
        $compacts = Strin::explode(
            ',',  (string) $variables->replaceNew([', ', ',', ' , '], ',')
        );

        return Strin::format(
            '<?php $this->includePartial("%s", %s) ?>', $includeFile, $this->getAliased($compacts)
        );

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
        $aliased = new Arra();

        $string = new Strin();

        $compacts->map(function($compact) use($aliased) {

            $cifrao = $compact->match('/\'(.*)\'/') ? '' : '$';
            if ($this->isCompactable($compact)) {
                $aliased->push(
                    Strin::format(
                        "'%s' => %s%s", 
                        (string) $compact->trim(), $cifrao, (string) $compact->trim()
                    )
                );
            } else {
                $args = Strin::explode(' to ', (string) $compact->replaceNew(['to ', ' to '], ' to ') );
                $aliased->push(
                    Strin::format(
                        "'%s' => %s%s", 
                        (string) $args[1]->trim(), $cifrao, (string) $args[0]->trim()
                    )
                );
            }

        });
        return (string) "[" . $aliased->implode(', ') .  "]";
    }

}