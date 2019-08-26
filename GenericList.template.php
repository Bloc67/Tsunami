<?php
/**
 * Simple Machines Forum (SMF)
 *
 * @package SMF
 * @author Simple Machines
 * @copyright 2011 Simple Machines
 * @license http://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 2.0
 */

function template_show_list($list_id = null)
{
	global $context, $settings, $options, $scripturl, $txt, $modSettings;

	// Get a shortcut to the current list.
	$list_id = $list_id === null ? $context['default_list'] : $list_id;
	$cur_list = &$context[$list_id];

	// These are the main tabs that is used all around the template.
	if (isset($cur_list['list_menu'], $cur_list['list_menu']['show_on']))
		template_create_list_menu($cur_list['list_menu'], 'top');

	if (isset($cur_list['form']))
		echo '
	<form action="', $cur_list['form']['href'], '" method="post"', empty($cur_list['form']['name']) ? '' : ' name="' . $cur_list['form']['name'] . '" id="' . $cur_list['form']['name'] . '"', ' accept-charset="', $context['character_set'], '">
		<div class="generic_list">';

	// Show the title of the table (if any).
	if (!empty($cur_list['title']))
		echo '
			<div class="title_bar clear_right">
				<h3 class="titlebg">
					', $cur_list['title'], '
				</h3>
			</div>';
	// This is for the old style menu with the arrows "> Test | Test 1"
	if (empty($settings['use_tabs']) && isset($cur_list['list_menu'], $cur_list['list_menu']['show_on']) && ($cur_list['list_menu']['show_on'] == 'both' || $cur_list['list_menu']['show_on'] == 'top'))
		template_create_list_menu($cur_list['list_menu'], 'top');

	if (isset($cur_list['additional_rows']['top_of_list']))
		template_additional_rows('top_of_list', $cur_list);

	if (isset($cur_list['additional_rows']['after_title']))
	{
		echo '
			<div class="information flow_hidden">';
		template_additional_rows('after_title', $cur_list);
		echo '
			</div>';
	}

	if (!empty($cur_list['items_per_page']) || isset($cur_list['additional_rows']['bottom_of_list']))
	{
		echo '
			<div class="flow_auto">';
		
		$cur_list['page_index'] = convertPageindex($cur_list['page_index']);
		// Show the page index (if this list doesn't intend to show all items).
		if (!empty($cur_list['items_per_page']))
			echo '
				<div class="floatleft">
					<div class="pagesection">', $cur_list['page_index'], '</div>
				</div>';

		if (isset($cur_list['additional_rows']['above_column_headers']))
		{
			echo '
				<div class="floatright">';

			template_additional_rows('above_column_headers', $cur_list);

			echo '
				</div>';
		}

		echo '
			</div>';
	}

	//use to quickly identify
	//echo '* a_' , $list_id , ' *';
	
	echo '
			<ol class="reset a_table_grid" id="a_' , $list_id , '">
				<li class="a_headers">';

	// Show the column headers.
	$header_count = count($cur_list['headers']);
	if (!($header_count < 2 && empty($cur_list['headers'][0]['label'])))
	{
		$col =1;
		foreach ($cur_list['headers'] as $col_header)
		{
			echo '
					<div class="a_col" ', empty($col_header['colspan']) ? '' : ' style="grid-column: ' . $col . ' / span ' . $col_header['colspan'] . ';"', '>
						', empty($col_header['href']) ? '' : '<a href="' . $col_header['href'] . '" rel="nofollow">', empty($col_header['label']) ? '&nbsp;' : $col_header['label'], empty($col_header['href']) ? '' : '</a>', empty($col_header['sort_image']) ? '' : ' <span class="blue icon-' . $col_header['sort_image'] . '-open"></span>', '
					</div>';
			$col++;
		}

		echo '
				</li>';
	}

	// Show a nice message informing there are no items in this list.
	if (empty($cur_list['rows']) && !empty($cur_list['no_items_label']))
		echo '
				<li>
					<div class="padding">', $cur_list['no_items_label'], '</div>
				</li>';

	// Show the list rows.
	elseif (!empty($cur_list['rows']))
	{
		foreach ($cur_list['rows'] as $id => $row)
		{
			echo '
				<li class="a_row" id="list_', $list_id, '_', $id, '">';

			foreach ($row as $row_data)
				echo '
					<div class="a_col">', $row_data['value'], '</div>';

			echo '
				</li>';
		}
	}

	echo '
			</ol>';

	if (!empty($cur_list['items_per_page']) || isset($cur_list['additional_rows']['below_table_data']) || isset($cur_list['additional_rows']['bottom_of_list']))
	{
		echo '
			<div class="flow_auto">';

		// Show the page index (if this list doesn't intend to show all items).
		if (!empty($cur_list['items_per_page']))
			echo '
				<div class="floatleft">
					<div class="pagesection">', $cur_list['page_index'], '</div>
				</div>';

		if (isset($cur_list['additional_rows']['below_table_data']))
		{
			echo '
				<div class="floatright">';

			template_additional_rows('below_table_data', $cur_list);

			echo '
				</div>';
		}

		if (isset($cur_list['additional_rows']['bottom_of_list']))
		{
			echo '
				<div class="floatright">';

			template_additional_rows('bottom_of_list', $cur_list);

			echo '
				</div>';
		}

		echo '
			</div>';
	}

	if (isset($cur_list['form']))
	{
		foreach ($cur_list['form']['hidden_fields'] as $name => $value)
			echo '
			<input type="hidden" name="', $name, '" value="', $value, '" />';

		echo '
		</div>
	</form>';
	}

	// Tabs at the bottom.  Usually bottom alligned.
	if (!empty($settings['use_tabs']) && isset($cur_list['list_menu'], $cur_list['list_menu']['show_on']) && ($cur_list['list_menu']['show_on'] == 'both' || $cur_list['list_menu']['show_on'] == 'bottom'))
		template_create_list_menu($cur_list['list_menu'], 'bottom');

	if (isset($cur_list['javascript']))
		echo '
	<script type="text/javascript"><!-- // --><![CDATA[
		', $cur_list['javascript'], '
	// ]]></script>';
}

