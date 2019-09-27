<?php

/*	@ Bloc 2019										*/
/*	@	SMF 2.0.x										*/

function template_body_id()
{
	echo ' id="m_display"';
}

function template_main()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	echo '
<div id="a_maside">
	<h2 class="header_name">', $context['subject'], '
		<small class="floatright smalltext grey">', $txt['read'], ' ', $context['num_views'], ' ', $txt['times'], '
			' , $context['is_sticky'] ? '<span class="icon-pin-outline red"></span>' : '' , '
			' , $context['is_locked'] ? '<span class="icon-lock blue"></span>' : '' , '
		</small>
	</h2>';

	if ($context['report_sent'])
		echo '
	<div class="information" id="profile_success">', $txt['report_sent'], '</div>';

	echo '
	<a id="top"></a>
	<a id="msg', $context['first_message'], '"></a>', $context['first_new_message'] ? '<a id="new"></a>' : '';

	// Is this topic also a poll?
	if ($context['is_poll'])
	{
		echo '
	<div id="poll">
		<div class="content" id="poll_options">
			<h3 id="pollquestion"><span class="icon-chart-bar grey"></span> ', $context['poll']['question'], $context['poll']['is_locked'] ? ' <span class="icon-lock"></span>' : '' , '</h3>';

		// Are they not allowed to vote but allowed to view the options?
		if ($context['poll']['show_results'] || !$context['allow_vote'])
		{
			echo '
			<dl class="options">';

			// Show each option with its corresponding percentage bar.
			foreach ($context['poll']['options'] as $option)
			{
				echo '
				<dt class="middletext bgline', $option['voted_this'] ? ' voted' : '', '"><span class="text">', $option['option'], '</span></dt>
				<dd class="middletext pollchart statsbar', $option['voted_this'] ? ' voted' : '', '">';

				if ($context['allow_poll_view'])
					echo '
					<span class="barchart"><span style="width: ', $option['percent'] , '%;"></span></span>
					<span class="percentage">', $option['votes'], ' (', $option['percent'], '%)</span>';
				else
					echo '
					<span></span><span></span>';

				echo '
				</dd>';
			}

			echo '
			</dl>';

			if ($context['allow_poll_view'])
				echo '
			<p><strong>', $txt['poll_total_voters'], ':</strong> ', $context['poll']['total_votes'], '</p>';
		}
		// They are allowed to vote! Go to it!
		else
		{
			echo '
			<form action="', $scripturl, '?action=vote;topic=', $context['current_topic'], '.', $context['start'], ';poll=', $context['poll']['id'], '" method="post" accept-charset="', $context['character_set'], '">';

			// Show a warning if they are allowed more than one option.
			if ($context['poll']['allowed_warning'])
				echo '
				<p class="information">', $context['poll']['allowed_warning'], '</p>';

			echo '
				<ul class="reset options" id="polloptions">';

			// Show each option with its button - a radio likely.
			foreach ($context['poll']['options'] as $option)
				echo '
					<li class="middletext"><span>', $option['vote_button'], '</span><label for="', $option['id'], '">', $option['option'], '</label></li>';

			echo '
				</ul>
				<div class="floatleft is_icon space" id="pollmoderation">
					<input type="submit" value="', $txt['poll_vote'], '" class="button_submit buts iconbig" />
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				</div>
			</form>
			';
		}
		// Build the poll moderation button array.
		$poll_buttons = array(
			'vote' => array('icon' => 'icon-flag', 'test' => 'allow_return_vote', 'text' => 'poll_return_vote', 'image' => 'poll_options.gif', 'lang' => true, 'url' => $scripturl . '?topic=' . $context['current_topic'] . '.' . $context['start']),
			'results' => array('icon' => 'icon-doc-text', 'test' => 'show_view_results_button', 'text' => 'poll_results', 'image' => 'poll_results.gif', 'lang' => true, 'url' => $scripturl . '?topic=' . $context['current_topic'] . '.' . $context['start'] . ';viewresults'),
			'change_vote' => array('icon' => 'icon-reply-outline', 'test' => 'allow_change_vote', 'text' => 'poll_change_vote', 'image' => 'poll_change_vote.gif', 'lang' => true, 'url' => $scripturl . '?action=vote;topic=' . $context['current_topic'] . '.' . $context['start'] . ';poll=' . $context['poll']['id'] . ';' . $context['session_var'] . '=' . $context['session_id']),
			'lock' => array('icon' => 'icon-lock', 'test' => 'allow_lock_poll', 'text' => (!$context['poll']['is_locked'] ? 'poll_lock' : 'poll_unlock'), 'image' => 'poll_lock.gif', 'lang' => true, 'url' => $scripturl . '?action=lockvoting;topic=' . $context['current_topic'] . '.' . $context['start'] . ';' . $context['session_var'] . '=' . $context['session_id']),
			'edit' => array('icon' => 'icon-edit', 'test' => 'allow_edit_poll', 'text' => 'poll_edit', 'image' => 'poll_edit.gif', 'lang' => true, 'url' => $scripturl . '?action=editpoll;topic=' . $context['current_topic'] . '.' . $context['start']),
			'remove_poll' => array('icon' => 'icon-trash', 'test' => 'can_remove_poll', 'text' => 'poll_remove', 'image' => 'admin_remove_poll.gif', 'lang' => true, 'custom' => 'onclick="return confirm(\'' . $txt['poll_remove_warn'] . '\');"', 'url' => $scripturl . '?action=removepoll;topic=' . $context['current_topic'] . '.' . $context['start'] . ';' . $context['session_var'] . '=' . $context['session_id']),
		);

		template_button_strip($poll_buttons,'right mobileclear');


		// Is the clock ticking?
		if (!empty($context['poll']['expire_time']))
			echo '
			<p class="information"><strong>', ($context['poll']['is_expired'] ? $txt['poll_expired_on'] : $txt['poll_expires_on']), ':</strong> ', $context['poll']['expire_time'], '</p>';

		echo '
			<span class="hr2"></span>
		</div>
	</div>';
	}

	// Does this topic have some events linked to it?
	if (!empty($context['linked_calendar_events']))
	{
		echo '
			<div class="information">
				<h3 class="header_name">', $txt['calendar_linked_events'], '</h3>';

		foreach ($context['linked_calendar_events'] as $event)
			echo '
					<h4>', $event['title'],'
						', ($event['can_edit'] ? '<a href="' . $event['modify_href'] . '"><span class="icon-cog-outline" title="' . $txt['modify'] . '"></span></a> ' : ''), '
					</h4>
					<small class="flow_hidden fullwidth">', $event['start_date'], ($event['start_date'] != $event['end_date'] ? ' - ' . $event['end_date'] : ''), '</small>';

		echo '
			</div>';
	}

	// Build the normal button array.
	$normal_buttons = array(
		'reply' => array('icon' => 'icon-comment', 'test' => 'can_reply', 'text' => 'reply', 'image' => 'reply.gif', 'lang' => true, 'url' => $scripturl . '?action=post;topic=' . $context['current_topic'] . '.' . $context['start'] . ';last_msg=' . $context['topic_last_message'], 'active' => true),
		'add_poll' => array('icon' => 'icon-chart-bar', 'test' => 'can_add_poll', 'text' => 'add_poll', 'image' => 'add_poll.gif', 'lang' => true, 'url' => $scripturl . '?action=editpoll;add;topic=' . $context['current_topic'] . '.' . $context['start']),
		'notify' => array('icon' => 'icon-bell', 'test' => 'can_mark_notify', 'text' => $context['is_marked_notify'] ? 'unnotify' : 'notify', 'image' => ($context['is_marked_notify'] ? 'un' : '') . 'notify.gif', 'lang' => true, 'custom' => 'onclick="return confirm(\'' . ($context['is_marked_notify'] ? $txt['notification_disable_topic'] : $txt['notification_enable_topic']) . '\');"', 'url' => $scripturl . '?action=notify;sa=' . ($context['is_marked_notify'] ? 'off' : 'on') . ';topic=' . $context['current_topic'] . '.' . $context['start'] . ';' . $context['session_var'] . '=' . $context['session_id']),
		'mark_unread' => array('icon' => 'icon-reply-outline', 'test' => 'can_mark_unread', 'text' => 'mark_unread', 'image' => 'markunread.gif', 'lang' => true, 'url' => $scripturl . '?action=markasread;sa=topic;t=' . $context['mark_unread_time'] . ';topic=' . $context['current_topic'] . '.' . $context['start'] . ';' . $context['session_var'] . '=' . $context['session_id']),
		'send' => array('icon' => 'icon-mail', 'test' => 'can_send_topic', 'text' => 'send_topic', 'image' => 'sendtopic.gif', 'lang' => true, 'url' => $scripturl . '?action=emailuser;sa=sendtopic;topic=' . $context['current_topic'] . '.0'),
		'print' => array('icon' => 'icon-print', 'text' => 'print', 'image' => 'print.gif', 'lang' => true, 'custom' => 'rel="new_win nofollow"', 'url' => $scripturl . '?action=printpage;topic=' . $context['current_topic'] . '.0'),
	);

	// Allow adding new buttons easily.
	call_integration_hook('integrate_display_buttons', array(&$normal_buttons));

	echo '
