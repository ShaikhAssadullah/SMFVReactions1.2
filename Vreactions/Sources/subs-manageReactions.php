<?php

/**
 * @package SMF Versatile Reactions
 * @version 1.0
 * @author Assadullah Shaikh a.k.a Decent_946 <asadullahshaikh20@gmail.com>
 * @copyright Copyright (c) 2020, The Versatile Pro
 * @license https://choosealicense.com/licenses/mpl-2.0/
 */

if (!defined('SMF'))
	die('Hacking attempt...');


/**
*Hooks Functions.
*/
function addreactionactions(&$actionArray)
{
	//an action to call for reactions
	$actionArray['callreactions'] = array('manageReactions.php', 'callReactions');
}

function addreactionadminsection(&$admin_areas) {

	global $txt;
	
	//load the language for strings..
	loadLanguage('vreactions');

	//set up admin area
	$admin_areas['layout']['areas']['vreactions'] = array (
		'label' => 'vreactions',
		'file' => 'manageReactions.php',
		'function' => 'manageReactions',
		'icon' => 'administration.gif',
		'subsections' => array(
			'general' => array($txt['vreactions_tabs_general'], 'manage_smileys'),
			'edit' => array($txt['vreactions_tabs_edit'], 'admin_forum'),
			'add' => array($txt['vreactions_tabs_add_new'], 'admin_forum'),
		),
	);
}

function loadreactionscripts() {

	global $context, $settings;

	// Load the JS and CSS in topics section.
	if (isset($_REQUEST['topic']))
	{
		$context['html_headers'] .= '
			<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
			<link rel="stylesheet" type="text/css" href="' .$settings['default_theme_url']. '/css/vreactions.css" />
			<script type="text/javascript" src="' .$settings['default_theme_url']. '/scripts/vreactions.js"></script>';
	}
}


/**
*Versatile Reaction Functions.
*
*	function to load the reactions in display.template.php
*	loadreactionemojis($msg_id, $member_id)
*
*	function that is called when an emoji is clicked. to add the reaction in db.
*	addreaction($msg_id, $emoji_id, $member_id)
*
*	This function is to get the name of user's who have reacted.
*	getwhoreacted($msg_id, $emoji_id)
*
*	This function isto get the reaction count.
*	function getreactioncount($msg_id, $emoji_id)
*
*	This is the function that catches the call returned from ajax and sends back the output.
*	callReactions()
*
*/

function loadreactionemojis($msg_id, $member_id) {

	global $context, $smcFunc, $db_prefix, $settings, $txt;

	//load the language for strings..
	loadLanguage('vreactions');

	//we want to load the data (emojis) from the database
	$request = $smcFunc['db_query']('',
		'SELECT * FROM {db_prefix}v_reactions_emoji',
		array(
		)
	);

	//fetching the rows.
	$num_rows = $smcFunc['db_num_rows']($request);

	//does the data exist? if no, return nothing..
	if($num_rows <= 0) return "";
	//if yes, load the emojis.
	else {
		//load the data in html layout.
		echo '<div class="versatile_reactions">';
		while ( $rows = $smcFunc['db_fetch_row']($request) ) {

			//$rows[0] == emoji_id -    $rows[1] == emoji_name   -  $rows[2] == emoji_title
			$emoji_format = 'gif'; //emoji format

			echo '
				<div class="emojis_wrapper windowbg2" id="emojis_wrapper-', $rows[0], '">
					<div class="emojis" id="emoji-', $rows[0], '-', $msg_id, '" onclick="loadreactions(', $msg_id, ', ', $rows[0], ',', $member_id, ', event);">
					<a href=""><img src="', $settings['default_images_url'], '/reactions/', $rows[1], '.', $emoji_format, '" alt="', $rows[2], '" title="', $rows[2], '"></a>
					</div>
					<div class="counts" id="counts-', $rows[0], '-', $msg_id, '">
					', getreactioncount($msg_id, $rows[0]), '
					</div>
				</div>
			';
		}
		//resetting the rows data..
		$smcFunc['db_data_seek']($request, 0);
		//setting up the div to view up the name of user's who have reacted.
		while ( $rows = $smcFunc['db_fetch_row']($request) ) {
			echo'
				<div class="who_reacted windowbg2" id="who_reacted-', $rows[0], '-', $msg_id, '">
				', getwhoreacted($msg_id, $rows[0]), '
				</div>
			';
		}
		echo '</div>';
	}

	//lets free the query
	$smcFunc['db_free_result']($request);
}


