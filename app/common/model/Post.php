<?php

namespace app\common\model;

use app\common\model\User;

class Post extends Base
{
    // 设置数据表（不含前缀）
    protected $name = 'post';
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;

    public function user()
    {
        return $this->hasOne('User', 'uid', 'uid');
    }
}
