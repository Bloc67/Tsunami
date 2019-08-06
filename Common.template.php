<?php

/*	@ Bloc 2019										*/
/*	@	SMF 2.0.x										*/

function a_boardindex($board, $category = '')
{
	global $context, $scripturl, $settings, $options, $modSettings, $txt;

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
			<li class="a_board_lastpost"' , !empty($options['hidelastpost_boardindex']) ? '': ' style="display: none;"' , '>';

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

function a_topic($topic, $check = false)
{
	global $context, $scripturl, $settings, $options, $modSettings, $txt;

	$exclass = '';
	if($topic['is_sticky'])
		$exclass .= '<span class="icon-pin-outline smaller red" title="' . $txt['sticky_topic'] . '"></span>';
	if($topic['is_locked'])
		$exclass .= '<span class="icon-lock smaller blue" title="' . $txt['locked_topic'] . '"></span>';
	if($context['can_approve_posts'] && $topic['unapproved_posts'])
		$exclass .= '<span class="icon-flag smaller orange" title="' . $txt['awaiting_approval'] . '"></span>';
	if($topic['is_posted_in'])
		$exclass .= '<span class="icon-comment smaller green" title="' . $txt['participation_caption'] . '"></span>';
	if($topic['is_poll'])
		$exclass .= '<span class="icon-chart-bar smaller" title="' . $txt['poll'] . '"></span>';

	$hotish = '';
	if($topic['is_hot'])
		$hotish =' hot';
	if($topic['is_very_hot'])
		$hotish =' veryhot';

	echo '
			<ul class="reset a_topics_single' ,  $hotish , $options['display_quick_mod'] != 1 ? ' boxes' : '' , '">
				<li class="icon1"><img src="', $settings['images_url'], '/post/svg/', $topic['first_post']['icon'], '.svg" alt="" /></li>
				<li class="subject">
					<div ', (!empty($topic['quick_mod']['modify']) ? 'id="topic_' . $topic['first_post']['id'] . '" onmouseout="mouse_on_div = 0;" onmouseover="mouse_on_div = 1;" ondblclick="modify_topic(\'' . $topic['id'] . '\', \'' . $topic['first_post']['id'] . '\');"' : ''), '>
						<span id="msg_' . $topic['first_post']['id'] . '">', $topic['first_post']['link'], (!$context['can_approve_posts'] && !$topic['approved'] ? '&nbsp;<em class="red">(' . $txt['awaiting_approval'] . ')</em>' : ''), '</span>';

	// Is this topic new? (assuming they are logged in!)
	if ($topic['new'] && $context['user']['is_logged'])
			echo '
						<a href="', $topic['new_href'], '" id="newicon' . $topic['first_post']['id'] . '"><span class="icon-micro-new"></span></a>';

	echo ' ' , $exclass, '
					</div>
				</li>
				<li class="user">', $topic['first_post']['member']['link'], '</li>
				<li class="pages">', $topic['pages'], '</li>
				<li class="stats">', $topic['replies'], ' |	', $topic['views'], '</li>
				<li class="lastpost">
					<a href="', $topic['last_post']['href'], '"><span class="floatleft icon-chat-alt"></span> ', $topic['last_post']['time'], '</a> | ', $topic['last_post']['member']['link'], '
				</li>';

	// Show the quick moderation options?
	if (!empty($context['can_quick_mod']) || $check)
	{
		echo '
				<li class="moderation">';
		if ($options['display_quick_mod'] == 1 || $check)
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

?>
