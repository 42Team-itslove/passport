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

use Phalcon\Db,
	Itslove\Passport\Core\Exception,
	Itslove\Passport\Models\Messages,
	Itslove\Passport\Models\MessageLogs;

/**
 * 消息通知资源控制器
 *
 * 该控制器用来获取相应用户的未读/已读/全部消息列表和消息数量, 并接受请求将相应用户的消息置为未读/已读/删除状态
 *
 * @property \Phalcon\Db\Adapter\Pdo\Mysql db
 * @package Itslove\Passport\Api
 */
class UserMessageController extends BaseController {

	/**
	 * 获取指定用户未读消息id列表和总数(包括私信、群组消息和全体消息)
	 *
	 * @param integer $uid  用户ID
	 */
	public function getUserUnreadMessageListAction($uid)
	{
		$result = array(
			'count' => 0,
			'list' => array()
		);

		$this->getUserPrivateMessageList($uid, MessageLogs::STATUS_UNREAD, $result['count'], $result['list']);
		$this->getUserPublicMessageList($uid, MessageLogs::STATUS_UNREAD, $result['count'], $result['list']);
		$this->getUserGlobalMessageList($uid, MessageLogs::STATUS_UNREAD, $result['count'], $result['list']);

		$this->response(200, 'OK', $result);
	}

	/**
	 * 获取指定用户已读消息id列表和总数(包括私信、群组消息和全体消息)
	 *
	 * @param integer $uid  用户ID
	 */
	public function getUserReadMessageListAction($uid)
	{
		$result = array(
			'count' => 0,
			'list' => array()
		);

		$this->getUserPrivateMessageList($uid, MessageLogs::STATUS_READ, $result['count'], $result['list']);
		$this->getUserPublicMessageList($uid, MessageLogs::STATUS_READ, $result['count'], $result['list']);
		$this->getUserGlobalMessageList($uid, MessageLogs::STATUS_READ, $result['count'], $result['list']);

		$this->response(200, 'OK', $result);
	}

	/**
	 * 获取指定用户所有消息id列表和总数(包括私信、群组消息和全体消息)
	 *
	 * 所有消息不包括被用户标记为删除的消息
	 *
	 * @param integer $uid  用户ID
	 */
	public function getUserAllMessageListAction($uid)
	{
		$result = array(
			'count' => 0,
			'list' => array()
		);

		$this->getUserPrivateMessageList($uid, MessageLogs::STATUS_NOT_DELETE, $result['count'], $result['list']);
		$this->getUserPublicMessageList($uid, MessageLogs::STATUS_NOT_DELETE, $result['count'], $result['list']);
		$this->getUserGlobalMessageList($uid, MessageLogs::STATUS_NOT_DELETE, $result['count'], $result['list']);

		$this->response(200, 'OK', $result);
	}

	/**
	 * 更新或创建指定用户对指定消息的阅读状态
	 *
	 * @param integer $uid     用户ID
	 * @param integer $msgId   消息ID
	 * @param string  $status  想要获取消息的标记类型
	 * @throws ResourceException
	 */
	public function putUserMessageStatusAction($uid, $msgId, $status)
	{
		$msgLog = MessageLogs::findFirst(array(
			'rec_UID = ?0 AND msg_id = ?1',
			'bind' => array($uid, $msgId)
		));

		if ( ! $msgLog) {
			$msgLog = new MessageLogs();
			$msgLog->rec_UID = $uid;
			$msgLog->msg_id = $msgId;
		}

		if ( ! in_array($status , array(MessageLogs::STATUS_UNREAD, MessageLogs::STATUS_READ, MessageLogs::STATUS_DELETE))) {
			throw new ResourceException('Conflict', 409);
		}

		$msgLog->status = $status;

		if ($msgLog->save()) {
			$this->response(200, 'OK');
		} else {
			throw new ResourceException('Internal Server Error', 500);
		}
	}

