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

use Phalcon\Mvc\Controller,
	Itslove\Passport\Models\Usermeta;

/**
 * 用户元数据过滤控制器类
 *
 * 该类用来配合UserMetaController类来过滤和处理特定的元数据
 *
 * @property \Itslove\Passport\Helper\Validation validation
 * @package Itslove\Passport\Api
 */
class UserMetaFilterController extends Controller {

	/**
	 * 该数组保存过滤规则
	 *
	 * @var array
	 */
	protected $fields = array();

	/**
	 * 用户ID
	 *
	 * @var integer
	 */
	protected $uid;

	/**
	 * 用户元数据键名
	 *
	 * @var string
	 */
	protected $metaKey;

	/**
	 * 注册过滤器
	 *
	 * 该方法会在类构造函数后自动调用
	 */
	public function onConstruct()
	{
		$this->fields['nickname'] = function(&$metaValue) {
			if ($this->filterUnique('nickname', $metaValue)) {
				throw new ResourceException('Conflict', 409);
			}
			$this->validation->validate('nickname', $metaValue);
		};
	}

	/**
	 * 运行过滤器
	 *
	 * @param int    $uid        用户ID
	 * @param string $metaKey    键名
	 * @param mixed  $metaValue  值
	 * @throws ResourceException
	 */
	public function run($uid, $metaKey, &$metaValue)
	{
		if ($metaValue == '') {
			throw new ResourceException('Conflict', 409);
		}

		$this->uid = $uid;
		$this->metaKey = $metaKey;

		if (isset($this->fields[$metaKey])) {
			$this->fields[$metaKey]($metaValue);
		}
	}

	/**
	 * 同键唯一值限定过滤器
	 *
	 * @param string $metaKey    键名
	 * @param string $metaValue  值
	 * @return bool              若同键存在该值则返回true, 否者为false
	 */
	public function filterUnique($metaKey, $metaValue)
	{
		$meta = Usermeta::findFirst(array(
			'conditions' => 'meta_key = ?0 and meta_value = ?1',
			'bind' => array($metaKey, $metaValue)
		));

		return (bool)$meta;
	}

} 