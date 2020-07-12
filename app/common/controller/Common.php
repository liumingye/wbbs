<?php

namespace app\common\controller;

class Common
{
    /**
     * 对字符串进行hash加密
     *
     * @access public
     * @param string $string 需要hash的字符串
     * @param string $salt 扰码
     * @return string
     */
    public static function hashStr($string, $salt = null)
    {
        /** 生成随机字符串 */
        $salt = empty($salt) ? \think\helper\Str::random(9) : $salt;
        $length = strlen($string);
        $hash = '';
        $last = ord($string[$length - 1]);
        $pos = 0;
        /** 判断扰码长度 */
        if (strlen($salt) != 9) {
            /** 如果不是9直接返回 */
            return;
        }
        while ($pos < $length) {
            $asc = ord($string[$pos]);
            $last = ($last * ord($salt[($last % $asc) % 9]) + $asc) % 95 + 32;
            $hash .= chr($last);
            $pos++;
        }
        return '$T$' . $salt . md5($hash);
    }

    /**
     * 判断hash值是否相等
     *
     * @access public
     * @param string $from 源字符串
     * @param string $to 目标字符串
     * @return boolean
     */
    public static function hashValidate($from, $to)
    {
        if ('$T$' == substr($to, 0, 3)) {
            $salt = substr($to, 3, 9);
            return self::hashStr($from, $salt) === $to;
        } else {
            return md5($from) === $to;
        }
    }

    /**
     * 时间戳翻译
     */
    public static function time_tran($time)
    {
        $time = strtotime($time);
        if (date("Y", $time) == date("Y", time())) {
            $rtime = date("m月d日 H:i", $time);
        } else {
            $rtime = date("y-m-d H:i", $time);
        }
        $htime = date("H:i", $time);
        $time = time() - $time;
        if ($time < 60) {
            $str = '刚刚';
        } elseif ($time < 60 * 60) {
            $min = floor($time / 60);
            $str = $min . '分钟前';
        } elseif ($time < 60 * 60 * 24) {
            $h = floor($time / (60 * 60));
            $str = $h . '小时前 ' . $htime;
        } elseif ($time < 60 * 60 * 24 * 3) {
            $d = floor($time / (60 * 60 * 24));
            if ($d == 1) {
                $str = '昨天 ' . $htime;
            } else {
                $str = '前天 ' . $htime;
            }
        } else {
            $str = $rtime;
        }
        return $str;
    }

}
