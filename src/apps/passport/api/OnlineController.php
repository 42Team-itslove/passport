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
	Itslove\Passport\Models\Online;

/**
 * 在线表资源控制器类
 *
 * 在线表存储着Ticket和UID之间的对应关系
 * 该表记录用户活跃情况和在线状态并用来支持单点登录功能
 * 过期的用户在线条目将有数据库定时任务删除
 *
 * @package Itslove\Passport\Api
 */
class OnlineController extends BaseController {

	/**
	 * 获取用户在线状态
	 *
	 * @param string $ticket  单点登录票据
	 * @throws ResourceException
	 */
	public function getUserAction($ticket)
	{
		$online = Online::findPrimary($ticket);

		if ($online) {
			$this->response(200, 'OK', $online);
		} else {
			throw new ResourceException('Not Found', 404);
		}
	}

	/**
	 * 将用户添加到在线列表
	 *
	 * @param integer $uid     用户ID
	 * @param string  $ticket  单点登录票据
	 * @throws ResourceException
	 */
	public function postUserAction($uid,  $ticket = '')
	{
		if ($ticket == '') {
			$ticket = Hash::unique_string();
		}

        $online = new Online();
        $online->ticket = $ticket;
        $online->UID = $uid;

        if ($online->create()) {
	 		$this->response(200, 'OK');
        } else {
        	throw new ResourceException('Internal Server Error', 500);
        }
	}

	/**
	 * 更新用户活动时间
	 *
	 * @param string $ticket  单点登录票据
	 * @throws ResourceException
	 */
	public function putUserAction($ticket)
	{
		$online = Online::findPrimary($ticket);

		if ( ! $online) {
			throw new ResourceException('Not Found', 404);
		}

		if ($online->save()) {
			$this->response(200, 'OK');
		} else {
			throw new ResourceException('Internal Server Error', 500);
		}
	}

	/**
	 * 删除用户在线状态
	 *
	 * @param string $ticket  Ticket票据
	 * @throws ResourceException
	 */
	public function deleteUserAction($ticket)
	{
		$online = Online::findPrimary($ticket);

		if ( ! $online) {
			throw new ResourceException('Not Found', 404);
		}

		if ($online->delete()) {
			$this->response(204, 'No Content');
		} else {
			throw new ResourceException('Internal Server Error', 500);
		}
	}

}