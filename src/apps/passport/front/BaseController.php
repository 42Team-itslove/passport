<?php
/**
 * Passport
 *
 * @author    Kezhao Pan <panlatent@gmail.com>
 * @link      https://github.com/42Team-itslove/passport
 * @license   https://github.com/42Team-itslove/passport/blob/master/LICENSE
 * @copyright (c) 2003 - 2014 42Team
 */

namespace Itslove\Passport\Front;

use Phalcon\Mvc\Controller,
	Phalcon\Http\Response;

/**
 * 用户页面控制器基类
 *
 * @package Itslove\Passport\Front
 */
class BaseController extends Controller {

	/**
	 * 用户登录状态
	 *
	 * @var bool
	 */
	protected $authStatus = false;

	/**
	 * 通用前置处理方法
	 *
	 * 该方法会在类构造函数后自动调用
	 * 更新用户Session活动时间和登陆状态
	 */
	public function onConstruct()
	{
		if ( ! $this->session->has('created_at')) {
			$this->session->set('created_at', time());
		}
		$this->session->set('updated_at', time());

		//处理用户登录状态
		if ($this->session->has('auth')) {
			$auth = $this->session->get('auth');
			if ($auth['auto_signin']) {
				if (time() - $auth['created_at'] > $this->config->online->auto_valid_time) {
					$this->session->set('last_signin_username', $auth['username']);
					$this->session->remove('auth');
				} else {
					$this->authStatus = true;
				}
			} else {
				if (time() - $auth['created_at'] > $this->config->online->default_valid_time) {
					$this->session->set('last_signin_username', $auth['username']);
					$this->session->remove('auth');
				} else {
					$this->authStatus = true;
				}
			}
		}
	}

	/**
	 * 创建JSON格式的Http响应
	 *
	 * @param integer $status   状态码, 该状态码采用HTTP状态码近似, 但为数据输出. 该Http响应的状态码始终为200
	 * @param string  $explain  状态描述
	 * @param array   $data     响应数据
	 * @return Response
	 */
	public function responseJson($status = 200, $explain = '', $data = array())
	{
		$response = new Response();
		$response->setStatusCode(200, 'OK');
		$response->setHeader("Content-Type", "text/json");

		$contents = array(
			'statusCode' => $status,
			'explain' => $explain,
			'original_val' => isset($_GET['original_val']) ? $_GET['original_val'] : $this->request->getPost('original_val', null, ''),
			'data' => (object)$data,
		);

		$response->setContent(json_encode($contents, true));
		return $response;
	}

	/**
	 * 发送常规文本格式的Http响应
	 *
	 * @param integer $statusCode Http状态码
	 * @param string  $explain    状态描述
	 * @param string  $content    内容
	 * @return Response
	 */
	protected function response($statusCode, $explain, $content = '')
	{
		$response = new Response();
		$response->setStatusCode($statusCode, $explain);
		$response->setContent($content);
		return $response;
	}

}