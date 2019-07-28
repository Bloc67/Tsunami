<?php

/*	@ Bloc 2019										*/
/*	@	SMF 2.0.x										*/

function template_main()
{
	global $context, $settings, $options, $txt, $scripturl;

	echo '
	<div id="recent" class="main_section">
		<div class="cat_bar">
			<h3 class="catbg">
				<span class="ie6_header floatleft"><img src="', $settings['images_url'], '/post/xx.gif" alt="" class="icon" />',$txt['recent_posts'],'</span>
			</h3>
		</div>
		<div class="pagesection">
			<span>', $txt['pages'], ': ', $context['page_index'], '</span>
		</div>';

	foreach ($context['posts'] as $post)
	{
		echo '
			<div class="', $post['alternate'] == 0 ? 'windowbg' : 'windowbg2', ' core_posts">
				<span class="topslice"><span></span></span>
				<div class="content">
					<div class="counter">', $post['counter'], '</div>
					<div class="topic_details">
						<h5>', $post['board']['link'], ' / ', $post['link'], '</h5>
						<span class="smalltext">&#171;&nbsp;', $txt['last_post'], ' ', $txt['by'], ' <strong>', $post['poster']['link'], ' </strong> ', $txt['on'], '<em> ', $post['time'], '</em>&nbsp;&#187;</span>
					</div>
					<div class="list_posts">', $post['message'], '</div>
				</div>';

		if ($post['can_reply'] || $post['can_mark_notify'] || $post['can_delete'])
			echo '
				<div class="quickbuttons_wrap">
					<ul class="reset smalltext quickbuttons">';

		// If they *can* reply?
		if ($post['can_reply'])
			echo '
						<li class="reply_button"><a href="', $scripturl, '?action=post;topic=', $post['topic'], '.', $post['start'], '"><span>', $txt['reply'], '</span></a></li>';

		// If they *can* quote?
		if ($post['can_quote'])
			echo '
						<li class="quote_button"><a href="', $scripturl, '?action=post;topic=', $post['topic'], '.', $post['start'], ';quote=', $post['id'], '"><span>', $txt['quote'], '</span></a></li>';

		// Can we request notification of topics?
		if ($post['can_mark_notify'])
			echo '
						<li class="notify_button"><a href="', $scripturl, '?action=notify;topic=', $post['topic'], '.', $post['start'], '"><span>', $txt['notify'], '</span></a></li>';

		// How about... even... remove it entirely?!
		if ($post['can_delete'])
			echo '
						<li class="remove_button"><a href="', $scripturl, '?action=deletemsg;msg=', $post['id'], ';topic=', $post['topic'], ';recent;', $context['session_var'], '=', $context['session_id'], '" onclick="return confirm(\'', $txt['remove_message'], '?\');"><span>', $txt['remove'], '</span></a></li>';

		if ($post['can_reply'] || $post['can_mark_notify'] || $post['can_delete'])
			echo '
					</ul>
				</div>';

		echo '
				<span class="botslice clear"><span></span></span>
			</div>';

	}

	echo '
		<div class="pagesection">
			<span>', $txt['pages'], ': ', $context['page_index'], '</span>
		</div>
	</div>';
}

function template_unread()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	$showCheckboxes = !empty($options['display_quick_mod']) && $options['display_quick_mod'] == 1 && $settings['show_mark_read'];
	echo '
	<div id="a_maside">
		<h2 class="header_name">' , $context['showing_all_topics'] ? $txt['unread_topics_all'] : $txt['unread_topics_visit'] , '</h2>';


	if ($showCheckboxes)
		echo '
		<form action="', $scripturl, '?action=quickmod" method="post" accept-charset="', $context['character_set'], '" name="quickModForm" id="quickModForm" style="margin: 0;">
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			<input type="hidden" name="qaction" value="markread" />
			<input type="hidden" name="redirect_url" value="action=unread', (!empty($context['showing_all_topics']) ? ';all' : ''), $context['querystring_board_limits'], '" />';

	if ($settings['show_mark_read'])
	{
		// Generate the button strip.
		$mark_read = array(
			'mark_read' => array('text' => !empty($context['no_board_limits']) ? 'mark_as_read' : 'mark_read_short', 'image' => 'markread.gif', 'lang' => true, 'url' => $scripturl . '?action=markasread;sa=' . (!empty($context['no_board_limits']) ? 'all' : 'board' . $context['querystring_board_limits']) . ';' . $context['session_var'] . '=' . $context['session_id']),
		);

		if ($showCheckboxes)
			$mark_read['markselectread'] = array('text' => 'quick_mod_markread','image' => 'markselectedread.gif','lang' => true,'url' => 'javascript:document.quickModForm.submit();',);
	}

	if (!empty($context['topics']))
	{
		convertPageindex();
		echo '
			<div class="pagesection">
				<div class="pagelinks">', $context['page_index'], '</div>';

		if (!empty($mark_read))
			template_button_strip($mark_read, 'right');

		echo '
			</div>';

		echo '
			<ul class="reset a_topics_headers">
				<li>
					<a href="', $scripturl, '?action=unread', $context['showing_all_topics'] ? ';all' : '', $context['querystring_board_limits'], ';sort=subject', $context['sort_by'] == 'subject' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $txt['subject'], $context['sort_by'] == 'subject' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a>
					/ <a href="', $scripturl, '?action=unread', $context['showing_all_topics'] ? ';all' : '', $context['querystring_board_limits'], ';sort=replies', $context['sort_by'] == 'replies' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $txt['replies'], $context['sort_by'] == 'replies' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a>';

		// Show a "select all" box for quick moderation?
		if ($showCheckboxes)
			echo '
					/ <a href="', $scripturl, '?action=unread', $context['showing_all_topics'] ? ';all' : '', $context['querystring_board_limits'], ';sort=last_post', $context['sort_by'] == 'last_post' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $txt['last_post'], $context['sort_by'] == 'last_post' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a>
					<input type="checkbox" onclick="invertAll(this, this.form, \'topics[]\');" class="input_check floatright" />';
		else
			echo '
					/ <a href="', $scripturl, '?action=unread', $context['showing_all_topics'] ? ';all' : '', $context['querystring_board_limits'], ';sort=last_post', $context['sort_by'] == 'last_post' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $txt['last_post'], $context['sort_by'] == 'last_post' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a>';
		echo '
				</li>
			</ul>';

		foreach ($context['topics'] as $topic)
		{
			a_topic($topic, $showCheckboxes);
		}

		if (!empty($context['topics']) && !$context['showing_all_topics'])
			$mark_read['readall'] = array('text' => 'unread_topics_all', 'image' => 'markreadall.gif', 'lang' => true, 'url' => $scripturl . '?action=unread;all' . $context['querystring_board_limits'], 'active' => true);

		echo '
			<div class="pagesection">';

		if (!empty($mark_read))
			template_button_strip($mark_read, 'right');

		echo '
				<div class="pagelinks">', $context['page_index'], '</div>
			</div>';
	}
	else
		echo '
			<h3 class="header_name centertext">
					', $context['showing_all_topics'] ? $txt['msg_alert_none'] : $txt['unread_topics_visit_none'], '
			</h3>';

	if ($showCheckboxes)
		echo '
		</form>';
	echo '
	</div>';
}

