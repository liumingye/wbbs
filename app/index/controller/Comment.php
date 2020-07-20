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
    public function data($pid = 0, $page = 1, $parent = 0, $raw = false)
    {
        if ($pid == 0 && $parent == 0) {
            return $this->error('参数错误');
        }
        $comment = new CommentModel;
        if ($pid == 0) {
            $comments = $comment->listData([], $page, $parent);
        } else {
            $comments = $comment->listData(['pid' => $pid], $page);
        }
        if ($raw && !input('raw')) {
            return $comments;
        }
        View::assign(compact('comments'));
        return $this->result($this->label_fetch(), 1, '', 'json');
    }
    /**
     * 提交评论
     */
    public function add()
    {
        if (!$this->user) {
            return $this->error('请先登录');
        }
        $id = input('param.id', null, 'intval');
        $parent = input('post.parent', 0, 'intval');
        $data = [
            'pid' => $id,
            'uid' => $this->user->uid,
            'text' => input('post.text', ''),
            'parent' => $parent,
        ];
        $comment = new CommentModel;
        return $comment->saveData($data);
    }
}
