<?php
require_once( (file_exists('../includes/mysql_login.inc.local.php') ? '../includes/mysql_login.inc.local.php' : '../includes/mysql_login.inc.php') );

return array(
    'server'   => 'localhost',
    'port'     => 6667,
    'name'     => 'TheArchitect',
    'nick'     => 'TheArchitect',
    'channels' => array(
        '#zooomclan'
    ),
    'max_reconnects' => 3,
    'log_file'       => '/var/data/errlog/php_ircbot-log_',
    'timezone'		 => 'Europe/Zurich',
    'quit_message'	 => '',
    'db_server'		 => MYSQL_HOST,
    'db_name'		 => MYSQL_DBNAME,
    'db_user'		 => MYSQL_DBUSER,
    'db_pass'		 => MYSQL_DBPASS,
    'commands'       => array(
        'Command\Say'     => array(),
        'Command\Joke'    => array(),
        'Command\Poke'    => array(),
        'Command\Join'    => array(),
        'Command\Part'    => array(),
        'Command\Timeout' => array(),
        'Command\Quit'    => array(),
        'Command\Restart' => array(),
        'Command\Zchat'    => array()
    ),
    'listeners' => array(),
);