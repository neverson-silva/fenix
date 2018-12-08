<?php

namespace Fenix\Traits\Auth;

use Fenix\Contracts\Database\Model as ModelInterface;
use Fenix\Http\Session;

trait Authentication
{
    protected $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }
    /**
     * Register an user
     * @param ModelInterface $user
     * @param array $create
     * @throws Exception
     * @return bool
     */
    public function register(ModelInterface $user, array $create)
    {
        if ($this->session->hasToken()) {
            return $user->create($create);
        }
        throw new Exception("Sorry, we can't to create your user now try again later.");
    }

    /**
     * Authentication
     *
     * @param ModelInterface $user
     * @param string $email
     * @param string $password
     * @return bool
     */
    public function login(ModelInterface $user, $email, $password)
    {

        if ($this->session->hasToken()) {
            try {
                $user = $user->where('email', $email)->get();

            } catch (PDOException $e) {
                $this->session->set('error', 'Usuário não encontrado, tente novamente');
                header('HTTP/1.1 403 Forbidden');
                return redirect('/auth');
            }
            if ($user->isEmpty()) {
                $this->session->set('error', 'Usuário não encontrado, tente novamente');
                header('HTTP/1.1 403 Forbidden');
                return redirect('/auth');
            }

            if (\password_verify($password, $user->password) === true) {
                $this->session->set('logado', true);
                $this->session->set('email', $user->email);
                $this->session->register('20 minutes');
                while ($this->session->isValid()) {
                    return true;
                }
            }
        }
        return false;
    }
}