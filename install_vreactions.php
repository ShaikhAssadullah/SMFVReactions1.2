<?php
/**
 * @package SMF Versatile Reactions
 * @version 1.0
 * @author Assadullah Shaikh a.k.a Decent_946 <asadullahshaikh20@gmail.com>
 * @copyright Copyright (c) 2020, The Versatile Pro
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

	if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
		require_once(dirname(__FILE__) . '/SSI.php');

	elseif (!defined('SMF'))
		exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');


	global $smcFunc, $db_prefix;
	
	db_extend('packages'); db_extend('extra');

	//we want to create a table to save the records of reactions. Such as no of reactions, who reacted, which emoji was reacted..
	$smcFunc['db_create_table']('{db_prefix}v_reactions', 
		array(
			array('name' => 'msg_id', 'type' => 'int', 'null' => false, 'default' => 0),
			array('name' => 'emoji_id', 'type' => 'int', 'null' => false, 'default' => 0),
			array('name' => 'members_id', 'type' => 'text', 'null' => false),
			array('name' => 'no_of_reacts', 'type' => 'int', 'null' => false, 'default' => 0),
		),
		array(
			array('type' => 'primary', 'columns' => array('msg_id', 'emoji_id')),
		)
	);

	//we want to create a table to store and fetch reaction emojis.
	$smcFunc['db_create_table']('{db_prefix}v_reactions_emoji',
		array(
			array('name' => 'emoji_id', 'type' => 'int', 'null' => false, 'default' => 0, 'auto' => true),
			array('name' => 'emoji_name', 'type' => 'varchar', 'size' => 15, 'null' => false),
			array('name' => 'emoji_title', 'type' => 'varchar', 'size' => 15, 'null' => false),
		),
		array(
			array('type' => 'primary', 'columns' => array('emoji_id')),
			array('columns' => array('emoji_name')),
		)
	);

	//the emoji's table is empty. so lets add some emojis in it to be loaded.
	$reactions = array('friendly', 'funny', 'informative', 'agree', 'disagree', 'pwnt', 'like', 'dislike', 'late');
	$reactions_id = 1;

	for($times = 0; $times<sizeof($reactions); $times++) {
		$smcFunc['db_insert']('insert',
			'{db_prefix}v_reactions_emoji',
			array(
				'emoji_name' => 'string', 'emoji_title' => 'string',
			),
			array(
				$reactions[$times], $reactions[$times],
			),
			array(
				'emoji_name', 'emoji_title',
			)
		);
	}

	if (!isset($modSettings['vreactions_revert_enabled'])) {
		
		updateSettings(
			array(
			'vreactions_revert_enabled' => 0,
			)
		);
	}
?>