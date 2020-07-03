<?php

namespace app\index\controller;

use app\common\model\Post;
use think\facade\View;

class Index extends Base
{
    public function index()
    {
        $title = "Wbbs轻社区";
        $description = "Wbbs轻社区";
        $keywords = "Wbbs轻社区";
        $page = input('param.page', 0, 'intval');
        if ($page < 1) {
            $page = 1;
        }
        $length = 10;
        $start = ($page - 1) * $length;
        $db = new Post;
        $data = $db->listData([], 'p.create_time desc', $start, $length, ['user'], 'p.text,u.username');
        $list = $data['list'];
        View::assign(compact('title', 'description', 'keywords', 'list'));
        return $this->label_fetch();
    }
}
