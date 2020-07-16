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
    public function getSubTree($data, $pid = 0)
    {
        $tmp = array();
        foreach ($data as $value) {
            if ($value['parent'] == $pid) {
                $value['child'] = $this->getSubTree($data, $value['id']);
                $tmp[] = $value;
            }
        }
        return $tmp;
    }

}
