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

use Closure;

/**
 * 提供者容器类
 *
 * 用于全局处理工程组件和属性的类, ServiceProvider为该类的门面类
 *
 * @package Itslove\Passport\Core
 */
class ProviderContainer {

	/**
	 * 该数组保存应用组件实例
	 *
	 * @var array
	 */
	protected $components = array();

	/**
	 * 该数组保存应用组件实例生成函数
	 *
	 * @var array
	 */
	protected $generators = array();

	/**
	 * 获得组件实例
	 *
	 * @param string $name  组件名
	 * @return object       组件实例对象
	 * @throws Exception
	 */
	public function get($name)
	{
		if ( ! isset($this->components[$name])) {
			if ( ! isset($this->generators[$name])) {
				throw new Exception("{$name} service provider does not exist");
			}
			$this->components[$name] = call_user_func($this->generators[$name], $this);
		}

		return $this->components[$name];
	}

	/**
	 * 组件类实例或该实例对应的生成函数是否存在
	 *
	 * @param string $name 组件名
	 * @return bool
	 */
	public function has($name)
	{
		return isset($this->components[$name]) || isset($this->generators[$name]);
	}

	/**
	 * 注册组件实例生成函数
	 *
	 * @param string   $name       组件名
	 * @param callable $generator  生成函数
	 * @return $this
	 */
	public function set($name, Closure $generator)
	{
		$this->generators[$name] = $generator;
		return $this;
	}

	/**
	 * 魔术方法, 获取组件实例对象
	 *
	 * @param string $name  组件名
	 * @return object
	 * @throws Exception
	 */
	public function __get($name)
	{
		return $this->get($name);
	}

} 