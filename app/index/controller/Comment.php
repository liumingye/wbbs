<?php

namespace app\index\controller;

use app\common\model\Comment as CommentModel;
use think\facade\View;
use think\exception\ValidateException;
use app\index\validate\Comment as CommentValidate;

class Comment extends Base
{
    use \app\common\controller\Jump;
    /**
     * 列出评论
     */
    public function data($pid, $page = 1, $parent = 0, $raw = false)
    {
        $comment = new CommentModel;
        $comments = $comment->listData(['pid' => $pid], $page, $parent);
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
        $comment = new CommentModel;
        $data = [
            'pid' => $id,
            'uid' => $this->user->uid,
            'text' => removeXSS(input('post.text', '', 'htmlspecialchars')),
            'parent' => $parent,
            'create_time' => time(),
            'update_time' => time(),
        ];
        try {
            /** 初始化验证类 */
            $validate = validate(CommentValidate::class);
            $validate->check($data);
            $comment = new CommentModel;
            $res = $comment->save($data);
            if ($res) {
                return $this->success('评论成功');
            } else {
                return $this->error('评论失败');
            }
        } catch (ValidateException $e) {
            /** 设置提示信息 */
            return $this->error($e->getError());
        }
        return $this->error('评论失败');
    }
}
