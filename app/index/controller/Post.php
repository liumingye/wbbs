<?php

namespace app\index\controller;

use app\common\model\Post as PostModel;
use app\index\validate\Post as PostValidate;
use think\exception\ValidateException;

class Post extends Base
{
    use \app\common\controller\Jump;
    /**
     * 发布文章
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
                'image' => input('post.image', '', 'htmlspecialchars'),
                'status' => 1,
                'create_time' => time(),
                'update_time' => time(),
            ];
            try {
                /** 初始化验证类 */
                $validate = validate(PostValidate::class);
                $validate->check($data);
                $post = new PostModel;
                // 图片处理
                if (isset($data['image']) && $data['image']) {
                    $date_image = explode("|", $data['image']);
                    unset($data['image']);
                    if (!empty($date_image)) {
                        $image = [];
                        foreach ($date_image as $img) {
                            list($type, $id) = explode(":", $img);
                            if (isset($type) && in_array($type, ['local', 'Alibaba']) && isset($id)) {
                                $image[] = [
                                    'id' => $id,
                                    'type' => $type,
                                ];
                            }
                        }
                        $data['image'] = $image;
                    } else {
                        $data['image'] = '';
                    }
                }
                $res = $post->save($data);
                if ($res) {
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
