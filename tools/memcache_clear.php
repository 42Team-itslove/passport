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
 * 清空Memcache全部缓存
 */
echo "Local Memcached: Clear\n\n";

if ( ! in_array('memcache', get_loaded_extensions())) {
	die('  Memcache Extension No Load');
}

$mem = new Memcache();
$mem->addserver('localhost', 11211);
if ($mem->flush()) {
	echo "  Success";
}
