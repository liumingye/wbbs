<?php

declare(strict_types=1);

namespace app;

use think\facade\View;

/**
 * 控制器基础类
 */
abstract class BaseController
{
    /**
     * @param string $template
     */
    protected function label_fetch($tpl = '')
    {
        $html = View::fetch($tpl);
        return $html;
    }
}
