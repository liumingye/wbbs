<?php

namespace app\index\validate;

use app\common\model\User;
use think\Validate;

class Post extends Validate
{
    protected $rule = [
        'uid' => ['require', 'isBan'],
        'text' => ['max' => 255],
    ];
    protected $message = [
        'uid.require' => '请先登录',
        'uid.isBan' => '您已被封禁',
        'text.require' => '最多只能输入255个文字',
    ];
    /**
     * 判断用户名称是否封禁
     */
    public function isBan($value)
    {
        $user = User::where('uid', $value)->find();
        if ($user->status == 0) {
            return false;
        }
        return true;
    }
}