function template_additional_rows($row_position, $cur_list)
{
	global $context, $settings, $options;

	foreach ($cur_list['additional_rows'][$row_position] as $row)
		echo '
			<div class="additional_row', empty($row['class']) ? '' : ' ' . $row['class'], '"', empty($row['style']) ? '' : ' style="' . $row['style'] . '"', '>', $row['value'], '</div>';
}

function template_create_list_menu($list_menu, $direction = 'top')
{
	global $context, $settings;

	/**
		// This is use if you want your generic lists to have tabs.
		$cur_list['list_menu'] = array(
			// This is the style to use.  Tabs or Buttons (Text 1 | Text 2).
			// By default tabs are selected if not set.
			// The main difference between tabs and buttons is that tabs get highlighted if selected.
			// If style is set to buttons and use tabs is diabled then we change the style to old styled tabs.
			'style' => 'tabs',
			// The posisiton of the tabs/buttons.  Left or Right.  By default is set to left.
			'position' => 'left',
			// This is used by the old styled menu.  We *need* to know the total number of columns to span.
			'columns' => 0,
			// This gives you the option to show tabs only at the top, bottom or both.
			// By default they are just shown at the top.
			'show_on' => 'top',
			// Links.  This is the core of the array.  It has all the info that we need.
			'links' => array(
				'name' => array(
					// This will tell use were to go when they click it.
					'href' => $scripturl . '?action=theaction',
					// The name that you want to appear for the link.
					'label' => $txt['name'],
					// If we use tabs instead of buttons we highlight the current tab.
					// Must use conditions to determine if its selected or not.
					'is_selected' => isset($_REQUEST['name']),
				),
			),
		);
	*/

	$links = array();
	foreach ($list_menu['links'] as $link)
		$links[] = '<li><a class="button_submit' . ($link['is_selected'] ? ' active' : '') . '" href="' . $link['href'] . '">' . $link['label'] . '</a></li>';

	echo '
		<ul class="reset multi_set">
			', implode('', $links), '
		</ul>';
}

?>