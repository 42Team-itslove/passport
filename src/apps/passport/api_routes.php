<?php
/**
 * Passport
 *
 * @author    Kezhao Pan <panlatent@gmail.com>
 * @link      https://github.com/42Team-itslove/passport
 * @license   https://github.com/42Team-itslove/passport/blob/master/LICENSE
 * @copyright (c) 2003 - 2014 42Team
 */

use Phalcon\Http\Response,
	Itslove\Passport\Api\BaseController,
	Itslove\Passport\Api\SsoController,
	Itslove\Passport\Api\UserController,
	Itslove\Passport\Api\UsermetaController,
	Itslove\Passport\Api\UploadController;

/**
 * Restful API 路由表
 *
 * 本程序面向API Client向应用程序创建所有的Restful形式的API
 */

/**
 * User API
 */
$app->get('/api/user/{uid:[0-9]+}', function($uid) {
	BaseController::auth();
	BaseController::run(new UserController(), 'getUserAction', array($uid));
});

$app->post('/api/user', function() {
	BaseController::auth();
	$request = new \Phalcon\Http\Request();
	$username = $request->getPost('username');
	$password = $request->getPost('password');
	$hash_method = $request->getPost('hash_method') or $hash_method = 'sha1_salt_sha1';
	$active = $request->getPost('active', null, 1);
	$reg_date = date('Y-m-d H:i:s');
	$reg_ip = $request->getPost('reg_ip') or $reg_ip = $request->getServer('REMOTE_ADDR');
	BaseController::run(new UserController(), 'postUserAction', array($username, $password, $hash_method, $active, $reg_date, $reg_ip));
});

$app->put('/api/user/{uid:[0-9]+}',  function($uid) {
	BaseController::auth();
	$request = new \Phalcon\Http\Request();
	$update_data = $request->getPut();
	BaseController::run(new UserController(), 'putUserAction', array($uid, $update_data));
});

$app->delete('/api/user/{uid:[0-9]+}',  function($uid) {
	BaseController::auth();
	BaseController::run(new UserController(), 'deleteUserAction', array($uid));
});

/**
 * UserMeta API
 */
$app->get('/api/user/meta/{uid:[0-9]+}/{meta_key:[0-9a-zA-Z_]+}',  function($uid, $meta_key) {
	BaseController::auth();
	BaseController::run(new UserMetaController(), 'getUserMetaAction', array($uid, $meta_key));
});

$app->post('/api/user/meta/{uid:[0-9]+}/{meta_key:[0-9a-zA-Z_]+}',  function($uid, $meta_key) {
	BaseController::auth();
	$request = new \Phalcon\Http\Request();
	$meta_value = $request->getPost('meta_value') or $meta_value = '';
	BaseController::run(new UserMetaController(), 'postUserMetaAction', array($uid, $meta_key, $meta_value));
});

$app->put('/api/user/meta/{uid:[0-9]+}/{meta_key:[0-9a-zA-Z_]+}',  function($uid, $meta_key) {
	BaseController::auth();
	$request = new \Phalcon\Http\Request();
	$meta_value = $request->getPut('meta_value') or $meta_value = '';
	BaseController::run(new UserMetaController(), 'putUserMetaAction', array($uid, $meta_key, $meta_value));
});

$app->delete('/api/user/meta/{uid:[0-9]+}/{meta_key:[0-9a-zA-Z_]+}',  function($uid, $meta_key) {
	BaseController::auth();
	BaseController::run(new UserMetaController(), 'deleteUserMetaAction', array($uid, $meta_key));
});

/**
 * SSO API
 */
$app->get('/api/sso/ticket', function() use($app) {
	$var_name = isset($_GET['var']) ? $_GET['var'] : 'il_passport_ticket';
	$response = new Response();
	$response->setHeader("Content-Type", "text/javascript");
	if ($app->session->has("auth")) {
		$auth = $app->session->get("auth");
		$response->setContent("; var {$var_name} = \"{$auth['ticket']}\";");
	} else {
		$response->setContent("; var {$var_name} = null;");
	}
	$response->send();
});

$app->get('/api/sso/status/{ticket:[0-9A-Z]+}', function($ticket) {
	BaseController::auth();
	BaseController::run(new SsoController(), 'getStatusAction', array($ticket));
});

$app->put('/api/sso/status/{ticket:[0-9A-Z]+}', function($ticket) {

});

$app->delete('/api/sso/status/{ticket:[0-9A-Z]+}', function($ticket) {
	BaseController::auth();
	BaseController::run(new SsoController(), 'deleteStatusAction', array($ticket));
});

$app->post('/api/sso/login', function() {
	BaseController::auth();
	$request = new \Phalcon\Http\Request();
	$username = $request->getPost('username');
	$password = $request->getPost('password');
	$last_login_date = date('Y-m-d H:i:s');
	$last_login_ip = $request->getPost('reg_ip') or $last_login_ip = $request->getServer('REMOTE_ADDR');
	BaseController::run(new SsoController(), 'postLoginAction', array($username, $password, $last_login_date, $last_login_ip));
});

$app->get('/api/sso/user/{ticket:[0-9A-Z]+}', function($ticket) {
	BaseController::auth();
	$needs = array('portrait', 'nickname', 'gender');
	BaseController::run(new SsoController(), 'getUserAction', array($ticket, $needs));
});

/**
 * Uploads API
 */
$app->get('/api/upload/user/portrait/{uid:[0-9]+}', function($uid) {
	BaseController::auth();
	$request = new Phalcon\Http\Request();
	$size = $request->get('size') or $size = '50';
	$accepts = explode(',', $request->getHeader('ACCEPT'));
	BaseController::run(new UploadController(), 'getUserPortraitAction', array($uid, $size, $accepts));
});

$app->post('/api/upload/user/portrait/{uid:[0-9]+}', function($uid) {
	BaseController::auth();
	$input_names = array('portrait_50', 'portrait_140', 'portrait_260');
	$files = array();
	foreach ($_FILES as $name => $file) {
		if (in_array($name, $input_names) && $file['error'] == 0) {
			$files[$name] = $file;
		}
	}
	BaseController::run(new UploadController(), 'postUserPortraitAction', array($uid, $files));
});

$app->put('/api/upload/user/portrait/{uid:[0-9]+}', function($uid) {
	BaseController::auth();
	$input_names = array('portrait_50', 'portrait_140', 'portrait_260');
	$files = array();
	foreach ($_FILES as $name => $file) {
		if (in_array($name, $input_names) && $file['error'] == 0) {
			$files[$name] = $file;
		}
	}
	BaseController::run(new UploadController(), 'postUserPortraitAction', array($uid, $files));
});

$app->delete('/api/upload/user/portrait/{uid:[0-9]+}', function($uid) {
	BaseController::auth();
	BaseController::run(new UploadController(), 'deleteUserPortraitAction', array($uid));
});

$app->get('/api/upload/user/portrait/address/{uid:[0-9]+}', function($uid) {
	BaseController::auth();
	BaseController::run(new UploadController(), 'getUserPortraitAddressAction', array($uid));
});