<?php
/**
 * Passport
 *
 * @author    Kezhao Pan <panlatent@gmail.com>
 * @link      https://github.com/42Team-itslove/passport
 * @license   https://github.com/42Team-itslove/passport/blob/master/LICENSE
 * @copyright (c) 2003 - 2014 42Team
 */

use Phalcon\Config,
	Phalcon\Db\Adapter\Pdo\Mysql,
	Phalcon\Events\Manager as EventsManager,
	Phalcon\Loader,
	Phalcon\Logger\Adapter\File as Logger,
	Phalcon\Mvc\Micro,
	Phalcon\Mvc\Url,
	Phalcon\Session\Adapter\Files as Session,
	Itslove\Passport\Core\CacheProvider,
	Itslove\Passport\Core\LogProvider,
	Itslove\Passport\Core\Provider,
	Itslove\Passport\Core\ViewProvider,
	Itslove\Passport\Helper\Validation;

/**
 * Passport 引导程序
 *
 * 加载配置文件, 设置通用功能并注册项目必要组件
 */
try {
	/**
	 * 注册加载器
	 */
	$loader = new Loader();
	$loader->registerNamespaces(
		array(
			'Itslove\Passport\Api'    => APP_PATH . 'api/',
			'Itslove\Passport\Core'   => APP_PATH . 'core/',
			'Itslove\Passport\Front'  => APP_PATH . 'front/',
			'Itslove\Passport\Helper' => APP_PATH . 'helper/',
			'Itslove\Passport\Models' => APP_PATH . 'models/',
		)
	)->registerDirs(
		array(
			APP_PATH . 'api/',
			APP_PATH . 'core/',
			APP_PATH . 'front/',
			APP_PATH . 'helper/',
			APP_PATH . 'models/',
			APP_PATH . 'libraries/',
		)
	)->register();

	/**
	 * 注册服务项
	 */
	Provider::init();

	// 创建Micro Application应用
	Provider::set('app', function() {
		return new Micro();
	});

	//加载配置文件
	Provider::set('config', function() {
		return new Config(require APP_PATH.'config/app.php');
	});

	// 缓存
	Provider::set('cache', function($provider) {
		if ($provider->config->cache->enable) {
			$cache = new CacheProvider($provider->config->cache->namespace);
			foreach ($provider->config->cache->servers as $server) {
				$info = explode(':', $server);
				$cache->addServer($info[0], $info[1]);
			}
			return $cache;
		} else {
			return false;
		}
	});

	// 数据库
	Provider::set('db', function($provider) {
		$db = new Mysql(
			array(
				'host' => $provider->config->database->host,
				'username' => $provider->config->database->username,
				'password' => $provider->config->database->password,
				'dbname' => $provider->config->database->dbname,
			)
		);

		//记录 SQL 语句
		if ($provider->config->log->groups->query_access->level != -1) {
			$eventsManager = new EventsManager();
			$eventsManager->attach('db', function($event, $connection) use ($provider) {
				if ($event->getType() == 'beforeQuery') {
					$provider->log['query_access']->info($connection->getSQLStatement());
				}
			});

			//设置事件管理器
			$db->setEventsManager($eventsManager);
		}

		return $db;
	});

	// 日志
	Provider::set('log', function($provider) {
		$log = new LogProvider();
		if ($provider->config->log->enable) {
			$logPath = APP_PATH . $provider->config->path->log . '/';
			foreach ($provider->config->log->groups as $name => $info) {
				$log[$name] = function() use($logPath, $name, $info) {
					$log = new Logger($logPath . $info['file']);
					$log->setLogLevel($info['level']);
					return $log;
				};
			}
			return $log;
		} else {
			return false;
		}
	});

	// 会话
	Provider::set('session', function($provider) {
		session_name($provider->config->session->name);
		session_save_path(APP_PATH . $provider->config->path->session);
		$session = new Session();
		$session->start();
		return $session;
	});

	// URL
	Provider::set('url', function($provider) {
		$url = new Url();
		$url->setBaseUri($provider->config->env->base_uri);
		return $url;
	});

	// 视图
	Provider::set('view', function() {
		$view = new ViewProvider();
		$view->setViewsDir(APP_PATH . 'views/');
		return $view;
	});

	// 验证
	Provider::set('validation', function() {
		$validation = new Validation();
		require APP_PATH . 'start/validation.php';
		return $validation;
	});

	/**
	 * 向应用程序注入服务项
	 */
	$app = Provider::get('app');

	$app['cache'] = function() {
		return Provider::get('cache');
	};
	$app['config'] = function() {
		return Provider::get('config');
	};
	$app['db'] = function() {
		return Provider::get('db');
	};
	$app['log'] = function() {
		return Provider::get('log');
	};
	$app['session'] = function() {
		return Provider::get('session');
	};
	$app['url'] = function() {
		return Provider::get('url');
	};
	$app['view'] = function() {
		return Provider::get('view');
	};
	$app['validation'] = function() {
		return Provider::get('validation');
	};

	// 404页面
	$app->notFound(function () use ($app) {
		$app->response->setStatusCode(404, "Not Found")->sendHeaders();
		echo 'Not Found';
	});

	//设置错误输出选项
	if (Provider::get('config')->env->testing) {
		ini_set('display_errors', 'On');
		error_reporting(E_ALL);
	} else {
		ini_set('display_errors', 'Off');
		error_reporting(0);
	}

	// 载入路由规则
	require APP_PATH . 'front_routes.php';
	require APP_PATH . 'api_routes.php';

	// 处理请求
	$app->handle();
} catch (Exception $e) {
	echo $e->getMessage();
}