//this functions adds the record in the database
function addreaction($msg_id, $emoji_id, $member_id) {
	
	global $context, $db_prefix, $smcFunc, $txt;

	//load the language for strings..
	loadLanguage('vreactions');

	//is it really a member? or a guest/unknown?
	if(!$member_id || $member_id == 0 ) fatal_error($txt['vreactions_error_not_reg'], false);

	//getting the query with respective msg id and emoji id.
	$query = $smcFunc['db_query']('',
		'SELECT * FROM {db_prefix}v_reactions 
		WHERE msg_id = {int:id_msg} 
		AND emoji_id = {int:id_emoji}',
		array(
			'id_msg' => $msg_id,
			'id_emoji' => $emoji_id,
		)
	);

	//we want to check if the user has previously reacted to the msg with same emoji..
	$row = $smcFunc['db_fetch_assoc']($query);
	$who_reacts = explode(",", $row['members_id']);

	for ($x=0; $x<sizeof($who_reacts); $x++) {

		if($member_id == $who_reacts[$x]) fatal_error($txt['vreactions_error_already_reacted'], false);
		else continue;
	}

	//We want to check if someone else has previously reacted to that msg with same emoji. We'll just update then..
	$check = $smcFunc['db_num_rows']($query);
	if ( $check > 0 ) {

		//now, we wanna update no of reactions and also add the user id in database so that we can see who reacted.
		$request = $smcFunc['db_query']('',
			'UPDATE {db_prefix}v_reactions 
			SET members_id = CONCAT_WS(",",members_id,{int:id_member}), no_of_reacts = no_of_reacts+1 
			WHERE msg_id = {int:id_msg} 
			AND emoji_id = {int:id_emoji} ',
			array(
				'id_member' => $member_id,
				'id_msg' => $msg_id,
				'id_emoji' => $emoji_id,
			)
		);

		//announce if successfull/failed.
		if ($request) return $txt['vreactions_reacted'];
		else return $txt['vreactions_failed'];
	}
	else {
		//if the query is empty, that means no one has voted to that msg with selected emoji. We'll then insert a new record.
		$smcFunc['db_insert']('insert',
			'{db_prefix}v_reactions',
			array(
				'msg_id' => 'int', 'emoji_id' => 'int', 'members_id' => 'text', 'no_of_reacts' => 'int',
			),
			array(
				$msg_id, $emoji_id, $member_id, 1,
			),
			array(
				'msg_id', 'emoji_id',
			)
		);

		return $txt['vreactions_added'];
	}

	//lets free the query
	$smcFunc['db_free_result']($query);

}



//this is to get the name of user's who have reacted.
function getwhoreacted($msg_id, $emoji_id) {

	global $context, $smcFunc, $db_prefix, $txt;

	//load the language for strings..
	loadLanguage('vreactions');

	$query = $smcFunc['db_query']('',
		'SELECT * FROM {db_prefix}v_reactions 
		WHERE msg_id = {int:id_msg} 
		AND emoji_id = {int:id_emoji} LIMIT 1',
		array(
			'id_msg' => $msg_id,
			'id_emoji' => $emoji_id,
		)
	);


	//we want to check if nobody has yet reacted..
	$check = $smcFunc['db_num_rows']($query);
	if ( $check == 0 ) return $txt['vreactions_empty'];
	//we want to check if someone or more have reacted. Display there names.
	else {

		$row = $smcFunc['db_fetch_assoc']($query);
		$who_reacts = explode(",", $row['members_id']);

		$emoji_query = $smcFunc['db_query']('',
			'SELECT * FROM {db_prefix}v_reactions_emoji 
			WHERE emoji_id = {int:id_emoji} LIMIT 1',
			array(
				'id_emoji' => $emoji_id,
			)
		);

		$emoji_data = $smcFunc['db_fetch_assoc']($emoji_query);
		$emoji_name = $emoji_data['emoji_name'];

		$data = $txt['vreactions_members_reacted']. ' ' .$emoji_name. ':';
		$data .= '<table><tr class="windowbg2">';
		for($x=0; $x<sizeof($who_reacts); $x++ ) {

			$request = $smcFunc['db_query']('',
				'SELECT real_name FROM {db_prefix}members 
				WHERE id_member = {int:member_id}',
				array(
					'member_id' => (int)$who_reacts[$x],
				)
			);

			$get = $smcFunc['db_fetch_assoc']($request);
			$realname = $get['real_name'];
			$data .= '<td class="centertext">' .$realname. ',</td>';
			//lets free the query..
			$smcFunc['db_free_result']($request);
		}
		$data .= '</tr></table>';
		return $data;

		//lets free the query..
		$smcFunc['db_free_result']($emoji_query);
	}
	
	//lets free the queries.
	$smcFunc['db_free_result']($query);
}


//this is the function to get the reaction count.
function getreactioncount($msg_id, $emoji_id) {

	global $smcFunc, $db_prefix;

	//getting the data from the database...
	$vquery = $smcFunc['db_query']('',
			'SELECT * FROM {db_prefix}v_reactions 
			WHERE msg_id = {int:id_msg} 
			AND emoji_id = {int:id_emoji} LIMIT 1',
			array(
				'id_msg' => $msg_id,
				'id_emoji' => $emoji_id,
			)
		);

	//checking if data even existss,
	$check = $smcFunc['db_num_rows']($vquery);
	if ( $check == 0 ) return "0";
	//print if the record exists.
	else {
		$row = $smcFunc['db_fetch_assoc']($vquery);
		$no_of_reacts = (int)$row['no_of_reacts'];

		return $no_of_reacts;
	}
	$smcFunc['db_free_result']($vquery);
}


//This is the function that catches the call returned from ajax and sends back the output.
function callReactions() {

	global $user_info, $txt;

	//load the language for strings..
	loadLanguage('vreactions');
	

	//verify POST data for security reasons.
	$request = file_get_contents('php://input');
	if ($request === FALSE || empty($request)) fatal_error($txt['vreactions_error_no_data']);

	if (isset($_POST['msg_id'])) {

		addreaction($_POST['msg_id'], $_POST['emoji_id'], $user_info['id']);

		$no_of_reactions = getreactioncount($_POST['msg_id'], $_POST['emoji_id']);
		$who_reacted = getwhoreacted($_POST['msg_id'], $_POST['emoji_id']);

		$return_arr = array('no_of_reactions' => $no_of_reactions, 'who_reacted' => $who_reacted );

		header('Content-type: application/json');
		echo json_encode($return_arr);
	}

	die();
}

?>