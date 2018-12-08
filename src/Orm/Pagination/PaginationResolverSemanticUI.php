<?php

namespace Fenix\Orm\Pagination;

use Fenix\Contracts\Pagination\PaginationResolver;
use Fenix\Contracts\Pagination\Pagination as Paginatable;

class PaginationResolverSemanticUI implements PaginationResolver
{

    protected $pagination;

    public function __construct(Paginatable $pagination)
    {
        $this->pagination = $pagination;
    }

    /**
     * Links
     * @param string $class
     * @return string
     */
    public function links($class = '') : string
    {

        $links = "<div class='ui pagination menu'> \n";
        $links .= $this->previousButton($this->pagination);
        $links .= $this->pageButtons($this->pagination);
        $links .= $this->nextButton($this->pagination) . "</div>";

        return $links;

    }

    /**
     * Pagina anterior
     * @param Pagination $pagination
     * @return string
     */
    private function previousButton(Pagination $pagination)
    {
        $prev = "<%s class='% icon item' href='?%s=%s'> \n";
        $prev .= "<i class='left arrow icon'></i> \n";
        $prev .= "</%s>";

        return $pagination->hasPreviousPage()
            ? sprintf($prev, 'a', '', $pagination->getPageName(), $pagination->previousPageNumber(), 'a')
            : '';
    }

    /**
     * Proxima pagina
     * @param Pagination $pagination
     * @return string
     */
    private function nextButton(Pagination $pagination)
    {
        $next = "<%s class='% icon item' href='?%s=%s'> \n";
        $next .= "<i class='right arrow icon'></i>\n";
        $next .= "</%s>\n";

        return $pagination->hasNextPage()
            ? sprintf($next, 'a', '', $pagination->getPageName(), $pagination->nextPageNumber(), 'a')
            : '';

    }

    /**
     * Links botões de paginação
     * @param Pagination $pagination
     * @return string
     */
    private function pageButtons(Pagination $pagination)
    {
        $page = 1;
        $buttons = '';
        for ($i = 1; $i <= $pagination->totalPages(); $i++) {
            $atual = $pagination->getCurrentPage() == $i ? 'active' : null;
            $args = [$atual, $pagination->getPageName(), $i, $i];
            $buttons .=   vsprintf("<a class='%s item' href='?%s=%s'> %d </a> ", $args);
        }
        return $buttons;
    }
}