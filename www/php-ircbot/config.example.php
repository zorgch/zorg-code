<?php
require ROOT_DIR.'/../includes/config.inc.php';
include ROOT_DIR.'/../includes/util.inc.php';

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
    'db_server'		 => $_ENV['MYSQL_HOST'],
    'db_name'		 => $_ENV['MYSQL_DATABASE'],
    'db_user'		 => $_ENV['MYSQL_USER'],
    'db_pass'		 => $_ENV['MYSQL_PASSWORD'],
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
