<?php

$app->get('/', 'app.controller:home')->setName('home');

$app->group('', function () {
    $this->map(['GET', 'POST'], '/login', 'auth.controller:login')->setName('login');
    $this->map(['GET', 'POST'], '/register', 'auth.controller:register')->setName('register');
})->add($container['guest.middleware']);

$app->get('/logout', 'auth.controller:logout')
    ->add($container['auth.middleware']())
    ->setName('logout');

$app->get('/profile', 'app.controller:profile')
    ->add($container['auth.middleware']())
    ->setName('profile');

$app->post('/upload', 'app.controller:upload')
    ->add($container['auth.middleware']())
    ->setName('upload');

$app->get('/delete/video/{id}', 'app.controller:deleteVideo')
    ->add($container['auth.middleware']())
    ->setName('deleteVideo');

$app->post('/rename/video/{id}', 'app.controller:renameVideo')
    ->add($container['auth.middleware']())
    ->setName('renameVideo');

$app->get('/dashboard', 'app.controller:dashboard')
    ->add($container['auth.middleware']())
    ->setName('dashboard');