function template_replies()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	echo '
	<div id="a_maside">
		<h2 class="header_name">' , $txt['unread_replies'] , '</h2>';


	$showCheckboxes = !empty($options['display_quick_mod']) && $options['display_quick_mod'] == 1 && $settings['show_mark_read'];

	if ($showCheckboxes)
		echo '
		<form action="', $scripturl, '?action=quickmod" method="post" accept-charset="', $context['character_set'], '" name="quickModForm" id="quickModForm" style="margin: 0;">
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
			<input type="hidden" name="qaction" value="markread" />
			<input type="hidden" name="redirect_url" value="action=unreadreplies', (!empty($context['showing_all_topics']) ? ';all' : ''), $context['querystring_board_limits'], '" />';

	if (isset($context['topics_to_mark']) && !empty($settings['show_mark_read']))
	{
		// Generate the button strip.
		$mark_read = array(
			'mark_read' => array('text' => 'mark_as_read', 'image' => 'markread.gif', 'lang' => true, 'url' => $scripturl . '?action=markasread;sa=unreadreplies;topics=' . $context['topics_to_mark'] . ';' . $context['session_var'] . '=' . $context['session_id']),
		);

		if ($showCheckboxes)
			$mark_read['markselectread'] = array('text' => 'quick_mod_markread','image' => 'markselectedread.gif','lang' => true,'url' => 'javascript:document.quickModForm.submit();',);
	}

	if (!empty($context['topics']))
	{
		convertPageindex();
		echo '
			<div class="pagesection">
				<div class="pagelinks">', $context['page_index'], '</div>';

		if (!empty($mark_read))
			template_button_strip($mark_read, 'right');

		echo '
			</div>';

		echo '
			<ul class="reset a_topics_headers">
				<li>
					<a href="', $scripturl, '?action=unreadreplies', $context['querystring_board_limits'], ';sort=subject', $context['sort_by'] === 'subject' && $context['sort_direction'] === 'up' ? ';desc' : '', '">', $txt['subject'], $context['sort_by'] === 'subject' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a>
					/ <a href="', $scripturl, '?action=unreadreplies', $context['querystring_board_limits'], ';sort=replies', $context['sort_by'] === 'replies' && $context['sort_direction'] === 'up' ? ';desc' : '', '">', $txt['replies'], $context['sort_by'] === 'replies' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a>';

		// Show a "select all" box for quick moderation?
		if ($showCheckboxes)
				echo '
					/ <a href="', $scripturl, '?action=unreadreplies', $context['querystring_board_limits'], ';sort=last_post', $context['sort_by'] === 'last_post' && $context['sort_direction'] === 'up' ? ';desc' : '', '">', $txt['last_post'], $context['sort_by'] === 'last_post' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a>
					<input type="checkbox" onclick="invertAll(this, this.form, \'topics[]\');" class="input_check floatright" />';
		else
			echo '
					/ <a href="', $scripturl, '?action=unreadreplies', $context['querystring_board_limits'], ';sort=last_post', $context['sort_by'] === 'last_post' && $context['sort_direction'] === 'up' ? ';desc' : '', '">', $txt['last_post'], $context['sort_by'] === 'last_post' ? ' <img src="' . $settings['images_url'] . '/sort_' . $context['sort_direction'] . '.gif" alt="" />' : '', '</a>';
		echo '
				</li>
			</ul>';

		foreach ($context['topics'] as $topic)
		{
			a_topic($topic, $showCheckboxes);
		}

		echo '
			<div class="pagesection">';

		if (!empty($mark_read))
			template_button_strip($mark_read, 'right');

		echo '
				<div class="pagelinks">', $context['page_index'], '</div>
			</div>';
	}
	else
		echo '
			<h3 class="header_name">
				', $context['showing_all_topics'] ? $txt['msg_alert_none'] : $txt['unread_topics_visit_none'], '
			</h3>';

	if ($showCheckboxes)
		echo '
		</form>';

	echo '
	</div>';
}

?>