<?php
/**
 * Passport
 *
 * @author    Kezhao Pan <panlatent@gmail.com>
 * @link      https://github.com/42Team-itslove/passport
 * @license   https://github.com/42Team-itslove/passport/blob/master/LICENSE
 * @copyright (c) 2003 - 2014 42Team
 */

namespace Itslove\Passport\Core;

/**
 * 视图提供者类
 *
 * @package Itslove\Passport\Helper
 */
class ViewProvider {

	/**
	 * 视图文件存放路径
	 *
	 * @var string
	 */
	protected $viewsDir;

	/**
	 * 该数组保存视图列表
	 *
	 * @var array
	 */
	protected $views = array();

	/**
	 * 该数组保存视图变量
	 *
	 * @var array
	 */
	protected $vars = array();

	/**
	 * 设置视图存放路径
	 *
	 * @param string $dir  视图存放路径
	 */
	public function setViewsDir($dir)
	{
		$this->viewsDir = $dir;
	}

	/**
	 * 载入视图
	 *
	 * @param string|array $names  视图文件名或由视图文件名构成的数组
	 * @param array        $vars   视图变量数组
	 * @throws Exception
	 */
	public function load($names, $vars = array())
	{	
		if (is_string($names)) {
			$names = array($names);
		}

		foreach ($names as $name) {
			$filename = $this->viewsDir.$name.'.php';
			if ( ! file_exists($filename)) {
				throw new Exception("{$filename} view file does not exist");
			}
			$this->views[] = $filename;
		}

		$this->vars = array_merge($this->vars, $vars);
	}

	/**
	 * 为视图变量赋值
	 *
	 * @param string $varName  视图变量名
	 * @param mixed  $value    视图变量值
	 */
	public function assign($varName, $value)
	{
		$this->vars[$varName] = $value;
	}

	/**
	 * 渲染并显示视图
	 */
	public function show()
	{
		extract($this->vars);
		foreach ($this->views as $filename) {
			/** @noinspection PhpIncludeInspection */
			require $filename;
		}
	}

	/**
	 * 魔术方法, 用以视图文件访问应用对象及其功能
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		static $app = false;
		if ( ! $app) {
			$app = Provider::get('app');
		}
		return $app[$name];
	}

}