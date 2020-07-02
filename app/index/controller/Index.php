<?php

namespace app\index\controller;

use app\common\model\Post;
use think\facade\View;

class Index extends Base
{
    public function index()
    {
        $title = "轻社区";
        $description = "轻社区";
        $keywords = "轻社区";
        $start = input('param.start', 0);
        $length = min(input('param.length', 10), 10);
        $db = new Post;
        $data = $db->listData([], null, $start, $length, ['user'], 'p.text,u.username');
        $list = $data['list'];
        View::assign(compact('title', 'description', 'keywords', 'list'));
        return $this->label_fetch();
    }
}
