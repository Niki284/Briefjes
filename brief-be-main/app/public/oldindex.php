<?php

require_once "../vendor/autoload.php";
// load .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
$dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'ALLOW_ORIGIN']);

// create router
$router = new \Bramus\Router\Router();
$router->setNamespace('Controllers');

$router->before('GET|POST|PUT|DELETE|PATCH', '.*', function () {
    header('Access-Control-Allow-Origin: ' . $_ENV['ALLOW_ORIGIN']);
    header('Access-Control-Allow-Credentials: true');
});
$router->options('.*', function () {
    header('Access-Control-Allow-Origin: ' . $_ENV['ALLOW_ORIGIN']);
    header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE, PATCH');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Credentials: true');
});


$router->mount('/api/users', function () use ($router) {
    $router->get('/', 'UserController@getAll'); //working
    $router->get('/bookings', 'UserController@getAllBookings');
    $router->post('/', 'UserController@addUser'); //working
    $router->post('/login', 'UserController@login'); //working
    $router->post('/refresh-token', 'UserController@refreshToken'); //working?
});

$router->mount('/api/bookings', function () use ($router) {
    $router->get('/', 'BookController@overview'); //working
    $router->get('/{id}', 'BookController@search'); //working
    $router->post('/', 'BookController@create'); //working
    $router->patch('/{id}', 'BookController@update'); //working
});

$router->mount('/api/drivers', function () use ($router) {
    $router->get('/', 'ChauffeurController@getAll');
    $router->patch('/', 'ChauffeurController@addInfo');
    $router->put('/{id}', 'DriverController@update');
    $router->delete('/{id}', 'DriverController@delete');
});

$router->mount('/companies', function () use ($router) {
    $router->get('/', 'CompanyController@overview');
    $router->post('/', 'CompanyController@create');
    $router->put('/{id}', 'CompanyController@update');
    $router->delete('/{id}', 'CompanyController@delete');
});

$router->mount('/api/cars', function () use ($router) {
    $router->get('/', 'CarsController@getAllCars');
});
$router->mount('/api/organisaties', function () use ($router) {
    $router->get('/', 'organisatiesController@getAllOrganisaties');
    $router->post('/', 'organisatiesController@create'); 
    $router->get('/{id}', 'organisatiesController@getOrganisatiesId'); 
    $router->get('/{id}kanalen', 'organisatiesController@getOrganisatiesIdKanalen'); 

});
$router->mount('/api/kanalen', function () use ($router) {
    $router->get('/', 'kanalenController@getAllKanalen');
    $router->post('/', 'kanalenController@create');
    $router->get('/{id}', 'kanalenController@getKanalenId');
    $router->get('/{id}/briefjes', 'kanalenController@getKanalenIdBriefjes');
    $router->delete('/{id}', 'kanalenController@deleteKanalen');
});
$router->mount('/api/briefjes', function () use ($router) {
    $router->get('/', 'briefjesController@getAllBriefjes');
        $router->get('/{id}', 'briefjesController@getBriefjesId');
    $router->post('/', 'briefjesController@create');

});
$router->mount('/api/abonnementen', function () use ($router) {
    $router->get('/', 'abonnementenController@getAllabonnementen'); //test
    $router->get('/me', 'abonnementenController@getAllabonnementen');
    $router->post('/', 'abonnementenController@create');
    $router->get('/{id}/goegekeurd', 'abonnementenController@getAllAbonnementen');
    $router->delete('/', 'abonnementenController@deleteAbonnementen');

});




$router->run();