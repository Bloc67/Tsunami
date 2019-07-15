<?php

function template_body_id()
{
	echo ' id="m_index"';
}

function template_main()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	echo '
<div id="a_maside">
	<h2 class="header_name">' , $context['name'] , '</h2>';

	if (!empty($options['show_board_desc']) && $context['description'] != '')
		echo '
	<p class="information">', $context['description'], '</p>';

	if (!empty($settings['display_who_viewing']))
	{
		echo '
	<div class="information">';
		if ($settings['display_who_viewing'] == 1)
			echo count($context['view_members']), ' ', count($context['view_members']) === 1 ? $txt['who_member'] : $txt['members'];
		else
			echo empty($context['view_members_list']) ? '0 ' . $txt['members'] : implode(', ', $context['view_members_list']) . ((empty($context['view_num_hidden']) or $context['can_moderate_forum']) ? '' : ' (+ ' . $context['view_num_hidden'] . ' ' . $txt['hidden'] . ')');
		echo $txt['who_and'], $context['view_num_guests'], ' ', $context['view_num_guests'] == 1 ? $txt['guest'] : $txt['guests'], $txt['who_viewing_board'], '
	</div>';
	}

	// If this person can approve items and we have some awaiting approval tell them.
	if (!empty($context['unapproved_posts_message']))
	{
		echo '
	<div class="information">
		<span class="alert">!</span> ', $context['unapproved_posts_message'], '
	</div>';
	}

	if (!empty($context['boards']) && (!empty($options['show_children']) || $context['start'] == 0))
	{
		echo '
	<div class="a_boards">';

		foreach ($context['boards'] as $board)
		{
				echo '
		<ul id="category_', $category['id'], '_boards" class="reset a_boards_single">
			<li class="a_board_subject">
				<a class="a_subject" href="', $board['href'], '" name="b', $board['id'], '">', $board['name'], '</a>' , ($board['new'] || $board['children_new']) ? '<span class="icon-micro-new"></span>' : '';

				// Has it outstanding posts for approval?
				if ($board['can_approve_posts'] && ($board['unapproved_posts'] || $board['unapproved_topics']))
					echo '
				<a class="a_approval" href="', $scripturl, '?action=moderate;area=postmod;sa=', ($board['unapproved_topics'] > 0 ? 'topics' : 'posts'), ';brd=', $board['id'], ';', $context['session_var'], '=', $context['session_id'], '" title="', sprintf($txt['unapproved_posts'], $board['unapproved_topics'], $board['unapproved_posts']), '" class="moderation_link"><span class="a_icons a_moderation"></span></a>';

				echo '
			</li>
			<li class="a_board_description">
				<p class="a_description">', $board['description'] , '</p>';

				// Show the "Moderators: ". Each has name, href, link, and id. (but we're gonna use link_moderators.)
				if (!empty($board['moderators']))
					echo '
				<p class="a_description">', count($board['moderators']) == 1 ? $txt['moderator'] : $txt['moderators'], ': ', implode(', ', $board['link_moderators']), '</p>';

				// Show some basic information about the number of posts, etc.
					echo '
			</li>
			<li class="a_board_stats">
				<span>' , $board['posts'], ' ', $board['is_redirect'] ? '' : ' <span style="opacity: 0.3;">|</span> '.$board['topics'], '</span>
			</li>
			<li class="a_board_lastpost">';

				if (!empty($board['last_post']['id']))
					echo '
					<span class="memb">
						<span class="icon-right-small"></span>
						<span class="item"><span class="icon-doc"></span>', $board['last_post']['link'], '</span>
						<span class="item"><span class="icon-user-outline"></span>', $board['last_post']['member']['link'] , '</span>
						<span class="item"><span class="icon-clock"></span>', $board['last_post']['time'],'</span>
					</span>';
				else
					echo '
					<span class="memb">&nbsp;</span>';
			
				echo '
			</li>
			<li class="a_board_avvy' , ($board['new'] || $board['children_new']) ? ' avvy_new'.($board['children_new'] ? '2' : '') : '' , '">
				<span class="avvy avvy_not" style="background-image: url(' , $avatars[$board['last_post']['member']['id']] , ');"></span>
			</li>
			<li class="a_board_childs">';
				// Show the "Child Boards: ". (there's a link_children but we're going to bold the new ones...)
				if (!empty($board['children']))
				{
					// Sort the links into an array with new boards bold so it can be imploded.
					$children = array();
					/* Each child in each board's children has:
							id, name, description, new (is it new?), topics (#), posts (#), href, link, and last_post. */
					foreach ($board['children'] as $child)
					{
						if (!$child['is_redirect'])
							$child['link'] = '<a href="' . $child['href'] . '" ' . ($child['new'] ? 'class="new_posts" ' : '') . 'title="' . ($child['new'] ? $txt['new_posts'] : $txt['old_posts']) . ' (' . $txt['board_topics'] . ': ' . comma_format($child['topics']) . ', ' . $txt['posts'] . ': ' . comma_format($child['posts']) . ')">' . $child['name'] . ($child['new'] ? '</a> <a href="' . $scripturl . '?action=unread;board=' . $child['id'] . '" title="' . $txt['new_posts'] . ' (' . $txt['board_topics'] . ': ' . comma_format($child['topics']) . ', ' . $txt['posts'] . ': ' . comma_format($child['posts']) . ')"><img src="' . $settings['lang_images_url'] . '/new.gif" class="new_posts" alt="" />' : '') . '</a>';
						else
							$child['link'] = '<a href="' . $child['href'] . '" title="' . comma_format($child['posts']) . ' ' . $txt['redirects'] . '">' . $child['name'] . '</a>';

						// Has it posts awaiting approval?
						if ($child['can_approve_posts'] && ($child['unapproved_posts'] || $child['unapproved_topics']))
							$child['link'] .= ' <a href="' . $scripturl . '?action=moderate;area=postmod;sa=' . ($child['unapproved_topics'] > 0 ? 'topics' : 'posts') . ';brd=' . $child['id'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '" title="' . sprintf($txt['unapproved_posts'], $child['unapproved_topics'], $child['unapproved_posts']) . '" class="moderation_link">(!)</a>';

						$children[] = $child['new'] ? '<strong>' . $child['link'] . '</strong>' : $child['link'];
					}
					echo '
				<span class="icon-folder"></span> <span>', implode('</span>|<span>', $children), '</span>';
				}
				echo '
			</li>
		</ul>';
		}
		echo '
	</div>';
	}

	echo '
</div>
<article id="a_messageindex">';

	// Create the button set...
	$normal_buttons = array(
		'new_topic' => array('test' => 'can_post_new', 'text' => 'new_topic', 'image' => 'new_topic.gif', 'lang' => true, 'url' => $scripturl . '?action=post;board=' . $context['current_board'] . '.0', 'active' => true),
		'post_poll' => array('test' => 'can_post_poll', 'text' => 'new_poll', 'image' => 'new_poll.gif', 'lang' => true, 'url' => $scripturl . '?action=post;board=' . $context['current_board'] . '.0;poll'),
		'notify' => array('test' => 'can_mark_notify', 'text' => $context['is_marked_notify'] ? 'unnotify' : 'notify', 'image' => ($context['is_marked_notify'] ? 'un' : ''). 'notify.gif', 'lang' => true, 'custom' => 'onclick="return confirm(\'' . ($context['is_marked_notify'] ? $txt['notification_disable_board'] : $txt['notification_enable_board']) . '\');"', 'url' => $scripturl . '?action=notifyboard;sa=' . ($context['is_marked_notify'] ? 'off' : 'on') . ';board=' . $context['current_board'] . '.' . $context['start'] . ';' . $context['session_var'] . '=' . $context['session_id']),
		'markasread' => array('text' => 'mark_read_short', 'image' => 'markread.gif', 'lang' => true, 'url' => $scripturl . '?action=markasread;sa=board;board=' . $context['current_board'] . '.0;' . $context['session_var'] . '=' . $context['session_id']),
	);

	// They can only mark read if they are logged in and it's enabled!
	if (!$context['user']['is_logged'] || !$settings['show_mark_read'])
		unset($normal_buttons['markasread']);

	// Allow adding new buttons easily.
	call_integration_hook('integrate_messageindex_buttons', array(&$normal_buttons));

	if (!$context['no_topic_listing'])
	{
		convertPageindex();
		echo '
	<div class="pagesection">
		<div class="pagelinks">', $context['page_index'],'</div>
		' , !empty($modSettings['topbottomEnable']) ? $context['menu_separator'] . ' <a id="a_go_down" href="#bot" class="button_submit buts"><strong>' . $txt['go_down'] . '</strong></a>' : '', '
		', template_button_strip($normal_buttons, 'right'), '
	</div>';

		// If Quick Moderation is enabled start the form.
		if (!empty($context['can_quick_mod']) && $options['display_quick_mod'] > 0 && !empty($context['topics']))
			echo '
	<form action="', $scripturl, '?action=quickmod;board=', $context['current_board'], '.', $context['start'], '" method="post" accept-charset="', $context['character_set'], '" class="clear" name="quickModForm" id="quickModForm">';

		echo '
		<div id="messageindex">';

		// Are there actually any topics to show?
		if (!empty($context['topics']))
		{
			echo '
			<ul class="reset a_topics_headers">
				<li>
					<a href="', $scripturl, '?board=', $context['current_board'], '.', $context['start'], ';sort=subject', $context['sort_by'] == 'subject' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $txt['subject'], $context['sort_by'] == 'subject' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a> 
					/ <a href="', $scripturl, '?board=', $context['current_board'], '.', $context['start'], ';sort=starter', $context['sort_by'] == 'starter' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $txt['started_by'], $context['sort_by'] == 'starter' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a>
					/ <a href="', $scripturl, '?board=', $context['current_board'], '.', $context['start'], ';sort=replies', $context['sort_by'] == 'replies' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $txt['replies'], $context['sort_by'] == 'replies' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a> 
					/ <a href="', $scripturl, '?board=', $context['current_board'], '.', $context['start'], ';sort=views', $context['sort_by'] == 'views' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $txt['views'], $context['sort_by'] == 'views' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a>
				';

			// Show a "select all" box for quick moderation?
			if (empty($context['can_quick_mod']))
				echo '
				/ <a href="', $scripturl, '?board=', $context['current_board'], '.', $context['start'], ';sort=last_post', $context['sort_by'] == 'last_post' && $context['sort_direction'] == 'up' ? ';desc' : '', '" >', $txt['last_post'], $context['sort_by'] == 'last_post' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a>';
			else
				echo '
				/ <a href="', $scripturl, '?board=', $context['current_board'], '.', $context['start'], ';sort=last_post', $context['sort_by'] == 'last_post' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $txt['last_post'], $context['sort_by'] == 'last_post' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a>';

			// Show a "select all" box for quick moderation?
			if (!empty($context['can_quick_mod']) && $options['display_quick_mod'] == 1)
				echo '
				<input type="checkbox" onclick="invertAll(this, this.form, \'topics[]\');" class="input_check floatright" />';
			
			echo '
				</li>
			</ul>';
		}
		// No topics.... just say, "sorry bub".
		else
			echo '
			<h3><strong>', $txt['msg_alert_none'], '</strong></h3>';

		foreach ($context['topics'] as $topic)
		{
			$exclass = '';
			if($topic['is_sticky'])
				$exclass .= '<span class="icon-pin-outline smaller blue" title="' . $txt['sticky_topic'] . '"></span>';
			if($topic['is_locked'])
				$exclass .= '<span class="icon-lock smaller grey" title="' . $txt['locked_topic'] . '"></span>';
			if($context['can_approve_posts'] && $topic['unapproved_posts'])
				$exclass .= '<span class="icon-flag smaller red" title="' . $txt['awaiting_approval'] . '"></span>';
			if($topic['is_posted_in'])
				$exclass .= '<span class="icon-bell smaller green" title="' . $txt['participation_caption'] . '"></span>';
			if($topic['is_poll'])
				$exclass .= '<span class="icon-chart-bar smaller" title="' . $txt['poll'] . '"></span>';
			
			$hotish = '';
			if($topic['is_hot'])
				$hotish =' hot';
			if($topic['is_very_hot'])
				$hotish =' veryhot';
			
			echo '
			<ul class="reset a_topics_single' ,  $hotish , '">
				<li class="icon1"><img src="', $settings['images_url'], '/post/svg/', $topic['first_post']['icon'], '.svg" alt="" /></li>
				<li class="subject">
					<div ', (!empty($topic['quick_mod']['modify']) ? 'id="topic_' . $topic['first_post']['id'] . '" onmouseout="mouse_on_div = 0;" onmouseover="mouse_on_div = 1;" ondblclick="modify_topic(\'' . $topic['id'] . '\', \'' . $topic['first_post']['id'] . '\');"' : ''), '>
						' , $exclass, '<span id="msg_' . $topic['first_post']['id'] . '">', $topic['first_post']['link'], (!$context['can_approve_posts'] && !$topic['approved'] ? '&nbsp;<em>(' . $txt['awaiting_approval'] . ')</em>' : ''), '</span>';

			// Is this topic new? (assuming they are logged in!)
			if ($topic['new'] && $context['user']['is_logged'])
					echo '
						<a href="', $topic['new_href'], '" id="newicon' . $topic['first_post']['id'] . '"><span class="icon-micro-new"></span></a>';

			echo '
					</div>
				</li>
				<li class="user">', $topic['first_post']['member']['link'], '</li>
				<li class="pages">', $topic['pages'], '</li>
				<li class="stats">', $topic['replies'], ' |	', $topic['views'], '</li>
				<li class="lastpost">
					<a href="', $topic['last_post']['href'], '"><span class="floatleft icon-chat-alt"></span> ', $topic['last_post']['time'], '</a> | ', $topic['last_post']['member']['link'], '
				</li>';

			// Show the quick moderation options?
			if (!empty($context['can_quick_mod']))
			{
				echo '
				<li class="moderation">';
				if ($options['display_quick_mod'] == 1)
					echo '
					<input type="checkbox" name="topics[]" value="', $topic['id'], '" class="input_check" />';
				else
				{
					// Check permissions on each and show only the ones they are allowed to use.
					if ($topic['quick_mod']['remove'])
						echo '
					<a href="', $scripturl, '?action=quickmod;board=', $context['current_board'], '.', $context['start'], ';actions[', $topic['id'], ']=remove;', $context['session_var'], '=', $context['session_id'], '" onclick="return confirm(\'', $txt['quickmod_confirm'], '\');"><span class="icon-trash" title="', $txt['remove_topic'], '"></span></a>';

					if ($topic['quick_mod']['lock'])
						echo '
					<a href="', $scripturl, '?action=quickmod;board=', $context['current_board'], '.', $context['start'], ';actions[', $topic['id'], ']=lock;', $context['session_var'], '=', $context['session_id'], '" onclick="return confirm(\'', $txt['quickmod_confirm'], '\');"><span class="icon-lock" title="', $txt['set_lock'], '"></span></a>';

					if ($topic['quick_mod']['sticky'])
						echo '
					<a href="', $scripturl, '?action=quickmod;board=', $context['current_board'], '.', $context['start'], ';actions[', $topic['id'], ']=sticky;', $context['session_var'], '=', $context['session_id'], '" onclick="return confirm(\'', $txt['quickmod_confirm'], '\');"><span class="icon-pin-outline" title="', $txt['set_sticky'], '"></span></a>';

					if ($topic['quick_mod']['move'])
						echo '
					<a href="', $scripturl, '?action=movetopic;board=', $context['current_board'], '.', $context['start'], ';topic=', $topic['id'], '.0"><span class="icon-forward-outline" title="', $txt['move_topic'], '"></span></a>';
				}
				echo '
				</li>';
			}
			else
				echo '
				<li class="moderation">&nbsp;</li>';

			echo '
			</ul>';
		}

		if (!empty($context['can_quick_mod']) && $options['display_quick_mod'] == 1 && !empty($context['topics']))
		{
			echo '
			<div class="moderation_bar">
				<select class="qaction" name="qaction"', $context['can_move'] ? ' onchange="this.form.moveItTo.disabled = (this.options[this.selectedIndex].value != \'move\');"' : '', '>
					<option value="">--------</option>', $context['can_remove'] ? '
					<option value="remove">' . $txt['quick_mod_remove'] . '</option>' : '', $context['can_lock'] ? '
					<option value="lock">' . $txt['quick_mod_lock'] . '</option>' : '', $context['can_sticky'] ? '
					<option value="sticky">' . $txt['quick_mod_sticky'] . '</option>' : '', $context['can_move'] ? '
					<option value="move">' . $txt['quick_mod_move'] . ': </option>' : '', $context['can_merge'] ? '
					<option value="merge">' . $txt['quick_mod_merge'] . '</option>' : '', $context['can_restore'] ? '
					<option value="restore">' . $txt['quick_mod_restore'] . '</option>' : '', $context['can_approve'] ? '
					<option value="approve">' . $txt['quick_mod_approve'] . '</option>' : '', $context['user']['is_logged'] ? '
					<option value="markread">' . $txt['quick_mod_markread'] . '</option>' : '', '
				</select>';

			// Show a list of boards they can move the topic to.
			if ($context['can_move'])
			{
					echo '
				<select class="qaction" id="moveItTo" name="move_to" disabled="disabled">';

					foreach ($context['move_to_boards'] as $category)
					{
						echo '
					<optgroup label="', $category['name'], '">';
						foreach ($category['boards'] as $board)
							echo '
						<option value="', $board['id'], '"', $board['selected'] ? ' selected="selected"' : '', '>', $board['child_level'] > 0 ? str_repeat('==', $board['child_level'] - 1) . '=&gt;' : '', ' ', $board['name'], '</option>';
						echo '
					</optgroup>';
					}
					echo '
				</select>';
			}

			echo '
				<input type="submit" value="', $txt['quick_mod_go'], '" onclick="return document.forms.quickModForm.qaction.value != \'\' &amp;&amp; confirm(\'', $txt['quickmod_confirm'], '\');" class="button_submit qaction" />
			</div>';
		}
		echo '
			<a id="bot"></a>
		</div>';

		// Finish off the form - again.
		if (!empty($context['can_quick_mod']) && $options['display_quick_mod'] > 0 && !empty($context['topics']))
			echo '
		<input type="hidden" name="' . $context['session_var'] . '" value="' . $context['session_id'] . '" />
	</form>';

		echo '
	<div class="pagesection">
		' , !empty($modSettings['topbottomEnable']) ? '<a href="#a_messageindex" id="a_go_up" class="button_submit buts"><strong>' . $txt['go_up'] . '</strong></a>' : '', template_button_strip($normal_buttons, 'right'), '
		<div class="pagelinks">', $context['page_index'], '</div>
	</div>';
	}

	echo '
	<div id="topic_icons">
		<div class="description">
			<p id="message_index_jump_to">&nbsp;</p>

			<script type="text/javascript"><!-- // --><![CDATA[
				if (typeof(window.XMLHttpRequest) != "undefined")
					aJumpTo[aJumpTo.length] = new JumpTo({
						sContainerId: "message_index_jump_to",
						sJumpToTemplate: "<label class=\"smalltext\" for=\"%select_id%\">', $context['jump_to']['label'], ':<" + "/label> %dropdown_list%",
						iCurBoardId: ', $context['current_board'], ',
						iCurBoardChildLevel: ', $context['jump_to']['child_level'], ',
						sCurBoardName: "', $context['jump_to']['board_name'], '",
						sBoardChildLevelIndicator: "==",
						sBoardPrefix: "=> ",
						sCatSeparator: "-----------------------------",
						sCatPrefix: "",
						sGoButtonLabel: "', $txt['quick_mod_go'], '"
					});
			// ]]></script>
			<br class="clear" />
		</div>
	</div>
</article>';

	// Javascript for inline editing.
	echo '
<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/topic.js"></script>
<script type="text/javascript"><!-- // --><![CDATA[

	// Hide certain bits during topic edit.
	hide_prefixes.push("lockicon", "stickyicon", "pages", "newicon");

	// Use it to detect when we\'ve stopped editing.
	document.onclick = modify_topic_click;

	var mouse_on_div;
	function modify_topic_click()
	{
		if (in_edit_mode == 1 && mouse_on_div == 0)
			modify_topic_save("', $context['session_id'], '", "', $context['session_var'], '");
	}

	function modify_topic_keypress(oEvent)
	{
		if (typeof(oEvent.keyCode) != "undefined" && oEvent.keyCode == 13)
		{
			modify_topic_save("', $context['session_id'], '", "', $context['session_var'], '");
			if (typeof(oEvent.preventDefault) == "undefined")
				oEvent.returnValue = false;
			else
				oEvent.preventDefault();
		}
	}

	// For templating, shown when an inline edit is made.
	function modify_topic_show_edit(subject)
	{
		// Just template the subject.
		setInnerHTML(cur_subject_div, \'<input type="text" name="subject" value="\' + subject + \'" size="60" style="width: 95%;" maxlength="80" onkeypress="modify_topic_keypress(event)" class="input_text" /><input type="hidden" name="topic" value="\' + cur_topic_id + \'" /><input type="hidden" name="msg" value="\' + cur_msg_id.substr(4) + \'" />\');
	}

	// And the reverse for hiding it.
	function modify_topic_hide_edit(subject)
	{
		// Re-template the subject!
		setInnerHTML(cur_subject_div, \'<a href="', $scripturl, '?topic=\' + cur_topic_id + \'.0">\' + subject + \'<\' +\'/a>\');
	}

// ]]></script>';
}

?>