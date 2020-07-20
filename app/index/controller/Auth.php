<?php

namespace app\index\controller;

use app\common\model\User;
use app\index\validate\Login;
use app\index\validate\Register;
use think\exception\ValidateException;
use think\facade\View;

class Auth extends Base
{
    use \app\common\controller\Jump;
    public function login()
    {
        $user = new User;
        /** 如果已经登录 */
        if ($user->hasLogin()) {
            /** 直接返回 */
            return redirect(url('/'));
        }
        if (request()->isPost()) {
            /* 验证数据 */
            $data = [
                '__token__' => input('__token__'),
                'name' => input('name'),
                'password' => input('password'),
            ];
            try {
                /** 初始化验证类 */
                $validate = validate(Login::class);
                $validate->check($data);
                /** 开始验证用户 **/
                $remember = input('remember');
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
                    if (isset($_SERVER['HTTP_REFERER']) && null != $_SERVER['HTTP_REFERER']) {
                        return $this->success('登录成功', $_SERVER['HTTP_REFERER']);
                    } else {
                        return $this->success('登录成功', url('/'));
                    }
                }
            } catch (ValidateException $e) {
                /** 设置提示信息 */
                $error = $e->getError();
                View::assign(compact('error'));
            }
        }
        return $this->label_fetch();
    }
    public function register()
    {
        $user = new User;
        /** 如果已经登录 */
        if ($user->hasLogin()) {
            /** 直接返回 */
            return redirect(url('/'));
        }
        if (request()->isPost()) {
            /* 验证数据 */
            $data = [
                '__token__' => input('__token__'),
                'name' => input('name'),
                'mail' => input('mail'),
                'password' => input('password'),
                'confirm' => input('confirm'),
                'captcha' => input('captcha'),
            ];
            try {
                /** 初始化验证类 */
                $validate = validate(Register::class);
                $validate->check($data);
                /** 开始注册用户 **/
                $hasher = new \app\common\util\PasswordHash(8, true);
                $generatedPassword = \think\helper\Str::random(7);
                $user = new User;
                $user->name = $data['name'];
                $user->mail = $data['mail'];
                $user->nickname = $data['name'];
                $user->password = $hasher->HashPassword($generatedPassword);
                $user->create_ip = ip2long(request()->ip());
                $user->save();
                $user->login($user->name, $generatedPassword);
                /** 跳转注册后地址 */
                if (isset($_SERVER['HTTP_REFERER']) && null != $_SERVER['HTTP_REFERER']) {
                    return $this->success('注册成功', $_SERVER['HTTP_REFERER']);
                } else {
                    return $this->success('注册成功', url('/'));
                }
            } catch (ValidateException $e) {
                /** 设置提示信息 */
                $error = $e->getError();
                View::assign(compact('error'));
            }
        }
        return $this->label_fetch();
    }
}
