<?php
namespace think\cache\driver;

use phpssdb\Core\SimpleSSDB;
use think\cache\Driver;

class Ssdb extends Driver
{
    protected $options = [
        'host' => '127.0.0.1',
        'port' => 8888,
        'password' => '',
        'timeout' => 2000,
        'expire' => 0,
        'prefix' => '',
    ];

    /**
     * 构造函数
     * @param array $options 缓存参数
     * @access public
     * @author Cong Peijun<me@congpeijun.com>
     */
    public function __construct($options = [])
    {
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }

        $this->handler = new SimpleSSDB($this->options['host'], $this->options['port'], $this->options['timeout']);
        if ($this->options['password']) {
            $this->handler->auth($this->options['password']);
        }
    }

    /**
     * 判断缓存
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     * @author Cong Peijun<me@congpeijun.com>
     */
    public function has($name)
    {
        return $this->handler->exists($this->getCacheKey($name));
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed  $default 默认值
     * @return mixed
     * @author Cong Peijun<me@congpeijun.com>
     */
    public function get($name, $default = false)
    {
        $value = $this->handler->get($this->getCacheKey($name));
        if (is_null($value) || false === $value) {
            return $default;
        }

        try {
            $result = 0 === strpos($value, 'think_serialize:') ? unserialize(substr($value, 16)) : $value;
        } catch (\Exception $e) {
            $result = $default;
        }

        return $result;
    }

    /**
     * 写入缓存
     * @access public
     * @param string            $name 缓存变量名
     * @param mixed             $value  存储数据
     * @param integer|\DateTime $expire  有效时间（秒）
     * @return boolean
     * @author Cong Peijun<me@congpeijun.com>
     */
    public function set($name, $value, $expire = null)
    {
        if (is_null($expire)) {
            $expire = $this->options['expire'];
        }
        if ($expire instanceof \DateTime) {
            $expire = $expire->getTimestamp() - time();
        }
        if ($this->tag && !$this->has($name)) {
            $first = true;
        }
        $key = $this->getCacheKey($name);
        $value = is_scalar($value) ? $value : 'think_serialize:' . serialize($value);
        if ($expire) {
            $result = $this->handler->setx($key, $value, $expire);
        } else {
            $result = $this->handler->set($key, $value);
        }
        isset($first) && $this->setTagItem($key);
        return $result;
    }

    /**
     * 自增缓存（针对数值缓存）
     * @access public
     * @param  string    $name 缓存变量名
     * @param  int       $step 步长
     * @return false|int
     * @author Cong Peijun<me@congpeijun.com>
     */
    public function inc($name, $step = 1)
    {
        $key = $this->getCacheKey($name);

        return $this->handler->incr($key, (int) $step);
    }

    /**
     * 自减缓存（针对数值缓存）
     * @access public
     * @param  string    $name 缓存变量名
     * @param  int       $step 步长
     * @return false|int
     * @author Cong Peijun<me@congpeijun.com>
     */
    public function dec($name, $step = 1)
    {
        $key = $this->getCacheKey($name);

        return $this->handler->decr($key, 0 - $step);
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolean
     * @author Cong Peijun<me@congpeijun.com>
     */
    public function rm($name)
    {
        return $this->handler->del($this->getCacheKey($name));
    }

    /**
     * 清除缓存
     * @access public
     * @param string $tag 标签名
     * @return boolean
     * @author Cong Peijun<me@congpeijun.com>
     */
    public function clear($tag = null)
    {
        if ($tag) {
            // 指定标签清除
            $keys = $this->getTagItem($tag);
            foreach ($keys as $key) {
                $this->handler->del($key);
            }
            $this->rm('tag_' . md5($tag));
            return true;
        }
        return true;
    }
}
