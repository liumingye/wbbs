<?php

namespace app\index\validate;

use app\common\model\Comment as CommentModel;
use app\common\model\Post as PostModel;
use app\common\model\User as UserModel;
use think\Validate;

class Comment extends Validate
{
    protected $rule = [
        'uid' => ['require', 'isBan'],
        'pid' => ['require', 'hasPost'],
        'parent' => ['hasParent'],
        'text' => ['require', 'max' => 140],
    ];
    protected $message = [
        'uid.require' => '请先登录',
        'uid.isBan' => '您已被封禁',
        'pid.require' => '回复文章不存在',
        'pid.hasPost' => '回复文章不存在',
        'parent.hasParent' => '回复评论不存在',
        'text.require' => '回复内容不能为空',
        'text.max' => '最多只能输入140个文字',
    ];
    /**
     * 文章是否存在
     */
    public function hasPost($value)
    {
        $post = PostModel::where('id', $value)->find();
        if ($post->status == 1) {
            return true;
        }
        return false;
    }
    /**
     * 回复ID是否存在
     */
    public function hasParent($value, $rule, $data)
    {
        if ($value == 0) {
            return true;
        }
        $comment = CommentModel::where(['id' => $value, 'pid' => $data['pid']])->find();
        if ($comment) {
            return true;
        }
        return false;
    }
    /**
     * 判断用户名称是否封禁
     */
    public function isBan($value)
    {
        $user = UserModel::where('uid', $value)->find();
        if ($user->status == 1) {
            return true;
        }
        return false;
    }
}
