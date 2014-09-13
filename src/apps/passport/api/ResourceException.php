<?php
/**
 * Passport
 *
 * @author    Kezhao Pan <panlatent@gmail.com>
 * @link      https://github.com/42Team-itslove/passport
 * @license   https://github.com/42Team-itslove/passport/blob/master/LICENSE
 * @copyright (c) 2003 - 2014 42Team
 */

namespace Itslove\Passport\Api;

use Itslove\Passport\Core\Provider;

/**
 * API资源异常类
 *
 * @package Itslove\Passport\Api
 */
class ResourceException extends \Exception {

	/**
	 * 构造函数
	 *
	 * 该异常产生时会自动写入core_error日志
	 *
	 * @param string    $message
	 * @param int       $code
	 * @param \Exception $previous
	 */
	public function __construct($message = '', $code = 0, \Exception $previous = null)
	{
		if ($code == 500) {
			/** @var \Itslove\Passport\Core\LogProvider $log */
			$log = Provider::get('log') and $log['core_error']->error("资源服务异常, \"{$message}\" {$code} {$this->file} {$this->line}");
		}

		parent::__construct($message, $code, $previous);
	}

}