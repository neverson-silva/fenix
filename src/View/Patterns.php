<?php

namespace Fenix\View;

/**
 * Class Patterns
 *
 * Responsible to store the patterns for the compiler
 *
 * @package Fenix\Template
 * @author Neverson Bento da Silva <neversonbs13@gmail.com>
 */
class Patterns
{
    /**
     *  Regex para encontrar o include
     * Regex to find the include
     */
    const FIND_INCLUDE = '/\#include\(\'%s\'\)/';

    /**
     * Regex com para Encontrar yield com valor espec�fico
     * Regex to find the yield with and specific value
     */
    //const YIELD_VALUE = '/\#yield\(\'%s\'\)([\s\S]*?)/';

    const YIELD_VALUE = '/\#yield\(\'(%s)\'\)/';

    /**
     * Regex com  para encontrar qualquer yield
     * Regex to find any yield
     */
    const FIND_ANY_YIELD = '/\#yield\((.*)\)/';

    /**
     * Regex com valor recebido pelo extends
     * Regex with value received by extends
     */
    const EXTEND_VALUE = '/\#extends\(\'(.*)\'\)/';

    /**
     * Regex com  para encontrar qualquer yield
     * Regex to find any yied
     */
    const FIND_EXTEND = '/\#extends\((.*)\)/';

    /**
     * Regex com valor recebido pelo section
     * Regex with value received by section
     */
    const SECTION_VALUE = '/\#section\(\'%s\'\)([\s\S]*?)\#endsection{0,1}\b/';

    /**
     * Regex com para Encontrar section com valor espec�fico
     * Regex to find section with and specific value
     */
    const FIND_SECTION = '/\#section\(\'(.*)\'\)/';

    const PATTERN = 0;

    const REPLACE = 1;

    public static $expressions = [
        'if' => [
            self::PATTERN => ['/\#if([\s])?\((.*)\)/', '/\#elseif([\s])?\((.*)\)/', '/\#else\b/', '/\#endif\b/'],
            self::REPLACE => ['<?php if${1}(${2}): ?>',  '<?php elseif${1}(${2}): ?> ', '<?php else: ?>', '<?php endif; ?>']
        ],
        'foreach' => [
            self::PATTERN => ['/\#foreach([\s])?\((.*)\)/', '/\#endforeach/', '/\#continue/'],
            self::REPLACE => ['<?php foreach${1}(${2}): ?>', '<?php endforeach; ?>', '<?php continue; ?>']
        ],
        'for' => [
            self::PATTERN => ['/\#for([\s])?\((.*)\)/', '/\#endfor/'],
            self::REPLACE => ['<?php for${1}(${2}): ?>', '<?php endfor; ?>']
        ],
        'while' => [
            self::PATTERN => ['/\#while([\s])?\((.*)\)/', '/\#endwhile/'],
            self::REPLACE => ['<?php while${1}(${2}): ?>', '<?php endwhile; ?>']
        ],
        'switch' => [
            self::PATTERN => [
                '/\#switch([\s])?\((.*)\)/', '/\#case(.*)\b/', '/\#break/',
                '/\#default/', '/\#endswitch/'
            ],
            self::REPLACE => [
                '<?php switch${1}(${2}): ?>', '<?php case ${1}: ?>', '<?php break; ?>',
                '<?php default: ?>', '<?php endswitch; ?>'
            ]
        ],
        'php' => [
            self::PATTERN => ['/#php/', '/#endphp/'],
            self::REPLACE => ['<?php', '?>']
        ],
        'mixed' => [
            self::PATTERN => ['/#include\(\s*([^)]+)\s*\)/', '/{{\s*([^}]+)\s*}}/', '/{!!\s*([^}]+)\s*!!}/', '/<@\s*([^@>]+)\s*@>/'],
            self::REPLACE => [
                '<?php $this->includePartial( ${1} ) ?>', '<?php echo $this->escapeHtmlChars(${1}) ?>',
                '<?php echo ${1} ?>', '{{${1}}}'
            ]
        ]

    ];


}