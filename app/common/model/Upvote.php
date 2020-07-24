<?php

namespace app\common\model;

use think\exception\ValidateException;

class Upvote extends Base
{
    public function saveData($data)
    {
        try {
            /** 初始化验证类 */
            // $validate = validate(CommentValidate::class);
            // $validate->check($data);
        }catch (ValidateException $e) {

        }
    }
    public function delData($data)
    {

    }
}
