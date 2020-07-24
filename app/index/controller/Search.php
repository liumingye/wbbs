<?php

namespace app\index\controller;

use app\common\model\Post as PostModel;
use think\facade\View;

class Search extends Base
{
    use \app\common\controller\Jump;
    public function index($q)
    {
        $page = input('param.page', 1, 'intval');
        $post = new PostModel;
        $data = $post->listData([['text', 'like', "%$q%"]], $page);
        View::assign($data);
        return $this->label_fetch();
    }
}
