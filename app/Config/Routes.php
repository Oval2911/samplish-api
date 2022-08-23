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

// resource
$routes->group("Resource", function($routes){
    $routes->get("/", "Resource::index");
});

//Brand 
$routes->group("Brand", function($routes){
    $routes->get("/datatable", "App\Brand::datatable");
    $routes->get("/datatable_all", "App\Brand::datatable_all");
    $routes->get("/data", "App\Brand::data");
    $routes->get("/dropdown", "App\Brand::dropdown");
    $routes->post("/store", "App\Brand::store");
    $routes->post("/amend", "App\Brand::amend");
    $routes->post("/destroy", "App\Brand::destroy");
    $routes->post("/destroys", "App\Brand::destroys");
});

//Brand Category
$routes->group("Brand_Category", function($routes){
    $routes->get("/datatable", "App\Brand_Category::datatable");
    $routes->get("/data", "App\Brand_Category::data");
    $routes->get("/dropdown", "App\Brand_Category::dropdown");
    $routes->post("/store", "App\Brand_Category::store");
    $routes->post("/amend", "App\Brand_Category::amend");
    $routes->post("/destroy", "App\Brand_Category::destroy");
    $routes->post("/destroys", "App\Brand_Category::destroys");
});

//Area
$routes->group("Area", function($routes){
    $routes->get("/datatable", "App\Area::datatable");
    $routes->get("/data", "App\Area::data");
    $routes->get("/dropdown", "App\Area::dropdown");
    $routes->post("/store", "App\Area::store");
    $routes->post("/amend", "App\Area::amend");
    $routes->post("/destroy", "App\Area::destroy");
    $routes->post("/destroys", "App\Area::destroys");
});

//Community
$routes->group("Community", function($routes){
    $routes->get("/datatable", "App\Community::datatable");
    $routes->get("/data", "App\Community::data");
    $routes->get("/dropdown", "App\Community::dropdown");
    $routes->post("/store", "App\Community::store");
    $routes->post("/amend", "App\Community::amend");
    $routes->post("/destroy", "App\Community::destroy");
    $routes->post("/destroys", "App\Community::destroys");
});

//Interest
$routes->group("Interest", function($routes){
    $routes->get("/datatable", "App\Interest::datatable");
    $routes->get("/data", "App\Interest::data");
    $routes->get("/dropdown", "App\Interest::dropdown");
    $routes->post("/store", "App\Interest::store");
    $routes->post("/amend", "App\Interest::amend");
    $routes->post("/destroy", "App\Interest::destroy");
    $routes->post("/destroys", "App\Interest::destroys");
});

//Tone & Manner
$routes->group("Tone_Manner", function($routes){
    $routes->get("/dropdown", "App\Tone_Manner::dropdown");
});

//Area
$routes->group("Campaign", function($routes){
    $routes->get("/datatable", "App\Campaign::datatable");
    $routes->get("/datatable_payment", "App\Campaign::datatable_payment");
    $routes->get("/datatable_all_company", "App\Campaign::datatable_all_company");
    $routes->get("/datatable_admin_payment_company", "App\Campaign::datatable_admin_payment_company");
    $routes->get("/datatable_overview", "App\Campaign::datatable_overview");
    $routes->get("/datatable_on_going", "App\Campaign::datatable_on_going");
    $routes->get("/datatable_upcoming", "App\Campaign::datatable_upcoming");
    $routes->get("/datatable_overview_sampler", "App\Campaign::datatable_overview_sampler");
    $routes->get("/data", "App\Campaign::data");
    $routes->get("/data_sampler", "App\Campaign::data_sampler");
    $routes->get("/data_payment", "App\Campaign::data_payment");
    $routes->get("/dropdown_mix", "App\Campaign::dropdown_mix");
    $routes->post("/store", "App\Campaign::store");
    $routes->post("/amend", "App\Campaign::amend");
    $routes->post("/amend_payment", "App\Campaign::amend_payment");
    $routes->post("/amend_brands", "App\Campaign::amend_brands");
    $routes->post("/destroy", "App\Campaign::destroy");
    $routes->post("/join", "App\Campaign::join");
    $routes->post("/join_sampler", "App\Campaign::join_sampler");
    $routes->post("/draft", "App\Campaign::draft");
    $routes->post("/wait_confirm", "App\Campaign::wait_confirm");
    $routes->post("/reject", "App\Campaign::reject");
    $routes->post("/confirm", "App\Campaign::confirm");
    $routes->post("/nego", "App\Campaign::nego");
    $routes->post("/wait_pay", "App\Campaign::wait_pay");
    $routes->post("/joined", "App\Campaign::joined");
    $routes->post("/not_received", "App\Campaign::not_received");
});

//Profile
$routes->group("Profile", function($routes){
    $routes->get("/data", "App\Profile::data");
    $routes->post("/amend_profile", "App\Profile::amend_profile");
    $routes->post("/amend_address", "App\Profile::amend_address");
    $routes->post("/amend_social", "App\Profile::amend_social");
    $routes->post("/amend_family", "App\Profile::amend_family");
    $routes->post("/amend_interest_community", "App\Profile::amend_interest_community");
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
