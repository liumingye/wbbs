<?php

namespace app\common\model;

class Comment extends Base
{
    /**
     * 模型事件 查询后
     */
    public static function onAfterRead($comment)
    {
        $comment->setAttr('text_format', "<p>" . htmlspecialchars($comment->text) . "</p>");
    }
    public function commentable()
    {
        return $this->morphTo();
    }
    public function user()
    {
        return $this->hasOne('User', 'uid', 'uid');
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
