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

/*$this->map(['GET', 'POST'], '/dashboard', 'app.controller:dashboard')*/
$app->post('/upload', 'app.controller:upload')
    ->add($container['auth.middleware']())
    ->setName('upload');

$app->get('/delete/video/{id}', 'app.controller:deleteVideo')
    ->add($container['auth.middleware']())
    ->setName('deleteVideo');

$app->post('/rename/video/{id}', 'app.controller:renameVideo')
    ->add($container['auth.middleware']())
    ->setName('renameVideo');

$app->post('/visible/video/{id}', 'app.controller:visibleVideo')
    ->add($container['auth.middleware']())
    ->setName('visibleVideo');

$app->get('/delete/sequence/{id}', 'app.controller:deleteSequence')
    ->add($container['auth.middleware']())
    ->setName('deleteSequence');

$app->post('/rename/sequence/{id}', 'app.controller:renameSequence')
    ->add($container['auth.middleware']())
    ->setName('renameSequence');

$app->get('/comments/{id}', 'app.controller:displayComments')
    ->setName('comments');

$app->post('/comments/add/{id}', 'app.controller:addComment')
    ->setName('addComment');

$app->get('/delete/comment/{id}', 'app.controller:deleteComment')
    ->add($container['auth.middleware']())
    ->setName('deleteComment');

$app->map(['GET', 'POST'],'/dashboard/{id}' , 'app.controller:dashboard')
    ->add($container['auth.middleware']())
    ->setName('dashboard');

$app->get('/export', 'app.controller:export')
    ->add($container['auth.middleware']())
    ->setName('export');

$app->post('/xml', 'app.controller:xmlExport')
    ->add($container['auth.middleware']())
    ->setName('xml');

$app->get('/vue_d_ensemble', 'app.controller:ensemble')
    ->add($container['auth.middleware']())
    ->setName('vueensemble');