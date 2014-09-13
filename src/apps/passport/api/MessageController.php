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

use Itslove\Passport\Models\MessageLogs,
	Itslove\Passport\Models\Messages,
	Itslove\Passport\Models\Users;

/**
 * 站内信资源控制器类
 *
 * 该类为消息实体控制器, 即Messages模型
 * UserMessageController为用户消息状态控制器, 管理消息通知和阅读状态
 *
 * @property \Phalcon\Db\Adapter\Pdo\Mysql       db
 * @property \Itslove\Passport\Helper\Validation validation
 * @package Itslove\Passport\Api
 */
class MessageController extends BaseController {

	/**
	 * 获取站内信
	 *
	 * @param integer $msgId  消息ID
	 * @throws ResourceException
	 */
	public function getMessageAction($msgId)
	{
		$msg = Messages::findPrimary($msgId);

		if ($msg) {
			$this->response(200, 'OK', $msg);
		} else {
			throw new ResourceException('Not Found', 404);
		}
	}

	/**
	 * 投递站内信
	 *
	 * @param integer $send_uid       发送者用户ID
	 * @param string  $content        消息内容
	 * @param string  $msg_options    消息选项
	 * @param integer $uid_or_gid     用户ID或用户组ID
	 * @param string  $post_type      投递类型
	 * @param string  $post_time      投递时间
	 * @param integer $expiry         消息有效期(仅供显示, 不参与最终的失效计算)
	 * @param string  $expiry_at_end  消息结束日期
	 * @throws ResourceException
	 * @throws \Itslove\Passport\Helper\ValidationException
	 */
	public function postMessageAction($send_uid, $content, $msg_options, $uid_or_gid, $post_type, $post_time, $expiry, $expiry_at_end)
	{
		//判断发送者发送权限
		/** @var \Itslove\Passport\Models\Users $sendUser */
		if ( ! ($sendUser = Users::findPrimary($send_uid)) || 0 == $sendUser->active) {
			throw new ResourceException('Forbidden', 403);
		}

		$msg = new Messages();
		$msg->send_UID = $this->validation->validate('id', $send_uid);
		$msg->content = $content;
		$msg->msg_options = $msg_options;
		$msg->post_type = $post_type;
		$msg->post_time = $this->validation->validate('datetime', $post_time);
		$msg->send_delete = 0;
		$msg->expiry = $this->validation->validate('uint', $expiry);
		$msg->expiry_at_end = $this->validation->validate('datetime', $expiry_at_end);

		switch ($msg->post_type) {
			case Messages::POST_TYPE_PRIVATE:
				$msg->GID = 0;
				$msgLog = new MessageLogs();
				$msgLog->rec_UID = $uid_or_gid;
				$msgLog->status = MessageLogs::STATUS_UNREAD;
				break;
			case Messages::POST_TYPE_PUBLIC:
				$msg->GID = $uid_or_gid;
				break;
			case Messages::POST_TYPE_GLOBAL:
				$msg->GID = 0;
				break;
			default:
				throw new ResourceException('Conflict', 409);
		}

		$this->db->begin();

		try {
			if (isset($msgLog) && false == Users::findPrimary($msgLog->rec_UID)) {
				throw new ResourceException('Not Found', 404);
			}

			if ($msg->create()) {
				$this->response(201, 'Created', array('msg_id' => $msg->msg_id));
				$this->response->setHeader('Location', 'message/'.$msg->msg_id);
			} else {
				throw new ResourceException('Internal Server Error', 500);
			}

			if (isset($msgLog)) {
				$msgLog->msg_id = $msg->msg_id;
				if ( ! $msgLog->create()) {
					throw new ResourceException('Internal Server Error', 500);
				}
			}

			$this->db->commit();
		} catch (ResourceException $e) {
			$this->db->rollback();
			throw $e;
		}
	}

	/**
	 * 更新站内信
	 *
	 * @param integer $msgId       消息ID
	 * @param array   $updateData  将要更新的数据的键值数组
	 * @throws ResourceException
	 */
	public function putMessageAction($msgId, $updateData)
	{
		/** @var \Itslove\Passport\Models\Messages $msg */
		$msg = Messages::findPrimary($msgId);

		if ( ! $msg) {
			throw new ResourceException('Not Found', 404);
		}

		foreach ($updateData as $key => $value) {
			switch ($key) {
				case 'content':
					$msg->$key = $value;
					break;
				case 'msg_options':
					$msg->$key = $value;
					break;
				case 'post_time':
					$msg->$key = $this->validation->validate('datetime', $value);
					break;
				case 'expiry':
					$msg->$key = $this->validation->validate('uint', $value);
					break;
				case 'expiry_at_end':
					$msg->$key = $this->validation->validate('datetime', $value);
					break;
				default:
					throw new ResourceException('Conflict', 409);
			}
		}

		if ($msg->save()) {
			$this->response(200, 'OK');
		} else {
			throw new ResourceException('Internal Server Error', 500);
		}
	}

	/**
	 * 删除站内信
	 *
	 * @param integer $msgId  消息ID
	 * @throws ResourceException
	 */
	public function deleteMessageAction($msgId)
	{
		/** @var \Itslove\Passport\Models\Messages $msg */
		$msg = Messages::findPrimary($msgId);

		if ( ! $msg) {
			throw new ResourceException('Not Found', 404);
		}

		$msg->send_delete = 1;
		if ($msg->save()) {
			$this->response(204, 'No Content');
		} else {
			throw new ResourceException('Internal Server Error', 500);
		}
	}

} 