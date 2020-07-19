<?php

namespace app\common\model;

use think\facade\Db;

class Comment extends Base
{
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
    public function listData($where, $page = 1, $parent = 0, $limit = 30, $start = 0, $order = "create_time DESC, id DESC", $field = '*')
    {
        if ($parent == 0) {
            $total = $this->where($where)->where('parent', 0)->count();
            $list = $this->with('user')->withCache('user', 60)->field($field)->where($where)->where('parent', 0)->order($order)->limit(($limit * ($page - 1) + $start), $limit)->select();
            /* 查询回复数量 */
            if (!$list->isEmpty()) {
                $sql = '';
                $last = end($list)[0];
                foreach ($list as $val) {
                    if ($last != $val) {
                        $sql .= " UNION ALL ";
                    }
                    $sql .= $this->where(['parent' => $val->id])->fetchSql(true)->count();

                }
                if ($sql != '') {
                    $count = Db::query($sql);
                    foreach ($list as $key => $val) {
                        if (isset($count[$key]['think_count'])) {
                            $list[$key]['parent_count'] = $count[$key]['think_count'];
                        }
                    }
                }
            }
        } else {
            $total = $this->where($where)->where('parent', '<>', 0)->count();
            $list = $this->with('user')->withCache('user', 60)->field($field)->where($where)->where('parent', '<>', 0)->order($order)->limit(($limit * ($page - 1) + $start), $limit)->select();
            $list = $this->getSubTree($list->toArray(), $parent);
        }
        $pid = 0;
        if (isset($where['pid'])) {
            $pid = $where['pid'];
        }
        return compact('list', 'total', 'page', 'limit', 'start', 'pid', 'parent');
    }

    /**
     * 一维数据数组生成数据树
     * @param array $data 数据列表
     * @return Array
     */
    public function getSubTree($data, $parent)
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
}
