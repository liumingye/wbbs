<?php

namespace app\index\validate;

use app\common\model\User;
use think\exception\ValidateException;
use think\Validate;

class Post extends Validate
{
    protected $rule = [
        'uid' => ['require', 'isBan'],
        'text' => ['require', 'max' => 255],
        'image' => ['maxImage'],
    ];
    protected $message = [
        'uid.require' => '请先登录',
        'uid.isBan' => '您已被封禁',
        'text.require' => '发布内容不能为空',
        'text.max' => '最多只能输入255个文字',
    ];
    /**
     * 验证上传图片个数
     */
    public function maxImage($value)
    {
        try {
            if (!empty($value)) {
                $images = explode("|", $value);
                function in_way($images)
                {
                    $arr = explode(":", $images);
                    if (count($arr) == 2) {
                        list($type, $id) = $arr;
                    }
                    if (!isset($type) || !isset($id) || !in_array($type, config('wbbs.upload.way')) || mb_strwidth($id) > 50) {
                        return false;
                    }
                    return true;
                }
                if (!empty($images)) {
                    if (count($images) > 9) {
                        return '最多只能上传9张图片';
                    }
                    // 多个图片验证
                    foreach ($images as $image) {
                        $res = in_way($image);
                        if (!$res) {
                            return '上传图片数据错误';
                        }
                    }
                } else {
                    // 单个图片验证
                    $res = in_way($images);
                    if (!$res) {
                        return '上传图片数据错误';
                    }
                }
            }
            return true;
        } catch (ValidateException $e) {
            return '上传图片数据错误';
        }
    }
    /**
     * 判断用户名称是否封禁
     */
    public function isBan($value)
    {
        $user = User::where('uid', $value)->find();
        if ($user->status == 1) {
            return true;
        }
        return false;
    }
}
