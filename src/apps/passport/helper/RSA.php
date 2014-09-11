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

use Crypt_RSA;

/**
 * RSA算法助手类
 *
 * 该类使用了位于phpseclib库
 *
 * @package Itslove\Passport\Helper
 */
class RSA {

	/**
	 * Crypt_RSA对象
	 *
	 * @var Crypt_RSA
	 */
	protected $rsa;

	/**
	 * 私钥
	 *
	 * @var string
	 */
	protected $privateKey;

	/**
	 * 公钥
	 *
	 * @var string
	 */
	protected $publicKey;

	/**
	 * 构造函数
	 */
	public function __construct()
	{
		$path = APP_PATH . 'libraries/phpseclib';
		set_include_path(get_include_path() . PATH_SEPARATOR . $path);
		include_once(APP_PATH . 'libraries/phpseclib/Crypt/RSA.php');
		$this->rsa = new Crypt_RSA();
	}

	/**
	 * 创建私钥
	 *
	 * @param integer $length 私钥长度, 默认为1024 长度越大生成耗时越多
	 */
	public function create($length = 1024)
	{
		$this->rsa->setPrivateKeyFormat(CRYPT_RSA_PRIVATE_FORMAT_PKCS1);
		$this->rsa->setPublicKeyFormat(CRYPT_RSA_PUBLIC_FORMAT_PKCS1);
		$this->privateKey = $this->rsa->createKey($length)['privatekey'];
	}

	/**
	 * 获取私钥
	 *
	 * @return string
	 */
	public function getPrivateKey()
	{
		return $this->privateKey;
	}

	/**
	 * 获取公钥
	 *
	 * @return string
	 */
	public function getPublicKey()
	{
		$rsa = new Crypt_RSA();
		$rsa->loadKey($this->privateKey);

		/** @var \Math_BigInteger $raw */
		$raw = $rsa->getPublicKey(CRYPT_RSA_PUBLIC_FORMAT_RAW)['n'];
		return $raw->toHex();
	}

	/**
	 * 设置私钥
	 *
	 * @param string $privateKey  私钥
	 */
	public function setPrivateKey($privateKey)
	{
		$this->privateKey = $privateKey;
	}

	/**
	 * 私钥解密
	 *
	 * @param string $data 被解密数据
	 * @return string
	 */
	public function decrypt($data)
	{
		$encrypted = pack('H*', $data);
		$this->rsa->loadKey($this->privateKey);
		$this->rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
		return $this->rsa->decrypt($encrypted);
	}

}