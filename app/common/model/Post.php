<?php

namespace app\common\model;

use app\common\model\User;

class Post extends Base
{
    // 设置数据表（不含前缀）
    protected $name = 'post';
    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
    /**
     * 计数数据
     */
    public function countData($where)
    {
        $total = $this->where($where)->count();
        return $total;
    }
    /**
     * 列出数据
     */
    public function listData($where, $order = "create_time desc", $start = 0, $length = 10, $with = [], $field = '*')
    {
        if (!is_array($where)) {
            $where = json_decode($where, true);
        }
        $total = $this->countData($where);
        $res = $this->where($where);
        if (array_key_exists('user', $with) || in_array('user', $with)) {
            $res = $res->buildSql();
            $where = isset($with['user']['where']) ? $with['user']['where'] : '';
            $res = User::where($where)->alias('u')->rightjoin([$res => 'p'], 'u.uid = p.uid');
        }
        $list = $res->field($field)->order($order)->limit($start, $length)->select();
        return ['code' => 1, 'msg' => '数据列表', 'total' => $total, 'list' => $list];
    }
}
