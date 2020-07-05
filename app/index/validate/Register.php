<?php

namespace app\index\validate;

use app\common\model\User;
use think\Validate;

class Register extends Validate
{
    protected $rule = [
        'name' => ['require', 'nameUnique', 'min' => 2, 'max' => 15, 'chsDash'],
        'mail' => ['require', 'mailUnique', 'email', 'max' => 64],
        'password' => ['require', 'min' => 6, 'max' => 20],
        'confirm' => ['require', 'confirm' => 'password'],
        'captcha' => ['require', 'captcha'],
    ];
    protected $message = [
        'name.require' => '必须填写用户名称',
        'name.nameUnique' => '用户名已经存在',
        'name.min' => '用户名至少包含2个字符',
        'name.max' => '用户名最多包含15个字符',
        'name.chsDash' => '请不要在用户名中使用特殊字符',
        'mail.require' => '必须填写电子邮箱',
        'mail.mailUnique' => '电子邮箱地址已经存在',
        'mail.email' => '电子邮箱格式错误',
        'mail.max' => '电子邮箱最多包含64个字符',
        'password.require' => '必须填写密码',
        'password.min' => '为了保证账户安全, 请输入至少六位的密码',
        'password.max' => '为了便于记忆, 密码长度请不要超过二十位',
        'confirm.require' => '两次输入的密码不一致',
        'confirm.confirm' => '两次输入的密码不一致',
        'captcha.require' => '必须填写验证码',
        'captcha.captcha' => '验证码填写错误',
    ];
    /**
     * 判断用户名称是否存在
     */
    public function nameUnique($value)
    {
        $user = User::where('name', $value)->find();
        if ($user) {
            return false;
        }
        return true;
    }
    /**
     * 判断电子邮件是否存在
     */
    public function mailUnique($value)
    {
        $user = User::where('mail', $value)->find();
        if ($user) {
            return false;
        }
        return true;
    }
}
