<?php
// Namespace
namespace Listener;

/**
 * Watcher for messages in a Textfile to be posted to the IRC Channels
 *
 * @package IRCBot
 * @subpackage Listener
 * @author Oliver Raduner <zorg@raduner.ch>
 * @date 29.12.2014
 */
class Webchats extends \Library\IRC\Listener\Base {

    /**
     * Main function to execute when listen even occurs
     */
    public function execute($data) {
	    $args = $this->getInfo();
		$lastpos = 0;
	    $filepath = '/../../irclogs/localhost/#zooomclan.log';
	    
	    $this->bot->log( '$args: ' . var_dump($args), 'INFO' );
	    $this->bot->log( '$getInfo: ' . var_dump($this->getInfo()), 'INFO' );
	    
	    if (!file_exists($filePath)) die('File does not exist!'); // given file doesn't exist
        if (substr($filePath, -4, 4) != '.txt') die('Wrong file type, no .txt!'); // no .txt extension
		if (substr($filePath, -1) == '/') die('File not found!'); // only a path provided
		/*while (true) {
			$this->bot->log( 'I sleep now for 15 secs', 'INFO' );
		    
		    usleep(15000000); //wait 15 seconds
		    
		    $this->bot->log( 'Done sleeping - up and running', 'INFO' );
		*/    
		    clearstatcache(false, $filePath);
		    $len = filesize($filePath);
		    if ($len < $lastpos) {
		        //file deleted or reset
		        $lastpos = $len;
		    }
		    elseif ($len > $lastpos) {
			    $theFile = @fopen($filePath, 'rb'); // open the file for reading
		        if ($theFile === false)
		            die();
		        fseek($theFile, $lastpos);
		        while (!feof($theFile)) {
		            $buffer = fread($theFile, 4096);
		            
		            $this->bot->log( 'I should say the following to the channel ' . $args[2] . 'now:' . $buffer, 'INFO' );
		            $this->say($args->nick . ', welcome to channel ' . $args->channel . '. Try following commands: ' . $this->getCommandsName(), $args->channel );
		            $this->say($buffer, $args[2]); // Post to the channel
		            file_put_contents($theFile, ''); // Empty the Textfile
		            flush();
		        }
		        $lastpos = ftell($theFile);
		        fclose($theFile);
		        
		        $this->bot->log( 'Did my job and closed the file again :)', 'INFO' );
		    }
		//}
    }

    /**
     * Returns keywords that listener is listening to.
     *
     * @return array
     */
    public function getKeywords() {
        return array("!false"); // Wir missbrauchen den Keywords-Check weil wir das nicht brauchen...
    }
}
