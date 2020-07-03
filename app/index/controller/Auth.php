<?php

namespace app\index\controller;

use think\facade\View;

class Auth extends Base
{
    public function login()
    {
        $title = "登录";
        View::assign(compact('title'));
        return $this->label_fetch();
    }
    public function register()
    {
    }
}
