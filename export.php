#!/usr/bin/php
<?php
/**
 * Exports All of Trello
 * curl 'https://api.trello.com/1/members/me/boards?key={yourKey}&token={yourToken}'
 * pear install Mime_Type
 */

require_once(__DIR__ . '/boot.php');

$b_list = []; // Board List
$l_list = []; // List List
$c_list = []; // Card List
$m_list = []; // Members List


// Boards
echo "Boards...\n";
$b_list = _curl_get('https://api.trello.com/1/members/me/boards');
foreach ($b_list as $b) {

	$b_file = sprintf('%s/%s.json', EXPORT_PATH_BOARD, $b['id']);
	$b_data = _json_encode($b);
	file_put_contents($b_file, $b_data);

	foreach ($b['memberships'] as $m) {
		$m_list[ $m['idMember'] ] = $m;
	}
}


// Boards/Lists
echo "Boards/Lists...\n";
foreach ($b_list as $b) {

	// foreach_list_on_board($p_u0, $proj, $b);
	$x_list = _curl_get(sprintf('https://api.trello.com/1/boards/%s/lists', $b['id']));
	usort($x_list, function($a, $b) {
		return ($a['pos'] > $b['pos']);
	});

	foreach ($x_list as $l0) {

		echo "Board: {$b['name']}; List: {$l0['name']}\n";

		$l_file = sprintf('%s/%s.json', EXPORT_PATH_LIST, $l0['id']);
		$l_data = _json_encode($l0);
		file_put_contents($l_file, $l_data);

		$l_list[$l0['id']] = $l0;
	}
}

// Cards on Lists on Boards
echo "Boards/Cards...\n";
foreach ($l_list as $l0) {

	echo "Board: {$l0['idBoard']}; List: {$l0['id']}\n";

	$c_list = _curl_get(sprintf('https://api.trello.com/1/lists/%s/cards', $l0['id']));

	// Fix Time
	foreach ($c_list as $c_idx => $c) {
		$c_list[$c_idx]['created_at'] = hexdec(substr($c['id'], 0, 8));
	}

	// @todo Sort by 'pos' attribute
	usort($c_list, function($a, $b) {
		return ($a['created_at'] > $b['created_at']);
	});

	foreach ($c_list as $c0) {

		$c_file = sprintf('%s/%s.json', EXPORT_PATH_CARD, $c0['id']);
		$c_data = _json_encode($c0);
		file_put_contents($c_file, $c_data);

		$a_peek = $c0['badges']['attachments'];
		if ($a_peek > 0) {

			$a_list = _curl_get(sprintf('https://api.trello.com/1/cards/%s/attachments', $c0['id']));
			if (count($a_list)) {

				$a1_path = sprintf('%s/%s', EXPORT_PATH_CARD, $c0['id']);
				_mkdir($a1_path);

				foreach ($a_list as $a0) {

					// Fix or Skip Card Attachments?
					if (empty($a0['isUpload'])
						&& empty($a0['mimeType'])
						&& preg_match('/^https:\/\/trello\.com\/c\/\w+/', $a0['name'])) {

						$a0['mimeType'] = 'card.json';
						continue;
					}

					$a0_file = sprintf('%s/%s.json', $a1_path, $a0['id']);
					$a0_data = _json_encode($a0);
					file_put_contents($a0_file, $a0_data);

					// Attachment Asset
					// Some have missing mime type but are valid upload :(
					$a1_type = basename($a0['mimeType']);
					$a1_file = sprintf('%s/%s.%s', $a1_path, $a0['id'], $a1_type);
					$a1_data = _curl_get($a0['url']);
					file_put_contents($a1_file, $a1_data);

					// Save Previews?
					// $o_list = $a0['preview'];
					unset($a0['previews']);

					// var_dump($a0);
					// exit;
				}
			}
		}

		// What are these?
		//   $url = sprintf('https://api.trello.com/1/cards/%s/actions', $c['id']);
		//   $a_list = t_get($url);


		// DELETE CARD
		// CURL -X DELETE? 'https://api.trello.com/1/cards/{id}'
		// t_del(sprintf('https://api.trello.com/1/cards/%s', $c0['id']));


	}

}


// Pull Member Data
echo "Members...\n";
foreach ($m_list as $mk => $m0) {

  // https://trello.com/docs/api/member/index.html#get-1-members-idmember-or-username-boards
  // print_r($m0);
	$m1 = _curl_get(sprintf('https://api.trello.com/1/members/%s', $m0['idMember']));
  // print_r($m1);

	$m_list[$mk] = $m1;

	$m_file = sprintf('%s/%s.json', EXPORT_PATH_USER, $m1['id']);
	$m_data = _json_encode($m1);
	file_put_contents($m_file, $m_data);

}
