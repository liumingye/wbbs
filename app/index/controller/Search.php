<?php

namespace app\index\controller;

use app\common\model\Post as PostModel;
use think\facade\View;

class Search extends Base
{
    use \app\common\controller\Jump;
    public function index()
    {
        $wd = input('param.wd', null);
        $page = input('param.page', 1);
        $post = new PostModel;
        $data = $post->listData([['text', 'like', "%$wd%"]], $page);
        View::assign($data);
        return $this->label_fetch();
    }
}
