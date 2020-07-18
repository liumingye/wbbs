<?php

declare (strict_types = 1);

namespace app;

use app\common\model\Config;
use app\common\model\User;
use think\facade\View;

/**
 * 控制器基础类
 */
abstract class BaseController
{
    /**
     * 网站配置
     */
    protected $config;
    /**
     * 用户信息
     */
    protected $user;
    /**
     * 初始化网站配置和用户信息
     */
    public function __construct()
    {
        $config = new Config;
        $this->config = $config->getval('site');
        $user = new User;
        if ($user->hasLogin()) {
            $this->user = $user->getUser();
        }
    }
    /**
     * 解析和获取模板内容 用于输出
     * @param string $template
     */
    protected function label_fetch($tpl = '')
    {
        View::assign(['user' => $this->user]);
        View::assign(['config' => $this->config]);
        $html = compressHtml(View::fetch($tpl));
        return $html;
    }
}
