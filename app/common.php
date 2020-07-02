<?php
// 应用公共文件

/**
 * 按json方式输出通信数据
 * @param array $data 数据
 * @param integer $code 状态码
 * @param string $msg 提示信息
 * @param string $headerCode 状态码
 * @return string
 */
function json_return($data = [], $code = 1, $msg = '', $other = [])
{
    return json(array_merge(['code' => $code, 'data' => $data, 'msg' => $msg], $other));
}
