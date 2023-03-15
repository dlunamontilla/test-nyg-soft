<?php

// use App\Auth\Auth;
use App\Config\Route;



Route::get("/", "App\\Controller\\SaveData@form");

Route::get("/cities", "App\\Controller\\SaveData@getCities");
Route::get("/users", "App\\Controller\\SaveData@getUsers");

Route::post("/save/user", "App\\Controller\\SaveData@saveUser", [
    'pretty' => 'pretty'
]);