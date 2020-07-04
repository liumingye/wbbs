<?php

namespace app\common\model;

class Config extends Base
{
    // 设置主键
    protected $pk = 'cname';
    // 设置数据表（不含前缀）
    protected $name = 'config';

    /**
     * 缓存的插件配置
     *
     * @access private
     * @var array
     */
    private $_cacheConfig = NULL;

    /**
     * 数据转换
     */
    protected function __convertData($configItem)
    {
        if (!empty($configItem)) {
            switch ($configItem['ctype']) {
                case 1:
                    $configItem['data'] = intval($configItem['data']);
                    break;
                case 2:
                    $configItem['data'] = unserialize($configItem['data']);
                    break;
            }
        }
        return $configItem;
    }

    /**
     * 获取数据
     * @param string $cname 名称
     * @param string $key 数据键名
     * @return mixed
     */
    public function getval($cname, $key = 'data')
    {
        // 是否有缓存
        if (!isset($this->_cacheConfig[$cname])) {
            $item = $this->where(['cname' =>  $cname])->find();
            if (!empty($item)) {
                $item = $item->toArray();
                $item = $this->__convertData($item);
                $this->_cacheConfig[$cname] = $item;
            } else {
                return false;
            }
        } else {
            $item = $this->_cacheConfig[$cname];
        }
        return $key ? $item[$key] : $item;
    }

    /**
     * 设置数据
     * @param string $cname 名称
     * @param string $value 数据
     */
    public function setval($cname, $value)
    {
        $data = ['cname' =>  $cname, 'ctype' => 0, 'dateline' => time()];
        if (is_array($value)) {
            $data['ctype'] = 2;
            $data['data'] = serialize(array_merge($this->getval($cname), $value));
        } elseif (is_integer($value)) {
            $data['ctype'] = 1;
            $data['data'] = intval($value);
        } else {
            $data['data'] = $value;
        }
        $user = new Config;
        $res = $user->replace()->save($data);
        if ($res) {
            // 设置新的缓存
            $this->_cacheConfig[$cname] = null;
        }
        return $res;
    }

    /**
     * 删除数据数组键值
     * @param string $cname 名称
     * @param string $key 数据
     */
    public function delval($cname, $key)
    {
        $config = $this->getval($cname);
        if (is_array($config)) {
            unset($config[$key]);
            $data['data'] = serialize($config);
            $data['cname'] = $cname;
            $data['ctype'] = 2;
            $data['dateline'] = time();
            // 设置新的缓存
            $this->_cacheConfig[$cname] = $data;
            return $this->replace()->save($data);
        }
        return false;
    }

    /**
     * 删除配置
     * @param string $cname 名称
     * @param string $key 数据
     */
    public function del($cname)
    {
        // 更新缓存
        unset($this->_cacheConfig[$cname]);
        return $this->delete($cname);
    }
}
