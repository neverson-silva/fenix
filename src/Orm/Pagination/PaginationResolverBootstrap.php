<?php

namespace Fenix\Orm\Pagination;

use Fenix\Contracts\Pagination\Pagination as Paginatable;
use Fenix\Contracts\Pagination\PaginationResolver;

/**
 * Links resolver for bootstrap
 * @author Neverson Bento da Silva <neverson_bs13@gmail.com>
 * @version 1.0
 * @copyright MIT © 2018
 *
 */
class PaginationResolverBootstrap implements PaginationResolver
{
    protected $pagination;
    public function __construct(Paginatable $pagination)
    {
        $this->pagination = $pagination;
    }
    /**
     * Generates a list with total page numbers
     *
     * @param string $class
     * @return string
     */
    public function links($class = '') : string
    {
        $it = 1;
        $links = '';
        $next = $this->pagination->nextPageNumber();
        $previous = $this->pagination->previousPageNumber();
        $temProx = $this->pagination->hasNextPage() ? '' : 'disabled';
        $temAnt = $this->pagination->hasPreviousPage() ? '' : 'disabled';

        $proxima = sprintf(
            "<li class='page-item %s'>
                                <a class='page-link' href='?%s=%s'>Próxima</a>
                            </li> \n",
            $temProx,
            $this->pagination->getPageName(),
            $next
        );
        $anterior = sprintf(
            "<li class='page-item %s'>
                                <a class='page-link' href='?%s=%s'>Anterior</a>
                            </li> \n",
            $temAnt,
            $this->pagination->getPageName(),
            $previous
        );
        while ($it <= $this->pagination->totalPages()) {
            $atual = $this->pagination->getCurrentPage() == $it ? 'active' : '';
            $links .= sprintf(
                "<li class='page-item %s'>
                                    <a class='page-link' href='?%s=%s'> %d </a>
                              </li> \n",
                $atual,
                $this->pagination->getPageName(),
                $it,
                $it
            );
            $it++;
        }
        $link = sprintf("<div class='%s'> <ul class='pagination'> %s %s %s </ul></div>", $class, $anterior, $links, $proxima);
        return $link;
    }
}