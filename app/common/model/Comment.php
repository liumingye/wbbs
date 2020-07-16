<?php

namespace app\common\model;

class Comment extends Base
{
    public function commentable()
    {
        return $this->morphTo();
    }
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent');
    }
    public function children()
    {
        return $this->hasMany(self::class, 'parent');
    }
    public function user()
    {
        return $this->hasOne('User', 'uid', 'uid');
    }
    public function nestable($comments, $parent = 0)
    {
        foreach ($comments as $key => $val) {
            $comments[$key]['aaa'] = 0;
            $val->aaa = 0;
            if (!$val->parent->isEmpty()) {
                $val->aa = $this->nestable($comments, $val->parent);
            }
        }
        return $comments;
    }

}
