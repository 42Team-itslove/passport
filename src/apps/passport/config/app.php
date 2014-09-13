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
 * Passport Application 配置文件
 *
 * 本文件存储着应用启动和运行时的必要配置
 */
return array(
	/**
	 * API配置
	 *
	 * authentication 对API开启HTTP基本认证, 此项设置仅为测试API时设置为false, 否者一律为true
	 */
	'api' => array(

		'authentication' => true

	),

	/**
	 * API认证用户配置
	 *
	 * 用户名以 username = password 的形式, 可自由添加
	 */
	'api_user' => array(

		'user' => '123456'

	),

	/**
	 * 缓存配置
	 *
	 * 本工程使用Memcache作为Key-Value缓存后端, 使用memcache拓展
	 *
	 * enable    是否开启缓存
	 * namespace Memcache键前缀, 用以区分不同应用
	 * servers   存储 host:port 形式的服务器配置数组
	 */
	'cache' => array(

		'enable'    => true,

		'namespace' => 'Itslove::Passport',

		'servers'   => array(

			'localhost:11211'

		)
	),

	/**
	 * 数据库配置
	 *
	 * host       数据库连接地址
	 * username   数据库用户名
	 * password   数据库密码
	 * dbname     数据库名称
	 */
	'database' => array(

		'host'     => 'localhost',

		'username' => 'root',

		'password' => 'root',

		'dbname'   => 'passport'

	),

	/**
	 * 应用环境配置
	 *
	 * base_uri   项目Web目录基础URI, 所有绝对URL根据此项配置生成
	 * testing    测试模式, 此项设置开启会导致项目输出调试信息和错误信息, 建立生产环境设置为false
	 */
	'env' => array(

		'base_uri' => '/',

		'testing'  => true

	),


	/**
	 * 日志配置
	 *
	 * api_error/api_access/cache_error/core_error 分别为API错误日志, API访问日志, 缓存错误日志, 应用核心错误日志, 数据查询日志
	 *
	 * enable    是否开启日志系统
	 * groups => file  为日志文件名
	 * groups => level 为日志级别 0-9(由高到低)
	 * 建议测试或开发环境设置为9或7(全开), 生产环境设置为6或5, 单独关闭日志请设置级别为-1
	 * 后缀标注*号的级别为本工程使用的级别
	 *
	 * SPECIAL   = 9
	 * CUSTOM    = 8
	 * DEBUG     = 7   *
	 * INFO      = 6   *
	 * NOTICE    = 5   *
	 * WARNING   = 4   *
	 * ERROR     = 3   *
	 * ALERT     = 2   *
	 * CRITICAL  = 1
	 * EMERGENCE = 0
	 * EMERGENCY = 0   *
	 *
	 */
	'log' => array(

		'enable'    => true,

		'groups'    => array(

			'api_error' => array(

				'file'  => 'api_error.log',

				'level' => 7

			),

			'api_access' => array(

				'file'  => 'api_access.log',

				'level' => 7

			),

			'cache_access' => array(

				'file'  => 'cache_access.log',

				'level' => 7

			),

			'core_error' => array(

				'file'  => 'core_error.log',

				'level' => 7

			),

			'query_access' => array(

				'file'  => 'query_access.log',

				'level' => 7

			)

		)

	),

	/**
	 * 在线用户配置
	 *
	 * default_valid_time = 7200      用户登录(非下次自动登陆)延续时间(秒)
	 * auto_valid_time    = 2592000   用户登录(下次自动登陆)延续时间(秒)
	 * last_active_time   = 300       用户活跃状态(在线状态)延续时间(秒), 以用户最后活动时间计算
	 * gc_frequency       = 0.01      用户在线表垃圾回收概率
	 */
	'online' => array(

		'default_valid_time' => 7200,

		'auto_valid_time'    => 2592000,

		'last_active_time'   => 300,

		'gc_frequency'       => 0.005,

	),

	/**
	 * 路径配置
	 *
	 * log        = storage/logs      应用日志存储路径
	 * session    = storage/sessions  Session会话存储路径
	 */
	'path' => array(

		'log'     => 'storage/logs',

		'session' => 'storage/sessions',

	),

	/**
	 * 会话配置
	 *
	 * name = ITEAMID 会话Cookie名称
	 */
	'session' => array(

		'name' => 'ITEAMID',

	)

);