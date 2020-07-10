<?php

namespace app\index\controller;

class Search extends Base
{
    public function index()
    {
        $keyword = input('get.q');
        echo '搜索' . $keyword;
    }
}
