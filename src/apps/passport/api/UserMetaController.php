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

use Itslove\Passport\Models\UserMeta;

/**
 * 用户元数据资源控制器类
 *
 * @package Itslove\Passport\Api
 */
class UserMetaController extends BaseController {

	/**
	 * 获取用户元数据
	 *
	 * @param integer $uid      用户ID
	 * @param string  $metaKey  键名
	 * @throws ResourceException
	 */
	public function getUserMetaAction($uid, $metaKey)
	{
		$meta = Usermeta::findRow($uid, $metaKey);
		if ($meta) {
			unset($meta->meta_id);
			$this->response(200, 'OK', $meta);
		} else {
			throw new ResourceException('Not Found', 404);
		}
	}

	/**
	 * 创建用户元数据
	 *
	 * @param integer $uid        用户ID
	 * @param string  $metaKey    键名
	 * @param string  $metaValue  值
	 * @throws ResourceException
	 */
	public function postUserMetaAction($uid, $metaKey, $metaValue)
	{
		$meta = new Usermeta();
		$meta->UID = $uid;
		$meta->meta_key = $metaKey;
		$meta->meta_value = $metaValue;

		//使用元数据过滤器处理特定的键值
		$metaFilter = new UserMetaFilterController();
		$metaFilter->run($uid, $metaKey, $metaValue);
		
		if ($meta->create()) {
			$this->response(201, 'Created');
			$this->response->setHeader('Location', 'user_meta/'.$meta->meta_id);
		} else {
			throw new ResourceException('Internal Server Error', 500);
		}
	}

	/**
	 * 更新或创建用户元数据
	 *
	 * @param integer $uid        用户ID
	 * @param string  $metaKey    键名
	 * @param string  $metaValue  值
	 * @throws ResourceException
	 */
	public function putUserMetaAction($uid, $metaKey, $metaValue)
	{
		$meta = UserMeta::findRow($uid, $metaKey);

		if ( ! $meta) {
			$meta = new UserMeta();
			$meta->UID = $uid;
			$meta->meta_key = $metaKey;
		}

		$meta->meta_value = $metaValue;

		//使用元数据过滤器处理特定的键值
		$metaFilter = new UserMetaFilterController();
		$metaFilter->run($uid, $metaKey, $metaValue);

		if ($meta->save()) {
			$this->response(200, 'OK');
		} else {
			throw new ResourceException('Internal Server Error', 500);
		}
	}

	/**
	 * 删除用户元数据
	 *
	 * @param integer $uid      用户ID
	 * @param string  $metaKey  键名
	 * @throws ResourceException
	 */
	public function deleteUserMetaAction($uid, $metaKey)
	{
		$meta = UserMeta::findRow($uid, $metaKey);

		if ( ! $meta) {
			throw new ResourceException('Not Found', 404);
		}

		if ($meta->delete()) {
			$this->response(204, 'No Content');
		} else {
			throw new ResourceException('Internal Server Error', 500);
		}
	}

	/**
	 * 删除所有用户元数据
	 *
	 * @param integer $uid  用户ID
	 * @throws ResourceException
	 */
	public function deleteUserMetaAll($uid)
	{
		$metaGroup = UserMeta::find(array(
			'conditions' => 'UID = ?0',
			'bind' => array($uid)
		));

		/** @var UserMeta $meta */
		foreach ($metaGroup as &$meta) {
			if ( ! $meta->delete()) {
				throw new ResourceException('Internal Server Error', 500);
			}
		}
	}

	/**
	 * 检查指定元数据键的值是否存在(该方法非响应资源型方法)
	 *
	 * @param string $metaKey    键名
	 * @param string $metaValue  值
	 * @return bool              若值存在则返回true, 否者为false
	 */
	public function getMetaValueExists($metaKey, $metaValue)
	{
		$meta = UserMeta::findFirst(array(
			'conditions' => 'meta_key = ?0 and meta_value = ?1',
			'bind' => array($metaKey, $metaValue)
		));

		return (bool)$meta;
	}

}