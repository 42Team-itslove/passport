<?php
/**
 * Passport
 *
 * @author    Kezhao Pan <panlatent@gmail.com>
 * @link      https://github.com/42Team-itslove/passport
 * @license   https://github.com/42Team-itslove/passport/blob/master/LICENSE
 * @copyright (c) 2003 - 2014 42Team
 */

namespace Itslove\Passport\Models;

use Phalcon\Db,
	Itslove\Passport\Core\ModelProvider;

/**
 * 消息状态日志模型
 *
 * 更改模型记录了用户对消息的标记状态, 私信一定会将状态写入此表, 公开或全部站内信会将已阅读或删除状态写入此表
 *
 * @package Itslove\Passport\Models
 */
class MessageLogs extends ModelProvider {

	/**
	 * 未读状态, 在数据库中有对应
	 */
	const STATUS_UNREAD = 'Unread';

	/**
	 * 已读状态, 在数据库中有对应值
	 */
	const STATUS_READ = 'Read';

	/**
	 * 删除状态, 在数据库中有对应值
	 */
	const STATUS_DELETE = 'Delete';

	/**
	 * 非删除状态(未读或已读)
	 *
	 * 该状态为模型常量, 在数据库中并没有对应值
	 */
	const STATUS_NOT_DELETE = 'NotDelete';

	protected static $cacheChildNamespace = 'Model::MessageLog';
	protected static $primaryKey = 'msg_log_id';

	public $msg_log_id;
	public $rec_UID;
	public $msg_id;
	public $status;
	public $created_at;
	public $updated_at;

	public static function getCache()
	{
		return false;
	}

	public function beforeValidationOnCreate()
	{
		$this->created_at = date('Y-m-d H:i:s');
	}

	public function beforeUpdate()
	{
		$this->updated_at = date('Y-m-d H:i:s');
	}

} 