<?php

namespace App\Controller;

use Awurth\Slim\Helper\Controller\Controller;
use Cartalyst\Sentinel\Checkpoints\ThrottlingException;
use Respect\Validation\Validator as V;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * @property \Awurth\SlimValidation\Validator validator
 * @property \Cartalyst\Sentinel\Sentinel auth
 */
class AuthController extends Controller
{
    public function login(Request $request, Response $response)
    {
        if ($request->isPost()) {
            $credentials = [
                'username' => $request->getParam('username'),
                'password' => $request->getParam('password')
            ];
            $remember = $request->getParam('remember') ? true : false;
            try {
                if ($this->auth->authenticate($credentials, $remember)) {
                    $this->flash('success', 'Vous êtes maintenant connecté.');
                    return $this->redirect($response, 'home');
                } else {
                    $this->validator->addError('auth', 'Mauvais nom d\'utilisateur ou mot de passe.');
                }
            } catch (ThrottlingException $e) {
                $this->validator->addError('auth', 'Trop de tentatives !');
            }
        }
        return $this->render($response, 'auth/login.twig');
    }

    public function register(Request $request, Response $response)
    {
        if ($request->isPost()) {
            $username = $request->getParam('username');
            $email = $request->getParam('email');
            $password = $request->getParam('password');
            $admin = false;
            $this->validator->request($request, [
                'username' => V::length(3, 25)->alnum('_')->noWhitespace(),
                'email' => V::noWhitespace()->email(),
                'password' => [
                    'rules' => V::noWhitespace()->length(6, 25),
                    'messages' => [
                        'length' => 'La taille du mot de passe doit être entre {{minValue}} et {{maxValue}} caractères.'
                    ]
                ],
                'password_confirm' => [
                    'rules' => V::equals($password),
                    'messages' => [
                        'equals' => 'Les mots de passes ne sont pas les mêmes.'
                    ]
                ]
            ]);
            if ($this->auth->findByCredentials(['login' => $username])) {
                $this->validator->addError('username', 'Ce nom d\'utilisateur est déjà utilisé.');
            }
            if ($this->auth->findByCredentials(['login' => $email])) {
                $this->validator->addError('email', 'Cet email est déjà utilisé.');
            }
            if ($this->validator->isValid()) {
                /** @var \Cartalyst\Sentinel\Roles\EloquentRole $role */
                $role = $admin ? $this->auth->findRoleByName('Admin') : $this->auth->findRoleByName('User');
                $user = $this->auth->registerAndActivate([
                    'username' => $username,
                    'email' => $email,
                    'password' => $password,
                    'permissions' => [
                        'user.delete' => 0
                    ]
                ]);
                $role->users()->attach($user);
                $this->flash('success', 'Votre compte a bien été créé.');
                return $this->redirect($response, 'login');
            }
        }
        return $this->render($response, 'auth/register.twig');
    }

    public function logout(Request $request, Response $response)
    {
        $this->auth->logout();
        $this->flash('success', 'Vous vous êtes déconnecté avec succès.');
        return $this->redirect($response, 'home');
    }
}