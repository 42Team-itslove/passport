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
	Itslove\Passport\Models\Online,
	Itslove\Passport\Models\Users;

/**
 * 单点登录资源控制器类
 *
 * @package Itslove\Passport\Api
 */
class SsoController extends BaseController {

	/**
	 * 单点登录用户
	 *
	 * 该方法是提供给Passport前端登录的方法, 大多数API Client不会使用本方法
	 *
	 * @param string $username       用户名
	 * @param string $password       密码
	 * @param string $lastLoginDate  最后登录时间
	 * @param string $lastLoginIp    最后登录IP
	 * @param string $ticket         单点登录票据
	 * @throws ResourceException
	 */
	public function postLoginAction($username, $password, $lastLoginDate, $lastLoginIp, $ticket = '')
	{
		$user = new UserController();
		$user->putAuthAction($username, $password, $lastLoginDate, $lastLoginIp);

		if ($ticket == '') {
			$ticket = Hash::unique_string();
		}

		(new OnlineController())->postUserAction($user->resource->UID, $ticket);

		$this->response(200, 'OK', array('UID' => $user->resource->UID, 'username' => $username, 'ticket' => $ticket));
	}

	/**
	 * 使用单点登录票据获取用户数据
	 *
	 * 该方法是为了便捷API Client获取资源流程而设计的富接口
	 *
	 * @param string $ticket  单点登录票据
	 * @param array  $needs   需要得到的资源
	 * @throws ResourceException
	 */
	public function getUserAction($ticket, $needs)
	{
		$online = Online::findPrimary($ticket);

		if ($online) {
			$user = Users::findPrimary($online->UID);
			if ($user) {
				$data = array('UID' => $user->UID, 'username' => $user->username);
				$meta = new UserMetaController();
				foreach ($needs as $need) {
					try {
						switch ($need) {
							case 'portrait':
								$upload = new UploadController();
								$upload->getUserPortraitAddressAction($user->UID);
								$data = array_merge($data, (array)$upload->resource);
								break;
							default:
								$meta->getUserMetaAction($user->UID, $need);
								$data[$need] = $meta->resource->meta_value;
						}
					} catch (ResourceException $e) {
						if ($e->getCode() == 404) {
							$data[$need] = '';
						} else {
							throw $e;
						}
					}
				}
				$this->response(200, 'OK', $data);
			} else {
				throw new ResourceException('Not Found', 404);
			}
		} else {
			throw new ResourceException('Not Found', 404);
		}
	}

}