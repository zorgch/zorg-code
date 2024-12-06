<?php
global $db, $user, $smarty;

$e = $db->query('SELECT a.*
				FROM gallery_albums a, gallery_pics p
				WHERE a.id=p.album
				GROUP BY a.id
				ORDER BY a.id DESC',
				__FILE__, __LINE__, 'SELECT a.* FROM gallery_albums');
$galleries = array('0'=>'keine');
while ($d = $db->fetch($e)) {
	$galleries[$d['id']] = sprintf('#%d - %s', $d['id'], $d['name']);
}
$smarty->assign('galleries', $galleries);
$smarty->assign('gallery_ids', $gallery_ids);
$smarty->assign('gallery_names', $gallery_names);