</div>
<article id="a_display" class="clear">
	<div class="pagesection clear">
		<div class="clear bgline">
			', !empty($modSettings['topbottomEnable']) ? $context['menu_separator'] . ' <a href="#lastPost" class="button_submit buts">' . $txt['go_down'] . '</a>' : '', '
			', template_button_strip($normal_buttons, 'right'), '
			<div class="button_submit buts is_icon">', $context['previous_next'], '</div>
		</div>
		<div class="pagelinks clear"><small class="desktop">', $txt['pages'], '</small> ', $context['page_index'], '</div>
	</div>';

	// Show the topic information - icon, subject, etc.
	echo '
	<div id="forumposts" class="a_topics clear">';

	if (!empty($settings['display_who_viewing']))
	{
		echo '
		<p id="whoisviewing" class="smalltext">';

		// Show just numbers...?
		if ($settings['display_who_viewing'] == 1)
				echo count($context['view_members']), ' ', count($context['view_members']) == 1 ? $txt['who_member'] : $txt['members'];
		// Or show the actual people viewing the topic?
		else
			echo empty($context['view_members_list']) ? '0 ' . $txt['members'] : implode(', ', $context['view_members_list']) . ((empty($context['view_num_hidden']) || $context['can_moderate_forum']) ? '' : ' (+ ' . $context['view_num_hidden'] . ' ' . $txt['hidden'] . ')');

		// Now show how many guests are here too.
		echo $txt['who_and'], $context['view_num_guests'], ' ', $context['view_num_guests'] == 1 ? $txt['guest'] : $txt['guests'], $txt['who_viewing_topic'], '
		</p>';
	}

	echo '
		<form action="', $scripturl, '?action=quickmod2;topic=', $context['current_topic'], '.', $context['start'], '" method="post" accept-charset="', $context['character_set'], '" name="quickModForm" id="quickModForm" style="margin: 0;" onsubmit="return oQuickModify.bInEditMode ? oQuickModify.modifySave(\'' . $context['session_id'] . '\', \'' . $context['session_var'] . '\') : false">';

	$ignoredMsgs = array();
	$removableMessageIDs = array();
	$alternate = false;

	// Get all the messages...
	while ($message = $context['get_message']())
	{
		$ignoring = false;
		$alternate = !$alternate;
		if ($message['can_remove'])
			$removableMessageIDs[] = $message['id'];

		// Are we ignoring this message?
		if (!empty($message['is_ignored']))
		{
			$ignoring = true;
			$ignoredMsgs[] = $message['id'];
		}

		// Show the message anchor and a "new" anchor if this message is new.
		if ($message['id'] != $context['first_message'])
			echo '
			<a id="msg', $message['id'], '"></a>', $message['first_new'] ? '<a id="new"></a>' : '';

		echo '
			<div class="', $message['approved'] ? 'normal_window' : 'approve_window', '">
				<div class="a_message">';

		// Show information about the poster of this message.
		echo '
					<div class="poster">
						<h3 class="floatleft">';

		// Show online and offline buttons?
		if (!empty($modSettings['onlineEnable']) && !$message['member']['is_guest'])
			echo '
							', $context['can_send_pm'] ? '<a href="' . $message['member']['online']['href'] . '" title="' . $message['member']['online']['label'] . '">' : '', !empty($message['member']['online']['is_online']) ? '<span class="icon-micro-new"></span>' : '' , $context['can_send_pm'] ? '</a>' : '';

		// Show a link to the member's profile.
		echo '
							', $message['member']['link'], '
						</h3>';

		if (!$message['member']['is_guest'])
		{
			// Show avatars, images, etc.?
			if (!empty($settings['show_user_images']) && empty($options['show_no_avatars']) && !empty($message['member']['avatar']['image']))
				echo '
						<a class="mavatar mobile floatright" href="', $scripturl, '?action=profile;u=', $message['member']['id'], '" style="background-image: url(', $message['member']['avatar']['href'], ');"></a>';
		}
		echo '
						<ul class="clear desktop reset smalltext" id="msg_', $message['id'], '_extra_info">';

		// Show the member's custom title, if they have one.
		if (!empty($message['member']['title']))
			echo '
							<li class="desktop title">', $message['member']['title'], '</li>';

		// Show the member's primary group (like 'Administrator') if they have one.
		if (!empty($message['member']['group']))
			echo '
							<li class="membergroup">', $message['member']['group'], '</li>';

		// Don't show these things for guests.
		if (!$message['member']['is_guest'])
		{
			// Show the post group if and only if they have no other group or the option is on, and they are in a post group.
			if ((empty($settings['hide_post_group']) || $message['member']['group'] == '') && $message['member']['post_group'] != '')
				echo '
							<li class="desktop postgroup">', $message['member']['post_group'], '</li>';
			echo '
							<li class="desktop stars">', $message['member']['group_stars'], '</li>';

			// Show avatars, images, etc.?
			if (!empty($settings['show_user_images']) && empty($options['show_no_avatars']) && !empty($message['member']['avatar']['image']))
				echo '
							<li>
								<a class="mavatar" href="', $scripturl, '?action=profile;u=', $message['member']['id'], '" style="background-image: url(', $message['member']['avatar']['href'], ');"></a>
							</li>';

			// Show how many posts they have made.
			if (!isset($context['disabled_fields']['posts']))
				echo '
							<li class="desktop postcount">', $txt['member_postcount'], ': ', $message['member']['posts'], '</li>';

			// Is karma display enabled?  Total or +/-?
			if ($modSettings['karmaMode'] == '1')
				echo '
							<li class="desktop karma"><span class="icon-heart"></span>', $message['member']['karma']['good'] - $message['member']['karma']['bad'], '</li>';
			elseif ($modSettings['karmaMode'] == '2')
				echo '
							<li class="desktop karma"><span class="icon-heart"></span> +', $message['member']['karma']['good'], '/-', $message['member']['karma']['bad'], '</li>';

			// Is this user allowed to modify this member's karma?
			if ($message['member']['karma']['allow'])
				echo '
							<li class="desktop karma_allow">
								<a href="', $scripturl, '?action=modifykarma;sa=applaud;uid=', $message['member']['id'], ';topic=', $context['current_topic'], '.' . $context['start'], ';m=', $message['id'], ';', $context['session_var'], '=', $context['session_id'], '"><span class="icon-thumbs-up circle green"></span></a>
								<a href="', $scripturl, '?action=modifykarma;sa=smite;uid=', $message['member']['id'], ';topic=', $context['current_topic'], '.', $context['start'], ';m=', $message['id'], ';', $context['session_var'], '=', $context['session_id'], '"><span class="icon-thumbs-down circle red"></span></a>
							</li>';

			// Show the member's gender icon?
			if (!empty($settings['show_gender']) && $message['member']['gender']['image'] != '' && !isset($context['disabled_fields']['gender']))
				echo '
							<li class="desktop gender">', $txt['gender'], ': ', $message['member']['gender']['image'], '</li>';

			// Show their personal text?
			if (!empty($settings['show_blurb']) && $message['member']['blurb'] != '')
				echo '
							<li class="desktop blurb">', $message['member']['blurb'], '</li>';

			// Any custom fields to show as icons?
			if (!empty($message['member']['custom_fields']))
			{
				$shown = false;
				foreach ($message['member']['custom_fields'] as $custom)
				{
					if ($custom['placement'] != 1 || empty($custom['value']))
						continue;
					if (empty($shown))
					{
						$shown = true;
						echo '
							<li class="im_icons desktop  flexlist">
								<ul>';
					}
					echo '
									<li>', $custom['value'], '</li>';
				}
				if ($shown)
					echo '
								</ul>
							</li>';
			}

			// This shows the popular messaging icons.
			if ($message['member']['has_messenger'] && $message['member']['can_view_profile'])
				echo '
							<li class="desktop im_icons flexlist">
								<ul class="reset flexlist">
									', !empty($message['member']['icq']['link']) ? '<li><a class="button_submit buts" href="' . $message['member']['icq']['href'] . '">ICQ</a></li>' : '', '
									', !empty($message['member']['msn']['link']) ? '<li><a class="button_submit buts" href="' . $message['member']['msn']['href'] . '">MSN</a></li>' : '', '
									', !empty($message['member']['aim']['link']) ? '<li><a class="button_submit buts" href="' . $message['member']['aim']['href'] . '">AIM</a></li>' : '', '
									', !empty($message['member']['yim']['link']) ? '<li><a class="button_submit buts" href="' . $message['member']['yim']['href'] . '">YIM</a></li>' : '', '
								</ul>
							</li>';

			// Show the profile, website, email address, and personal message buttons.
			if ($settings['show_profile_buttons'])
			{
				echo '
							<li class="desktop profile">
								<ul class="flexlist">';
				// Don't show the profile button if you're not allowed to view the profile.
				if ($message['member']['can_view_profile'])
					echo '
									<li><a href="', $message['member']['href'], '"><span class="icon-user-outline" title="' , $txt['view_profile'], '"></span></a></li>';

				// Don't show an icon if they haven't specified a website.
				if ($message['member']['website']['url'] != '' && !isset($context['disabled_fields']['website']))
					echo '
									<li><a href="', $message['member']['website']['url'], '" title="' . $message['member']['website']['title'] . '" target="_blank" class="new_win"><span class="icon-home-outline"></span></a></li>';

				// Don't show the email address if they want it hidden.
				if (in_array($message['member']['show_email'], array('yes', 'yes_permission_override', 'no_through_forum')))
					echo '
									<li><a href="', $scripturl, '?action=emailuser;sa=email;msg=', $message['id'], '" rel="nofollow"><span class="icon-mail"></span></a></li>';

				// Since we know this person isn't a guest, you *can* message them.
				if ($context['can_send_pm'])
					echo '
									<li><a href="', $scripturl, '?action=pm;sa=send;u=', $message['member']['id'], '" title="', $message['member']['online']['is_online'] ? $txt['pm_online'] : $txt['pm_offline'], '"><span class="icon-comment"></span></a></li>';

				echo '
								</ul>
							</li>';
			}

			// Any custom fields for standard placement?
			if (!empty($message['member']['custom_fields']))
			{
				foreach ($message['member']['custom_fields'] as $custom)
					if (empty($custom['placement']) || empty($custom['value']))
						echo '
							<li class="desktop custom">', $custom['title'], ': ', $custom['value'], '</li>';
			}

			// Are we showing the warning status?
			if ($message['member']['can_see_warning'])
				echo '
							<li class="desktop warning">', $context['can_issue_warning'] ? '<a href="' . $scripturl . '?action=profile;area=issuewarning;u=' . $message['member']['id'] . '">' : '', '<span class="icon-warning-empty" title="', $txt['user_warn_' . $message['member']['warning_status']], '"></span>', $context['can_issue_warning'] ? '</a>' : '', '<span class="warn_', $message['member']['warning_status'], '">', $txt['warn_' . $message['member']['warning_status']], '</span></li>';
		}
		// Otherwise, show the guest's email.
		elseif (!empty($message['member']['email']) && in_array($message['member']['show_email'], array('yes', 'yes_permission_override', 'no_through_forum')))
			echo '
							<li class="desktop email"><a href="', $scripturl, '?action=emailuser;sa=email;msg=', $message['id'], '" rel="nofollow">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt['email'] . '" title="' . $txt['email'] . '" />' : $txt['email']), '</a></li>';

		// Done with the information about the poster... on to the post itself.
		echo '
						</ul>
					</div>
					<div class="postarea">
						<div class="flow_hidden">
							<h4 id="subject_', $message['id'], '" class="mobile headerpost">
								<a href="', $message['href'], '" rel="nofollow">', $message['subject'], ' <small class="floatright">' , $message['time'], '</small></a>
							</h4>
							<div class="keyinfo desktop">
								<div class="messageicon">
									<img src="', $settings['images_url'] .'/post/svg/'. $message['icon'] . '.svg" alt=""', $message['can_modify'] ? ' id="msg_icon_' . $message['id'] . '"' : '', ' />
								</div>
								<h5 id="subject_', $message['id'], '">
									<a href="', $message['href'], '" rel="nofollow">', $message['subject'], '</a>
								</h5>
							</div>
							<div class="keyinfotext smalltext desktop">&#171; <strong>', !empty($message['counter']) ? $txt['reply_noun'] . ' #' . $message['counter'] : '', ' ', $txt['on'], ':</strong> ', $message['time'], ' &#187;</div>
							<div class="" id="msg_', $message['id'], '_quick_mod"></div>
						</div>';

		// Ignoring this user? Hide the post.
		if ($ignoring)
			echo '
						<div id="msg_', $message['id'], '_ignored_prompt" class="information">
							', $txt['ignoring_user'], '
							<a href="#" id="msg_', $message['id'], '_ignored_link" style="display: none;">', $txt['show_ignore_user_post'], '</a>
						</div>';

		// Show the post itself, finally!
		echo '
						<div class="post">';

		if (!$message['approved'] && $message['member']['id'] != 0 && $message['member']['id'] == $context['user']['id'])
			echo '
							<div class="approve_post information">
								', $txt['post_awaiting_approval'], '
							</div>';
		echo '
							<div class="inner" id="msg_', $message['id'], '"', '>', $message['body'], '</div>
						</div>';


		// Assuming there are attachments...
		if (!empty($message['attachment']))
		{
			echo '
						<div id="msg_', $message['id'], '_footer" class="attachments smalltext" style="columns: ' , ($modSettings['attachmentThumbWidth'] + 20) , 'px;">';

			$last_approved_state = 1;
			foreach ($message['attachment'] as $attachment)
			{
				echo '
							<div class="a_attach">';
				// Show a special box for unapproved attachments...
				if ($attachment['is_approved'] != $last_approved_state)
				{
					$last_approved_state = 0;
					echo '
								<div class="information">
									', $txt['attach_awaiting_approve'];

					if ($context['can_approve'])
						echo ':	<a href="', $scripturl, '?action=attachapprove;sa=all;mid=', $message['id'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['approve_all'], '</a>';

					echo '
								</div';
				}

				if ($attachment['is_image'])
				{
					if ($attachment['thumbnail']['has_thumb'])
						echo '
								<a href="#att' , $attachment['id'] , '">
									<img id="thumb' , $attachment['id'] , '" class="a_block" src="', $attachment['thumbnail']['href'], '" alt="', $attachment['name'], '" />
								</a>
								<div id="att' , $attachment['id'] , '" class="modal">
									<a href="#thumb' , $attachment['id'] , '"><span class="close">&times;</span></a>
									<a href="' . $attachment['href'] . ';image" target="_blank"><span class="ddload icon-picture"></span></a>
									<a href="' . $attachment['href'] . '"><span class="dsload icon-download-outline"></span></a>
									<img src="', $attachment['href'], ';image" class="modal-content" alt="*">
									<div class="caption">' , $attachment['name'], '</div>
								</div>
								';
					else
						echo '
								<img  class="a_block" src="' . $attachment['href'] . ';image" alt="" width="' . $attachment['width'] . '" height="' . $attachment['height'] . '"/>';
				}
				else
					echo '
								<a href="' . $attachment['href'] . '"><span class="a_block a_filetype">' , substr($attachment['name'],strlen($attachment['name'])-3,3) , ' <span class="icon-download-outline transient"></span></span>
								</a>';
				echo '
								<a class="a_block a_name" href="' . $attachment['href'] . '">' . $attachment['name'] . '</a>';

				if (!$attachment['is_approved'] && $context['can_approve'])
					echo '
								<a  class="a_block" href="', $scripturl, '?action=attachapprove;sa=approve;aid=', $attachment['id'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['approve'], '</a>]&nbsp;|&nbsp;[<a href="', $scripturl, '?action=attachapprove;sa=reject;aid=', $attachment['id'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['delete'], '</a>';

				echo '
								<div>', $attachment['size'], ($attachment['is_image'] ? ', ' . $attachment['real_width'] . 'x' . $attachment['real_height'] . ' - ' . $txt['attach_viewed'] : ' - ' . $txt['attach_downloaded']) . ' ' . $attachment['downloads'] . ' ' . $txt['attach_times'] . '.</div>
							</div>';
			}

			echo '
						</div>';
		}

		// If this is the first post, (#0) just say when it was posted - otherwise give the reply #.
		if ($message['can_approve'] || $context['can_reply'] || $message['can_modify'] || $message['can_remove'] || $context['can_split'] || $context['can_restore_msg'])
			echo '
							<ul class="reset smalltext quickbuttons">';

		// Maybe we can approve it, maybe we should?
		if ($message['can_approve'])
			echo '
								<li class="is_icon approve_button button_submit buts"><a href="', $scripturl, '?action=moderate;area=postmod;sa=approve;topic=', $context['current_topic'], '.', $context['start'], ';msg=', $message['id'], ';', $context['session_var'], '=', $context['session_id'], '"><span class="icon-thumbs-up iconbig mobile"></span><span class="desktop">', $txt['approve'], '</span></a></li>';

		// Can they reply? Have they turned on quick reply?
		if ($context['can_quote'] && !empty($options['display_quick_reply']))
			echo '
								<li class="is_icon quote_button button_submit buts"><a href="', $scripturl, '?action=post;quote=', $message['id'], ';topic=', $context['current_topic'], '.', $context['start'], ';last_msg=', $context['topic_last_message'], '" onclick="return oQuickReply.quote(', $message['id'], ');"><span class="icon-chat iconbig mobile"></span><span class="desktop">', $txt['quote'], '</span></a></li>';

		// So... quick reply is off, but they *can* reply?
		elseif ($context['can_quote'])
			echo '
								<li class="is_icon quote_button button_submit buts"><a href="', $scripturl, '?action=post;quote=', $message['id'], ';topic=', $context['current_topic'], '.', $context['start'], ';last_msg=', $context['topic_last_message'], '"><span class="icon-comment iconbig mobile"></span><span class="desktop">', $txt['quote'], '</span></a></li>';

		// Can the user modify the contents of this post?
		if ($message['can_modify'])
			echo '
								<li class="is_icon modify_button  button_submit buts"><a href="', $scripturl, '?action=post;msg=', $message['id'], ';topic=', $context['current_topic'], '.', $context['start'], '"><span class="icon-wrench-outline iconbig mobile"></span><span class="desktop">', $txt['modify'], '</span></a></li>';

		// How about... even... remove it entirely?!
		if ($message['can_remove'])
			echo '
								<li class="is_icon remove_button button_submit buts"><a href="', $scripturl, '?action=deletemsg;topic=', $context['current_topic'], '.', $context['start'], ';msg=', $message['id'], ';', $context['session_var'], '=', $context['session_id'], '" onclick="return confirm(\'', $txt['remove_message'], '?\');"><span class="icon-trash iconbig mobile"></span><span class="desktop">', $txt['remove'], '</span></a></li>';

		// What about splitting it off the rest of the topic?
		if ($context['can_split'] && !empty($context['real_num_replies']))
			echo '
								<li class="is_icon split_button button_submit buts"><a href="', $scripturl, '?action=splittopics;topic=', $context['current_topic'], '.0;at=', $message['id'], '"><span class="icon-flow-split iconbig mobile"></span><span class="desktop">', $txt['split'], '</span></a></li>';

		// Can we restore topics?
		if ($context['can_restore_msg'])
			echo '
								<li class="is_icon restore_button button_submit buts"><a href="', $scripturl, '?action=restoretopic;msgs=', $message['id'], ';', $context['session_var'], '=', $context['session_id'], '"><span class="icon-cog-outline iconbig mobile"></span><span class="desktop">', $txt['restore_message'], '</span></a></li>';

		// Show a checkbox for quick moderation?
		if (!empty($options['display_quick_mod']) && $options['display_quick_mod'] == 1 && $message['can_remove'])
			echo '
								<li class="inline_mod_check floatright" style="display: none;" id="in_topic_mod_check_', $message['id'], '"></li>';

		// Can the user modify the contents of this post?  Show the modify inline image.
		if ($message['can_modify'])
			echo '
								<li class="is_icon button_submit buts">
									<span class="icon-edit iconbig" id="modify_button_', $message['id'], '" style="cursor: pointer; " onclick="oQuickModify.modifyMsg(\'', $message['id'], '\')"></span>
								</li>';

		if ($message['can_approve'] || $context['can_reply'] || $message['can_modify'] || $message['can_remove'] || $context['can_split'] || $context['can_restore_msg'])
			echo '
							</ul>';
		echo '
					</div>
				</div>
				<div>';

		if ($settings['show_modify'] && !empty($message['modified']['name']))
			echo '
					<div class="smalltext modified information" id="modified_', $message['id'], '">
						<span class=""></span> <em>', $txt['last_edit'], ': ', $message['modified']['time'], ' ', $txt['by'], ' ', $message['modified']['name'], '</em>
					</div>';

		echo '
					<div class="smalltext reportlinks information">';

		// Maybe they want to report this post to the moderator(s)?
		if ($context['can_report_moderator'])
			echo '
						<a href="', $scripturl, '?action=reporttm;topic=', $context['current_topic'], '.', $message['counter'], ';msg=', $message['id'], '">', $txt['report_to_mod'], '</a> &nbsp;';

		// Can we issue a warning because of this post?  Remember, we can't give guests warnings.
		if ($context['can_issue_warning'] && !$message['is_message_author'] && !$message['member']['is_guest'])
			echo '
						<a href="', $scripturl, '?action=profile;area=issuewarning;u=', $message['member']['id'], ';msg=', $message['id'], '">
							<img src="', $settings['images_url'], '/warn.gif" alt="', $txt['issue_warning_post'], '" title="', $txt['issue_warning_post'], '" />
						</a>';
		echo '
						<span class="icon-flow-split"></span>';

		// Show the IP to this user for this post - because you can moderate?
		if ($context['can_moderate_forum'] && !empty($message['member']['ip']))
			echo '
						<a href="', $scripturl, '?action=', !empty($message['member']['is_guest']) ? 'trackip' : 'profile;area=tracking;sa=ip;u=' . $message['member']['id'], ';searchip=', $message['member']['ip'], '">', $message['member']['ip'], '</a> <a href="', $scripturl, '?action=helpadmin;help=see_admin_ip" onclick="return reqWin(this.href);" class="help">(?)</a>';
		// Or, should we show it because this is you?
		elseif ($message['can_see_ip'])
			echo '
						<a href="', $scripturl, '?action=helpadmin;help=see_member_ip" onclick="return reqWin(this.href);" class="help">', $message['member']['ip'], '</a>';
		// Okay, are you at least logged in?  Then we can show something about why IPs are logged...
		elseif (!$context['user']['is_guest'])
			echo '
						<a href="', $scripturl, '?action=helpadmin;help=see_member_ip" onclick="return reqWin(this.href);" class="help">', $txt['logged'], '</a>';
		// Otherwise, you see NOTHING!
		else
			echo '
						', $txt['logged'];

		echo '
					</div>';

		// Are there any custom profile fields for above the signature?
		if (!empty($message['member']['custom_fields']))
		{
			$shown = false;
			foreach ($message['member']['custom_fields'] as $custom)
			{
				if ($custom['placement'] != 2 || empty($custom['value']))
					continue;
				if (empty($shown))
				{
					$shown = true;
					echo '
					<div class="custom_fields_above_signature">
						<ul class="reset nolist">';
				}
				echo '
							<li>', $custom['value'], '</li>';
			}
			if ($shown)
				echo '
						</ul>
					</div>';
		}

		// Show the member's signature?
		if (!empty($message['member']['signature']) && empty($options['show_no_signatures']) && $context['signature_enabled'])
			echo '
					<div class="signature" id="msg_', $message['id'], '_signature"><div style="">', $message['member']['signature'], '</div></div>';

		echo '
				</div>
			</div>
		</div>';
	}

	echo '
		</form>
	</div>

	<a id="lastPost"></a>';

	// Show the page index... "Pages: [1]".
	echo '
	<div class="pagesection clear">
		<div class="pagelinks clear"><small class="desktop">', $txt['pages'], '</small> ', $context['page_index'], '</div>
		<div class="clear bgline">
			', !empty($modSettings['topbottomEnable']) ? $context['menu_separator'] . ' <a href="#top" class="button_submit buts">' . $txt['go_up'] . '</a>' : '', '
			', template_button_strip($normal_buttons, 'right'), '
		</div>
	</div>';

	$mod_buttons = array(
		'move' => array('aactive' => true, 'test' => 'can_move', 'text' => 'move_topic', 'image' => 'admin_move.gif', 'lang' => true, 'url' => $scripturl . '?action=movetopic;topic=' . $context['current_topic'] . '.0'),
		'delete' => array('test' => 'can_delete', 'text' => 'remove_topic', 'image' => 'admin_rem.gif', 'lang' => true, 'custom' => 'onclick="return confirm(\'' . $txt['are_sure_remove_topic'] . '\');"', 'url' => $scripturl . '?action=removetopic2;topic=' . $context['current_topic'] . '.0;' . $context['session_var'] . '=' . $context['session_id']),
		'lock' => array('test' => 'can_lock', 'text' => empty($context['is_locked']) ? 'set_lock' : 'set_unlock', 'image' => 'admin_lock.gif', 'lang' => true, 'url' => $scripturl . '?action=lock;topic=' . $context['current_topic'] . '.' . $context['start'] . ';' . $context['session_var'] . '=' . $context['session_id']),
		'sticky' => array('test' => 'can_sticky', 'text' => empty($context['is_sticky']) ? 'set_sticky' : 'set_nonsticky', 'image' => 'admin_sticky.gif', 'lang' => true, 'url' => $scripturl . '?action=sticky;topic=' . $context['current_topic'] . '.' . $context['start'] . ';' . $context['session_var'] . '=' . $context['session_id']),
		'merge' => array('test' => 'can_merge', 'text' => 'merge', 'image' => 'merge.gif', 'lang' => true, 'url' => $scripturl . '?action=mergetopics;board=' . $context['current_board'] . '.0;from=' . $context['current_topic']),
		'calendar' => array('test' => 'calendar_post', 'text' => 'calendar_link', 'image' => 'linktocal.gif', 'lang' => true, 'url' => $scripturl . '?action=post;calendar;msg=' . $context['topic_first_message'] . ';topic=' . $context['current_topic'] . '.0'),
	);

	// Restore topic. eh?  No monkey business.
	if ($context['can_restore_topic'])
		$mod_buttons[] = array('icon' => array('icon-right-open','icon-reply-outline'),'text' => 'restore_topic', 'image' => '', 'lang' => true, 'url' => $scripturl . '?action=restoretopic;topics=' . $context['current_topic'] . ';' . $context['session_var'] . '=' . $context['session_id']);

	// Allow adding new mod buttons easily.
	call_integration_hook('integrate_mod_buttons', array(&$mod_buttons));

	echo '
	<div class="clear">', template_button_strip($mod_buttons, 'bottom', array('id' => 'moderationbuttons_strip')), '</div>';

	// Show the jumpto box, or actually...let Javascript do it.
	echo '
	<hr class="clear" style="width: 100%;">
	<div class="button_submit clear_right buts floatright">', $context['previous_next'], '</div>
	<div class="plainbox floatleft" id="display_jump_to">&nbsp;</div>';

	if ($context['can_reply'] && !empty($options['display_quick_reply']))
	{
		echo '
	<a id="quickreply"></a>
	<h3 class="header_name clear">
		 ', $txt['quick_reply'], '
	</h3>
	<div id="quickReplyOptions">
			<div class="roundframe">
				<p class="smalltext lefttext">', $txt['quick_reply_desc'], '</p>
					', $context['is_locked'] ? '<p class="alert smalltext">' . $txt['quick_reply_warning'] . '</p>' : '',
					$context['oldTopicError'] ? '<p class="alert smalltext">' . sprintf($txt['error_old_topic'], $modSettings['oldTopicDays']) . '</p>' : '', '
					', $context['can_reply_approved'] ? '' : '<em>' . $txt['wait_for_approval'] . '</em>', '
					', !$context['can_reply_approved'] && $context['require_verification'] ? '<br />' : '', '
					<form action="', $scripturl, '?board=', $context['current_board'], ';action=post2" method="post" accept-charset="', $context['character_set'], '" name="postmodify" id="postmodify" onsubmit="submitonce(this);" style="margin: 0;">
						<input type="hidden" name="topic" value="', $context['current_topic'], '" />
						<input type="hidden" name="subject" value="', $context['response_prefix'], $context['subject'], '" />
						<input type="hidden" name="icon" value="xx" />
						<input type="hidden" name="from_qr" value="1" />
						<input type="hidden" name="notify" value="', $context['is_marked_notify'] || !empty($options['auto_notify']) ? '1' : '0', '" />
						<input type="hidden" name="not_approved" value="', !$context['can_reply_approved'], '" />
						<input type="hidden" name="goback" value="', empty($options['return_to_post']) ? '0' : '1', '" />
						<input type="hidden" name="last_msg" value="', $context['topic_last_message'], '" />
						<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
						<input type="hidden" name="seqnum" value="', $context['form_sequence_number'], '" />';

			// Guests just need more.
			if ($context['user']['is_guest'])
				echo '
						<strong>', $txt['name'], ':</strong> <input type="text" name="guestname" value="', $context['name'], '" size="25" class="input_text" tabindex="', $context['tabindex']++, '" />
						<strong>', $txt['email'], ':</strong> <input type="text" name="email" value="', $context['email'], '" size="25" class="input_text" tabindex="', $context['tabindex']++, '" /><br />';

			// Is visual verification enabled?
			if ($context['require_verification'])
				echo '
						<strong>', $txt['verification'], ':</strong>', template_control_verification($context['visual_verification_id'], 'quick_reply'), '<br />';

			echo '
						<div class="quickReplyContent">
							<textarea style="width: 100%; height: 10rem;" name="message" tabindex="', $context['tabindex']++, '"></textarea>
						</div>
						<div class="righttext padding">
							<input type="submit" name="post" value="', $txt['post'], '" onclick="return submitThisOnce(this);" accesskey="s" tabindex="', $context['tabindex']++, '" class="button_submit" />
							<input type="submit" name="preview" value="', $txt['preview'], '" onclick="return submitThisOnce(this);" accesskey="p" tabindex="', $context['tabindex']++, '" class="button_submit" />';

			if ($context['show_spellchecking'])
				echo '
							<input type="button" value="', $txt['spell_check'], '" onclick="spellCheck(\'postmodify\', \'message\');" tabindex="', $context['tabindex']++, '" class="button_submit" />';

			echo '
						</div>
					</form>
				</div>
			</div>
		</div>';
	}
	else
		echo '
		<br class="clear" />';

	echo '
