<?php
use think\facade\Route;

Route::group('api/user', function () {
    Route::get('/', 'UserController/index');
    Route::get('/:id', 'UserController/read');
    Route::post('/', 'UserController/save');
    Route::put('/:id', 'UserController/update');
    Route::delete('/:id', 'UserController/delete');
    Route::post('/status', 'UserController/updateStatus');
    Route::post('/role', 'UserController/updateRole');
    Route::get('/openid/:openid', 'UserController/getByOpenid');
    Route::get('/children/:parent_id', 'UserController/getChildren');
    Route::get('/shop/:shop_id', 'UserController/getByShop');
    Route::get('/role/list', 'UserController/getRoleList');
});
