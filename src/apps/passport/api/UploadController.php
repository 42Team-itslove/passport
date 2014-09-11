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

use Itslove\Passport\Helper\Hash,
	Itslove\Passport\Helper\Image;

/**
 * 上传文件资源控制器
 *
 * @package Itslove\Passport\Api
 */
class UploadController extends BaseController {

	/**
	 * 该数组存储头像图片保存路径
	 *
	 * @var array
	 */
	protected $portraitSavePaths = array(
		'portrait_50' => 'uploads/user/portrait/50',
		'portrait_140' => 'uploads/user/portrait/140',
		'portrait_260' => 'uploads/user/portrait/260',
	);

	/**
	 * 该数组存储头像图片类型
	 *
	 * @var array
	 */
	protected $portraitMimes = array(
		'image/gif',
		'image/jpeg',
		'image/png',
		'image/bmp'
	);

	/**
	 * 该数组存储头像图片宽高
	 *
	 * @var array
	 */
	protected $portraitSize = array(
		'portrait_50' => array(50, 50),
		'portrait_140' => array(140, 140),
		'portrait_260' => array(260, 260),
	);

	/**
	 * 获取用户头像数据
	 *
	 * 该方法直接返回图像数据, 若ACCEPT中存在text/plain, 则返回base64格式的图片数据
	 *
	 * @param integer $uid      用户ID
	 * @param integer $size     头像图片大小
	 * @param array   $accepts  请求的格式, 默认输出图片数据, 若包含text/plain则输出base64字符串格式
	 * @throws ResourceException
	 */
	public function getUserPortraitAction($uid, $size, $accepts = array())
	{
		$meta = new UserMetaController();
		$meta->getUsermetaAction($uid, 'portrait');
		$portrait = $meta->resource->meta_value;

		if ( ! file_exists($filename = PUBLIC_PATH . $this->portraitSavePaths['portrait_' . $size] . '/'. $portrait . '.jpg')) {
			throw new ResourceException('Not Found', 404);
		}

		$portraitData = fread(fopen($filename,'rb'), filesize($filename));

		if (in_array('text/plain', $accepts)) {
			$this->response(200, 'OK', base64_encode($portraitData));
			$this->response->setHeader('Content-Type', 'text/plain');
		} else {
			$this->response(200, 'OK', $portraitData);
			$this->response->setHeader('Content-Type', getimagesize($filename)['mime']);
		}
	}

	/**
	 * 建立用户头像
	 *
	 * 若用户头像已经存在, 该方法会删除旧头像且新头像的地址与原头像的地址不同
	 *
	 * @param integer $uid    用户ID
	 * @param array   $files  包含所有上传图像文件信息的数组
	 * @throws ResourceException
	 */
	public function postUserPortraitAction($uid, $files)
	{
		$portrait  = Hash::unique_string();
		$this->saveUserPortrait($files, $portrait);

		$meta = new UserMetaController();
		$meta->putUserMetaAction($uid, 'portrait', $portrait);

		try {
			$this->deleteUserPortraitAction($uid);
		} catch (ResourceException $e) {
			if ($e->getCode() != 404) {
				throw $e;
			}
		}

		$this->response(200, 'OK');
	}

	/**
	 * 更新用户头像
	 *
	 * 该方法不会导致用户头像文件名变化
	 *
	 * @param integer $uid    用户ID
	 * @param array   $files  包含所有上传图像文件信息的数组
	 * @throws ResourceException
	 */
	public function putUserPortraitAction($uid, $files)
	{
		$meta = new UserMetaController();
		$meta->getUsermetaAction($uid, 'portrait');
		$portrait = $meta->resource->meta_value;

		$this->saveUserPortrait($files, $portrait);

		$this->response(200, 'OK');
	}

	/**
	 * 删除用户头像
	 *
	 * @param integer $uid  用户ID
	 * @throws ResourceException
	 */
	public function deleteUserPortraitAction($uid)
	{
		$meta = new UserMetaController();
		$meta->getUsermetaAction($uid, 'portrait');
		$portrait = $meta->resource->meta_value;

		foreach ($this->portraitSavePaths as $savePath) {
			if (file_exists($filename = PUBLIC_PATH . $savePath . '/' . $portrait.'.jpg')) {
				if ( ! unlink($filename)) {
					throw new ResourceException('Internal Server Error', 500);
				}
			}
		}

		$meta->deleteUsermetaAction($uid, 'portrait');

		$this->response(204, 'No Content');
	}

	/**
	 * 获取用户头像地址
	 *
	 * @param integer $uid  用户ID
	 * @throws ResourceException
	 */
	public function getUserPortraitAddressAction($uid)
	{
		$portraitAddresses = array(
			'portrait_50' => '',
			'portrait_140' => '',
			'portrait_260' => '',
		);

		try {
			$meta = new UserMetaController();
			$meta->getUsermetaAction($uid, 'portrait');
			$portrait = $meta->resource->meta_value;

			foreach ($portraitAddresses as $portraitSize => &$value) {
				if (file_exists(PUBLIC_PATH . $this->portraitSavePaths[$portraitSize] . '/' . $portrait . '.jpg')) {
					$value = $this->url->get($this->portraitSavePaths[$portraitSize] . '/' .  $portrait . '.jpg');
				}
			}
		} catch (ResourceException $e) {
			if ($e->getCode() != 404) {
				throw $e;
			}
		}

		$this->response(200, 'OK', $portraitAddresses);
	}

	/**
	 * 保存用户头像
	 *
	 * 该函数将校验图片类型和图片大小, 对符合规定且非JPEG的的图片进行格式转换
	 *
	 * @param array  $files     包含所有上传图像文件信息的数组
	 * @param string $portrait  头像存储文件名
	 * @throws ResourceException
	 */
	protected function saveUserPortrait($files, $portrait)
	{
		foreach ($files as $name => $file) {
			$re = getimagesize($file['tmp_name']);
			if ($re && in_array($re['mime'], $this->portraitMimes)) {
				if ($this->portraitSize[$name][0] != $re[0] || $this->portraitSize[$name][1] != $re[1]) {
					throw new ResourceException('Conflict', 409);
				}

				if ($re['mime'] != 'image/jpeg') {
					(new Image($file['tmp_name']))->save($file['tmp_name'], 'image/jpeg');
				}
				move_uploaded_file($file['tmp_name'], PUBLIC_PATH . $this->portraitSavePaths[$name] . '/' . $portrait . '.jpg');
			} else {
				throw new ResourceException('Conflict', 409);
			}
		}
	}

}