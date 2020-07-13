<?php

namespace app\index\controller;

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
    public function index()
    {
        echo '123';
    }
    /**
     * 列出内容
     */
    function list() {
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
