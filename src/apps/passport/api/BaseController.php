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

use RuntimeException,
	Phalcon\Http\Response,
	Phalcon\Mvc\Controller,
	Itslove\Passport\Core\Provider,
	Itslove\Passport\Helper\ValidationException;

/**
 * Restful API控制器基类
 *
 * 该类提供了面向资源的统一的响应方式, 会对API Client进行认证并处理响应的资源(包含对失败异常的处理)
 *
 * @package Itslove\Passport\Api
 */
class BaseController extends Controller {

	/**
	 * 该变量为处理API响应资源的类
	 *
	 * @var \Phalcon\Http\Response
	 */
	public $response;

	/**
	 * 该变量存储API响应资源
	 *
	 * @var mixed
	 */
	public $resource;

	/**
	 * 对调用Restful API的Client进行身份验证
	 *
	 * 验证采用HTTP基本验证, 认证口令以键值对形式保存在app配置文件的api_user数组中
	 */
	public static function auth()
	{
		/** @var \Itslove\Passport\Core\LogProvider $log */
		if ( ! Provider::get('config')->api->authentication) {
			$log = Provider::get('log') and $log['api_access']->notice("认证功能被关闭, 客户端 {$_SERVER['REMOTE_ADDR']} 身份未被认证");
			return;
		}

		$user = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '';
		$password = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';

		if ($user && $password && isset(Provider::get('config')->api_user->$user)
			&& Provider::get('config')->api_user->$user == $password) {

			$log = Provider::get('log') and $log['api_access']->info("身份认证成功, 客户端 {$_SERVER['REMOTE_ADDR']} 使用口令 {$user}:{$password}");

		} else {
			header('HTTP/1.1 401 Unauthorized');
			header('WWW-Authenticate: Basic realm="passport"');
			echo 'Authorization Required.';

			$log = Provider::get('log') and $log['api_access']->notice("身份认证失败, 客户端 {$_SERVER['REMOTE_ADDR']} 尝试口令 {$user}:{$password}");

			exit();
		}
	}

	/**
	 * 运行指定API控制器的方法, 并统一处理响应结果
	 *
	 * @param object $api     API资源控制器对象
	 * @param string $method  调用的请求方法
	 * @param array  $params  参数
	 */
	public static function run($api, $method, $params = array())
	{
		/** @var \Itslove\Passport\Core\LogProvider $log */
		try {
			call_user_func_array(array($api, $method), $params);
			$api->send();
			$log = $api->log and $log['api_access']->info("资源请求成功, 客户端 {$_SERVER['REMOTE_ADDR']} 访问资源 {$_SERVER['REQUEST_URI']}");
		} catch (ResourceException $e) {
			$response = new Response();
			$response->setStatusCode($e->getCode(), $e->getMessage());
			switch ($e->getCode()) {
				case 404:
					$response->setContent('这个资源不存在');
					$log = $api->log and $log['api_error']->warning("资源请求失败, 客户端 {$_SERVER['REMOTE_ADDR']} 访问资源 {$_SERVER['REQUEST_URI']}: 资源不存在");
					break;
				case 409:
					$response->setContent('条件判断失败');
					$log = $api->log and $log['api_error']->warning("资源请求失败, 客户端 {$_SERVER['REMOTE_ADDR']} 访问资源 {$_SERVER['REQUEST_URI']}: 资源不存在");
					break;
				case 500:
					$response->setContent('服务器错误');
					$log = $api->log and $log['api_error']->error("资源请求失败, 客户端 {$_SERVER['REMOTE_ADDR']} 访问资源 {$_SERVER['REQUEST_URI']}: 服务器错误");
					break;
				default:
					$response->setContent('未知错误');
					$log = $api->log and $log['api_error']->error("资源请求失败, 客户端 {$_SERVER['REMOTE_ADDR']} 访问资源 {$_SERVER['REQUEST_URI']}: 未知错误");
					break;
			}
			$response->send();
		} catch (ValidationException $e) {
			$response = new Response();
			$response->setStatusCode(409, 'Conflict');
			$response->setContent('提供的数据格式未通过校验');
			$response->send();
			$log = $api->log and $log['api_error']->warning("资源请求失败, 客户端 {$_SERVER['REMOTE_ADDR']} 访问资源 {$_SERVER['REQUEST_URI']}: 提供的数据格式未通过校验");
		} catch (RuntimeException $e) {
			$response = new Response();
			$response->setStatusCode(500, 'Internal Server Error');
			$response->setContent('服务器错误');
			$response->send();
			$log = $api->log and $log['api_error']->error("资源请求失败, 客户端 {$_SERVER['REMOTE_ADDR']} 访问资源 {$_SERVER['REQUEST_URI']}: 服务器异常");
		}
	}

	/**
	 * 向Client发送响应的资源
	 */
	public function send()
	{
		if (is_array($this->resource) || is_object($this->resource)) {
			$this->response->setHeader('Content-Type', 'text/json');
			$this->response->setContent(json_encode($this->resource, true));
		} else {
			$this->response->setContent($this->resource);
		}

		$this->response->send();
	}

	/**
	 * 设置响应状态码和响应资源
	 *
	 * 当API控制器发送响应时调用此方法
	 *
	 * @param integer $statusCode 资源响应状态码, 本状态码与HTTP状态码完全吻合
	 * @param string  $explain    状态描述
	 * @param mixed   $resource   资源
	 */
	protected function response($statusCode, $explain, $resource = '')
	{
		$this->response = new Response();
		$this->response->setStatusCode($statusCode, $explain);
		
		if (is_array($resource)) {
			$this->resource = (object)$resource;
		} else {
			$this->resource = $resource;
		}
	}

}