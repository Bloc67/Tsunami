<?php

/*	@ Bloc 2019										*/
/*	@	SMF 2.0.x										*/

// Initialize the template... mainly little settings.
function template_init()
{
	global $context, $settings, $options, $txt;

	$settings['use_default_images'] = 'never';
	$settings['doctype'] = 'xhtml';
	$settings['theme_version'] = '2.0';
	$settings['use_tabs'] = true;
	$settings['use_buttons'] = true;
	$settings['separate_sticky_lock'] = true;
	$settings['strict_doctype'] = false;
	$settings['message_index_preview'] = false;
	$settings['require_theme_strings'] = true;
	$settings['show_member_bar'] = true;
}

// The main sub template above the content.
function template_html_above()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	loadtemplate('Common');

	// Show right to left and the character set for ease of translating.
	echo '
<!DOCTYPE html>
<html', $context['right_to_left'] ? ' dir="rtl"' : '', '>
<head>
	<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/index', $context['theme_variant'], '.css?v2" />';

	// RTL languages require an additional stylesheet.
	if ($context['right_to_left'])
		echo '
	<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/rtl.css" />';

	// Here comes the JavaScript bits!
	echo '
	<script type="text/javascript" src="', $settings['default_theme_url'], '/scripts/script.js?fin20"></script>
	<script type="text/javascript" src="', $settings['theme_url'], '/scripts/theme.js?fin20"></script>
	<script type="text/javascript"><!-- // --><![CDATA[
		var smf_theme_url = "', $settings['theme_url'], '";
		var smf_default_theme_url = "', $settings['default_theme_url'], '";
		var smf_images_url = "', $settings['images_url'], '";
		var smf_scripturl = "', $scripturl, '";
		var smf_iso_case_folding = ', $context['server']['iso_case_folding'] ? 'true' : 'false', ';
		var smf_charset = "', $context['character_set'], '";', $context['show_pm_popup'] ? '
		var fPmPopup = function ()
		{
			if (confirm("' . $txt['show_personal_messages'] . '"))
				window.open(smf_prepareScriptUrl(smf_scripturl) + "action=pm");
		}
		addLoadEvent(fPmPopup);' : '', '
		var ajax_notification_text = "', $txt['ajax_in_progress'], '";
		var ajax_notification_cancel_text = "', $txt['modify_cancel'], '";
	// ]]></script>';

	echo '
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="Content-Type" content="text/html; charset=', $context['character_set'], '" />
	<meta name="description" content="', $context['page_title_html_safe'], '" />', !empty($context['meta_keywords']) ? '
	<meta name="keywords" content="' . $context['meta_keywords'] . '" />' : '', '
	<title>', $context['page_title_html_safe'], '</title>';

	// Please don't index these Mr Robot.
	if (!empty($context['robot_no_index']))
		echo '
	<meta name="robots" content="noindex" />';

	// Present a canonical url for search engines to prevent duplicate content in their indices.
	if (!empty($context['canonical_url']))
		echo '
	<link rel="canonical" href="', $context['canonical_url'], '" />';

	// Show all the relative links, such as help, search, contents, and the like.
	echo '
	<link rel="help" href="', $scripturl, '?action=help" />
	<link rel="search" href="', $scripturl, '?action=search" />
	<link rel="contents" href="', $scripturl, '" />';

	// If RSS feeds are enabled, advertise the presence of one.
	if (!empty($modSettings['xmlnews_enable']) && (!empty($modSettings['allow_guestAccess']) || $context['user']['is_logged']))
		echo '
	<link rel="alternate" type="application/rss+xml" title="', $context['forum_name_html_safe'], ' - ', $txt['rss'], '" href="', $scripturl, '?type=rss;action=.xml" />';

	// If we're viewing a topic, these should be the previous and next topics, respectively.
	if (!empty($context['current_topic']))
		echo '
	<link rel="prev" href="', $scripturl, '?topic=', $context['current_topic'], '.0;prev_next=prev" />
	<link rel="next" href="', $scripturl, '?topic=', $context['current_topic'], '.0;prev_next=next" />';

	// If we're in a board, or a topic for that matter, the index will be the board's index.
	if (!empty($context['current_board']))
		echo '
	<link rel="index" href="', $scripturl, '?board=', $context['current_board'], '.0" />';

	// Output any remaining HTML headers. (from mods, maybe?)
	echo $context['html_headers'];

	if(!empty($settings['mycss']))
		echo '
	<style>' , $settings['mycss'] , '</style>';
	
	echo '
