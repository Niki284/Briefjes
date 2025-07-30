<?php

require_once "../vendor/autoload.php";

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
$dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'ALLOW_ORIGIN']);

// Setup router
$router = new \Bramus\Router\Router();
$router->setNamespace('Controllers');

// CORS Headers
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

// === USERS ===
$router->mount('/api/users', function () use ($router) {
    $router->get('/', 'UserController@getAll'); // werkt
    $router->post('/', 'UserController@registerUser'); // werkt
    $router->post('/login', 'UserController@login'); // werkt
    $router->post('/refresh-token', 'UserController@refreshToken'); // werkt
});

// === ORGANISATIONS ===
$router->mount('/api/organisations', function () use ($router) {
    $router->get('/', 'OrganisatiesController@getAllorganizations'); // werkt
    $router->post('/', 'OrganisatiesController@createOrganization'); // werkt
    // $router->get('/{id}', 'OrganisatiesController@getOrganizationById'); // werkt
    $router->get('/{id}/channels', 'OrganisatiesController@getOrganizationChannels'); // werkt  straks aanpassen
});

// === CHANNELS ===
$router->mount('/api/channels', function () use ($router) {
    $router->get('/', 'ChannelsController@getAllChannels'); // werkt
    $router->post('/', 'ChannelsController@createChannel'); // werkt
    // $router->get('/{id}', 'ChannelsController@getChannelById'); 
    $router->get('/{id}/posts', 'ChannelsController@getChannelPosts');// werkt
    $router->get('/{channelId}/posts/{postId}', 'ChannelsController@getChannelPostById'); // werkt
    $router->delete('/{id}', 'ChannelsController@deleteChannel'); // werkt
    $router->post('/{channelId}/posts', 'ChannelsController@createPostInChannel'); // werkt 
});

// === POSTS (BRIEFJES) ===
$router->mount('/api/posts', function () use ($router) {
    $router->get('/', 'PostsController@getAllPosts'); // werkt maar ik neem de channels /posts mee
    $router->get('/{id}', 'PostsController@getPostById'); // werkt maar ik neem de channels /posts/1 mee
    // $router->post('/', 'PostsController@createPostInChannel');
});

// === SUBSCRIPTIONS ===
$router->mount('/api/subscriptions', function () use ($router) {
    $router->get('/', 'AbonnementenController@getAllSubscriptions'); // werkt
    $router->get('/me', 'AbonnementenController@getMySubscriptions');
    $router->post('/', 'AbonnementenController@createSubscription');
    $router->patch('/{id}/approve', 'AbonnementenController@approveSubscription');
    $router->delete('/{id}', 'AbonnementenController@deleteSubscription');
});

// === AUTHENTICATION ===
$router->mount('/api/auth', function () use ($router) {
    $router->post('/logout', 'UserController@logout');
    $router->get('/autologin', 'UserController@autoLogin');
    $router->get('/whoami', 'UserController@whoAmI');
});

$router->run();
