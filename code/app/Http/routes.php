<?php

Route::get('/', function () {
	return Redirect::to('/dashboard');
});

Route::get('/home', function () {
	return Redirect::to('/dashboard');
});

Route::get('users/search', 'UsersController@search');
Route::post('notifications/markread/{id}', 'NotificationsController@markread');

Route::resource('gas', 'GasController');
Route::resource('users', 'UsersController');
Route::resource('suppliers', 'SuppliersController');
Route::resource('products', 'ProductsController');
Route::resource('categories', 'CategoriesController');
Route::resource('measures', 'MeasuresController');
Route::resource('variants', 'VariantsController');
Route::resource('orders', 'OrdersController');
Route::resource('booking.user', 'BookingUserController');
Route::resource('booking', 'BookingController');
Route::resource('notifications', 'NotificationsController');
Route::resource('movements', 'MovementsController');

Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
	'dashboard' => 'CommonsController'
]);
