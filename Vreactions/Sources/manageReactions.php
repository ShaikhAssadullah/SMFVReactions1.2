<?php

/**
 * @package SMF Versatile Reactions
 * @version 1.0
 * @author Assadullah Shaikh a.k.a Decent_946 <asadullahshaikh20@gmail.com>
 * @copyright Copyright (c) 2020, The Versatile Pro
 * @license https://choosealicense.com/licenses/mpl-2.0/
 *
 *
 *	This file has the functions of admin template.
 *
 *	It loads the main templates and handles sub actions.
 *	manageReactions()
 *
 *	Function to load template of Add Reactions section.
 *	addnewReaction()
 *
 *	Function that is called upon submitting of add reaction's section.
 *	addsuccessReaction()
 *
 *
 *	Function to delete a reaction from db and images file.
 *	deleteReaction()
 *
 *	Function to load general section.
 *	generalReactions()
 *
 *  Function to load the Edit Reaction's section that contains the list of rection emojis.
 *	editReactions()
 *
 *	Function to get reaction emoji's in the list.
 *	getReactionEmojis()
 *
 *	Function to get Reaction Emoji's count.
 *  countReactionEmojis()
 *
 *
 */

if (!defined('SMF'))
	die('Hacking attempt...');


//this is the function to load admin template.
function manageReactions()
{
	global $context, $txt;

	//lets check permisionss first..
	isAllowedTo(array('manage_smileys', 'admin_forum'));

	//here, we create the subsections..
	$subActions = array(
		'general' => array('generalReactions', 'manage_smileys'),
		'edit' => array('editReactions', 'admin_forum'),
		'add' => array('addnewReaction', 'admin_forum'),
		'delete' => array('deleteReaction', 'admin_forum'),
		'addsuccess' => array('addsuccessReaction', 'admin_forum'),
	);


	$_REQUEST['sa'] = ((isset($_REQUEST['sa']) && !empty($_REQUEST['sa'])) ? $_REQUEST['sa'] : 'general');

	//are the permissions ok?
	isAllowedTo($subActions[$_REQUEST['sa']][1]);

	//load the language for strings..
	loadLanguage('vreactions');

	//set up the title
	$context['page_title'] = $txt['vreactions_title'];

	//set up the tabs data. i.e tab name and description..
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['vreactions_title'],
		'help' => '',
		'description' => $txt['vreactions_tabs_general_desc'],
		'tabs' => array(
			'general' => array(),
			'edit' => array(
				'description' => $txt['vreactions_tabs_edit_desc'],
			),
			'add' => array(
				'description' => $txt['vreactions_tabs_add_new_desc'],
			),
		),
	);

	// Force the right area...
	if ($_REQUEST['sa'] == 'edit') {
		$context[$context['admin_menu_name']]['current_subsection'] = 'edit';
	}
	else if ($_REQUEST['sa'] == 'general') {
		$context[$context['admin_menu_name']]['current_subsection'] = 'general';
	}
	else if ($_REQUEST['sa'] == 'add') {
		$context[$context['admin_menu_name']]['current_subsection'] = 'add';
	}
	

	$subActions[$_REQUEST['sa']][0]();
}



function addnewReaction() {

	global $context, $txt;

	//load the required template.
	loadTemplate('manageReactions');

	//load the language for strings..
	loadLanguage('vreactions');

	$context['page_title'] = $txt['vreactions_tabs_add_new'];

	//load the required template
	$context['sub_template'] = 'add_new_reactions';

}

