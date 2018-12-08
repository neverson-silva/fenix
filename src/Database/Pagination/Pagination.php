<?php

namespace Fenix\Database\Pagination;

use Fenix\Contracts\Pagination\Pagination as Paginatable;
use Fenix\Support\Collection as Collectable;
use Fenix\Database\Collection\Collection;
use Pagerfanta\Adapter\ArrayAdapter;
use Fenix\Http\Request;
use Pagerfanta\Pagerfanta;


/**
 * Paginate database results
 * 
 * This class handle's back-end pagination with the database results
 * 
 * @author Neverson Bento da Silva <neversonbs13@gmail.com>
 * @package Fenix
 * @subpackage Database\Pagination
 * @see Pagerfanta\Pagerfanta
 * @see Pagerfanta\Adapter\ArrayAdapter
 */
class Pagination extends Pagerfanta implements Paginatable
{
    /**
     * Page name
     *
     * @var string
     */
    private $pageName = 'page';

    /**
     * Request
     *
     * @var Request
     */
    private $request;

    public function __construct(Collectable $collection)
    {
        $arrayAdapter = new ArrayAdapter($collection->toArray());
        $this->request = new Request;
        parent::__construct($arrayAdapter);

    }

      /**
     * Set Max Per Page
     *
     * @param integer $page
     * @return void
     */
    public function setPerPage(int $page)
    {
        return $this->setMaxPerPage($page);
    }
    /**
     * Get max per page
     *
     * @return integer
     */
    public function getPerPage() : int
    {
        return $this->getMaxPerPage();
    }
    
    /**
     * Set a page name
     *
     * @param string $name
     * @return void
     */
    public function setPageName(string $name)
    {
        $this->pageName = $name;
    }
    /**
     * Get page name
     *
     * @return void
     */
    public function getPageName()
    {
        return $this->pageName;
    }
    /**
     * Total Pages
     *
     * @return integer
     */
    public function totalPages() : int
    {
        return $this->getNbPages();
    }
    /**
     * Get current page results
     *
     * @return Collection
     */
    public function currentPageResults()
    {
        return new Collection($this->getCurrentPageResults());
    }
    /**
     * Get a page results
     *
     * @param integer $page
     * @return Collection
     */
    public function getPage(int $page)
    {
        $this->setCurrentPage($page);
        return $this->currentPageResults();
    }
    /**
     * Show current page results
     * Getting input as parameter
     *
     * @return Collection
     */
    public function show()
    {  
        $request = $this->request;
        $page = $this->getPageName();
        $pageNumber = $request->input($page);
        if (empty($pageNumber)) {
            return $this->currentPageResults();
        }
        return $this->getPage($pageNumber);
    }
    /**
     * Last Page Results
     *
     * @return void
     */
    public function lastPage()
    {
        $lastPage = $this->getCurrentPageOffsetEnd();
        return $this->getPage($lastPage);
    }
    /**
     * First Page Results
     *
     * @return void
     */
    public function firstPage()
    {
        $firstPage  = $this->getCurrentPageOffsetStart();
        return $this->getPage($firstPage);
    }
    /**
     * Next Page results
     *
     * @return void
     */
    public function nextPage()
    {
        $next = $this->nextPageNumber();
        return $this->getPage($next);
    }
    /**
     * Previuos Page results
     *
     * @return void
     */
    public function previousPage()
    {
        $previous = $this->previousPageNumber();
        return $this->getPage($previous);
    }
    /**
     * Next Page Number
     *
     * @return integer
     */
    public function nextPageNumber() : int
    {
        if ($this->hasNextPage()) {
            return $this->getCurrentPage() + 1;
        }
        return $this->getCurrentPage();
    }
    /**
     * Previous Page Number
     *
     * @return integer
     */
    public function previousPageNumber() :int
    {
        if ($this->hasPreviousPage()) {
            return $this->getCurrentPage() - 1;
        }
        return $this->getCurrentPage();
    }
}