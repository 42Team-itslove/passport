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

/**
 * Passport核心异常类
 *
 * 该类用于Passport工程组件等级别的非具体业务逻辑的异常情况
 *
 * @package Itslove\Passport\Core
 */
class Exception extends \Exception {

	/**
	 * 构造函数
	 *
	 * 该异常产生时会自动写入core_error日志
	 *
	 * @param string    $message
	 * @param int       $code
	 * @param Exception $previous
	 */
	public function __construct($message = '', $code = 0, Exception $previous = null)
	{
		/** @var LogProvider $log */
		$log = Provider::get('log') and $log['core_error']->error("应用核心异常, \"{$message}\" {$code} {$this->file} {$this->line}");

		parent::__construct($message, $code, $previous);
	}
} 