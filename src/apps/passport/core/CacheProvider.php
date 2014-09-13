<?php
/**
 * Passport
 *
 * @author    Kezhao Pan <panlatent@gmail.com>
 * @link      https://github.com/42Team-itslove/passport
 * @license   https://github.com/42Team-itslove/passport/blob/master/LICENSE
 * @copyright (c) 2003 - 2014 42Team
 */

namespace Itslove\Passport\Core;

use Memcache;

/**
 * 缓存提供者类
 *
 * @package Itslove\Passport\Helper
 */
class CacheProvider {

	/**
	 * 缓存键名命名空间, 该值为Memcache键名前缀
	 *
	 * @var string
	 */
	protected $namespace;

	/**
	 * Memcache对象
	 *
	 * @var Memcache
	 */
	protected $memcache;

	/**
	 * 构造函数
	 *
	 * @param string $namespace  缓存键名命名空间
	 */
	public function __construct($namespace = '')
	{
		$this->namespace = $namespace;
		$this->memcache = new Memcache();
	}

	/**
	 * 添加缓存
	 *
	 * @param string  $childNamespace  子命名空间
	 * @param string  $key             键名
	 * @param mixed   $var             值
	 * @param integer $expire          有效期
	 * @return array|bool
	 */
	public function add($childNamespace, $key, $var, $expire = 0)
	{
		$fullKey = $this->getFullKey($childNamespace, $key);
		return $this->memcache->add($fullKey, $var, 0, $expire);
	}

	/**
	 * 添加Memcache服务器
	 *
	 * @param string  $host  Memcache服务器地址
	 * @param integer $port  Memcache服务器端口
	 * @return bool
	 */
	public function addServer($host = 'localhost', $port = 11211)
	{
		return $this->memcache->addServer($host, $port);
	}

	/**
	 * 删除缓存
	 *
	 * @param string  $childNamespace  子命名空间
	 * @param string  $key             键名
	 * @param integer $timeout         延迟时间
	 * @return bool
	 */
	public function delete($childNamespace, $key, $timeout = 0)
	{
		$fullKey = $this->getFullKey($childNamespace, $key);
		return $this->memcache->delete($fullKey, $timeout);
	}

	/**
	 * 清空缓存
	 *
	 * @return bool
	 */
	public function flush()
	{
		return $this->memcache->flush();
	}

	/**
	 * 获取缓存
	 *
	 * @param string $childNamespace  子命名空间
	 * @param string $key             键名
	 * @return array|string
	 */
	public function get($childNamespace, $key)
	{
		$fullKey = $this->getFullKey($childNamespace, $key);

		if ( false === ($result = $this->memcache->get($fullKey))) {
			/** @var \Itslove\Passport\Core\LogProvider $log */
			$log = Provider::get('log');
			$log['cache_access']->notice("缓存未命中, Memcached {$fullKey}");
		}

		return $result;
	}

	/**
	 * 获取服务器拓展状态
	 *
	 * @param string  $type    类型
	 * @param integer $slabid  slabid
	 * @param integer $limit   数量限制
	 * @return array|bool
	 */
	public function getExtendedStats($type, $slabid = 0, $limit = 100)
	{
		return $this->memcache->getExtendedStats($type, $slabid, $limit);
	}

	/**
	 * 获取服务器状态
	 *
	 * @return array|bool
	 */
	public function getStatus()
	{
		return $this->memcache->getstats();
	}

	/**
	 * 获取命名空间
	 *
	 * @return string
	 */
	public function getNamespace()
	{
		return $this->namespace;
	}

	/**
	 * 缓存中是否存在键名
	 *
	 * @param string $childNamespace  子命名空间
	 * @param string $key             键名
	 * @return bool
	 */
	public function has($childNamespace, $key)
	{
		$fullKey = $this->getFullKey($childNamespace, $key);
		return ($this->memcache->get($fullKey) !== false);
	}

	/**
	 * 替换缓存
	 *
	 * @param string  $childNamespace  子命名空间
	 * @param string  $key             键名
	 * @param mixed   $var             值
	 * @param integer $expire          有效期
	 * @return bool
	 */
	public function replace($childNamespace, $key, $var, $expire = 0)
	{
		$fullKey = $this->getFullKey($childNamespace, $key);
		return $this->memcache->replace($fullKey, $var, 0, $expire);
	}

	/**
	 * 设置缓存
	 *
	 * @param string  $childNamespace  子命名空间
	 * @param string  $key             键名
	 * @param mixed   $var             值
	 * @param integer $expire          有效期
	 * @return bool
	 */
	public function set($childNamespace, $key, $var, $expire = 0)
	{
		$fullKey = $this->getFullKey($childNamespace, $key);
		return $this->memcache->set($fullKey, $var, 0, $expire);
	}

	/**
	 * 设置命名空间
	 *
	 * @param string $namespace
	 */
	public function setNamespace($namespace)
	{
		$this->namespace = $namespace;
	}

	/**
	 * 获得由命名空间,子命名空间和键名组成的完全键名
	 *
	 * @param string $childNamespace  子命名空间
	 * @param string $key             键名
	 * @return string
	 */
	protected function getFullKey($childNamespace, $key)
	{
		is_array($key) and $key = implode('.', $key);
		return "$this->namespace::$childNamespace::$key";
	}

}