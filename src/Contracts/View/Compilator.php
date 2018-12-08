<?php

namespace Fenix\Contracts\View;

interface Compilator
{

    /**
     * Check if the view is still valid
     *
     * If the view had an edition in the last 20 seconds it will be expired
     *
     * @param string $what Check from what directory get the file to check
     * @return bool
     * @throws \Exception
     */
    public function isExpired($name, $what = false);

    /**
     * Compile the view
     *
     * @return mixed
     */
    public function compile();

    /**
     * Replace
     * @param $search
     * @param $replace
     * @return bool
     * @throws \Fenix\Support\FileNotFoundException
     */
    public function replace($search, $replace) : bool;

    /**
     * Read the view content
     *
     * @return void
     */
    public function readView($view);

    /**
     * Get the view read content
     *
     * @return string
     */
    public function getViewContent() : string;
}
