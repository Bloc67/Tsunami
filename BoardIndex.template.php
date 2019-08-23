<?php

/*	@ Bloc 2019										*/
/*	@	SMF 2.0.x										*/

function template_body_id()
{
	echo ' id="b_index"';
}

function more_aside()
{
	global $options, $context, $txt;

	echo '
		<div>
			<a href="' , $scripturl , '?action=profile;area=theme#a_hidelastpost"><span id="bindex_switch" class="icon-doc"></span>
			' , empty($options['hidelastpost_boardindex']) ? $txt['lastpostbindex'] : $txt['lastpostbindex2'] , '</a>
		</div>

		<div>
			<a href="' , $scripturl , '?action=profile;area=theme#a_hideinfo"><span id="binfo_switch" class="icon-doc"></span>
			' , empty($options['hideinfo_boardindex']) ? $txt['infobindex'] : $txt['infobindex2'] , '</a>
		</div>
		';

}


function template_main()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	echo '
<section id="a_boardindex_section">
<article id="a_boardindex">
	<div class="a_boards">';

	foreach ($context['categories'] as $category)
	{
		// If theres no parent boards we can see, avoid showing an empty category (unless its collapsed)
		if (empty($category['boards']) && !$category['is_collapsed'])
			continue;

		echo '
		<h3 id="category_', $category['id'], '">';

		// If this category even can collapse, show a link to collapse it.
		if ($category['can_collapse'])
			echo '
			<a class="collapse" href="', $category['collapse_href'], '"></a>';

		if (!$context['user']['is_guest'] && !empty($category['show_unread']))
		 echo '
			<a class="unreadlink floatright" href="', $scripturl, '?action=unread;c=', $category['id'], '">', $txt['view_unread_category'], '</a>';

		echo '
			', $category['link'], '
		</h3>';

		// Assuming the category hasn't been collapsed...
		if (!$category['is_collapsed'])
		{
			foreach ($category['boards'] as $board)
			{
				a_boardindex($board);
			}
		}
	}
	echo '
	</div>';

	if ($context['user']['is_logged'])
	{
		// Mark read button.
		$mark_read_button = array(
			'markread' => array('text' => 'mark_as_read', 'image' => 'markread.gif', 'lang' => true, 'url' => $scripturl . '?action=markasread;sa=all;' . $context['session_var'] . '=' . $context['session_id']),
		);

		// Show the mark all as read button?
		if ($settings['show_mark_read'] && !empty($context['categories']))
			echo '
	<div class="a_markread">', template_button_strip($mark_read_button, 'right'), '</div>';
	}

	echo '
</article>';
	template_info_center();
	
	echo '
</section>';
}

function template_news_slider()
{

	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	// Show the news fader?  (assuming there are things to show...)
	if ($settings['show_newsfader'] && !empty($context['fader_news_lines']))
	{
		echo '
	<div id="newsfader">
		<div class="cat_bar">
			<h3 class="catbg">
				<img id="newsupshrink" src="', $settings['images_url'], '/collapse.gif" alt="*" title="', $txt['upshrink_description'], '" align="bottom" style="display: none;" />
				', $txt['news'], '
			</h3>
		</div>
		<ul class="reset" id="smfFadeScroller"', empty($options['collapse_news_fader']) ? '' : ' style="display: none;"', '>';

			foreach ($context['news_lines'] as $news)
				echo '
			<li>', $news, '</li>';

	echo '
		</ul>
	</div>
	<script type="text/javascript" src="', $settings['default_theme_url'], '/scripts/fader.js"></script>
	<script type="text/javascript"><!-- // --><![CDATA[

		// Create a news fader object.
		var oNewsFader = new smf_NewsFader({
			sSelf: \'oNewsFader\',
			sFaderControlId: \'smfFadeScroller\',
			sItemTemplate: ', JavaScriptEscape('<strong>%1$s</strong>'), ',
			iFadeDelay: ', empty($settings['newsfader_time']) ? 5000 : $settings['newsfader_time'], '
		});

		// Create the news fader toggle.
		var smfNewsFadeToggle = new smc_Toggle({
			bToggleEnabled: true,
			bCurrentlyCollapsed: ', empty($options['collapse_news_fader']) ? 'false' : 'true', ',
			aSwappableContainers: [
				\'smfFadeScroller\'
			],
			aSwapImages: [
				{
					sId: \'newsupshrink\',
					srcExpanded: smf_images_url + \'/collapse.gif\',
					altExpanded: ', JavaScriptEscape($txt['upshrink_description']), ',
					srcCollapsed: smf_images_url + \'/expand.gif\',
					altCollapsed: ', JavaScriptEscape($txt['upshrink_description']), '
				}
			],
			oThemeOptions: {
				bUseThemeSettings: ', $context['user']['is_guest'] ? 'false' : 'true', ',
				sOptionName: \'collapse_news_fader\',
				sSessionVar: ', JavaScriptEscape($context['session_var']), ',
				sSessionId: ', JavaScriptEscape($context['session_id']), '
			},
			oCookieOptions: {
				bUseCookie: ', $context['user']['is_guest'] ? 'true' : 'false', ',
				sCookieName: \'newsupshrink\'
			}
		});
	// ]]></script>';
	}
}

