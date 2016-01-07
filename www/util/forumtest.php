<?PHP
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/forum.inc.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/mysql.inc.php');

echo 'start';
flush();

$starttime = time();

/*
SELECT *
FROM comments_threads ct
LEFT JOIN comments c ON ( c.id = (
SELECT MAX( id )
FROM comments
WHERE thread_id = ct.thread_id ) )
ORDER BY c.date DESC 
*/

$sql = "
	  	SELECT 

			c.board, c.id, c.parent_id, c.text last_post_text, 
			UNIX_TIMESTAMP(c.date) last_post_date, 
			lu.id lu_id, lu.clan_tag lu_clan_tag, lu.username lu_username, 	  	
		  
			t.thread_id, 
			tu.id tu_id, tu.clan_tag tu_clan_tag, tu.username tu_username,
			UNIX_TIMESTAMP(t.date) thread_date
		
			, count(DISTINCT cnum.id) numposts
			
			FROM comments_threads ct
		
			LEFT JOIN comments c 
				ON (ct.last_comment_id = c.id)
			LEFT JOIN comments t 
				ON (ct.comment_id = t.id)
			LEFT JOIN comments cnum ON (ct.board = cnum.board AND ct.thread_id = cnum.thread_id)
			LEFT JOIN templates s ON s.id = c.thread_id
			LEFT JOIN bugtracker_bugs b ON b.id=c.thread_id
			LEFT JOIN gallery_pics p ON p.id = c.thread_id
			LEFT JOIN gallery_albums g ON g.id=p.album
			LEFT JOIN `events` ge ON ge.id=g.event
			LEFT JOIN user lu ON lu.id=c.user_id
			LEFT JOIN user tu ON tu.id=t.user_id
		
			WHERE c.board IN ('f', 'i', 'e', 'b', 't') 
		
			GROUP BY ct.thread_id
		
			ORDER BY ct.last_comment_id DESC
	  
	  	LIMIT 0,23
";

$result = $db->query($sql, __FILE__, __LINE__);

flush();


echo '
			<br />
			<table cellpadding="1" cellspacing="1" class="border" width="100%">
			<tr class="title">
			<td align="center">Thread</td>
			<td align="center" class="small">Thread starter</a></td>'
			.'<td align="center">#</td>'
			.'<td align="center">Datum</td>'
			.'<td align="center">Last Post</td>'
			.'</tr>'
;

while($rs = $db->fetch($result)) {
	
	$i++;

			$color = ($i % 2 == 0) ? BACKGROUNDCOLOR : TABLEBACKGROUNDCOLOR;

	    echo
	      '<tr>'
	      .'<td align="left" bgcolor="'.$color.'">'
	      .Comment::getLinkThread($rs['board'], $rs['thread_id'])
	      .'</td><td align="left" bgcolor="'.$color.'" class="small">'
	      .usersystem::userpagelink($rs['tu_id'], $rs['tu_clan_tag'], $rs['tu_username'])
	      .'</td><td align="center" bgcolor="'.$color.'" class="small">'
	      .$rs['numposts']
	      .'</td><td align="center" bgcolor="'.$color.'" class="small">'
	      .datename($rs['thread_date'])
	      .'</td><td align="left" bgcolor="'.$color.'" class="small">'
	      .'<a href="'.Comment::getLink($rs['board'], $rs['parent_id'], $rs['id'], $rs['thread_id']).'">'
	      .Comment::getTitle($rs['last_post_text'])
	      .'</a>'
	      .' &raquo;</a>'
	      .' by '
	      .usersystem::userpagelink($rs['lu_id'], $rs['lu_clan_tag'], $rs['lu_username'])
	      .'</td><td align="center" bgcolor="'.$color.'" class="small">'
	      .datename($rs['last_post_date'])
	      .'</td>'
	      .'</tr>'
	    ;
}

echo
	   	'<tr class="title">'
	   	.'<td colspan="6">'
	   	.'<table width="100%">'
			.'<tr>'
			.'<td align="center" class="bs" colspan="3">'
			.Forum::getNavigation($page, $pagesize, $numpages)
			.'</td>'
			
			.'</tr>'
			.'</table>'
			.'</tr>'
			.'</table>'
		;

		
$endtime = time();
echo '<br />Start: '.$starttime;
echo '<br />End: '.$endtime;
echo '<br />Diff: '.($endtime-$starttime);

	  
?>