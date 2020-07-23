<?php

namespace app\common\model;

use app\index\validate\Comment as CommentValidate;
use think\exception\ValidateException;

class Comment extends Base
{
    use \app\common\controller\Jump;
    /**
     * 模型事件 查询后
     */
    public static function onAfterRead($comment)
    {
        $comment->setAttr('text_format', str_replace(["\r\n", "\n", "\r"], "<br>", $comment->text));
    }

    /**
     * 关联用户表
     */
    public function user()
    {
        return $this->hasOne('User', 'uid', 'uid');
    }

    /**
     * 列出评论数据
     */
    public function listData($where, $page = 1, $parent = 0, $limit = 30, $start = 0, $order = "create_time DESC, id DESC", $field = '')
    {
        $pid = 0;
        if (isset($where['pid'])) {
            $pid = $where['pid'];
        }
        if ($parent == 0) {
            if ($field != '') {
                $field .= ",";
            }
            $post = new Post;
            $post_cache = $post->where('id', $pid)->cache('post_' . $pid)->find();
            if ($post_cache) {
                $total = $post_cache->comment_num;
            } else {
                $total = $post->where('id', $pid)->cache('post_' . $pid)->value('comment_num');
            }
            $field .= "id,uid,text,reply,create_time";
            $list = $this
                ->with('user')
                ->withCache('user', 60)
                ->field($field)
                ->where($where)
                ->where('parent', 0)
                ->order($order)
                ->limit(($limit * ($page - 1) + $start), $limit)
                ->select();
        } else {
            if ($field != '') {
                $field .= ",";
            }
            $post = new Post;
            $total = $post->where('id', $pid)->cache('post_' . $pid)->value('reply_num');
            $field .= "id,uid,text,parent,create_time";
            $list = $this
                ->with('user')
                ->withCache('user', 60)
                ->field($field)
                ->where($where)
                ->where('parent', '<>', 0)
                ->order($order)
                ->limit(($limit * ($page - 1) + $start), $limit)
                ->select();
            $list = $this->getReplyTree($list->toArray(), $parent);
        }
        return compact('list', 'total', 'page', 'limit', 'start', 'pid', 'parent');
    }

    /**
     * 构建回复评论数据树
     * @param array $data 数据列表
     * @param int $parent 父评论id
     * @return Array
     */
    public function getReplyTree($data, $parent)
    {
        $items = [];
        foreach ($data as $v) {
            $items[$v['id']] = $v;
        }
        krsort($items);
        $tree = [];
        foreach ($items as $k => $item) {
            if (isset($items[$item['parent']])) {
                $item['text_format'] = '回复@' . $items[$item['parent']]['user']['nickname'] . ":" . $items[$k]['text_format'];
                $tree[] = $item;
            } else {
                if ($item['parent'] == $parent) {
                    $tree[] = $items[$k];
                } else {
                    unset($items[$k]);
                }
            }
        }
        return $tree;
    }

    /**
     * 增加评论
     */
    public function saveData($data)
    {
        try {
            $data['text'] = htmlspecialchars(removeXSS(trim($data['text'])));
            $data['create_time'] = time();
            $data['update_time'] = time();
            /** 初始化验证类 */
            $validate = validate(CommentValidate::class);
            $validate->check($data);
            // 一些变量
            $id = $data['pid'];
            $parent = $data['parent'];
            // 增加评论
            $res = $this->save($data);
            if ($res) {
                if ($parent != 0) {
                    // 更新回复数
                    $this->where('id', $parent)->inc('reply')->update();
                    $post = new Post;
                    $post->where('id', $id)->cache('post_' . $id)->inc('reply_num')->update();
                } else {
                    // 更新评论数
                    $post = new Post;
                    $post->where('id', $id)->cache('post_' . $id)->inc('comment_num')->update();
                }
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