function template_info_center()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	// Here's where the "Info Center" starts...
	echo '
	' , !empty($options['hideinfo_boardindex']) ? template_news_slider() : '' , ' 
	<div id="a_infocenter" class="a_infocenters"' , !empty($options['hideinfo_boardindex']) ? '': ' style="display: none;"' , '>
		<div id="a_info_items">';

	// This is the "Recent Posts" bar.
	if (!empty($settings['number_recent_posts']) && (!empty($context['latest_posts']) || !empty($context['latest_post'])))
	{
		echo '
			<div class="a_info_item" id="a_info_recent">
				<h4><a href="', $scripturl, '?action=recent">', $txt['recent_posts'], '</a></h4>';

		// Only show one post.
		if ($settings['number_recent_posts'] == 1)
		{
			// latest_post has link, href, time, subject, short_subject (shortened with...), and topic. (its id.)
			echo '
				<p>', $txt['recent_view'], ' &quot;', $context['latest_post']['link'], '&quot; ', $txt['recent_updated'], ' (', $context['latest_post']['time'], ')</p>';
		}
		// Show lots of posts.
		elseif (!empty($context['latest_posts']))
		{
			echo '
				<ol class="reset a_recentblock">';

			/* Each post in latest_posts has:
					board (with an id, name, and link.), topic (the topic's id.), poster (with id, name, and link.),
					subject, short_subject (shortened with...), time, link, and href. 
			[7] => Array
					(
						[board] => Array
							(
								[id] => 51
								[name] => Mosjonskroken
								[href] => http://127.0.0.1/smf20/index.php?board=51.0
								[link] => Mosjonskroken
							)

						[topic] => 84886
						[poster] => Array
							(
								[id] => 715
								[name] => Mammen
								[href] => http://127.0.0.1/smf20/index.php?action=profile;u=715
								[link] => Mammen
							)

						[subject] => Sv: Mammen skal i form (til neste KK-mila)
						[short_subject] => Sv: Mammen skal i form (...
						[preview] => 3.2 km l�ping p� tirsdag og 32 minutter med styrketrening i g�r.
						[time] => Thursday 09 Jun 2016 kl.13:17
						[timestamp] => 1465471050
						[raw_timestamp] => 1465471050
						[href] => http://127.0.0.1/smf20/index.php?topic=84886.msg1237388;topicseen#msg1237388
						[link] => Sv: Mammen skal i form (til neste KK-mila)
					)
					*/
			foreach ($context['latest_posts'] as $post)
				echo '
					<li>
						<ul class="rec_block">
							<li class="a_recent_post">', $post['link'], '</li> 
							<li class="a_recent_poster"><span class="icon-user-outline"></span>', $post['poster']['link'], '</li>
							<li class="a_recent_board"><span class="icon-folder" title="', $post['board']['name'], '"></span>', $post['board']['link'], '</li>
							<li class="a_recent_time"><span class="icon-clock" title="', $post['time'], '"></span>', $post['time'], '</li>
						</ul>
					</li>';
			echo '
				</ol>';
		}
		echo '
			</div>';
	}

	// Show information about events, birthdays, and holidays on the calendar.
	if ($context['show_calendar'])
	{
		echo '
			<div class="a_info_item" id="a_info_cal">
				<h4><a href="', $scripturl, '?action=calendar' . '">', $context['calendar_only_today'] ? $txt['calendar_today'] : $txt['calendar_upcoming'], '</a></h4>
				<p>';

		// Holidays like "Christmas", "Chanukah", and "We Love [Unknown] Day" :P.
		if (!empty($context['calendar_holidays']))
				echo '
					<span class="a_holiday">', $txt['calendar_prompt'], ' ', implode(', ', $context['calendar_holidays']), '</span>';

		// People's birthdays. Like mine. And yours, I guess. Kidding.
		if (!empty($context['calendar_birthdays']))
		{
			echo '
					<span class="a_birthday">
						<strong>', $context['calendar_only_today'] ? $txt['birthdays'] : $txt['birthdays_upcoming'], '</strong>: ';
			foreach ($context['calendar_birthdays'] as $member)
				echo '
						<a href="', $scripturl, '?action=profile;u=', $member['id'], '">', $member['is_today'] ? '<strong>' : '', $member['name'], $member['is_today'] ? '</strong>' : '', isset($member['age']) ? ' (' . $member['age'] . ')' : '', '</a>';
			echo '
					</span>';
		}
		// Events like community get-togethers.
		if (!empty($context['calendar_events']))
		{
			echo '
					<span class="a_event">
						<strong>', $context['calendar_only_today'] ? $txt['events'] : $txt['events_upcoming'], '</strong> ';
			/* Each event in calendar_events should have:
					title, href, is_last, can_edit (are they allowed?), modify_href, and is_today. */
			foreach ($context['calendar_events'] as $event)
				echo '
						', $event['can_edit'] ? '<a href="' . $event['modify_href'] . '" title="' . $txt['calendar_edit'] . '"><img src="' . $settings['images_url'] . '/icons/modify_small.gif" alt="*" /></a> ' : '', $event['href'] == '' ? '' : '<a href="' . $event['href'] . '">', $event['is_today'] ? '<strong>' . $event['title'] . '</strong>' : $event['title'], $event['href'] == '' ? '' : '</a>', $event['is_last'] ? '<br />' : ', ';
			echo '
					</span>';
		}
		echo '
				</p>
			</div>';
	}

	// Show statistical style information...
	if ($settings['show_stats_index'])
	{
		echo '
			<div class="a_info_item" id="a_info_stats">
				<h4><a href="', $scripturl, '?action=stats">', $txt['forum_stats'], '</a></h4>
				<ul>
					<li>
						', $context['common_stats']['total_posts'], ' ', $txt['posts_made'], ' ', $txt['in'], ' ', $context['common_stats']['total_topics'], ' ', $txt['topics'], ' ', $txt['by'], ' ', $context['common_stats']['total_members'], ' ', $txt['members'], '. ', !empty($settings['show_latest_member']) ? '<br>' . $txt['latest_member'] . ': <strong> ' . $context['common_stats']['latest_member']['link'] . '</strong>' : '', '
						| ', (!empty($context['latest_post']) ? $txt['latest_post'] . ': <strong>&quot;' . $context['latest_post']['link'] . '&quot;</strong> ( ' . $context['latest_post']['time'] . ' )' : ''), '
					</li>
					<li class="padding_top flow_hidden">
						<a href="', $scripturl, '?action=recent" class="button_submit buts">', $txt['recent_view'], '</a>', $context['show_stats'] ? '<span class="floatright"><a href="' . $scripturl . '?action=stats" class="button_submit buts">' . $txt['more_stats'] . '</a></span>' : '', '
					</li>
				</ul>
			</div>';
	}

	// "Users online" - in order of activity.
	echo '
			<div class="a_info_item" id="a_info_users">
				<h4>', $context['show_who'] ? '<a href="' . $scripturl . '?action=who' . '">' : '', $txt['online_users'], $context['show_who'] ? '</a>' : '', '</h4>
				<p>
					', $context['show_who'] ? '<a href="' . $scripturl . '?action=who">' : '', comma_format($context['num_guests']), ' ', $context['num_guests'] == 1 ? $txt['guest'] : $txt['guests'], ', ' . comma_format($context['num_users_online']), ' ', $context['num_users_online'] == 1 ? $txt['user'] : $txt['users'];

	// Handle hidden users and buddies.
	$bracketList = array();
	if ($context['show_buddies'])
		$bracketList[] = comma_format($context['num_buddies']) . ' ' . ($context['num_buddies'] == 1 ? $txt['buddy'] : $txt['buddies']);
	if (!empty($context['num_spiders']))
		$bracketList[] = comma_format($context['num_spiders']) . ' ' . ($context['num_spiders'] == 1 ? $txt['spider'] : $txt['spiders']);
	if (!empty($context['num_users_hidden']))
		$bracketList[] = comma_format($context['num_users_hidden']) . ' ' . $txt['hidden'];

	if (!empty($bracketList))
		echo ' (' . implode(', ', $bracketList) . ')';

	echo $context['show_who'] ? '</a>' : '';

	// Assuming there ARE users online... each user in users_online has an id, username, name, group, href, and link.
	if (!empty($context['users_online']))
	{
		echo '
					| ', sprintf($txt['users_active'], $modSettings['lastActive']), ': ', implode(', ', $context['list_users_online']);

		// Showing membergroups?
		if (!empty($settings['show_group_key']) && !empty($context['membergroups']))
			echo '
					| [' . implode(']&nbsp;&nbsp;[', $context['membergroups']) . ']';
	}

	echo '
					<br>', $txt['most_online_today'], ': <strong>', comma_format($modSettings['mostOnlineToday']), '</strong>.
					| ', $txt['most_online_ever'], ': ', comma_format($modSettings['mostOnline']), ' (', timeformat($modSettings['mostDate']), ')
				</p>
			</div>';

	// If they are logged in, but statistical information is off... show a personal message bar.
	if ($context['user']['is_logged'] && !$settings['show_stats_index'])
	{
		echo '
			<div class="a_info_item" id="a_info_logged">
				<h4>', $context['allow_pm'] ? '<a href="' . $scripturl . '?action=pm">' : '', $txt['personal_message'], $context['allow_pm'] ? '</a>' : '', '</h4>
				<p class="pminfo">
					<strong><a href="', $scripturl, '?action=pm">', $txt['personal_message'], '</a></strong>
					<span class="smalltext">
						', $txt['you_have'], ' ', comma_format($context['user']['messages']), ' ', $context['user']['messages'] == 1 ? $txt['message_lowercase'] : $txt['msg_alert_messages'], '.... ', $txt['click'], ' <a href="', $scripturl, '?action=pm">', $txt['here'], '</a> ', $txt['to_view'], '
					</span>
				</p>
			</div>';
	}

	echo '
		</div>
	</div>';
}

?>
