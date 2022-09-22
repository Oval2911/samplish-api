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
$routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.


/*Auth group*/

$routes->group("Auth", function($routes){
    $routes->post("login", "Auth::login");
    // $routes->post("logout", "Auth::logout");
    $routes->post("register", "User::register");
});

$routes->group("User", function($routes){
    $routes->post("sampler", "User::sampler");
    $routes->post("company", "User::company");
});

// resource
$routes->group("Resource", function($routes){
    $routes->get("", "Resource::index");
});

//Brand 
$routes->group("Brand", function($routes){
    $routes->get("datatable", "Brand::datatable");
    $routes->get("datatable_all", "Brand::datatable_all");
    $routes->get("data", "Brand::data");
    $routes->get("dropdown", "Brand::dropdown");
    $routes->post("store", "Brand::store");
    $routes->post("amend", "Brand::amend");
    $routes->post("destroy", "Brand::destroy");
    $routes->post("destroys", "Brand::destroys");
});

//Brand Category
$routes->group("Brand_Category", function($routes){
    $routes->get("datatable", "Brand_Category::datatable");
    $routes->get("data", "Brand_Category::data");
    $routes->get("dropdown", "Brand_Category::dropdown");
    $routes->post("store", "Brand_Category::store");
    $routes->post("amend", "Brand_Category::amend");
    $routes->post("destroy", "Brand_Category::destroy");
    $routes->post("destroys", "Brand_Category::destroys");
});

//Area
$routes->group("Area", function($routes){
    $routes->get("datatable", "Area::datatable");
    $routes->get("data", "Area::data");
    $routes->get("dropdown", "Area::dropdown");
    $routes->post("store", "Area::store");
    $routes->post("amend", "Area::amend");
    $routes->post("destroy", "Area::destroy");
    $routes->post("destroys", "Area::destroys");
});

//Community
$routes->group("Community", function($routes){
    $routes->get("datatable", "Community::datatable");
    $routes->get("data", "Community::data");
    $routes->get("dropdown", "Community::dropdown");
    $routes->post("store", "Community::store");
    $routes->post("amend", "Community::amend");
    $routes->post("destroy", "Community::destroy");
    $routes->post("destroys", "Community::destroys");
});

//Interest
$routes->group("Interest", function($routes){
    $routes->get("datatable", "Interest::datatable");
    $routes->get("data", "Interest::data");
    $routes->get("dropdown", "Interest::dropdown");
    $routes->post("store", "Interest::store");
    $routes->post("amend", "Interest::amend");
    $routes->post("destroy", "Interest::destroy");
    $routes->post("destroys", "Interest::destroys");
});

//Tone & Manner
$routes->group("Tone_Manner", function($routes){
    $routes->get("dropdown", "Tone_Manner::dropdown");
});

//Area
$routes->group("Campaign", function($routes){
    $routes->get("datatable", "Campaign::datatable");
    $routes->get("datatable_payment", "Campaign::datatable_payment");
    $routes->get("datatable_all_company", "Campaign::datatable_all_company");
    $routes->get("datatable_admin_payment_company", "Campaign::datatable_admin_payment_company");
    $routes->get("datatable_overview", "Campaign::datatable_overview");
    $routes->get("datatable_on_going", "Campaign::datatable_on_going");
    $routes->get("datatable_upcoming", "Campaign::datatable_upcoming");
    $routes->get("datatable_overview_sampler", "Campaign::datatable_overview_sampler");
    $routes->get("datatable_sampler", "Campaign::datatable_sampler");
    $routes->get("datatable_admin_assign", "Campaign::datatable_admin_assign");
    $routes->get("datatable_brands", "Campaign::datatable_brands");
    $routes->get("datatable_sampler_feedback", "Campaign::datatable_sampler_feedback");
    $routes->get("data", "Campaign::data");
    $routes->get("data_sampler", "Campaign::data_sampler");
    $routes->get("data_payment", "Campaign::data_payment");
    $routes->get("dropdown_mix", "Campaign::dropdown_mix");
    $routes->post("store", "Campaign::store");
    $routes->post("amend", "Campaign::amend");
    $routes->post("amend_payment", "Campaign::amend_payment");
    $routes->post("amend_brands", "Campaign::amend_brands");
    $routes->post("destroy", "Campaign::destroy");
    $routes->post("join", "Campaign::join");
    $routes->post("join_sampler", "Campaign::join_sampler");
    $routes->post("draft", "Campaign::draft");
    $routes->post("wait_confirm", "Campaign::wait_confirm");
    $routes->post("reject", "Campaign::reject");
    $routes->post("confirm", "Campaign::confirm");
    $routes->post("nego", "Campaign::nego");
    $routes->post("wait_pay", "Campaign::wait_pay");
    $routes->post("joined", "Campaign::joined");
    $routes->post("rejected", "Campaign::rejected");
    $routes->post("otw", "Campaign::otw");
    $routes->post("not_received", "Campaign::not_received");
    $routes->post("arrived", "Campaign::arrived");
    $routes->post("review", "Campaign::review");
    $routes->post("done", "Campaign::done");
});

//Profile
$routes->group("Profile", function($routes){
    $routes->get("data", "Profile::data");
    $routes->post("amend_profile", "Profile::amend_profile");
    $routes->post("amend_address", "Profile::amend_address");
    $routes->post("amend_social", "Profile::amend_social");
    $routes->post("amend_family", "Profile::amend_family");
    $routes->post("amend_interest_community", "Profile::amend_interest_community");
});

//Sampler
$routes->group("Sampler", function($routes){
    $routes->get("", "Samplers::index");
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
