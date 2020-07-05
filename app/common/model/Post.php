<?php

namespace app\common\model;

class Post extends Base
{
    public function user()
    {
        return $this->hasOne('User', 'uid', 'uid');
    }
}
