<?php
namespace Fenix\Http;

use Fenix\Contracts\Session\SessionInterface;

/**
 * Session
 * @author Neverson Bento da Silva <neverson_bs13@gmail.com>
 * @package Fenix\Http\Session
 * @version 0.1
 * @copyright GPL © 2018
 *
 */

class Session implements SessionInterface
{

    protected $session;
    public function __construct($session = 'NEWSESS')
    {
        empty(session_name()) ? session_name($session) : null;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->session = (object) $_SESSION;
    }
    /**
     * Retrieve Session value
     * @param string $name
     * @return mixed
     */
    public function get(string $name)
    {
        return $this->has($name) ? $_SESSION[$name] : null;
    }

    public function getMessage(string $name)
    {
        $message = $this->get($name) ?? null;
        unset($_SESSION[$name]);
        return $message;
    }

    /**
     * Check if value exists in current session
     * @param string $name
     * @return bool
     */
    public function has(string $name)
    {
        return isset($_SESSION[$name]);
    }

    /**
     * Set a value to session
     * @param $name
     * @param $message
     * @return void
     */
    public function set(string $name, $message)
    {
        $_SESSION[$name] = $message;
    }

    /**
     * Register expiration time
     * @param string $duration
     * @return $this
     */
    public function register($duration = '5 seconds')
    {
        $this->setExpireTime($duration);
        return $this;
    }

    /**
     * Ends current session
     * @return bool
     */
    public function destroy()
    {
        $_SESSION = [];
        session_destroy();
    }

    /**
     * Set expiration time
     *
     * @param string $time
     * @return void
     */
    public function setExpireTime($time)
    {
        $this->set('duration', $time);
        $this->set('expire', strtotime("+$time"));
    }

    /**
     * Check if session is expired
     * @return bool
     */
    public function isExpired()
    {
        if (time() > $this->get('expire')) {
            return true;
        }
        return false;
    }

    /**
     * See if current session is still valid
     *
     * @return boolean
     */
    public function isValid()
    {
        if ($this->isExpired()) {
            $this->destroy();
            return false;
        }
        $this->renew();
        return true;
    }

    /**
     * Renew session
     *
     * @return $this
     */
    public function renew()
    {
        // if (session_status() == PHP_SESSION_NONE) {
        //     session_start();
        //     session_regenerate_id();
        // }
        \session_regenerate_id();
        $this->setExpireTime($this->get('duration'));
        return $this;
    }

    /**
     * CSRF
     *
     * @return string
     */
    public function csrf()
    {
        $token = sha1(mt_rand(1, 100)) ;
        $this->set('__token', $token);
        $input = "<input type='hidden' name='__token' value='$token' />";
        return $input;
    }

    /**
     * Session all
     *
     * @return void
     */
    public function all()
    {
        if (!empty($_SESSION)) {
            return $_SESSION;
        }
    }

    /**
     * See if session has token
     *
     * @return boolean
     */
    public function hasToken()
    {
        if (request()->hasInput('__token')) {
            if ($this->has('__token')) {
                $token = $this->get('__token');
                $input = request()->input('__token');
                if ($token === $input) {
                    return true;
                }
            }
        }
        return false;
    }
}