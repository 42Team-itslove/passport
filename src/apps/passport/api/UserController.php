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

use Itslove\Passport\Helper\Hash,
	Itslove\Passport\Models\Users;

/**
 * 用户资源控制器
 *
 * @property \Itslove\Passport\Helper\Validation validation
 * @package Itslove\Passport\Api
 */
class UserController extends BaseController {

	/**
	 * 获取用户表用户用户信息
	 *
	 * @param integer $uid  用户ID
	 * @throws ResourceException
	 */
	public function getUserAction($uid)
	{
		$user = Users::findPrimary($uid);

		if ($user) {
			unset($user->password);
			unset($user->hash_method);
			$this->response(200, 'OK', $user);
		} else {
			throw new ResourceException('Not Found', 404);
		}
	}

	/**
	 * 向用户表表注册新用户
	 *
	 * @param string $username     用户名
	 * @param string $password     密码
	 * @param string $hashMethod   密码散列方式
	 * @param int    $active       用户激活状态
	 * @param string $regDate      注册日期
	 * @param string $regIp        注册IP地址
	 * @throws ResourceException
	 */
	public function postUserAction($username, $password, $hashMethod, $active, $regDate, $regIp)
	{
		$user = Users::findFirst(array(
			'username = ?0',
			'bind' => array($username)
		));

		if ($user) {
			throw new ResourceException('Conflict', 409);
		}

		$password = Hash::rich_hash($password, $hashMethod);

		$user = new Users();
		$user->username = $this->validation->validate('username', $username);
		$user->password = $this->validation->validate('password', $password);
		$user->hash_method = $hashMethod;
		$user->active =  $active;
		$user->reg_date = $regDate;
		$user->reg_ip = $this->validation->validate('ipv4', $regIp);
		$user->last_login_date = '';
		$user->last_login_ip = '';

		if ($user->create()) {
			$this->response(201, 'Created', array('UID' => $user->UID));
			$this->response->setHeader('Location', 'user/'.$user->UID);
		} else {
			throw new ResourceException('Internal Server Error', 500);
		}
	}

	/**
	 * 更新User表用户资料
	 *
	 * @param integer $uid         用户ID
	 * @param array   $updateData  包含更新的数据的数组
	 * @throws ResourceException
	 */
	public function putUserAction($uid, $updateData)
	{
		$user = Users::findPrimary($uid);

		if ( ! $user) {
			throw new ResourceException('Not Found', 404);
		}

		foreach ($updateData as $field => $value) {
			$user->$field = $value;
		}

		if ($user->save()) {
			$this->response(200, 'OK');
		} else {
			throw new ResourceException('Internal Server Error', 500);
		}
	}

	/**
	 * 删除User表用户资料
	 *
	 * @param integer $uid  用户ID
	 * @throws ResourceException
	 */
	public function deleteUserAction($uid)
	{
		$user = Users::findPrimary($uid);

		if ( ! $user) {
			throw new ResourceException('Not Found', 404);
		}

		if ($user->delete()) {
			$this->response(204, 'No Content');
		} else {
			throw new ResourceException('Internal Server Error', 500);
		}
	}

	/**
	 * 验证用户名和密码
	 *
	 * @param string $username  用户名
	 * @param string $password  密码
	 * @throws ResourceException
	 */
	public function getAuthAction($username, $password)
	{
		$user = Users::findFirst(array(
			'username = ?0',
			'bind' => array($username)
		));

		if ( ! $user) {
			throw new ResourceException('Not Found', 404);
		}

		if ( ! Hash::check_rich_hash($user->password, $user->hash_method, $password)) {
			throw new ResourceException('Conflict', 409);
		}

		$this->response(200, 'OK');
	}

	/**
	 * 验证用户并更新用户登陆记录
	 *
	 * @param string $username       用户名
	 * @param string $password       密码
	 * @param string $lastLoginDate  最后登录日期
	 * @param string $lastLoginIp    最后登录IP
	 * @throws ResourceException
	 */
	public function putAuthAction($username, $password, $lastLoginDate, $lastLoginIp)
	{
		$user = Users::findFirst(array(
			'conditions' => 'username = ?0',
			'bind' => array($username)
		));

		if ( ! $user) {
			throw new ResourceException('Not Found', 404);
		}

		if ( ! Hash::check_rich_hash($user->password, $user->hash_method, $password)) {
			throw new ResourceException('Conflict', 409);
		}

		$user->last_login_date = $lastLoginDate;
 		$user->last_login_ip = $lastLoginIp;

 		if ($user->save()) {
 			$this->response(200, 'OK', $user);
 		} else {
			throw new ResourceException('Internal Server Error', 500);
 		}
	}

	/**
	 * 检查用户名是否存在(该方法非响应资源型方法)
	 *
	 * @param string $username  用户名
	 * @return bool             若用户名存在则返回true, 否者为false
	 */
	public function getUsernameExists($username)
	{
		$this->validation->validate('username', $username);

		$user = Users::findFirst(array(
			'conditions' => 'username = ?0',
			'bind' => array($username)
		));

		return (bool)$user;
	}

}