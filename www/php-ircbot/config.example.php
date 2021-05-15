<?php
require_once ROOT_DIR . (file_exists(ROOT_DIR.'/../includes/mysql_login.inc.local.php') ? '/../includes/mysql_login.inc.local.php' : '/../includes/mysql_login.inc.php') ;
include(ROOT_DIR.'/../includes/util.inc.php');

return array(
    'server'   		=> '',
    'serverPassword' => '',
    'port'     		=> 6667,
    'name'     		=> '',
    'nick'     		=> '',
    'adminPassword' => '',
    'commandPrefix' => '!',
    'channels' 		=> array(
        '#zooomclan'
    ),
    'max_reconnects' => 1,
    'log_file'       => '/data/errlog/php_ircbot-log_',
    'timezone'		 => 'Europe/Zurich',
    'quit_message'	 => '',
    'db_server'		 => MYSQL_HOST,
    'db_name'		 => MYSQL_DBNAME,
    'db_user'		 => MYSQL_DBUSER,
    'db_pass'		 => MYSQL_DBPASS,
    'commands'       => array(
        'Command\Say'     	=> array(),
        'Command\Joke'    	=> array(),
        'Command\Poke'    	=> array(),
        'Command\Join'    	=> array(),
        'Command\Part'    	=> array(),
        'Command\Timeout'	=> array(),
        'Command\Quit'    	=> array(),
        'Command\Restart' 	=> array(),
        'Command\Zchat'    	=> array(),
        'Command\Webchat'    	=> array(),
        // DISABLED 'Command\Ip'    => array(),
        // DISABLED 'Command\Imdb'  => array(),
        /* DISABLED 'Command\Weather' => array(
            'yahooKey' => 'a',
        ),*/
    ),
    'listeners' => array(
        'Listener\Webchats' => array(),
        'Listener\Joins' => array(),
    ),
);
