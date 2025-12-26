<?php
use think\facade\Route;
// 获取当前计数
Route::get('/api/count', 'index/getCount');

// 更新计数，自增或者清零
Route::post('/api/count', 'index/updateCount');
// 用户相关路由
Route::post('user/login', 'user/login');
Route::get('user/info', 'user/info');
Route::get('user/list', 'user/list');
Route::post('user/create', 'user/create');
Route::post('user/update/:id', 'user/update');
Route::post('user/delete/:id', 'user/delete');

// 店铺相关路由
Route::get('shop/list', 'shop/list');
Route::post('shop/create', 'shop/create');
Route::post('shop/update/:id', 'shop/update');
Route::post('shop/delete/:id', 'shop/delete');
Route::get('shop/detail/:id', 'shop/detail');

// WIFI相关路由
Route::get('wifi/list', 'wifi/list');
Route::post('wifi/create', 'wifi/create');
Route::post('wifi/update/:id', 'wifi/update');
Route::post('wifi/delete/:id', 'wifi/delete');
Route::get('wifi/detail/:id', 'wifi/detail');
Route::get('wifi/connect/:id', 'wifi/connect');
Route::get('wifi/qrcode/:id', 'wifi/qrcode');

// 小程序扫码连接路由

Route::get('connect/:id', 'wifi/connect');
