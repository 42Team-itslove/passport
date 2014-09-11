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

use RuntimeException,
	Phalcon\Http\Response,
    Itslove\Passport\Api\ResourceException,
	Itslove\Passport\Api\UserController,
	Itslove\Passport\Api\UsermetaController,
	Itslove\Passport\Api\SsoController,
	Itslove\Passport\Helper\RSA;

/**
 * 前端页面主控制器
 *
 * @property \Itslove\Passport\Core\ViewProvider view
 * @property \Phalcon\Db\Adapter\Pdo\Mysql       db
 * @package Itslove\Passport\Front
 */
class MainController extends BaseController {

	/**
	 * 默认首页
	 */
	public function getIndexAction()
	{
		$response = new Response();
		if ( ! $this->session->has('auth')) {
			$response->redirect($this->url->get('signin'), true);
		} else {
			$response->redirect($this->url->get('center'), true);
		}
		$response->send();
	}

	/**
	 * 用户中心页
	 */
	public function getCenterAction()
	{
		if ( ! $this->session->has('auth')) {
			$response = new Response();
			$response->redirect(isset($_GET['callback']) ? $_GET['callback'] : $this->url->get('signin'), true);
			$response->send();
			return;
		}

		$data = array('title' => '用户中心');
		$this->view->load(array( 'head', 'center', 'foot'), $data);
		$this->view->show();
	}

	/**
	 * 用户注册页
	 */
	public function getRegAction()
	{
		if ($this->session->has('auth')) {
			$response = new Response();
			$response->redirect(isset($_GET['callback']) ? $_GET['callback'] : $this->url->get('center'), true);
			$response->send();
			return;
		}

		$data = array('title' => '注册新用户 - 用户中心');
		$this->view->load(array( 'head', 'register', 'foot'), $data);
		$this->view->show();
	}

	/**
	 * 用户登录页
	 */
	public function getSignInAction()
	{
		if ($this->session->has('auth')) {
			$response = new Response();
			$response->redirect(isset($_GET['callback']) ? $_GET['callback'] : $this->url->get('center'), true);
			$response->send();
			return;
		}
		$data = array('title' => '登录 - 用户中心');
		$this->view->load(array('head',  'signin', 'foot'), $data);
		$this->view->show();
	}

	/**
	 * 用户注销页
	 */
	public function getSignOutAction()
	{
		$this->session->has('auth') and $this->session->remove('auth');
		$response = new Response();

		$response->redirect(isset($_GET['callback']) ? $_GET['callback'] : $this->url->get('signin'), true);
		$response->send();
	}

	/**
	 * 用户登录动作
	 *
	 * @param string $username       用户名
	 * @param string $password       密码
	 * @param bool   $auto_signin    是否自动登录(记注我)
	 * @param string $lastLoginDate  最后登录时间
	 * @param string $lastLoginIp    最后登录IP
	 */
	public function postSignInAction($username, $password, $auto_signin, $lastLoginDate, $lastLoginIp)
	{
		if ( ! $this->security->checkToken()) {
			$this->response(403, 'Forbidden', '未通过安全验证')->send();
			return;
		}

		try {
			if ( ! $this->session->has('rsa_private_key')) {
				$this->response(403, 'Forbidden', '传输了未经加密的密码')->send();
			}

			$rsa = new RSA();
			$rsa->setPrivateKey($this->session->get('rsa_private_key'));
			$password =  $rsa->decrypt($password);

			$sso = new SsoController();
			$sso->postLoginAction($username, $password, $lastLoginDate, $lastLoginIp);
			$this->session->set('auth', array(
	            'id' => $sso->resource->UID,
	            'username' => $sso->resource->username,
	            'ticket' => $sso->resource->ticket,
				'auto_signin' => $auto_signin,
	            'created_at' => time()
	        ));
			$this->responseJson(200, '登陆成功')->send();
		} catch (ResourceException $e) {
			switch ($e->getCode()) {
				case 404:
					$this->responseJson($e->getCode(), '用户不存在')->send();
					break;
				case 409:
					$this->responseJson($e->getCode(), '密码不正确')->send();
					break;
				case 500:
					$this->responseJson($e->getCode(), '服务器错误')->send();
					break;
				default:
					throw new RuntimeException('使用不存在的返回值');
					break;
			}
	    }
	}

	/**
	 * 用户注册动作
	 *
	 * @param string $username
	 * @param string $password
	 * @param string $nickname
	 * @param string $regDate
	 * @param string $regIp
	 */
	public function postRegAction($username, $password, $nickname, $regDate, $regIp)
	{
		if ( ! $this->security->checkToken()) {
			$this->response(403, 'Forbidden', '未通过安全验证')->send();
			return;
		}

		$this->db->begin();
		try {
			//RSA解密密码
			if ( ! $this->session->has('rsa_private_key')) {
				$this->response(403, 'Forbidden', '传输了未经加密的密码')->send();
			}

			$rsa = new RSA();
			$rsa->setPrivateKey($this->session->get('rsa_private_key'));
			$password =  $rsa->decrypt($password);

			//注册用户
			$user = new UserController();
			$user->postUserAction($username, $password, 'sha1_salt_sha1', 1, $regDate, $regIp);
			$usermeta = new UsermetaController();
			$usermeta->postUsermetaAction($user->resource->UID, 'nickname', $nickname);
			$this->db->commit();
			$this->responseJson(200, '注册成功')->send();

			//登陆用户
			$sso = new SsoController();
			$sso->postLoginAction($username, $password, $regDate, $regIp);
			$this->session->set('auth', array(
				'id' => $sso->resource->UID,
				'username' => $sso->resource->username,
				'ticket' => $sso->resource->ticket,
				'auto_signin' => false,
				'created_at' => time()
			));
		} catch (ResourceException $e) {
			$this->db->rollback();
			switch ($e->getCode()) {
				case 409:
					$this->responseJson($e->getCode(), '用户或昵称已存在')->send();
					break;
				case 500:
					$this->responseJson($e->getCode(), '服务器错误')->send();
					break;
				default:
					throw new RuntimeException('使用不存在的返回值');
					break;
			}
	    }
	}

}