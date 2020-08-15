<?php

/**
 * @package SMF Versatile Reactions
 * @version 1.0
 * @author Assadullah Shaikh a.k.a Decent_946 <asadullahshaikh20@gmail.com>
 * @copyright Copyright (c) 2020, The Versatile Pro
 * @license https://choosealicense.com/licenses/mpl-2.0/
 */

function template_main() {

	global $context, $txt;

	//load the language for strings..
	loadLanguage('vreactions');

	echo '
	<div id="admincenter">
	<div id="admin_main_section">
		<div id="live_news" class="floatleft">
			<div class="cat_bar">
				<h3 class="catbg">
					<span class="ie6_header floatleft">', $txt['vreactions_text_author'], '</span>
				</h3>
			</div>
			<div class="windowbg nopadding">
				<span class="topslice"><span></span></span>
				<div class="padding">
					<dl>
						<dt><strong>', $txt['vreactions_text_dev'], '</strong></dt>
						<dd>', $txt['vreactions_dev'], '</dd>
						<dt><strong>', $txt['vreactions_text_tools_used'], '</strong></dt>
						<dd>', $txt['vreactions_text_js'], '</dd>
						<dd>', $txt['vreactions_text_jquery'], '</dd>
						<dd>', $txt['vreactions_text_ajax'], '</dd>
						<dd>', $txt['vreactions_text_joypixels'], '</dd>
					</dl>
				</div>
				<span class="botslice"><span></span></span>
			</div>
		</div>
		<div id="supportVersionsTable" class="floatright">
			<div class="cat_bar">
				<h3 class="catbg">
				<span class="ie6_header floatleft">', $txt['vreactions_text_support'], '</span>
				</h3>
			</div>
			<div class="windowbg nopadding">
				<span class="topslice"><span></span></span>
				<div class="padding">
					<dl>
						<dt><strong>', $txt['vreactions_text_support_ver'], '</strong></dt>
						<dd>', $txt['vreactions_support_ver'], '</dd>
					</dl>
				</div>
				<span class="botslice"><span></span></span>
			</div>
		</div>
	</div>
</div>
<div id="admincenter">
	<div id="admin_main_section">
		<div class="cat_bar">
			<h3 class="catbg">
			<span class="ie6_header floatleft">', $txt['vreactions_text_add_features'], '</span>
			</h3>
		</div>
		<div class="windowbg nopadding">
			<span class="topslice"><span></span></span>
			<div class="content">
					<dl>
						<dt><strong>', $txt['vreactions_add_features_title'], '</strong></dt>
						<dd>', $txt['vreactions_add_features_desc'], '</dd>
					</dl>
			</div>
			<span class="botslice"><span></span></span>
		</div>
	</div>
</div>
	';
}


function template_add_new_reactions() {
	
	global $context, $scripturl, $txt;

	//load the language for strings..
	loadLanguage('vreactions');

	echo '
	<div id="admincenter">
		<form action="', $scripturl, '?action=admin;area=vreactions;sa=addsuccess" method="post" accept-charset="', $context['character_set'], '" name="emoji_form" id="emoji_form" enctype="multipart/form-data">
			<div id="admin_main_section">
				<div class="cat_bar">
					<h3 class="catbg">
						<span class="ie6_header floatleft">', $txt['vreactions_add_reaction'], '</span>
					</h3>
				</div>
				<div class="windowbg nopadding">
					<span class="topslice"><span></span></span>
					<div class="content">
						<fieldset id="ul_settings">
							<dl class="settings">
								<dt>
									<strong>', $txt['vreactions_upload'], '</strong><br />
									<span class="smalltext">', $txt['vreactions_upload_desc'], '</span>
								</dt>
								<dd>
									<input type="file" name="emoji_file" id="emoji_file" class="input_file" required/>
								</dd>
								<dt>
									<strong>', $txt['vreactions_desc'], '</strong>
								</dt>
								<dd>
									<input type="text" name="emoji_title" id="emoji_title" value="" class="input_text" required/>
								</dd>
							</dl>
							<input type="submit" value="', $txt['vreactions_text_upload'], '" name="emoji_upload" id="emoji_upload" class="button_submit">
						</fieldset>
					</div>
					<span class="botslice"><span></span></span>
				</div>
			</div>
		</form>
	</div>
	';
}

?>