<?php
// Namespace
namespace Command;

/**
 * Sends the arguments to the channel, like say from a user.
 *
 * @package IRCBot
 * @subpackage Command
 * @author Oliver Raduner <zorg@raduner.ch>
 *
 * @date 29.12.2014
 * @version 1.0
 */
class Zchat extends \Library\IRC\Command\Base {
	/**
     * The command's help text.
     *
     * @var string
     */
    protected $help = '!zchat [amount of messages]';

    /**
     * The number of arguments the command needs.
     *
     * @var integer
     */
    protected $numberOfArguments = 1;

    /**
     * Sends the arguments to the channel, like say from a user.
     *
     * IRC-Syntax: zchat [# of messages]
     */
    public function command() {
		if(!$db = mysqli_connect($this->bot->db_server, $this->bot->db_user, $this->bot->db_pass, $this->bot->db_name)){
			die('Unable to connect to database');
		}

	    $chatmessages = array();
		$sql = "SELECT
				chat.text
				, UNIX_TIMESTAMP(date) AS date
				, chat.from_mobile AS mobile
				, user.username AS username
				, user.clan_tag AS clantag
				, chat.user_id
			FROM chat
			LEFT JOIN user ON (chat.user_id = user.id)
			ORDER BY date DESC
			LIMIT 0,".$this->arguments[0];
		if(!$result = mysqli_query($db, $sql)){
			die('There was an error running the query');
		}

		while ($rs = mysqli_fetch_array($result)) {
			array_push($chatmessages, $rs);
		}

	    for($i=0;$i<=count($chatmessages)-1;$i++) {
	    	$this->say(
		    	sprintf(
		    	($chatmessages[$i]['mobile']==1 ? '[%s] %s%s (mobile): %s' : '[%s] %s%s: %s'),
		    		date("d.m.Y H:i", $chatmessages[$i]['date']),
		    		html_entity_decode($chatmessages[$i]['clantag']),
		    		html_entity_decode($chatmessages[$i]['username']),
		    		html_entity_decode($chatmessages[$i]['text'])
		    	)
	    	);
	    }

	    mysqli_close($db);
    }
}
?>