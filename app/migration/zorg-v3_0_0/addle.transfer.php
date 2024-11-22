<?php
/**
 * Addle Data-Transfer
 *
 * Addle Datentransfer von Zorg V2 nach Zorg V3 MySQL-DB
 *
 * @author [z]biko
 * @date 16.05.2003
 * @version 1.0
 * @package zorg
 * @subpackage Addle
 */
echo 'DROP TABLE IF EXISTS `zooomclan`.`addle`';

echo 'CREATE TABLE `zooomclan`.`addle` (
`id` bigint( 20 ) unsigned NOT NULL AUTO_INCREMENT ,
`date` int( 10 ) unsigned NOT NULL default "0",
`player1` int( 10 ) unsigned NOT NULL default "0",
`player2` int( 10 ) unsigned NOT NULL default "0",
`score1` int( 2 ) unsigned NOT NULL default "0",
`score2` int( 2 ) unsigned NOT NULL default "0",
`data` varchar( 64 ) NOT NULL default "",
`nextturn` int( 1 ) unsigned NOT NULL default "1",
`nextrow` int( 1 ) unsigned NOT NULL default "0",
`finish` int( 1 ) unsigned NOT NULL default "0",
PRIMARY KEY ( `id` ) 
) TYPE = MYISAM ;';

echo 'INSERT INTO `zooomclan`.`addle` 
SELECT * 
FROM `v3`.`addle`';
