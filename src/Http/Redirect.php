<?php
namespace Fenix\Http;

/**
 * Redirecting
 * @author Neverson Bento da Silva <neverson_bs13@gmail.com>
 * @package Fenix\Http\Request
 * @version 0.1
 * @copyright GPL © 2018
 *
 */
class Redirect
{
    protected $url;

    /**
     * Redirect page to an url
     * @param string $to
     * @return mixed
     */
    public function to($to = '')
    {

        if (empty($to)) {
            $this->url = $_SERVER['HTTP_REFERER'];
            return $this;
        } else {
            return header("location:$to");
        }
    }

    /**
     * Back to previous page
     */
    public function back()
    {
        if (!empty($this->url)) {
            return "<script>history.go(-1)</script>";
        }
    }
}