</head>
<body' , function_exists('template_body_id') ? template_body_id() : ''   , '>';
}

function template_body_above()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
<section id="headersection">
	<header id="topheader">
		<h1 class="forumtitle">
			<a href="', $scripturl, '">' , $context['forum_name'] , '</a>
		</h1>
		<div id="h_menu">
			' , template_menu() , '
			<nav id="mobilnav">
				<input type="checkbox" id="mobilmeny" />
				<label id="mobilmeny_label" for="mobilmeny" onclick><span class="icon-menu"></span></label>
			' , template_menu(true) , '
			</nav>
		</div>
	</header>
</section>
<div id="h_linktree">' , theme_linktree() , '</div>
<div id="tsunami"' , !empty($settings['a_hide_credit']) ? ' style="display: none;"' : '' , '>Tsunami <span>theme by Bloc</span></div>

<section id="contentsection">
	<aside id="maside">
		<div id="h_user">' , template_head_user() , '</div>
		<div id="h_search">' , template_head_search() , '</div>
		<div id="h_news">' , template_head_news() , '</div>';
	
	if(function_exists('more_aside'))
		echo '
		<div id="h_more">', more_aside() ,'</div>';

	echo '
	</aside>
	<main id="maincontent">';
	
	// convert any pages
	convertPageindex();
}

function template_body_below()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
	</main>
</section>
<section id="footersection">
	<footer id="subfooter">
		', theme_copyright();

	// Show the load time?
	if ($context['show_load_time'])
		echo '
		<small>', $txt['page_created'], $context['load_time'], $txt['seconds_with'], $context['load_queries'], $txt['queries'], '</small>';

	echo '
	</footer>
</section>
';
}

function template_html_below()
{
	echo '
</body></html>';
}

