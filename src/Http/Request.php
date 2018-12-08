<?php
namespace Fenix\Http;

use Fenix\Contracts\Request\Request as Requestable;
use Fenix\Routing\Request as RequestRouting;
/**
 * Handle requests
 * @author Neverson Bento da Silva <neverson_bs13@gmail.com>
 * @package Foundation\Http\Request
 * @version 0.1
 * @copyright GPL © 2018
 *
 */
class Request extends RequestRouting implements Requestable
{
    /**
     * Check if a request has an input
     *
     * @param  $input
     * @return boolean
     */
    public function hasInput($input)
    {
        $params = $this->getServerParams();
        return isset($params[$input]);
    }
    public function url()
    {
        return parent::url()->getPath();
    }
}