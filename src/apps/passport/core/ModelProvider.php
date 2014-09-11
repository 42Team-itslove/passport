<?php
/**
 * Passport
 *
 * @author    Kezhao Pan <panlatent@gmail.com>
 * @link      https://github.com/42Team-itslove/passport
 * @license   https://github.com/42Team-itslove/passport/blob/master/LICENSE
 * @copyright (c) 2003 - 2014 42Team
 */

namespace Itslove\Passport\Core;

use Phalcon\Mvc\Model;

/**
 * 模型提供者类
 *
 * @package Itslove\Passport\Models
 */
class ModelProvider extends Model {

	/**
	 * 缓存类实例
	 *
	 * @var \Itslove\Passport\Core\CacheProvider
	 */
	public static $cache;

	/**
	 * 缓存子命名空间
	 *
	 * @var string
	 */
	protected static $cacheChildNamespace = 'Model::Null';

	/**
	 * 缓存持续时间(秒)
	 *
	 * 该值为默认值, 若子类覆写本属性, 则使用子类值
	 *
	 * @var integer
	 */
	protected static $cacheExpire = 7200;

	/**
	 * 模型数据库主键
	 *
	 * 该值为默认值, 若子类覆写本属性, 则使用子类值
	 *
	 * @var string
	 */
	protected static $primaryKey = 'id';

	/**
	 * 获取缓存类实例
	 *
	 * @return object
	 */
	public static function getCache()
	{
		return Provider::get('cache');
	}

	/**
	 * 使用主机获取模型
	 *
	 * @param mixed $id  主键值
	 * @return Model
	 */
	public static function findPrimary($id)
	{
		if (($cache = static::getCache()) && ($model = $cache->get(static::$cacheChildNamespace, array($id)))) {
			return $model;
		}

		$model = static::findFirst(array(
			static::$primaryKey . '=?0',
			'bind' => array($id)
		));

		if ($cache && $model) {
			$cache->set(static::$cacheChildNamespace, array($id), $model, static::$cacheExpire);
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
			$cache->delete(static::$cacheChildNamespace, array($this->{static::$primaryKey}));
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
			$cache->replace(static::$cacheChildNamespace, array($this->{static::$primaryKey}), $this, self::$cacheExpire);
		}

		return parent::save($data, $whiteList);
	}

}