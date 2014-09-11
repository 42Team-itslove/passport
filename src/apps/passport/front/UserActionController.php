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

use Itslove\Passport\Api\UserController,
	Itslove\Passport\Api\UserMetaController,
	Itslove\Passport\Helper\RSA,
	Itslove\Passport\Helper\ValidationException;

/**
 * 用户动作前端控制器
 *
 * @package Itslove\Passport\Front
 */
class UserActionController extends BaseController {

	/**
	 * 获取验证码
	 */
	public function getCaptchaImageAction()
	{
		require APP_PATH.'libraries/securimage/securimage.php';
		$img = new \Securimage(array('session' => $this->session, 'use_wordlist' => true));
		$img->show(APP_PATH.'libraries/securimage/backgrounds/bg4.jpg');
	}

	/**
	 * 检查验证码(会使验证码失效)
	 *
	 * @param string $code  被检测的验证码
	 */
	public function getCheckCaptchaAction($code)
	{
		require APP_PATH.'libraries/securimage/securimage.php';
		$img = new \Securimage(array('session' => $this->session));
		$data = array(
			'name' => 'captcha',
			'result' => (bool)(strtolower($img->getCode()) === strtolower($code))
		);
		$this->responseJson('200', 'OK', $data);
	}

	/**
	 * 检查用户名是否使用
	 *
	 * @param string $username  被检测的用户名
	 */
	public function getCheckUsernameAction($username)
	{
		try {
			$user = new UserController();
			$this->responseJson(200, 'OK', array(
				'name' => 'username',
				'result' => $user->getUsernameExists($username)
			))->send();
		} catch (ValidationException $e) {
			$this->responseJson(400, 'Bad Request', array(
				'name' => 'username',
				'result' => true
			))->send();
		}
	}

	/**
	 * 检查用户昵称是否使用
	 *
	 * @param string $nickname 被检测的昵称
	 */
	public function getCheckNicknameAction($nickname)
	{
		$meta = new UserMetaController();
		$data = array(
			'name' => 'nickname',
			'result' => $meta->getMetaValueExists('nickname', $nickname)
		);
		$this->responseJson(200, 'OK', $data)->send();
	}

	/**
	 * 获得RSA算法的公钥, 并将私钥记录在Session中
	 *
	 * 该方法一般用于重要的数据加密, 如登录或注册时的密码加密
	 */
	public function getPublicKey()
	{
		$rsa = new RSA();
		$rsa->create();
		$content = array(
			'pubkey' => $rsa->getPublicKey()
		);

		$this->session->set('rsa_private_key', $rsa->getPrivateKey());
		$this->responseJson(200, 'OK', $content)->send();
	}

} 