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
    public function commentable()
    {
        return $this->morphTo();
    }
    public function user()
    {
        return $this->hasOne('User', 'uid', 'uid');
    }
    public function listData($where, $order = "id desc", $page = 1, $limit = 10, $start = 0, $field = '*')
    {
        $where = array_merge($where, ['parent' => 0]);
        $total = $this->where($where)->count();
        $list = $this->with('user')->withCache('user', 60)->field($field)->where($where)->order($order)->limit(($limit * ($page - 1) + $start), $limit)->select();
        /* 查询回复数量 */
        if (!$list->isEmpty()) {
            $sql = '';
            $last = end($list)[0];
            foreach ($list as $val) {
                if ($last != $val) {
                    $sql .= " union all ";
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
        return compact('list', 'total', 'page', 'limit', 'start');
    }

    /**
     * 一维数据数组生成数据树
     * @param array $data 数据列表
     * @return Array
     */
    public function getSubTree($data)
    {
        $data = $data->toArray();
        $items = [];
        foreach ($data as $v) {
            $items[$v['id']] = $v;
        }
        $tree = [];
        foreach ($items as $k => $item) {
            if (isset($items[$item['parent']])) {
                $items[$item['parent']]['child'][] = &$items[$k];
            } else {
                $tree[] = &$items[$k];
            }
        }
        return $tree;
    }
}