	/**
	 * 获取指定用户的私信id列表和总数
	 *
	 * @param integer $uid    用户ID
	 * @param string  $status 想要获取消息的标记类型
	 * @param integer $count  返回的消息数量(引用)
	 * @param array   $list   返回的消息数量(引用)
	 * @throws Exception
	 */
	protected function getUserPrivateMessageList($uid, $status, &$count, &$list)
	{
		switch ($status) {
			case MessageLogs::STATUS_UNREAD:
			case MessageLogs::STATUS_READ:
			case MessageLogs::STATUS_DELETE:
				$sql = 'SELECT `msg_id`
						FROM `messages`
						WHERE `msg_id` IN (SELECT `msg_id` FROM `message_logs` WHERE `rec_UID`=? AND `status`=?)
						AND `post_type`=?
						AND `expiry_at_end`>=Now()';
				break;
			case MessageLogs::STATUS_NOT_DELETE:
				$sql = 'SELECT `msg_id`
						FROM `messages`
						WHERE `msg_id` IN (SELECT `msg_id` FROM `message_logs` WHERE `rec_UID`=? AND `status`!=?)
						AND `post_type`=?
						AND `expiry_at_end`>=Now()';
				$status = MessageLogs::STATUS_DELETE;
				break;
			default:
				throw new Exception("使用了未定义的消息状态 {$status}");
		}


		$msgLogs = $this->db->fetchAll($sql, DB::FETCH_ASSOC, array($uid, $status, Messages::POST_TYPE_PRIVATE));
		$count += count($msgLogs);
		foreach ($msgLogs as $msgLog) {
			$list[] = $msgLog['msg_id'];
		}
		unset($msgLogs);
	}

	/**
	 * 获取指定用户的群组消息id列表和总数
	 *
	 * @param integer $uid     用户ID
	 * @param string  $status  想要获取消息的标记类型
	 * @param integer $count   返回的消息数量(引用)
	 * @param array   $list    返回的消息数量(引用)
	 */
	protected function getUserPublicMessageList($uid, $status, &$count, &$list)
	{
		//..该方法为预留方法
	}

	/**
	 * 获取指定用户的全体消息id列表和总数
	 *
	 * @param integer $uid    用户ID
	 * @param string  $status 想要获取消息的标记类型
	 * @param integer $count  返回的消息数量(引用)
	 * @param array   $list   返回的消息数量(引用)
	 * @throws Exception
	 */
	protected function getUserGlobalMessageList($uid, $status, &$count, &$list)
	{
		switch ($status) {
			case MessageLogs::STATUS_UNREAD:
				$sql = 'SELECT `msg_id`
						FROM (SELECT `msg_id` FROM `messages` WHERE `send_delete`=0 AND `post_type`=? AND `expiry_at_end`>=Now()) AS `list`
						WHERE `msg_id` NOT IN (SELECT `msg_id` FROM `message_logs` WHERE `msg_id`=`list`.`msg_id` AND `rec_UID`=? AND `status`!=?)';
				break;
			case MessageLogs::STATUS_READ:
			case MessageLogs::STATUS_DELETE:
				$sql = 'SELECT `msg_id`
						FROM `messages`
						WHERE `send_delete`=0
						AND `post_type`=?
						AND `msg_id` IN (SELECT `msg_id` FROM `message_logs` WHERE `rec_UID`=? AND `status`=?)';
				break;
			case MessageLogs::STATUS_NOT_DELETE:
				$sql = 'SELECT `msg_id`
						FROM (SELECT `msg_id` FROM `messages` WHERE `send_delete`=0 AND `post_type`=? AND `expiry_at_end`>=Now()) AS `list`
						WHERE `msg_id` NOT IN (SELECT `msg_id` FROM `message_logs` WHERE `msg_id`=`list`.`msg_id` AND `rec_UID`=? AND `status`=?)';
				$status = MessageLogs::STATUS_DELETE;
				break;
			default:
				throw new Exception("使用了未定义的消息状态 {$status}");
		}

		$msgLogs = $this->db->fetchAll($sql, DB::FETCH_ASSOC, array(Messages::POST_TYPE_GLOBAL, $uid, $status));
		$count += count($msgLogs);
		foreach ($msgLogs as $msgLog) {
			$list[] = $msgLog['msg_id'];
		}
		unset($msgLogs);
	}

} 