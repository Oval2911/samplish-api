<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(true);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.


/*Auth group*/

// $routes->post('auth/login', "App\Auth::login");

$routes->group("Auth", function($routes){

    // URL - /login orlogout
    $routes->post("/login", "App\Auth::login");
    $routes->post("/logout", "App\Auth::logout");
  
    // $routes->match(["get", "post", ], "test", "Auth::test");
});

$routes->group("Samplers", function($routes){

    $routes->get("/", "App\Samplers::index");
    // $routes->post("/logout", "App\Auth::logout");
  
});

//User 
$routes->group("User", function($routes){

    $routes->post("/", "App\User::register");
    // $routes->post("/logout", "App\Auth::logout");
  
});

//Brand 
$routes->group("Brand", function($routes){
    $routes->get("/", "App\Brand::index");
});

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