function template_head_user()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	// If the user is logged in, display stuff like their name, new messages, etc.
	if ($context['user']['is_logged'])
	{
		echo '
			<div class="user">';
		if (!empty($context['user']['avatar']))
			echo '
				<p class="mavatar" style="background-image: url(', $context['user']['avatar']['href'], '"></p>';
		else
			echo '
				<p class="mavatar"></p>';
			
		echo '
				<ul class="reset">
					<li class="greet">', $txt['hello_member_ndt'], ' <span>', $context['user']['name'], '</span></li>
					<li class="unr"><a href="', $scripturl, '?action=unread">', $txt['a_unread'], '</a></li>
					<li class="rep"><a href="', $scripturl, '?action=unreadreplies">', $txt['a_replies'], '</a></li>';

		// Is the forum in maintenance mode?
		if ($context['in_maintenance'] && $context['user']['is_admin'])
			echo '
					<li class="notice">', $txt['maintain_mode_on'], '</li>';

		// Are there any members waiting for approval?
		if (!empty($context['unapproved_members']))
			echo '
					<li class="unapp">', $context['unapproved_members'] == 1 ? $txt['approve_thereis'] : $txt['approve_thereare'], ' <a href="', $scripturl, '?action=admin;area=viewmembers;sa=browse;type=approve">', $context['unapproved_members'] == 1 ? $txt['approve_member'] : $context['unapproved_members'] . ' ' . $txt['approve_members'], '</a> ', $txt['approve_members_waiting'], '</li>';

		if (!empty($context['open_mod_reports']) && $context['show_open_reports'])
			echo '
					<li class="openm"><a href="', $scripturl, '?action=moderate;area=reports">', sprintf($txt['mod_reports_waiting'], $context['open_mod_reports']), '</a></li>';

		echo '
					<li class="dat">', $context['current_time'], '</li>
				</ul>
			</div>';
	}
	// Otherwise they're a guest - this time ask them to either register or login - lazy bums...
	elseif (!empty($context['show_login_bar']))
	{
		echo '
				<script type="text/javascript" src="', $settings['default_theme_url'], '/scripts/sha1.js"></script>
				<form id="guest_form" action="', $scripturl, '?action=login2" method="post" accept-charset="', $context['character_set'], '" ', empty($context['disable_login_hashing']) ? ' onsubmit="hashLoginPassword(this, \'' . $context['session_id'] . '\');"' : '', '>
					<div class="info">', sprintf($txt['welcome_guest'], $txt['guest_title']), '</div>
					
					<div class="multi_set">
						<input type="text" name="user" class="input_text" />
						<input type="password" name="passwrd" class="input_text input_password" />
						<select name="cookielength" class="input_select">
							<option value="60">', $txt['one_hour'], '</option>
							<option value="1440">', $txt['one_day'], '</option>
							<option value="10080">', $txt['one_week'], '</option>
							<option value="43200">', $txt['one_month'], '</option>
							<option value="-1" selected="selected">', $txt['forever'], '</option>
						</select>
						<input type="submit" value="', $txt['login'], '" class="button_submit" />
					</div>

					<div class="info">', $txt['quick_login_dec'], '</div>';

		if (!empty($modSettings['enableOpenID']))
			echo '
					<div id="a_openid"><span class="icon-openid" title="OpenID"></span><input type="text" name="openid_identifier" id="openid_url" class="input_text openid_login" /></div>';

		echo '
					<input type="hidden" name="hash_passwrd" value="" /><input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				</form>';
	}
}
function template_head_news()
{
	global $context, $settings, $txt;

	if(function_exists("template_news_slider"))
	{
		$done = template_news_slider();
		if($done)
			return;
	}
	
	// Show a random news item? (or you could pick one from news_lines...)
	if (!empty($settings['enable_news']) && !empty($context['random_news_line']))
		echo '
				<h2>', $txt['news'], ': </h2>
				<p>', $context['random_news_line'], '</p>';
}
function template_head_search()
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	echo '
				<form id="search_form" action="', $scripturl, '?action=search2" method="post" accept-charset="', $context['character_set'], '">
					<div class="multi_set">
						<input type="text" name="search" value="" class="input_text multi_start" />
						<input type="submit" name="submit" value="', $txt['search'], '" class="button_submit multi_end" />
					</div>
					<input type="hidden" name="advanced" value="0" />';

	// Search within current topic?
	if (!empty($context['current_topic']))
		echo '
					<input type="hidden" name="topic" value="', $context['current_topic'], '" />';
	// If we're on a certain board, limit it to this board ;).
	elseif (!empty($context['current_board']))
		echo '
					<input type="hidden" name="brd[', $context['current_board'], ']" value="', $context['current_board'], '" />';

	echo '	</form>';

}


// Show a linktree. This is that thing that shows "My Community | General Category | General Discussion"..
function theme_linktree($force_show = false)
{
	global $context, $settings, $options, $shown_linktree;

	// If linktree is empty, just return - also allow an override.
	if (empty($context['linktree']) || (!empty($context['dont_default_linktree']) && !$force_show))
		return;

	echo '
		<ul class="reset">';

	// Each tree item has a URL and name. Some may have extra_before and extra_after.
	foreach ($context['linktree'] as $link_num => $tree)
	{
		echo '
			<li', ($link_num == count($context['linktree']) - 1) ? ' class="last"' : '', '>';

		// Show something before the link?
		if (isset($tree['extra_before']))
			echo $tree['extra_before'];

		// Show the link, including a URL if it should have one.
		echo isset($tree['url']) ? '
				<a href="' . $tree['url'] . '"><span>' . $tree['name'] . '</span></a>' : '<span>' . $tree['name'] . '</span>';

		// Show something after the link...?
		if (isset($tree['extra_after']))
			echo $tree['extra_after'];

		echo '
			</li>';
	}
	echo '
		</ul>';

	$shown_linktree = true;
}

