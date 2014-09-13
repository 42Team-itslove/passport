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

use ArrayAccess;

/**
 * 日志提供者类
 *
 * 该类为日志类的容器类, 会按需创建多种日志
 *
 * @package Itslove\Passport\Helper
 */
class LogProvider implements ArrayAccess {

	/**
	 * 该数组保存日志类实例
	 *
	 * @var array
	 */
	protected $loggers = array();

	/**
	 * 该数组保存日志类实例生成函数
	 *
	 * @var array
	 */
	protected $makers = array();

	/**
	 * 数组式访问接口, 判断指定的日志类实例是否存在
	 *
	 * @param mixed $offset
	 * @return bool
	 */
	public function offsetExists($offset)
	{
		return isset($this->loggers[$offset]);
	}

	/**
	 * 数组式访问接口, 获得指定的日志类实例
	 *
	 * 若指定的日志类实例不存在且存在日志类实例生成函数, 则使用该函数生成实例
	 *
	 * @param mixed $offset
	 * @throws Exception
	 * @return \Phalcon\Logger\Adapter
	 */
	public function offsetGet($offset)
	{
		if ( ! isset($this->loggers[$offset])) {
			if ( ! isset($this->makers[$offset])) {
				throw new Exception("该日志服务驱动不存在 {$offset}");
			}
			$this->loggers[$offset] = $this->makers[$offset]();
		}

		return $this->loggers[$offset];
	}

	/**
	 * 数组式访问接口, 设置指定的类实例生成函数
	 *
	 * @param mixed    $offset
	 * @param \Closure $value   生成函数
	 */
	public function offsetSet($offset, $value)
	{
		$this->makers[$offset] = $value;
	}

	/**
	 * 数组式访问接口, 删除指定的类实例
	 *
	 * @param mixed $offset
	 */
	public function offsetUnset($offset)
	{
		unset($this->loggers[$offset]);
	}

} 