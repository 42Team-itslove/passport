<?php
/**
 * Passport
 *
 * @author    Kezhao Pan <panlatent@gmail.com>
 * @link      https://github.com/42Team-itslove/passport
 * @license   https://github.com/42Team-itslove/passport/blob/master/LICENSE
 * @copyright (c) 2003 - 2014 42Team
 */

/**
 * 验证类过滤器和验证器添加程序
 *
 * @var Itslove\Passport\Helper\Validation $validation
 */
$validation->addFilter('username', function($value) {
	return trim($value);
});

$validation->addFilter('nickname', function($value) {
	return trim($value);
});

$validation->addValidator('username', function($value) {
	return preg_match('/^[0-9a-zA-Z_]{5,60}$/', $value);
});

$validation->addValidator('password', function($value) {
	return strlen($value) >= 8;
});

$validation->addValidator('nickname', function($value) {
	return (preg_match('/^[a-zA-Z]{4,14}$/', $value) || preg_match('/^[\x{4e00}-\x{9fa5}]{2,7}$/u', $value));
});

$validation->addValidator('ipv4', function($value) {
	return (filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false);
});

$validation->addValidator('datetime', function($value) {
	return preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}\d{2}$/', $value);
});