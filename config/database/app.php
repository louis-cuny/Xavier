<?php

use Cartalyst\Sentinel\Native\Facades\Sentinel;
use Cartalyst\Sentinel\Native\SentinelBootstrapper;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;


$sentinel = (new Sentinel(new SentinelBootstrapper(__DIR__ . '/../sentinel.php')))->getSentinel();

// Your application's tables
Manager::schema()->create('video', function (Blueprint $table) {
    $table->increments('id');
    $table->string('name');
    $table->string('link');
    $table->unsignedInteger('user_id');
    $table->foreign('user_id')->references('id')->on('user');
});

Manager::schema()->create('sequence', function (Blueprint $table) {
    $table->increments('id');
    $table->string('start'); //TODO reprendre le format utilisé par jplayer
    $table->string('end');
    $table->string('expression');
    $table->unsignedInteger('video_id');
    $table->foreign('video_id')->references('id')->on('video');
    $table->timestamps();

});