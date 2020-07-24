<?php

namespace app\index\controller;

use app\common\model\Post;
use think\facade\View;

class Index extends Base
{
    public function index()
    {
        $post = new Post;
        $data = $post->listData([], 1);
        View::assign($data);
        return $this->label_fetch();
    }
}
