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
 * Passport 单入口程序
 *
 * 使用URl重写将Http请求重定向到此程序, 并由该程序加载启动引导程序
 * 当App目录位置发生变化时, 需要修改本程序中的APP_PATH常量
 *
 * @package Itslove\Passport
 */

/**
 * APP_PATH    App所在目录
 * PUBLIC_PATH Http服务器对外访问的Web目录, 即本文件所在目录
 *
 * 建议使用不包含.或..并以/开头的绝对路径
 */
define('APP_PATH', __DIR__ . '/../../apps/passport/');
define('PUBLIC_PATH', __DIR__ . '/');

/**
 * 加载启动程序
 */
require APP_PATH . 'start/bootstrap.php';
