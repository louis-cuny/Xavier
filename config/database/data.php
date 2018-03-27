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

\App\Model\Sequence::insert([
    [
        'id' => 1,
        'name' => 'Atlas opens the door',
        'start' => '10',
        'end' => '14',
        'expression' => 'atlas.open(door)',
        'video_id' => '2'
    ],[
        'id' => 2,
        'name' => 'Atlas takes the box',
        'start' => '70',
        'end' => '74',
        'expression' => 'atlas.take(box)',
        'video_id' => '2'
    ]
]);

\App\Model\Video::insert([
    [
        'id' => 2,
        'name' => 'Atlas walking',
        'link' => 'assets/videos/5ab904a765de6',
        'user_id' => 1
    ], [
        'id' => 3,
        'name' => 'Atlas jumping',
        'link' => 'assets/videos/5ab904bc83752',
        'user_id' => 1
    ]
]);

