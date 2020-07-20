<?php
// 应用公共文件

/**
 * 对字符串进行hash加密
 *
 * @access public
 * @param string $string 需要hash的字符串
 * @param string $salt 扰码
 * @return string
 */
function hashStr($string, $salt = null)
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
function hashValidate($from, $to)
{
    if ('$T$' == substr($to, 0, 3)) {
        $salt = substr($to, 3, 9);
        return hashStr($from, $salt) === $to;
    } else {
        return md5($from) === $to;
    }
}

/**
 * 压缩 HTML 代码
 *
 * @param string $html_source HTML 源码
 * @return string 压缩后的代码
 */
function compressHtml($s)
{
    $s = str_replace(array("\r\n", "\n", "\t"), array('', '', ''), $s);
    $pattern = array(
        "/> *([^ ]*) *</",
        "/[\s]+/",
        "/<!--[\\w\\W\r\\n]*?-->/",
        "/ \"/",
        "'/\*[^*]*\*/'",
    );
    $replace = array(
        ">\\1<",
        " ",
        "",
        "\"",
        "",
    );
    return preg_replace($pattern, $replace, $s);
}

/**
 * 时间戳翻译
 */
function time_tran($time)
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

/**
 * 处理XSS跨站攻击的过滤函数
 */
function removeXSS($val)
{
    // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
    // this prevents some character re-spacing such as <java\0script>
    // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
    $val = preg_replace('/([\x00-\x08]|[\x0b-\x0c]|[\x0e-\x19])/', '', $val);

    // straight replacements, the user should never need these since they're normal characters
    // this prevents like <IMG SRC=&#X40&#X61&#X76&#X61&#X73&#X63&#X72&#X69&#X70&#X74&#X3A&#X61&#X6C&#X65&#X72&#X74&#X28&#X27&#X58&#X53&#X53&#X27&#X29>
    $search = 'abcdefghijklmnopqrstuvwxyz';
    $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $search .= '1234567890!@#$%^&*()';
    $search .= '~`";:?+/={}[]-_|\'\\';

    for ($i = 0; $i < strlen($search); $i++) {
        // ;? matches the ;, which is optional
        // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars

        // &#x0040 @ search for the hex values
        $val = preg_replace('/(&#[xX]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $val); // with a ;
        // &#00064 @ 0{0,7} matches '0' zero to seven times
        $val = preg_replace('/(&#0{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $val); // with a ;
    }

    // now the only remaining whitespace attacks are \t, \n, and \r
    $ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
    $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
    $ra = array_merge($ra1, $ra2);

    $found = true; // keep replacing as long as the previous round replaced something
    while ($found == true) {
        $val_before = $val;
        for ($i = 0; $i < sizeof($ra); $i++) {
            $pattern = '/';
            for ($j = 0; $j < strlen($ra[$i]); $j++) {
                if ($j > 0) {
                    $pattern .= '(';
                    $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                    $pattern .= '|';
                    $pattern .= '|(&#0{0,8}([9|10|13]);)';
                    $pattern .= ')*';
                }
                $pattern .= $ra[$i][$j];
            }
            $pattern .= '/i';
            $replacement = substr($ra[$i], 0, 2) . '<x>' . substr($ra[$i], 2); // add in <> to nerf the tag
            $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags

            if ($val_before == $val) {
                // no replacements were made, so exit the loop
                $found = false;
            }
        }
    }
    return $val;
}
