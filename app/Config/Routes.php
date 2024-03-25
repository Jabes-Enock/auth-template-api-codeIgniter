<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */


$routes->group(
    "api/auth",
    [
        "namespace" => "App\Controllers\Api\Auth",
    ],
    static function ($routes) {
        $routes->post("register", "AuthController::register");
        $routes->post("login", "AuthController::login");
        $routes->get("profile", "AuthController::profile", ["filter" => "authByToken"]);
        $routes->get("logout", "AuthController::logout", ["filter" => "authByToken"]);
        $routes->post("set-email/(:num)", "AuthController::setEmail/$1", ["filter" => "authByToken"]);
        $routes->post("set-username/(:num)", "AuthController::setUsername/$1", ["filter" => "authByToken"]);
        $routes->delete("delete/(:num)", "AuthController::delete/$1", ["filter" => "authByToken"]);

        $routes->get("invalid-access", "AuthController::accessDenied");
    }
);

