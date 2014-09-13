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

use Itslove\Passport\Core\ModelProvider;

/**
 * 站内信消息模型
 *
 * @package Itslove\Passport\Models
 */
class Messages extends ModelProvider {

	/**
	 * 私信类型, 该类型消息默认在MessageLogs中存储阅读状态
	 */
	const POST_TYPE_PRIVATE = 'Private';

	/**
	 * 群组消息类型, 该类型消息默认没有在MessageLogs中存储阅读状态
	 *
	 * 若用户没有对此类型的阅读或删除标记, 则为用户新消息
	 */
	const POST_TYPE_PUBLIC = 'Public';

	/**
	 * 全体消息类型, 该类型消息默认没有在MessageLogs中存储阅读状态
	 *
	 * 若用户没有对此类型的阅读或删除标记, 则为用户新消息
	 */
	const POST_TYPE_GLOBAL = 'Global';

	protected static $cacheChildNamespace = 'Model::Messages';
	protected static $primaryKey = 'msg_id';

	public $msg_id;
	public $send_UID;
	public $content;
	public $msg_options;
	public $GID;
	public $post_type;
	public $post_time;
	public $send_delete;
	public $expiry;
	public $expiry_at_end;
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