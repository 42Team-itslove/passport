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
 * 服务提供者类
 *
 * 该类是Provide类的门面类, 配合Provide类实现了全局组件访问
 *
 * @package Itslove\Passport\Core
 */
class Provider {

	/**
	 * 提供者类实例
	 *
	 * @var \Itslove\Passport\Core\ProviderContainer
	 */
	private static $providerContainer;

	/**
	 * 初始化提供者类实例
	 */
	public static function init()
	{
		self::$providerContainer = new ProviderContainer();
	}

	/**
	 * 提供者类get方法静态代理
	 *
	 * @param string $name 组件名
	 * @return object
	 */
	public static function get($name)
	{
		return self::$providerContainer->get($name);
	}

	/**
	 * 提供者类set方法静态代理
	 *
	 * @param string   $name       组件名
	 * @param callable $generator  生成函数
	 * @return Provider
	 */
	public static function set($name, Closure $generator)
	{
		return self::$providerContainer->set($name, $generator);
	}

	/**
	 * 魔术方法, 实现外观模式
	 *
	 * @param string $method 方法名
	 * @param array  $args   参数数组
	 * @return mixed
	 */
	public static function __callStatic($method, $args)
	{
		switch (count($args)) {
			case 0:
				return self::$providerContainer->$method();
			case 1:
				return self::$providerContainer->$method($args[0]);
			case 2:
				return self::$providerContainer->$method($args[0], $args[1]);
			case 3:
				return self::$providerContainer->$method($args[0], $args[1], $args[2]);
			case 4:
				return self::$providerContainer->$method($args[0], $args[1], $args[2], $args[3]);
			default:
				return call_user_func_array(array(self::$providerContainer, $method), $args);
		}
	}

}