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
 * 用户元数据表模型
 *
 * @package Itslove\Passport\Models
 */
class UserMeta extends ModelProvider {

	protected static $cacheChildNamespace = 'Model::UserMeta';
	protected static $primaryKey = 'meta_id';

	public $meta_id;
	public $UID;
	public $meta_key;
	public $meta_value;
	public $created_at;
	public $updated_at;

	/**
	 * 通过UID和元数据键名获取模型
	 *
	 * 该模型的使用者都应该用过次方法来获取模型
	 *
	 * @param integer $uid      用户ID
	 * @param string  $metaKey  键名
	 * @return \Phalcon\Mvc\Model
	 */
	public static function findRow($uid, $metaKey)
	{
		if (($cache = static::getCache()) && ($model = $cache->get(self::$cacheChildNamespace, array($uid, $metaKey)))) {
			return $model;
		}

		$model = self::findFirst(array(
			'conditions' => 'UID = ?0 AND meta_key = ?1',
			'bind' => array($uid, $metaKey)
		));

		if (($cache = static::getCache()) && $model) {
			$cache->set(self::$cacheChildNamespace, array($uid, $metaKey), $model, self::$cacheExpire);
		}

		return $model;
	}

	/**
	 * 删除模型
	 *
	 * @return bool
	 */
	public function delete()
	{
		if ($cache = static::getCache()) {
			$cache->delete(self::$cacheChildNamespace, array($this->UID, $this->meta_key));
		}

		return parent::delete();
	}

	/**
	 * 保存模型
	 *
	 * @param array $data
	 * @param array $whiteList
	 * @return bool
	 */
	public function save($data = null, $whiteList = null)
	{
		if ($cache = static::getCache()) {
			$cache->replace(self::$cacheChildNamespace, array($this->UID, $this->meta_key), $this, self::$cacheExpire);
		}

		return parent::save($data, $whiteList);
	}

	public function beforeValidationOnCreate(){
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