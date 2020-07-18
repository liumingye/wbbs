<?php

namespace app\common\model;

class User extends Base
{
    // 设置主键
    protected $pk = 'uid';

    /**
     * 用户
     *
     * @access private
     * @var object
     */
    private $_user;

    /**
     * 是否已经登录
     *
     * @access private
     * @var boolean
     */
    private $_hasLogin = null;

    /**
     * 模型事件 查询后
     */
    public static function onAfterRead($user)
    {
        if ($user->avatar) {
            $uid = $user->uid;
            $dir = substr(sprintf("%09d", $uid), 0, 3);
            $avatar = url("/upload/avatar/$dir/$uid.png?" . $user->avatar);
        } elseif ($user->mail) {
            $avatar = 'https://dn-qiniu-avatar.qbox.me/avatar/' . md5($user->mail);
        } else {
            $avatar = url("/static/img/avatar.png");
        }
        $avatar && $user->setAttr('avatar_url', $avatar);
    }
    /**
     * 以用户名和密码登录 [参考 typecho]
     *
     * @access public
     * @param string $name 用户名
     * @param string $password 密码
     * @param boolean $temporarily 是否为临时登录
     * @param integer $expire 过期时间
     * @return boolean
     */
    public function login($name, $password, $temporarily = false, $expire = 0)
    {
        /** 开始验证用户 **/
        $user = $this->where((strpos($name, '@') ? 'mail' : 'name'), $name)->find();
        if (empty($user)) {
            return false;
        }
        $hasher = new \app\common\util\PasswordHash(8, true);
        $hashValidate = $hasher->CheckPassword($password, $user['password']);
        if ($user && $hashValidate) {
            if (!$temporarily) {
                $token = function_exists('openssl_random_pseudo_bytes') ? bin2hex(openssl_random_pseudo_bytes(16)) : sha1(\think\helper\Str::random(20));
                $user['token'] = $token;
                cookie('wbbs_uid', $user['uid'], $expire);
                cookie('wbbs_token', hashStr($token), $expire);
                //更新最后登录时间以及验证码
                $user = $this->find($user['uid']);
                $user->token = $token;
                $user->login_ip = ip2long(request()->ip());
                $user->login_time = time();
                $user->save();
            }
            /** 压入数据 */
            $this->_user = $user;
            $this->_hasLogin = true;
            return true;
        }
        return false;
    }
    /**
     * 判断用户是否已经登录 [参考 typecho]
     *
     * @access public
     * @return boolean
     */
    public function hasLogin()
    {
        if (null !== $this->_hasLogin) {
            return $this->_hasLogin;
        } else {
            $cookieUid = cookie('wbbs_uid');
            if (null !== $cookieUid) {
                /** 验证登陆 */
                $user = $this->cache(true)->find($cookieUid);
                $token = cookie('wbbs_token');
                if ($user && hashValidate($user['token'], $token)) {
                    $this->_user = $user;
                    return ($this->_hasLogin = true);
                }
                $this->logout();
            }
            return ($this->_hasLogin = false);
        }
    }

    /**
     * 用户登出函数 [参考 typecho]
     *
     * @access public
     * @return void
     */
    public function logout()
    {
        cookie('wbbs_uid', null);
        cookie('wbbs_token', null);
    }
    /**
     * 获取用户信息
     *
     * @access public
     * @return object
     */
    public function getUser()
    {
        return $this->_user;
    }
}