// Show the menu up top. Something like [home] [help] [profile] [logout]...
function template_menu($mobil = false)
{
	global $context, $settings, $options, $scripturl, $txt;

	if($mobil)
	{
		echo '
				<ul class="reset mobilmenu" id="menu_nav_mobile">';

		foreach ($context['menu_buttons'] as $act => $button)
		{
			echo '
					<li id="button_', $act, '">
						<a class="', $button['active_button'] ? 'active ' : '', 'firstlevel" href="', $button['href'], '"', isset($button['target']) ? ' target="' . $button['target'] . '"' : '', '>
							<span class="', !empty($button['sub_buttons']) ? 'parent ' : '' , isset($button['is_last']) ? 'last ' : '', 'firstlevel">', $button['title'], '</span>
						</a>';
			if (!empty($button['sub_buttons']))
			{
				echo '
						<ul class="reset">';

				foreach ($button['sub_buttons'] as $childbutton)
				{
					echo '
							<li>
								<a href="', $childbutton['href'], '"', isset($childbutton['target']) ? ' target="' . $childbutton['target'] . '"' : '', '>
									<span', isset($childbutton['is_last']) ? ' class="last"' : '', '>', $childbutton['title'], !empty($childbutton['sub_buttons']) ? '...' : '', '</span>
								</a>';
					// 3rd level menus :)
					if (!empty($childbutton['sub_buttons']))
					{
						echo '
								<ul class="reset">';

						foreach ($childbutton['sub_buttons'] as $grandchildbutton)
							echo '
									<li>
										<a href="', $grandchildbutton['href'], '"', isset($grandchildbutton['target']) ? ' target="' . $grandchildbutton['target'] . '"' : '', '>
											<span', isset($grandchildbutton['is_last']) ? ' class="last"' : '', '>', $grandchildbutton['title'], '</span>
										</a>
									</li>';

						echo '
								</ul>';
					}

					echo '
							</li>';
				}
					echo '
						</ul>';
			}
			echo '
					</li>';
		}

		echo '
				</ul>';
	}
	else
	{
		echo '
			<menu id="desktopmenu">
				<ul class="reset dropmenu" id="menu_nav">';

		foreach ($context['menu_buttons'] as $act => $button)
		{
			echo '
					<li id="button_', $act, '">
						<a class="', $button['active_button'] ? 'active ' : '', 'firstlevel" href="', $button['href'], '"', isset($button['target']) ? ' target="' . $button['target'] . '"' : '', '>
							<span class="', !empty($button['sub_buttons']) ? 'parent ' : '' , isset($button['is_last']) ? 'last ' : '', 'firstlevel">', $button['title'], '</span>
						</a>';
			if (!empty($button['sub_buttons']))
			{
				echo '
						<ul class="reset">';

				foreach ($button['sub_buttons'] as $childbutton)
				{
					echo '
							<li>
								<a href="', $childbutton['href'], '"', isset($childbutton['target']) ? ' target="' . $childbutton['target'] . '"' : '', '>
									<span', isset($childbutton['is_last']) ? ' class="last"' : '', '>', $childbutton['title'], !empty($childbutton['sub_buttons']) ? '...' : '', '</span>
								</a>';
					// 3rd level menus :)
					if (!empty($childbutton['sub_buttons']))
					{
						echo '
								<ul class="reset">';

						foreach ($childbutton['sub_buttons'] as $grandchildbutton)
							echo '
									<li>
										<a href="', $grandchildbutton['href'], '"', isset($grandchildbutton['target']) ? ' target="' . $grandchildbutton['target'] . '"' : '', '>
											<span', isset($grandchildbutton['is_last']) ? ' class="last"' : '', '>', $grandchildbutton['title'], '</span>
										</a>
									</li>';

						echo '
								</ul>';
					}

					echo '
							</li>';
				}
					echo '
						</ul>';
			}
			echo '
					</li>';
		}

		echo '
				</ul>
			</menu>';
	}
}

