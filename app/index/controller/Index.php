<?php

namespace app\index\controller;

use app\index\controller\Post;
use think\facade\View;

class Index extends Base
{
    public function index()
    {
        $page = input('param.page', 0, 'intval');
        $post = new Post;
        $data = $post->data($page, true);
        View::assign($data);
        return $this->label_fetch();
    }
}
