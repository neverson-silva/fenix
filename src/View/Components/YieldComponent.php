<?php

namespace Fenix\View\Components;

use Fenix\Support\Collection;

class YieldComponent extends Component
{
    /**
     * Find yields
     *
     * @param Section $section
     * @param ParentComponent $parent
     * @param Pattern $patterns
     * @return void
     */
    public function findYields(Section $section, ParentComponent &$parent, $patterns)
    {      
        if (!$section->isEmpty()) {
            Collection::create($section->getValues())
                ->map(function($value, $key) use($parent, $patterns){
                    $pattern = sprintf($patterns::YIELD_VALUE, $value->name);
                    if ($parent->exists('content')) {
                        if (preg_match($pattern, $parent->content)) {

                            $parent->content = preg_replace(
                                $pattern, '<@' . $value->name . '@>', $parent->content
                            );
                        }
                    }
            });
        }
        if (!preg_match_all('/\<\@(.*)\@\>/', $parent->content)) {
            $parent->setValue(null);
            return false;
        } else {

            $parent->content = preg_replace(
                $patterns::FIND_ANY_YIELD, "", $parent->content
            );
            $parent->content = preg_replace(
                '/\v+/', "\n", $parent->content
            );
        }
        return true;
    }
}