</article>';

	if ($context['show_spellchecking'])
		echo '
			<form action="', $scripturl, '?action=spellcheck" method="post" accept-charset="', $context['character_set'], '" name="spell_form" id="spell_form" target="spellWindow"><input type="hidden" name="spellstring" value="" /></form>
				<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/spellcheck.js"></script>';

	echo '
				<script type="text/javascript" src="' . $settings['default_theme_url'] . '/scripts/topic.js"></script>
				<script type="text/javascript"><!-- // --><![CDATA[';

	if (!empty($options['display_quick_reply']))
		echo '
					var oQuickReply = new QuickReply({
						bDefaultCollapsed: ', !empty($options['display_quick_reply']) && $options['display_quick_reply'] == 2 ? 'false' : 'true', ',
						iTopicId: ', $context['current_topic'], ',
						iStart: ', $context['start'], ',
						sScriptUrl: smf_scripturl,
						sImagesUrl: "', $settings['images_url'], '",
						sContainerId: "quickReplyOptions",
						sImageId: "quickReplyExpand",
						sImageCollapsed: "collapse.gif",
						sImageExpanded: "expand.gif",
						sJumpAnchor: "quickreply"
					});';

	if (!empty($options['display_quick_mod']) && $options['display_quick_mod'] == 1 && $context['can_remove_post'])
		echo '
					var oInTopicModeration = new InTopicModeration({
						sSelf: \'oInTopicModeration\',
						sCheckboxContainerMask: \'in_topic_mod_check_\',
						aMessageIds: [\'', implode('\', \'', $removableMessageIDs), '\'],
						sSessionId: \'', $context['session_id'], '\',
						sSessionVar: \'', $context['session_var'], '\',
						sButtonStrip: \'moderationbuttons\',
						sButtonStripDisplay: \'moderationbuttons_strip\',
						bUseImageButton: false,
						bCanRemove: ', $context['can_remove_post'] ? 'true' : 'false', ',
						sRemoveButtonLabel: \'', $txt['quickmod_delete_selected'], '\',
						sRemoveButtonImage: \'delete_selected.gif\',
						sRemoveButtonConfirm: \'', $txt['quickmod_confirm'], '\',
						bCanRestore: ', $context['can_restore_msg'] ? 'true' : 'false', ',
						sRestoreButtonLabel: \'', $txt['quick_mod_restore'], '\',
						sRestoreButtonImage: \'restore_selected.gif\',
						sRestoreButtonConfirm: \'', $txt['quickmod_confirm'], '\',
						sFormId: \'quickModForm\'
					});';

	echo '
					if (\'XMLHttpRequest\' in window)
					{
						var oQuickModify = new QuickModify({
							sScriptUrl: smf_scripturl,
							bShowModify: ', $settings['show_modify'] ? 'true' : 'false', ',
							iTopicId: ', $context['current_topic'], ',
							sTemplateBodyEdit: ', JavaScriptEscape('
								<div id="quick_edit_body_container" style="width: 90%">
									<div id="error_box" style="padding: 4px;" class="error"></div>
									<textarea class="editor" name="message" rows="12" style="' . ($context['browser']['is_ie8'] ? 'width: 635px; max-width: 100%; min-width: 100%' : 'width: 100%') . '; margin-bottom: 10px;" tabindex="' . $context['tabindex']++ . '">%body%</textarea><br />
									<input type="hidden" name="' . $context['session_var'] . '" value="' . $context['session_id'] . '" />
									<input type="hidden" name="topic" value="' . $context['current_topic'] . '" />
									<input type="hidden" name="msg" value="%msg_id%" />
									<div class="righttext">
										<input type="submit" name="post" value="' . $txt['save'] . '" tabindex="' . $context['tabindex']++ . '" onclick="return oQuickModify.modifySave(\'' . $context['session_id'] . '\', \'' . $context['session_var'] . '\');" accesskey="s" class="button_submit" />&nbsp;&nbsp;' . ($context['show_spellchecking'] ? '<input type="button" value="' . $txt['spell_check'] . '" tabindex="' . $context['tabindex']++ . '" onclick="spellCheck(\'quickModForm\', \'message\');" class="button_submit" />&nbsp;&nbsp;' : '') . '<input type="submit" name="cancel" value="' . $txt['modify_cancel'] . '" tabindex="' . $context['tabindex']++ . '" onclick="return oQuickModify.modifyCancel();" class="button_submit" />
									</div>
								</div>'), ',
							sTemplateSubjectEdit: ', JavaScriptEscape('<input type="text" style="width: 90%;" name="subject" value="%subject%" size="80" maxlength="80" tabindex="' . $context['tabindex']++ . '" class="input_text" />'), ',
							sTemplateBodyNormal: ', JavaScriptEscape('%body%'), ',
							sTemplateSubjectNormal: ', JavaScriptEscape('<a href="' . $scripturl . '?topic=' . $context['current_topic'] . '.msg%msg_id%#msg%msg_id%" rel="nofollow">%subject%</a>'), ',
							sTemplateTopSubject: ', JavaScriptEscape($txt['topic'] . ': %subject% &nbsp;(' . $txt['read'] . ' ' . $context['num_views'] . ' ' . $txt['times'] . ')'), ',
							sErrorBorderStyle: ', JavaScriptEscape('1px solid red'), '
						});

						aJumpTo[aJumpTo.length] = new JumpTo({
							sContainerId: "display_jump_to",
							sJumpToTemplate: "<label class=\"smalltext\" for=\"%select_id%\">', $context['jump_to']['label'], ':<" + "/label> %dropdown_list%",
							iCurBoardId: ', $context['current_board'], ',
							iCurBoardChildLevel: ', $context['jump_to']['child_level'], ',
							sCurBoardName: "', $context['jump_to']['board_name'], '",
							sBoardChildLevelIndicator: "==",
							sBoardPrefix: "=> ",
							sCatSeparator: "-----------------------------",
							sCatPrefix: "",
							sGoButtonLabel: "', $txt['go'], '"
						});

						aIconLists[aIconLists.length] = new bIconList({
							sBackReference: "aIconLists[" + aIconLists.length + "]",
							sIconIdPrefix: "msg_icon_",
							sScriptUrl: smf_scripturl,
							bShowModify: ', $settings['show_modify'] ? 'true' : 'false', ',
							iBoardId: ', $context['current_board'], ',
							iTopicId: ', $context['current_topic'], ',
							sSessionId: "', $context['session_id'], '",
							sSessionVar: "', $context['session_var'], '",
							sLabelIconList: "', $txt['message_icon'], '",
							sBoxBackground: "transparent",
							sBoxBackgroundHover: "#ffffff",
							iBoxBorderWidthHover: 1,
							sBoxBorderColorHover: "#adadad" ,
							sContainerBackground: "#ffffff",
							sContainerBorder: "1px solid #adadad",
							sItemBorder: "1px solid #ffffff",
							sItemBorderHover: "1px dotted gray",
							sItemBackground: "transparent",
							sItemBackgroundHover: "#e0e0f0"
						});
					}';

	if (!empty($ignoredMsgs))
	{
		echo '
					var aIgnoreToggles = new Array();';

		foreach ($ignoredMsgs as $msgid)
		{
			echo '
					aIgnoreToggles[', $msgid, '] = new smc_Toggle({
						bToggleEnabled: true,
						bCurrentlyCollapsed: true,
						aSwappableContainers: [
							\'msg_', $msgid, '_extra_info\',
							\'msg_', $msgid, '\',
							\'msg_', $msgid, '_footer\',
							\'msg_', $msgid, '_quick_mod\',
							\'modify_button_', $msgid, '\',
							\'msg_', $msgid, '_signature\'

						],
						aSwapLinks: [
							{
								sId: \'msg_', $msgid, '_ignored_link\',
								msgExpanded: \'\',
								msgCollapsed: ', JavaScriptEscape($txt['show_ignore_user_post']), '
							}
						]
					});';
		}
	}

	echo '
				// ]]></script>';
}

?>
