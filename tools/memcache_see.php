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
 * 显示本地服务器Memcache中的所有Key
 */
echo "Local Memcached: See\n\n";

if ( ! in_array('memcache', get_loaded_extensions())) {
	die('  Memcache Extension No Load');
}

$mem = new Memcache();
$mem->addserver('localhost', 11211, 1, 1, 300);
$items = $mem->getExtendedStats('items')['localhost:11211']['items'];

$keys_container = array();

if ( ! is_array($items) || empty($items)) {
	echo "There are no any key \n\n";
	exit();
}

foreach ($items as $key => $values) {
	$id = $key;
	$str = $mem->getExtendedStats("cachedump", $id, 0);
	$line = $str['localhost:11211'];
	if(is_array($line) && count($line) > 0)
	{
		foreach(array_keys($line) as $key)
		{
			$keys_container[] = $key;
		}
	}
}

$mem->close();

$keys_count = count($keys_container);

echo "  Kys Count: {$keys_count}\n\n";

foreach ($keys_container as $key) {
	echo '- ' . $key . "\n";
}


