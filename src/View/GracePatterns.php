<?php

namespace Fenix\View;

class GracePatterns extends Patterns
{

    /**
     *  Regex para encontrar o include
     * Regex to find the include
     */
    const FIND_INCLUDE = '/\<(\/php|php):include([\s])?=([\s])?\"(.*)\"\>/';

    /**
     * Regex com para Encontrar yield com valor espec�fico
     * Regex to find the yield with and specific value
     */
    //const YIELD_VALUE = '/\#yield\(\'%s\'\)([\s\S]*?)/';

    const YIELD_VALUE = '/\<(\/php|php):yield([\s])?=([\s])?\"%s\"\>/';

    /**
     * Regex com  para encontrar qualquer yield
     * Regex to find any yield
     */
    const FIND_ANY_YIELD = '/\<(\/php|php):yield([\s])?=([\s])?\"(.*)\"\>/';

    /**
     * Regex com valor recebido pelo extends
     * Regex with value received by extends
     */
    const EXTEND_VALUE = '/\<php:extends([\s])?=\"(.*)\"\>/';

    /**
     * Regex com  para encontrar qualquer yield
     * Regex to find any yied
     */
    const FIND_EXTEND = '/\<php:extends([\s])?=\"(.*)\"\>([\s\S]*?)<\/php:extends\>{0,1}\b/';

    /**
     * Regex com valor recebido pelo section
     * Regex with value received by section
     */
    const SECTION_VALUE = '/\<php:section([\s])?=\"%s\"\>([\s\S]*?)<\/php:section\>{0,1}\b/';

    /**
     * Regex com para Encontrar section com valor espec�fico
     * Regex to find section with and specific value
     */
    const FIND_SECTION = '/\<php:section([\s])?=\"(.*)\"\>/';

    public static $expressions = [
        'if' => [
            self::PATTERN => ['/\<php:if([\s])?=([\s])?\"(.*)\"\>/', '/\<php:elseif([\s])?=([\s])?\"(.*)\"\>/',
                                '/\<(\/php|php):else([\s])?\>/', '/\<\/php:if\>/'],
            self::REPLACE => ['<?php if%s(%s): ?>',  '<?php elseif%s(%s): ?> ', '<?php else: ?>', '<?php endif; ?>']
        ],
        'foreach' => [
            self::PATTERN => [
                '/\<php:foreach([\s])?=([\s])?\"\((.*), (.*)\) in (.*)\"\>/',
                '/\<php:foreach([\s])?=([\s])?\"(.*) in (.*)\"\>/',
                '/\<(\/php|php):continue\>/', '/\<\/php:foreach\>/',
            ],
            self::REPLACE => [
                        '<?php foreach(${3}): ?>',                        
            ]
        ],
        'for' => [
            self::PATTERN => ['/\<php:for([\s])?=([\s])?\"(.*)\"\>/', '/\<\/php:for\>/'],
            self::REPLACE => ['<?php for${3}(${4}): ?>', '<?php endfor; ?>']
        ],
        'while' => [
            self::PATTERN => ['/\<php:while([\s])?=([\s])?\"(.*)\"\>/', '/\<\/php:while\>/'],
            self::REPLACE => ['<?php while${3}(${4}): ?>', '<?php endwhile; ?>']
        ],
        'switch' => [
            self::PATTERN => [
                '/\<php:switch=\"(.*)\"\>/',
                '/\<\/php:switch>/',
                '/\<php:case=\"(.*)\"\>/',
                '/\<\/php:case>/',
                '/\<php:default>/',
                '/\<\/php:default>/',
                '/\<\/php:switch\>/'
            ],
            self::REPLACE => [
                '<?php switch${3}(${4}): ?>', '<?php case ${4}: ?>', '<?php break; ?>',
                '<?php default: ?>', '<?php endswitch; ?>'
            ]
        ],
        'php' => [
            self::PATTERN => ['/\<php:php\>/', '/\<\/php:php\>/'],
            self::REPLACE => ['<?php ', '?>']
        ],
        'mixed' => [
            'include' => [
                self::PATTERN => [
                    '/\<php:include([\s])?=([\s])?\"(.*)\"\>([\s\S]*?)<\/php:include\>/',

                ],
                self::REPLACE => [
                    '<?php echo $this->includePartial("${3}", ${4})?>'
                ]
            ],
            'echo' => [
                self::PATTERN => [
                    '/{{\s*([^}]+)\s*}}/', 
                    '/{!!\s*([^}]+)\s*!!}/', 
                    '/<@\s*([^@>]+)\s*@>/',
                    '/<%\s*([^@>]+)\s*%>/',
                    '/\<php:echo\>\s*([^<]+)\s*\<\/php:echo\>/',

                ],
                self::REPLACE => [
                    '<?php echo $this->escapeHtmlChars(${1})?>',
                    '<?php echo $this->escapeHtmlChars(${1}) ?>',
                    '<?php echo ${1} ?>', 
                    '{{${1}}}',
                    '{{${1}}}'
                ],
            ],
            'includeSlot' => [
                self::PATTERN => [
                    '/([^\s\S]*?)\<php:include([\s])?=([\s])?\"(.*)\"\>([^\s\S]*?)/',
                ]
            ]
        ],

    ];


}