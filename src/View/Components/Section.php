<?php

namespace Fenix\View\Components;

use Fenix\Support\Collection;

class Section extends Component
{
    /**
     * Find sections
     *
     * @param boolean|integer $hasSection
     * @param array $matches
     * @param string $sectionValue
     * @param Content $content
     * @return void
     */
    public function findSections($hasSection, array $matches = [], string $sectionValue, Content &$content)
    {
        if (!isset($matches[1])) {
            return;
        }
        $pattern = new Collection;
        $name = new Collection;
        $matches = new Collection($matches[1]);
        if ($hasSection) {
            $this->initWithArray();
            $matches->map(function($sectionName) use ($pattern, $name, $sectionValue){
                $pattern->push(sprintf($sectionValue, $sectionName));
                $name->push($sectionName);
            });
            $pattern->map(function($item, $key) use($content, $name){
                preg_match($item, $content->getValues()['content'], $valores);

                $valoes = $this->valores($valores);
                    $this->addSection($name[$key], $valoes ?? null);
            });
        }

    }

    protected function valores($valores)
    {
        if (!empty($valores[1])) {
            return $valores[1];
        }
        if (isset($valores[3]) && !empty($valores[3])) {
            return $valores[3];
        }
        return $valores[2];
    }

    /**
     * Add an section
     *
     * @param $sectionName
     * @param $valor
     * @return void
     */
    public function addSection($sectionName, $valor)
    {
        $valor = is_null($valor) ?: (object) ['name' => $sectionName, 'content' => $valor];
        $this->push($valor);
    }
}