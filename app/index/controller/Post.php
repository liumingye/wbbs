<?php

namespace app\index\controller;

use app\common\model\Post as PostModel;
use app\common\model\Relationships;
use app\index\controller\Comment;
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
        $post = new PostModel;
        $info = $post->with('user')->withCache(60)->cache('post_info_' . $id)->where('id', $id)->find();
        if (empty($info)) {
            return $this->error('未找到此文章');
        }
        $comment = new Comment;
        $comments = $comment->data($id, 1, 0, true);
        View::assign(compact('info', 'comments'));
        return $this->label_fetch();
    }
    /**
     * 列出内容
     */
    public function data($page, $raw = false)
    {
        if ($page < 1) {
            $page = 1;
        }
        $length = 10;
        $start = ($page - 1) * $length;
        $post = new PostModel;
        $total = $post->count();
        $list = $post->with('user')->withCache(60)->limit($start, $length)->order('create_time desc')->select();
        $data = compact('list', 'page', 'length', 'total');
        if ($raw && !input('raw')) {
            return $data;
        }
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
            'text' => removeXSS(input('post.text', '', 'htmlspecialchars')),
            'image' => removeXSS(input('post.image', '', 'htmlspecialchars')),
            'type' => '',
            'status' => 1,
            'create_time' => time(),
            'update_time' => time(),
        ];
        if (empty($data['text']) && empty($data['image'])) {
            return $this->error('内容不能为空');
        }
        try {
            /** 初始化验证类 */
            $validate = validate(PostValidate::class);
            $validate->check($data);
            $post = new PostModel;
            $data['text'] = $post->handle($data['text'], $data['image']);
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
        return $this->error('发布失败');
    }
}
