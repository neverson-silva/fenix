<?php

namespace Fenix\Contracts\Session;
/**
 * Session
 * @author Neverson Bento da Silva <neverson_bs13@gmail.com>
 * @package Meltdown\Contracts\Session
 * @version 1.0
 * @copyright MIT © 2018
 *
 */
interface SessionInterface
{
    /**
     * Retrieve Session value
     * @param string $name
     * @return mixed
     */
    public function get(string $name);

    /**
     * Check if value exists in current session
     * @param string $name
     * @return bool
     */
    public function has(string $name);

    /**
     * Set a value to session
     * @param $name
     * @param $message
     * @return void
     */
    public function set(string $name, $message);

    /**
     * Register expiration time
     * @param string $duration
     * @return $this
     */
    public function register($duration);
    
    /**
     * Ends current session
     * @return bool
     */
    public function destroy();
}
