<?php

$app->get('/', 'app.controller:home')->setName('home');

$app->group('', function () {
    $this->map(['GET', 'POST'], '/login', 'auth.controller:login')->setName('login');
    $this->map(['GET', 'POST'], '/register', 'auth.controller:register')->setName('register');
})->add($container['guest.middleware']);

$app->get('/logout', 'auth.controller:logout')
    ->add($container['auth.middleware']())
    ->setName('logout');

$this->map(['GET', 'POST'], '/profile', 'app.controller:profile')
    ->add($container['auth.middleware']())
    ->setName('profile');

$app->get('/dashboard', 'app.controller:dashboard')
    ->add($container['auth.middleware']())
    ->setName('dashboard');