// Generate a strip of buttons.
function template_button_strip($button_strip, $direction = 'top', $strip_options = array())
{
	global $settings, $context, $txt, $scripturl;

	if (!is_array($strip_options))
		$strip_options = array();

	// List the buttons in reverse order for RTL languages.
	if ($context['right_to_left'])
		$button_strip = array_reverse($button_strip, true);

	// Create the buttons...
	$buttons = array();
	foreach ($button_strip as $key => $value)
	{
		if (!isset($value['test']) || !empty($context[$value['test']]))
		{
			if(isset($value['icon']))
			{
				if(is_array($value['icon']))
					$buttons[] = '
				<a' . (isset($value['id']) ? ' id="button_strip_' . $value['id'] . '"' : '') . ' class="button_strip_' . $key . (isset($value['active']) ? ' active' : '') . '" href="' . $value['url'] . '"' . (isset($value['custom']) ? ' ' . $value['custom'] : '') . '><span class="button_submit buts is_icon"><span style="opacity: 0.7;" class="mobile ' . (implode('"></span><span class="mobile iconbig ',$value['icon'])) . '"></span><span class="desktop">' . $txt[$value['text']] . '</span></span></a>';
				else
					$buttons[] = '
				<a' . (isset($value['id']) ? ' id="button_strip_' . $value['id'] . '"' : '') . ' class="button_strip_' . $key . (isset($value['active']) ? ' active' : '') . '" href="' . $value['url'] . '"' . (isset($value['custom']) ? ' ' . $value['custom'] : '') . '><span class="button_submit buts is_icon"><span class="' . $value['icon'] . ' mobile iconbig"></span><span class="desktop">' . $txt[$value['text']] . '</span></span></a>';
			}
			else
				$buttons[] = '
				<a' . (isset($value['id']) ? ' id="button_strip_' . $value['id'] . '"' : '') . ' class="button_strip_' . $key . (isset($value['active']) ? ' active' : '') . '" href="' . $value['url'] . '"' . (isset($value['custom']) ? ' ' . $value['custom'] : '') . '><span class="button_submit buts">' . $txt[$value['text']] . '</span></a>';
		}
	}

	// No buttons? No button strip either.
	if (empty($buttons))
		return;

	echo 	implode('', $buttons);
}
function get_avatars($ids = '')
{
	global $smcFunc, $user_profile, $scripturl, $modSettings, $settings, $boardurl, $image_proxy_enabled, $image_proxy_secret;
	
	if(empty($ids))
		return;

	$request = $smcFunc['db_query']('','
		SELECT mem.id_member, 
		IFNULL(a.id_attach, 0) AS id_attach, a.filename, a.attachment_type, mem.avatar AS avatar
		FROM {db_prefix}members AS mem
		LEFT JOIN {db_prefix}attachments AS a ON (a.id_member = mem.id_member)
		WHERE mem.id_member IN ({string:users})',
		array( 'users'	=> $ids)
	);
	$avatars = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{		
		$avatar = $row['avatar'] == '' ? ($row['id_attach'] > 0 ? (empty($row['attachment_type']) ? $scripturl . '?action=dlattach;attach=' . $row['id_attach'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row['filename']) : '') : (stristr($row['avatar'], 'http://') ? $row['avatar'] : $modSettings['avatar_url'] . '/' . $row['avatar']);
		if ($image_proxy_enabled && stripos($avatar, 'http://') !== false)
			$avatar = strtr($boardurl, array('http://' => 'https://')) . '/proxy.php?request=' . urlencode($avatar) . '&hash=' . md5($avatar . $image_proxy_secret);
		
		if (empty($avatar))
			$avatar = $settings['images_url']. '/noavatar.png';
		
		$avatars[$row['id_member']] = $avatar;
	}
	$smcFunc['db_free_result']($request);
	return $avatars;
}

function convertPageindex($custom = '')
{
	global $context, $txt;
	
	if(!empty($custom))
	{
		$return =  '<span class="page_index">' . (str_replace(array('[',']'), array('<span>','</span>'),$custom)) . '</span>';
		return $return;
	}	
	
	if(empty($context['page_index']))
		return;

	$context['page_index'] = '<span class="page_index">' . (str_replace(array('[',']'), array('<span>','</span>'),$context['page_index'])) . '</span>';
}

function convertPages($code= '')
{
	global $context, $txt;
	
	if(!empty($code))
	{
		$code = str_replace(array('&#171;','&#187;'), array('',''), $code);
		echo $code;
	}	
}

?>