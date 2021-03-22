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
 * @date 30.12.2014
 * @version 1.0
 */
class Webchat extends \Library\IRC\Command\Base {
	/**
     * The command's help text.
     *
     * @var string
     */
    protected $help = '!webchat [text]';

    /**
     * The number of arguments the command needs.
     *
     * @var integer
     */
    protected $numberOfArguments = 1;

    /**
     * Sends the arguments to the channel, like say from a user.
     *
     * IRC-Syntax: !webchat [text]
     */
    public function command() {
		if(!$db = mysqli_connect($this->bot->db_server, $this->bot->db_user, $this->bot->db_pass, $this->bot->db_name)){
			die('Unable to connect to database');
		}
		
	    $sql = 'INSERT
	    		INTO chat (user_id, date, text, irc)
	    		VALUES (59, now(), "(IRC) '.escape_text($this->arguments[0]).'", "from")';
		if(!$result = mysqli_query($db, $sql)){
			die('There was an error running the query');
		} else {
	    	$this->say('Check! I posted that to Zorg');
	    }
		
	    mysqli_close($db);
    }
}
?>