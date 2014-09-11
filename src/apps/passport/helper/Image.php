<?php
/**
 * Passport
 *
 * @author    Kezhao Pan <panlatent@gmail.com>
 * @link      https://github.com/42Team-itslove/passport
 * @license   https://github.com/42Team-itslove/passport/blob/master/LICENSE
 * @copyright (c) 2003 - 2014 42Team
 */

namespace Itslove\Passport\Helper;

use Itslove\Passport\Core\Exception;

/**
 * 图像处理助手类
 *
 * @package Itslove\Passport\Helper
 */
class Image {

	/**
	 * 图片Mime类型
	 *
	 * @var string
	 */
	protected $mime;

	/**
	 * 打开的图片资源
	 *
	 * @var resource
	 */
	protected $resource;

	/**
	 * 构造函数, 打开一个图片文件
	 *
	 * @param string $inputFile  文件路径
	 * @throws Exception
	 */
	public function __construct($inputFile)
	{
		$re = getimagesize($inputFile);
		$this->mime = $re['mime'];

		switch ($this->mime) {
			case 'image/gif':
				$this->resource = imagecreatefromgif($inputFile);
				break;
			case  'image/jpeg':
				$this->resource = imagecreatefromjpeg($inputFile);
				break;
			case 'image/png':
				$this->resource = imagecreatefrompng($inputFile);
				break;
			case 'image/bmp':
				$this->resource = imagecreatefromwbmp($inputFile);
				break;
			default:
				throw new Exception("{$this->mime} type does not be image helper support");
		}
	}

	/**
	 * 将图片资源保存到文件
	 *
	 * @param string $output      输出文件路径
	 * @param string $outputType  输出类型, 默认为原图片类型
	 * @return bool
	 * @throws Exception
	 */
	public function save($output, $outputType = '')
	{
		$outputType or $outputType = $this->mime;

		switch ($outputType) {
			case 'image/gif':
				return imagegif($this->resource, $output);
			case  'image/jpeg':
				return imagejpeg($this->resource, $output);
			case 'image/png':
				return imagepng($this->resource, $output);
			case 'image/bmp':
				return imagewbmp($this->resource, $output);
			default:
				throw new Exception("{$this->mime} type does not be image helper support");
		}
	}

} 