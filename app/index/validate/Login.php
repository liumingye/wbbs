<?php

namespace app\index\validate;

use think\Validate;

class Login extends Validate
{
    protected $rule = [
        '__token__' => ['token'],
        'name' => ['require'],
        'password' => ['require'],
    ];
    protected $message = [
        '__token__.token' => 'CSRF令牌验证失败',
        'name.require' => '请输入用户名',
        'password.require' => '请输入密码',
    ];
}
