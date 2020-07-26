<?php

use think\facade\Route;

Route::rule('p/:id', 'Post/info');
Route::rule('s/:wd', 'Search/index');
