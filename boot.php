<?php
/**
 * Bootstrap File for Trello Export
 */

define('TRELLO_KEY', getenv('TRELLO_KEY'));
define('TRELLO_TOK', getenv('TRELLO_TOKEN'));

define('EXPORT_PATH', sprintf('%s/trello-export-data', __DIR__));
define('EXPORT_PATH_BOARD', sprintf('%s/board', EXPORT_PATH));
define('EXPORT_PATH_LIST', sprintf('%s/list', EXPORT_PATH));
define('EXPORT_PATH_CARD', sprintf('%s/card', EXPORT_PATH));
define('EXPORT_PATH_USER', sprintf('%s/member', EXPORT_PATH));

_mkdir(EXPORT_PATH);
_mkdir(EXPORT_PATH_BOARD);
_mkdir(EXPORT_PATH_LIST);
_mkdir(EXPORT_PATH_CARD);
_mkdir(EXPORT_PATH_USER);


function _curl_del($url)
{
	echo "_curl_del($url)\n";

	$url = sprintf('%s?key=%s&token=%s&fields=all', $url, TRELLO_KEY, TRELLO_TOK);
	$req = curl_init($url);
	curl_setopt($req, CURLOPT_BINARYTRANSFER, true);
	curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($req, CURLOPT_CUSTOMREQUEST, 'DELETE');
	$ret = curl_exec($req);
	$inf = curl_getinfo($req);
	curl_close($req);
	sleep(1);
}


function _curl_get($url)
{
	usleep(50000);

	//$url = parse_url($url);
	//print_r($url);
	//$url['query'] = parse_str($url['query']);
	//$url['query']['key'] = TRELLO_KEY;
	//$url['query']['token'] = TRELLO_TOK;
	//$url = url_assemble($url);
	$url = sprintf('%s?key=%s&token=%s&fields=all', $url, TRELLO_KEY, TRELLO_TOK);
	$req = curl_init($url);
	// $uri->replaceQueryParam('key', TRELLO_KEY);
	// $uri->replaceQueryParam('token', TRELLO_TOK);
	curl_setopt($req, CURLOPT_BINARYTRANSFER, true);
	curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
	$ret = curl_exec($req);
	$inf = curl_getinfo($req);
	curl_close($req);

	// Proceess
	$type = strtok($inf['content_type'], ';');
	$type = strtolower($type);
	switch ($type) {
		case 'application/json':
			$ret = json_decode($ret, true);
		break;
	}

	return $ret;
}

function _json_encode($x)
{
	return json_encode($x, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

function _mkdir($p)
{
	if (!is_dir($p)) {
		mkdir($p, 0755, true);
	}
}
