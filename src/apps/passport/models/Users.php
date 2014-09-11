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
 * 用户表模型
 *
 * @package Itslove\Passport\Models
 */
class Users extends ModelProvider {

	protected static $cacheChildNamespace = 'Model::User';
	protected static $primaryKey = 'UID';

	public $UID;
	public $username;
	public $password;
	public $hash_method;
	public $active;
	public $reg_date;
	public $reg_ip;
	public $last_login_date;
	public $last_login_ip;
	public $created_at;
	public $updated_at;

	public function beforeValidationOnCreate()
	{
		$this->created_at = date('Y-m-d H:i:s');
	}

	public function beforeCreate()
	{
		$this->created_at = date('Y-m-d H:i:s');
	}

	public function beforeUpdate()
	{
		$this->updated_at = date('Y-m-d H:i:s');
	}

}