<?php

use \Illuminate\Database\Capsule\Manager as DB;

DB::table('activations')->insert([
    [
        'id' => 1,
        'user_id' => 1,
        'code' => '5vnkFl9d8FxUWDq9spnF7jSA5qg9yNIx',
        'completed' => 1
    ]
]);

DB::table('role_users')->insert([
    [
        'user_id' => 1,
        'role_id' => 1
    ]
]);

\App\Model\User::insert(
    [
        'id' => 1,
        'username' => 'admin',
        'email' => 'admin@admin.com',
        'password' => '$2y$10$S7Tyq28k6H1ExCbyn/Gij.o0gaRIGwuQOZPY2Y6sfVbKd1Kwbym0y',
        'permissions' => '{"user.delete":0}'
    ]
);

\App\Model\Label::insert([
    [
        'id' => 1, 
        'expression' => '(atlas).ouvre(porte)',
    ],
    [
        'id' => 2, 
        'expression' => '(atlas).prends(boîte)',
    ],
    [
        'id' => 3, 
        'expression' => '(atlas).marche()',
    ]
]);

\App\Model\Sequence::insert([
    [
        'id' => 1,
        'start' => '10',
        'end' => '14',
        'video_id' => '2',
        'label_id' => 1
    ],[
        'id' => 2,
        'start' => '70',
        'end' => '74',
        'video_id' => '2',
        'label_id' => 2
    ]
]);

\App\Model\Video::insert([
    [
        'id' => 2,
        'name' => 'Présentation Atlas',
        'link' => 'assets/videos/5ab904a765de6',
        'estVisible' => false,
        'user_id' => 1
    ], [
        'id' => 3,
        'name' => 'Capacité Atlas',
        'link' => 'assets/videos/5ab904bc83752',
        'estVisible' => true,
        'user_id' => 1
    ]
]);

