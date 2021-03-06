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
    $table->boolean('estVisible');
    $table->unsignedInteger('user_id');
    $table->foreign('user_id')->references('id')->on('user')->onDelete('cascade');
});

Manager::schema()->create('label', function (Blueprint $table) {
    $table->increments('id');
    $table->string('expression');
    $table->timestamps();
});

Manager::schema()->create('mots', function (Blueprint $table) {
    $table->increments('id');
    $table->string('type');
    $table->string('mot');
    $table->timestamps();
});

Manager::schema()->create('sequence', function (Blueprint $table) {
    $table->increments('id');
    $table->string('start'); //TODO reprendre le format utilisé par jplayer
    $table->string('end');
    $table->unsignedInteger('video_id');
    $table->unsignedInteger('label_id');
    $table->foreign('video_id')->references('id')->on('video')->onDelete('cascade');
    $table->foreign('label_id')->references('id')->on('label');
    $table->timestamps();
});

Manager::schema()->create('comment', function (Blueprint $table) {
    $table->increments('id');
    $table->string('comment');
    $table->unsignedInteger('sequence_id');
    $table->foreign('sequence_id')->references('id')->on('sequence')->onDelete('cascade');
    $table->unsignedInteger('user_id')->nullable();
    $table->foreign('user_id')->references('id')->on('user')->onDelete('cascade');
    $table->timestamps();
});
