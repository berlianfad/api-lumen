<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->get('/stuff', 'StuffController@index');
$router->post('/stuff', 'StuffController@store');
$router->get('/stuff/trash', 'StuffController@deleted');
$router->delete('/stuff/permanent', 'StuffController@permanentDeleteAll');
$router->delete('/stuff/permanent/{id}', 'StuffController@permanentDelete');
$router->put('/stuff/ restore', 'StuffController@restoreAll');
$router->put('/stuff/restore/{id}', 'StuffController@restore');

$router->get('/stuff/{id}', 'StuffController@show');
$router->put('/stuff/{id}', 'StuffController@update');
$router->delete('/stuff/{id}', 'StuffController@destroy');



$router->get('/stuff-stock', 'StuffStockController@index');
$router->post('/stuff-stock', 'StuffStockController@store');
$router->get('/stuff-stock/trash', 'StuffStockController@deleted');
$router->delete('/stuff-stock/permanent', 'StuffStockController@permanentDeleteAll');
$router->delete('/stuff-stock/permanent{id}', 'StuffStockController@permanentDelete');
$router->put('/stuff-stock/restore{id}', 'StuffStockController@restore');

$router->get('/stuff-stock/{id}', 'StuffStockController@show');
$router->put('stuff-stock/{id}', 'StuffStockController@update');
$router->delete('/stuff-stock/{id}', 'StuffStockController@destroy');

$router->get('/users','UserController@index');
$router->post('/users', 'UserController@store');
$router->get('/users/trash','UserController@trash');
$router->get('/users/{id}','UserController@show');
$router->patch('/users/update/{id}', 'UserController@update');
$router->delete('/users/delete/{id}','UserController@destroy');
$router->get('/users/trash/restore/{id}','UserController@restore');
$router->get('/users/trash/permanent-delete/{id}','UserController@permanentDelete');


$router->get('/Inbound','InboundStuffController@index');
$router->post('/Inbound', 'InboundStuffController@store');
$router->get('/Inbound/trash','InboundStuffController@trash');
$router->get('/Inbound/{id}','InboundStuffController@show');
$router->patch('/Inbound/update/{id}', 'InboundStuffController@update');
$router->delete('/Inbound/delete/{id}','InboundStuffController@destroy');
$router->patch('/Inbound/trash/restore/{id}','InboundStuffController@restore');
$router->delete('/Inbound/trash/permanent-delete/{id}','InboundStuffController@permanentDelete');

$router->post('/login', 'AuthController@authenticate');

$router->get('/restore/{id}','InboundStuffController@restore');
$router->delete('/permanent-delete/{id}', 'InboundStuffController@deletePermanent');


