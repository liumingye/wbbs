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
        $pid = input('param.pid', 0);
        $parent = input('param.parent', 0);
        $page = input('param.page', 1);
        if ($pid <= 0) {
            return $this->error('参数错误');
        }
        $comment = new CommentModel;
        if ($parent != 0) {
            $comments = $comment->listData(['pid' => $pid], $page, $parent);
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
        $id = input('param.id', null);
        $parent = input('param.parent', 0);
        $data = [
            'pid' => $id,
            'uid' => $this->user->uid,
            'text' => input('param.text', ''),
            'parent' => $parent,
        ];
        $comment = new CommentModel;
        $res = $comment->saveData($data);
        if ($res['code'] == 1) {
            return $this->success($res['msg']);
        } else {
            return $this->error($res['msg']);
        }
    }
}
