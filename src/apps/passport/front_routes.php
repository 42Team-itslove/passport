<?php
/**
 * Passport
 *
 * @author    Kezhao Pan <panlatent@gmail.com>
 * @link      https://github.com/42Team-itslove/passport
 * @license   https://github.com/42Team-itslove/passport/blob/master/LICENSE
 * @copyright (c) 2003 - 2014 42Team
 */

use Itslove\Passport\Front\MainController,
	Itslove\Passport\Front\UserActionController;

/**
 * 前端页面和数据投递路由表
 *
 * 本程序面浏览器向应用程序创建和浏览器交互的页面并接收数据
 */
$app->get('/', function () {
	(new MainController())->getIndexAction();
});

$app->get('/center', function() {
	(new MainController())->getCenterAction();
});

$app->get('/reg', function() {
	(new MainController())->getRegAction();
});

$app->post('/reg', function() {
	$request = new Phalcon\Http\Request();
	$username = $request->getPost('username');
	$password = $request->getPost('password');
	$nickname = $request->getPost('nickname');
	$reg_date = date('Y-m-d H:i:s');
	$reg_ip = $request->getServer('REMOTE_ADDR');
	(new MainController())->postRegAction($username, $password, $nickname, $reg_date, $reg_ip);
});

$app->get('/signin', function() {
	(new MainController())->getSignInAction();
});

$app->post('/signin', function() {
	$request = new Phalcon\Http\Request();
	$username = $request->getPost('username');
	$password = $request->getPost('password');
	$auto_signin = $request->getPost('auto_signin') ? true : false;
	$last_login_date = date('Y-m-d H:i:s');
	$last_login_ip = $request->getServer('REMOTE_ADDR');
	(new MainController())->postSignInAction($username, $password, $auto_signin, $last_login_date, $last_login_ip);
});

$app->get('/signout', function() {
	(new MainController())->getSignOutAction();
});

$app->get('/user/action/captcha/image', function() {
	(new UserActionController())->getCaptchaImageAction();
});

$app->get('/user/action/check', function() use($app) {
	$request = new Phalcon\Http\Request();
	$name = $request->get('name');
	$value = $request->get('value');
	$action = new UserActionController();
	switch ($name) {
		case 'captcha':
			$action->getCheckCaptchaAction($value);
			break;
		case 'username':
			$action->getCheckUsernameAction($value);
			break;
		case 'nickname':
			$action->getCheckNicknameAction($value);
			break;
		default:
			$action->responseJson('404', 'Not Found')->send();
			break;
	}
});

$app->get('/user/action/getpublickey', function() {
	(new UserActionController())->getPublicKey();
});