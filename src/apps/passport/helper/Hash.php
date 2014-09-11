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
 * 哈希算法助手类
 *
 * @package Itslove\Passport\Helper
 */
class Hash {

	/**
	 * 富哈希散列算法
	 *
	 * 通过指定的已知算法组合算出hash值
	 *
	 * @param string $var         被散列的字符串
	 * @param string $hashMethod  算法组合
	 * @return string
	 * @throws Exception
	 */
	public static function rich_hash($var, $hashMethod)
	{
		$salts = '';
		$bufferSalt = '';

		foreach (explode('_', $hashMethod) as $arithmetic) {
			switch ($arithmetic) {
				case 'sha1':
					if ($bufferSalt) {
						$var = sha1($var . $bufferSalt);
						$bufferSalt = '';
					} else {
						$var = sha1($var);
					}
					break;
				case 'md5':
					if ($bufferSalt) {
						$var = md5($var . $bufferSalt);
						$bufferSalt = '';
					} else {
						$var = md5($var);
					}
					break;
				case 'salt':
					$length = 6; $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
					$bufferSalt = substr(str_shuffle(str_repeat($chars, 5)), 0, $length);
					$salts = $bufferSalt . $salts;
					break;
				default:
					throw new Exception("{$arithmetic} method does not be hash helper support");
			}
		}

		return $var . $salts;
	}

	/**
	 * 检查值与富哈希散列算法的结果是否一致
	 *
	 * @param string $varHash     散列后的值
	 * @param string $hashMethod  算法组合
	 * @param string $var         被检测的值
	 * @return bool
	 * @throws Exception
	 */
	public static function check_rich_hash($varHash, $hashMethod, $var)
	{
		$bufferSalt = '';

		foreach (explode('_', $hashMethod) as $arithmetic) {
			switch ($arithmetic) {
				case 'sha1':
					if ($bufferSalt) {
						$var = sha1($var . $bufferSalt);
						$bufferSalt = '';
					} else {
						$var = sha1($var);
					}
					break;
				case 'md5':
					if ($bufferSalt) {
						$var = md5($var . $bufferSalt);
						$bufferSalt = '';
					} else {
						$var = md5($var);
					}
					break;
				case 'salt':
					$bufferSalt = substr($varHash, count($varHash) - 7);
					$varHash = substr($varHash, 0, count($varHash) - 7);
					break;
				default:
					throw new Exception("{$arithmetic} method does not be hash helper support");
			}
		}

		return $var == $varHash;
	}

	/**
	 * 获取唯一字符串
	 *
	 * @return string 返回长度为40且英文字母为大写字符串
	 */
	public static function unique_string()
	{
		return strtoupper(sha1(md5(microtime(true)).uniqid(mt_rand(), true)));
	}

}