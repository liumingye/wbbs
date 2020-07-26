<?php

namespace app\index\controller;

use app\common\model\Comment as CommentModel;
use app\common\model\Post as PostModel;
use think\facade\View;

class Post extends Base
{
    use \app\common\controller\Jump;
    /**
     * 内容详细
     */
    public function info()
    {
        $id = input('param.id', 0);
        if ($id <= 0) {
            return $this->error('未找到此文章');
        }
        $post = new PostModel;
        $info = $post
            ->with('user')
            ->withCache(60)
            ->cache('post_' . $id)
            ->where('id', $id)->find();
        if (empty($info)) {
            return $this->error('未找到此文章');
        }
        $comment = new CommentModel;
        $comments = $comment->listData(['pid' => $id], 1);
        View::assign(compact('info', 'comments'));
        return $this->label_fetch();
    }

    /**
     * 列出内容
     */
    public function data($page)
    {
        $page = input('param.page', 1);
        $post = new PostModel;
        $data = $post->listData([], $page);
        View::assign($data);
        return $this->result($this->label_fetch(), 1, '', 'json');
    }

    /**
     * 发布内容
     */
    public function add()
    {
        if (!request()->isPost()) {
            return $this->error('请求错误');
        }
        if (!$this->user) {
            return $this->error('请先登录');
        }
        $data = [
            'uid' => $this->user->uid,
            'text' => input('post.text', ''),
            'image' => input('post.image', ''),
            'type' => '',
            'status' => 1,
            'create_time' => time(),
            'update_time' => time(),
        ];
        $post = new PostModel;
        $res = $post->saveData($data);
        if ($res['code'] == 1) {
            return $this->success($res['msg']);
        } else {
            return $this->error($res['msg']);
        }
    }

    /**
     * 删除文章
     */
    public function del()
    {
        if (!request()->isPost()) {
            return $this->error('请求错误');
        }
        if (!$this->user) {
            return $this->error('请先登录');
        }
        $data = [
            'id' => input('id', 0),
            'uid' => $this->user->uid
        ];
        $post = new PostModel;
        $res = $post->delData($data);
        if ($res['code'] == 1) {
            return $this->success($res['msg']);
        } else {
            return $this->error($res['msg']);
        }
    }

    /**
     * 点赞功能
     */
    public function upvote()
    {

    }
}
