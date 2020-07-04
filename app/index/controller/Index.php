<?php

namespace app\index\controller;

use app\common\model\Post;
use think\facade\View;

class Index extends Base
{
    public function index()
    {
        $description = $this->config['description'];
        $keywords = $this->config['keywords'];
        $page = input('param.page', 0, 'intval');
        if ($page < 1) {
            $page = 1;
        }
        $length = 10;
        $start = ($page - 1) * $length;
        $post = new Post;
        $data = $post->listData([], 'p.create_time desc', $start, $length, ['user'], 'p.uid,p.id,p.text,u.nickname,u.avatar,u.mail');

        $list = $data['list'];
        View::assign(compact('description', 'keywords', 'list'));
        return $this->label_fetch();
    }
}
