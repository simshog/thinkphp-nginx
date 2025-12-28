<?php
use think\facade\Route;

Route::group('api', function () {
    include __DIR__ . '/user.php';
});
