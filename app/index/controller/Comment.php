<?php

namespace app\index\controller;

use app\common\model\Comment as CommentModel;
use think\facade\View;

class Comment extends Base
{
    use \app\common\controller\Jump;
    /**
     * 列出评论
     */
    public function data()
    {
        if (!request()->isPost()) {
            return $this->error('请求错误');
        }
        $pid = input('param.pid', 0, 'intval');
        $parent = input('param.parent', 0, 'intval');
        $page = input('param.page', 1, 'intval');
        if ($pid == 0 && $parent == 0) {
            return $this->error('参数错误');
        }
        $comment = new CommentModel;
        if ($pid == 0) {
            $comments = $comment->listData([], $page, $parent);
        } else {
            $comments = $comment->listData(['pid' => $pid], $page);
        }
        View::assign(compact('comments'));
        return $this->result($this->label_fetch(), 1, '', 'json');
    }
    /**
     * 提交评论
     */
    public function add()
    {
        if (!request()->isPost()) {
            return $this->error('请求错误');
        }
        if (!$this->user) {
            return $this->error('请先登录');
        }
        $id = input('param.id', null, 'intval');
        $parent = input('param.parent', 0, 'intval');
        $data = [
            'pid' => $id,
            'uid' => $this->user->uid,
            'text' => input('param.text', ''),
            'parent' => $parent,
        ];
        $comment = new CommentModel;
        return $comment->saveData($data);
    }
}
