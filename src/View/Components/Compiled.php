<?php

namespace Fenix\View\Components;

use Fenix\View\Compilers\FenixCompiler as Compiler;
use Fenix\Support\Collection;
use Fenix\Support\File;

class Compiled extends Component
{
    /**
     * Check if a view is Compiled
     *
     * @param File $file
     * @param string $name
     * @return boolean
     */
    public static function isCompiled(File &$file, string $name)
    {
        $file = clone $file;
        $file->newName($name);
        return $file->isFile(true);
    }

    public function glueCompiled(ParentComponent $parent, Section $section, Content $content,
                                 $patterns, $endsection = '/\#endsection\b/')
    {
        if (!$parent->isNull() && !$section->isNull()) {
            $this->setValue($parent->content);
            Collection::create($section->getValues())
                ->map(function($value, $key){
                    $pattern = sprintf('/\<\@%s\@\>/', $value->name);
                    $value = preg_replace( $pattern, $value->content, $this->getValue() );
                    $this->setValue($value);
            });
            $this->setValue( preg_replace('/\v+/', "\n", $this->getValue()));
        } else {
            $this->setValue($content->getValues()['content']);
            $pattern = [$patterns::FIND_EXTEND, $patterns::FIND_SECTION, $endsection];
            $replace = ['', '',''];

            $value = preg_replace('/\v+/', "\n", $this->getValue());
            $value = preg_replace($pattern, $replace, $value);
            $this->setValue($value);
        }
        return true;
        
    }
}