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
 * 用户在线表模型
 *
 * @package Itslove\Passport\Models
 */
class Online extends ModelProvider {

	protected static $cacheChildNamespace = 'Model::Online';
	protected static $primaryKey = 'ticket';

	public $ticket;
	public $UID;
	public $created_at;
	public $updated_at;

	public function beforeValidationOnCreate()
	{
		$this->created_at = date('Y-m-d H:i:s');
	}

	public function beforeUpdate()
	{
		$this->updated_at = date('Y-m-d H:i:s');
	}

}