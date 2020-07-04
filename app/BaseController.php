<?php

declare(strict_types=1);

namespace app;

use think\facade\View;
use app\common\model\Config;

/**
 * 控制器基础类
 */
abstract class BaseController
{
    protected $config;
    public function __construct()
    {
        $config = new Config;
        $this->config = $config->getval('site');
    }
    /**
     * @param string $template
     */
    protected function label_fetch($tpl = '')
    {
        View::assign(['config' => $this->config]);
        $html = View::fetch($tpl);
        return $html;
    }
}
