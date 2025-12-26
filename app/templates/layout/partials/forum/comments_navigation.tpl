/**
 * @TODO move HTML-Markup to from PHP-Class to here!
 */
<table class="border" width="100%" itemscope itemtype="http://schema.org/BreadcrumbList">
	<tr><td class="small">

	$count = 1;
	$parent_id = $id;
	while ($parent_id > $thread_id) {
		$up_e = $db->query('SELECT * FROM comments WHERE id='.$parent_id, __FILE__, __LINE__, __FUNCTION__);
		$up = $db->fetch($up_e);

		<span itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
			<a itemprop="item" href="{get_changed_url change="parent_id='.$up['id'].'"}">
				<span itemprop="name">'.$count.' up</span>
				<meta itemprop="position" content="'.$count.'" />
			</a> | 
		</span>

		$parent_id = $up['parent_id'];
		$count++;
	}

	$html .= Comment::getLinkThread($board, $thread_id);

	</td></tr></table>


		<table bgcolor="{$color.background}" class="border forum"  style="table-layout:fixed;" width="100%">
			<tr>
				<td align="left" bgcolor="{$color.background}" valign="top"><nobr>
					<a href="{get_changed_url change="parent_id='.$id.'"}">
					<font size="4">^^^ Additional posts ^^^</font></a>
				</td>
			</tr>
		</table>