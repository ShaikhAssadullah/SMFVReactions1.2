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


add_integration_function('integrate_pre_include', '$sourcedir/manageReactions.php');
add_integration_function('integrate_pre_include', '$sourcedir/subs-manageReactions.php');
add_integration_function('integrate_actions', 'addreactionactions');
add_integration_function('integrate_admin_areas', 'addreactionadminsection');
add_integration_function('integrate_load_theme', 'loadreactionscripts');

?>