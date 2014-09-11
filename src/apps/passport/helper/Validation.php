<?php
/**
 * Passport
 *
 * @author    Kezhao Pan <panlatent@gmail.com>
 * @link      https://github.com/42Team-itslove/passport
 * @license   https://github.com/42Team-itslove/passport/blob/master/LICENSE
 * @copyright (c) 2003 - 2014 42Team
 */

namespace Itslove\Passport\Helper;

use Closure,
	Itslove\Passport\Core\Exception;

class ValidationException extends Exception {}

/**
 * 验证过滤助手类
 *
 * @package Itslove\Passport\Helper
 */
class Validation {

	/**
	 * 该数组保存过滤函数
	 *
	 * @var array
	 */
	protected $filters = array();

	/**
	 * 该数组保存验证函数
	 *
	 * @var array
	 */
	protected $validators = array();

	/**
	 * 添加过滤器
	 *
	 * 过滤器可能会改变被过滤数据的值
	 *
	 * @param string   $name      过滤数据名
	 * @param callable $callback  过滤函数
	 */
	public function addFilter($name, Closure $callback)
	{
		$this->filters[$name] = $callback;
	}

	/**
	 * 添加验证器
	 *
	 * @param string   $name      验证数据名
	 * @param callable $callback  验证函数
	 */
	public function addValidator($name, Closure $callback)
	{
		$this->validators[$name] = $callback;
	}

	/**
	 * 对数据验证
	 *
	 * @param string $name       验证数据名
	 * @param mixed  $value      被验证数据
	 * @param bool   $useFilter  是否使用过滤器
	 * @return mixed
	 * @throws ValidationException
	 */
	public function validate($name, &$value, $useFilter = true)
	{
		if ($useFilter && isset($this->filters[$name])) {
			$value = $this->filters[$name]($value);
		}

		if ( ! $this->validators[$name]($value)) {
			throw new ValidationException("$name 未通过校验", 409);
		}

		return $value;
	}

} 