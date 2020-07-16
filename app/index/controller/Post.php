<?php

namespace app\index\controller;

use app\common\model\Comment;
use app\common\model\Post as PostModel;
use app\common\model\Relationships;
use app\index\validate\Post as PostValidate;
use think\exception\ValidateException;
use think\facade\View;

class Post extends Base
{
    use \app\common\controller\Jump;
    /**
     * 内容详细
     */
    public function info()
    {
        $id = input('param.id', null, 'intval');
        if ($id <= 0) {
            return redirect(url('/', ['page' => input('param.page', 1)]));
        }
        $post = new PostModel;
        $info = $post->with(['user', 'comment.user' => function ($query) {
            $query->order('create_time desc')->limit(0, 10);
        }])->withCache(30)->cache('post_info_' . $id)->where('id', $id)->find();

        $comment = new Comment;
        $comments = $comment->getSubTree($info->comment);

        if (empty($info)) {
            return $this->error('未找到此文章');
        }
        View::assign(compact('info', 'comments'));
        return $this->label_fetch();
    }
    /**
     * 列出评论 & 提交评论
     */
    public function comment()
    {
        $id = input('param.id', null, 'intval');
        $comment = new Comment;
        $comments = $comment->where('pid', $id)->select();
        $comments = $comment->getSubTree($comments);
        View::assign(compact('comments'));
        return $this->label_fetch();
    }
    /**
     * 列出内容
     */
    function list() {
        if (!request()->isPost()) {
            return redirect(url('/', ['page' => input('param.page', 1)]));
        }
        $page = input('param.page', 0, 'intval');
        if ($page < 1) {
            $page = 1;
        }
        $length = 10;
        $start = ($page - 1) * $length;
        $post = new PostModel;
        $total = $post->count();
        $list = $post->with('user')->withCache(60)->limit($start, $length)->order('create_time desc')->select();
        View::assign(compact('list', 'page', 'length', 'total'));
        return $this->label_fetch();
    }
    /**
     * 发布内容
     */
    public function add()
    {
        if (!request()->isPost()) {
            return $this->error('请求错误');
        }
        if ($this->user) {
            $data = [
                'uid' => $this->user->uid,
                'text' => input('post.text', '', 'htmlspecialchars'),
                'type' => '',
                'status' => 1,
                'create_time' => time(),
                'update_time' => time(),
            ];
            try {
                /** 初始化验证类 */
                $validate = validate(PostValidate::class);
                $validate->check($data);
                $post = new PostModel;
                $data['text'] = $post->handle($data['text'], input('post.image', '', 'htmlspecialchars'));

                /** 发布 */
                $res = $post->save($data);
                if ($res) {
                    // 文章 关联 话题
                    if (!empty($topicid)) {
                        $relationships = new Relationships;
                        $data = [];
                        foreach ($topicid as $tid) {
                            $data[] = [
                                'pid' => $post->id,
                                'tid' => $tid,
                            ];
                        }
                        $relationships->saveAll($data);
                    }
                    return $this->success('发布成功');
                } else {
                    return $this->error('发布失败');
                }
            } catch (ValidateException $e) {
                /** 设置提示信息 */
                return $this->error($e->getError());
            }
        } else {
            return $this->error('请先登录');
        }
    }

}
