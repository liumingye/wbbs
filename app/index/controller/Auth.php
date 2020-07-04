<?php

namespace app\index\controller;

use think\facade\View;
use app\common\model\User;
use think\exception\ValidateException;
use app\common\validate\Login;

class Auth extends Base
{
    public function login()
    {
        $user = new User;
        /** 如果已经登录 */
        if ($user->hasLogin()) {
            /** 直接返回 */
            return redirect(url('/index'));
        }
        if (request()->isPost()) {
            /* 验证令牌 */
            $check = request()->checkToken();
            if (false === $check) {
                /** 设置提示信息 */
                $error = 'CSRF令牌验证失败';
                View::assign(compact('error'));
            } else {
                /* 开始登录 */
                $data = [
                    'name' => input('name'),
                    'password' => input('password')
                ];
                try {
                    /** 初始化验证类 */
                    $validate = validate(Login::class);
                    $validate->check($data);
                } catch (ValidateException $e) {
                    /** 设置提示信息 */
                    $error = $e->getError();
                    View::assign(compact('error'));
                }
                $remember = input('remember');
                /** 开始验证用户 **/
                $valid = $user->login($data['name'], $data['password'], false, $remember ? 30 + 24 * 3600 : 0);
                /** 比对密码 */
                if (!$valid) {
                    /** 防止穷举,休眠3秒 */
                    sleep(3);
                    /** 设置提示信息 */
                    $error = '用户名或密码无效';
                    View::assign(compact('error'));
                } else {
                    /** 跳转验证后地址 */
                    if (isset($_SERVER['HTTP_REFERER']) && NULL != $_SERVER['HTTP_REFERER']) {
                        return redirect($_SERVER['HTTP_REFERER']);
                    } else {
                        return redirect(url('/'));
                    }
                }
            }
        }
        return $this->label_fetch();
    }
    public function register()
    {
    }
}