function addsuccessReaction() {

	global $context, $smcFunc, $db_prefix, $settings, $boarddir, $txt;

	//load the language for strings..
	loadLanguage('vreactions');

	//set the page title
	$context['page_title'] = $txt['vreactions_tabs_add_success'];

	// If nothing was chosen to delete (shouldn't happen, but meh)
	if ( (!isset($_POST['emoji_title'])) || (!isset($_FILES['emoji_file'])) ) fatal_error($txt['vreactions_error_no_data']);

	//defining allowed types and some disabled files for security.
	$allowedTypes = array('gif');
	$disabledFiles = array('con', 'com1', 'com2', 'com3', 'com4', 'prn', 'aux', 'lpt1', '.htaccess', 'index.php');
	$allok = true;

	// Sorry, no spaces, dots, or anything else but letters allowed.
	$_FILES['emoji_file']['name'] = preg_replace(array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'), array('_', '.', ''), $_FILES['emoji_file']['name']);

	// We only allow image files - it's THAT simple - no messing around here...
	if (!in_array(strtolower(substr(strrchr($_FILES['emoji_file']['name'], '.'), 1)), $allowedTypes)) {
		fatal_error($txt['vreactions_error_invalid_image']);
		$allok = false;
	}

	// We only need the filename...
	$emojiName = basename($_FILES['emoji_file']['name']);
	$explode = explode(".", $emojiName);
	$emoji_name_without_ext = $explode[0];

	// Make sure they aren't trying to upload a nasty file - for their own good here!
	if (in_array(strtolower($emojiName), $disabledFiles)) {
		fatal_error($txt['vreactions_error_invalid_file']);
		$allok = false;
	}

	//so, we have a proper image now. Defining it's upload directroy.
	$emoji_loc = $boarddir. '/Themes/default/images/reactions/' .$emojiName;

	//oh oh.. we need to check if it exists previously..
	if (file_exists($emoji_loc)){
		fatal_error($txt['vreactions_error_file_exists']);	
		$allok = false;
	} 

	//so all is okay. let's upload the image and insert it in database as well.
	if ($allok === true ) {
		//so everything is fine now. Just upload the file.
		move_uploaded_file($_FILES['emoji_file']['tmp_name'], $emoji_loc);
		@chmod($emoji_loc, 0644);

		$smcFunc['db_insert']('insert',
			'{db_prefix}v_reactions_emoji',
			array(
				'emoji_name' => 'string', 'emoji_title' => 'string',
			),
			array(
				$emoji_name_without_ext, $_POST['emoji_title'],
			),
			array(
				'emoji_name', 'emoji_title',
			)
		);
	}

	redirectexit('action=admin;area=vreactions;sa=edit;added;emoji=' .$emoji_name_without_ext);
}

function deleteReaction() {

	global $context, $smcFunc, $db_prefix, $boarddir, $txt;

	//load the language for strings..
	loadLanguage('vreactions');

	$context['page_title'] = $txt['vreactions_tabs_delete'];

	// If nothing was chosen to delete (shouldn't happen, but meh)
		if (!isset($_REQUEST['delete_emoji_id']) || !isset($_REQUEST['delete_emoji_name'])) 
			fatal_error($txt['vreactions_error_no_data']);

	//so, we have a proper image now. Defining it's upload directroy.
	$emoji_loc = $boarddir. '/Themes/default/images/reactions/' .$_REQUEST['delete_emoji_name']. '.gif';

	//we need to inform if the file doesn't exist.
	$query = $smcFunc['db_query']('',
		'SELECT emoji_id FROM {db_prefix}v_reactions_emoji 
		WHERE emoji_id = {int:id_emoji} 
		OR emoji_name ={string:name_emoji}',
			array(
				'id_emoji' => $_REQUEST['delete_emoji_id'],
				'name_emoji' => $_REQUEST['delete_emoji_name'],
			)
	);

	if (!file_exists($emoji_loc) && (!$query)) {

		$smcFunc['db_free_result']($query);
		fatal_error( $txt['vreactions_error_file_not_found'], '- path:'.$emoji_loc. ' and ID ' .$_REQUEST['delete_emoji_id']);
	}
	else if (file_exists($emoji_loc) && (!$query)) {

		unlink($emoji_loc);
	}
	else if (!file_exists($emoji_loc) && ($query)) {

		$smcFunc['db_query']('',
			'DELETE FROM {db_prefix}v_reactions_emoji
			WHERE emoji_id = {int:id_emoji}',
			array(
				'id_emoji' => $_REQUEST['delete_emoji_id'],
			)
		);
	}
	else {
		//if file is present, delete from DB and from images directory.
		$smcFunc['db_query']('',
			'DELETE FROM {db_prefix}v_reactions_emoji
			WHERE emoji_id = {int:id_emoji}',
			array(
				'id_emoji' => $_REQUEST['delete_emoji_id'],
			)
		);
		
		unlink($emoji_loc);
	}


	$smcFunc['db_free_result']($query);
	//we don't want the user to stay on this action.. right?
	redirectexit('action=admin;area=vreactions;sa=edit;deleted;id=' .$_REQUEST['delete_emoji_id']);
}

function generalReactions() {

	//load the required template.
	loadTemplate('manageReactions');

}

function editReactions() {

	global $context, $sourcedir, $txt;

	//load the language for strings..
	loadLanguage('vreactions');

	//set up the title
	$context['page_title'] = $txt['vreactions_tabs_edit'];

	// We need this files
	require_once($sourcedir . '/Subs-List.php');

	//okay, set upt the table to display the reaction smiley's data..
	$listOptions = array(
		'id' => 'vreactions',
		'title' => $txt['vreactions_tabs_edit_list'],
		'items_per_page' => 15,
		'base_href' => '?action=admin;area=vreactions;sa=edit',
		'default_sort_col' => 'emoji_id',
		'get_items' => array(
			'function' => 'getReactionEmojis',
		),
		'get_count' => array(
			'function' => 'countReactionEmojis',
		),
		'no_items_label' => $txt['vreactions_error_no_reactions'],
		'no_items_align' => 'center',
		'columns' => array(
			'emoji_id' => array(
				'header' => array(
					'value' => $txt['vreactions_emoji_id'],
					'class' => 'first_th',
				),
				'data' => array(
					'db' => 'emoji_id',
					'class' => 'centertext',
				),
				'sort' => array(
					'default' => 'emoji_id DESC',
					'reverse' => 'emoji_id',
				),
			),
			'emoji_img' => array(
				'header' => array(
					'value' => $txt['vreactions_emoji'],
					'class' => 'centertext',
				),
				'data' => array(
					'function' => create_function('$row', 
						'global $settings; 
						return (\'<img width="26px" height="26px" src="\'. $settings[\'default_images_url\']. \'/reactions/\'. $row[\'emoji_name\']. \'.gif" alt="\'. $row[\'emoji_title\']. \'" title="\'. $row[\'emoji_title\']. \'" />\');'
					),
					'class' => 'centertext',
				),
			),
			'emoji_name' => array(
				'header' => array(
					'value' => $txt['vreactions_emoji_name'],
					'class' => 'centertext',
				),
				'data' => array(
					'db' => 'emoji_name',
					'class' => 'centertext',
				),
			),
			'emoji_title' => array(
				'header' => array(
					'value' => $txt['vreactions_emoji_desc'],
					'class' => 'centertext',
				),
				'data' => array(
					'db' => 'emoji_title',
					'class' => 'centertext',
				),
			),
			'emoji_delete' => array(
				'header' => array(
					'value' => $txt['vreactions_emoji_delete'],
					'class' => 'last_th',
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<input type="submit" name="delete" value="Delete" class="button_submit" onclick="confirm(\'' .(string)$txt['vreactions_delete_confirm']. '\');">
						<input type="hidden" name="delete_emoji_id" value="%1d">
						<input type="hidden" name="delete_emoji_name" value="%s">',
						'params' => array(
							'emoji_id' => false,
							'emoji_name' => false,
						),
					),
					'class' => 'centertext',
				),
			),
		),
		'form' => array(
			'href' => '?action=admin;area=vreactions;sa=delete',
		),
	);

	// Let's finishem
	createList($listOptions);
	$context['sub_template'] = 'show_list';
	$context['default_list'] = 'vreactions';

}


function getReactionEmojis() {

	global $context, $smcFunc, $db_prefix;

	//get the emojis data for the list above.
	$request = $smcFunc['db_query']('',
		'SELECT * FROM {db_prefix}v_reactions_emoji'
	);

	// Return the data
	$context['reactions_list'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['reactions_list'][] = $row;
		$smcFunc['db_free_result']($request);

	return $context['reactions_list'];
}


function countReactionEmojis() {

	global $smcFunc, $db_prefix;

		// Count the items for the list above.
		$items = $smcFunc['db_query']('', '
			SELECT emoji_id
			FROM {db_prefix}v_reactions_emoji'
		);

		return $smcFunc['db_num_rows']($items);	
		$smcFunc['db_free_result']($items);
}

?>
