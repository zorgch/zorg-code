<?PHP
include_once($_SERVER['DOCUMENT_ROOT'].'/includes/util.inc.php');
class imap {	//IMAP connection handle	
	var $conn; 	//Mailbox overview handle	
	var $box;	//IMAP server address	
	var $server = "{localhost:143}";	//Mailbox overview sort options	
	var $mail_order = array("0" => SORTFROM, "1" => SORTDATE, "2" => SORTSUBJECT, "3" => SORTSIZE);	//Class main variable	
	var $data;	//IMAP class status variable	
	var $status;		

	/**	
	* @return imap	
	* @param $user string username	
	* @param $pw string mail Passwort	
	* @desc Klassenkonstruktor, erstellt eine Verbindung zu einer Mailbox 	
	*/	
	function imap($user, $pw) {		
		global $db;		
		/*$this->conn = @imap_open($this->server."INBOX",$user."@zooomclan.org",$pw);		
		if(!$this->conn) {
				imapStatic::writeError();
		}*/
	}


	
	/**	
	* @return void	
	* @param $oder int Sortby	
	* @desc Schreibt s?mtliche Mailheader einer Mailbox in imap::data    
	*/	
	function getMails($oder) {
		$this->box = @imap_sort($this->conn,$order,1,SE_UID);
			if($this->box) {
				foreach($this->box as $uid) {
					$id = @imap_msgno($this->conn,$uid);
						$header = @imap_header($this->conn,$id);
						if($id && $header) {
							$this->data['id'][] = $id;
							$this->data['subject'][] = $header->subject;
							$this->data['fromname'][] = $header->from[0]->personal;
							$this->data['from'][] = $header->from[0]->mailbox."@".$header->from[0]->host;					
							$this->data['to'][] = $header->toaddress;					
							$this->data['date'][] = $header->udate;					
							$this->data['size'][] = $header->Size;					
							$this->data['head'][] = $header;									
						} else {					
							imapStatic::writeError();					
						}			
				}		
			} else {			
				imapStatic::writeError();		
			}	
	}			
	
	/**	
	* @return void	
	* @param $uid int message Number	
	* @desc Schreibt ein Mail einer aktiven Mailbox in imap::data, wobei $uid die Message ID ist 	
	*/	
	function getMessage($uid) {	
			
		$header = @imap_header($this->conn,$uid);
				
		if($header) {			
			$this->data['id'] = $id;			
			$this->data['subject'] = $header->subject;			
			$this->data['fromname'] = $header->from[0]->personal;			
			$this->data['from'] = $header->from[0]->mailbox."@".$header->from[0]->host;			
			$this->data['to'] = $header->toaddress;			
			$this->data['date'] = $header->udate;			
			$this->data['size'] = $header->Size;			
			$this->data['head'] = $header;		
		} else {			
			imapStatic::writeError();		
		}				
		$body =  @imap_body($this->conn,$uid);		
		if($body) {			
			$this->data['body'] = imap_body($this->conn,$uid);	
			$this->data['body'] = strip_tags($this->data['body'],"<a> <img> <image> <b> <i> <br>");		
			$this->setFlag($uid,1);		
		} else {			
			imapStatic::writeError();			
		}
						
		return $this;	
	}		
	
	/**	
	* @return void	
	* @desc Gibt den Status der Aktiven Mailbox zur?ck (Anzahl Messages, Ungelesen, Neu)    
	*/	
	function getMailboxStatus() {		
		$status = @imap_status ($this->conn, $this->server."INBOX", SA_ALL);			
		if($status) {			
			$this->status['num'] = $status->messages;			
			$this->status['neu'] = $status->recent;			
			$this->status['ungelesen'] = $status->unseen;		
		} else {			
			imapStatic::writeError();		
		}	
	}		
	
	/**	
	* @return void	
	* @desc Schliesst die zugeh?rige IMAP Verbindung einer Klassen instanz	
	*/	
	function close() {		
		if(!@imap_close($this->conn)) imapStatic::writeError();	
	}		
	
	/**	
	* @return void	
	* @param $uid int Message ID	
	* @param $flag int Flag ID (1 = Gelesen, 2 = zum l?schen)	
	* @desc Setzt IMAP Flags auf eine Message 	
	*/	
	function setFlag($uid,$flag) {		
		$flag_array = array(1 => "\\Seen", 2 => "\\Deleted");		
		if(!@imap_setflag_full($this->conn,$uid,$flag_array[$flag],SE_UID)) 
		imapStatic::writeError();	
	}			
	
	/**	
	* @return void	
	* @desc L?scht alle zum l?schen gemerkte Mails einer imap instanz 	
	*/	
	function deleteAllFlagged() {		
		if(!@imap_expunge($this->conn)) 
		imapStatic::writeError();	
	}		
	
	// imap_mail ( string to, string subject, string message [, string additional_headers 	
	function sendMail($mailto, $subject, $message, $from) {		
		if(!@imap_mail($mailto, $subject, $message, $from)) imapStatic::writeError();	
	}	
}
		
class ImapStatic {		
	
	
	/**
	* @return object
	* @param $uid int
	* @param $imap object
	* @desc holt infis aus einem mail heraus
      subject
      from - Absender
      date - Sendedatum
      message_id - Message-ID
      references - bezieht sich auf Message-ID
      size - Gr??e in Byte
      uid - UID der Nachricht im Postfach
      msgno - Index der Nachricht im Postfach
      recent - Flag gesetzt
      flagged - Flag gesetzt
      answered - Flag gesetzt
      deleted - Flag gesetzt
      seen - Flag gesetzt
      draft - Flag gesetzt 	
 	*/
	function getMailStatus($uid, $imap) {
		$mail_status = imap_fetch_overview($imap->conn,$uid);
		return $mail_status[0];
	}
	
	
	/**	
	* @return void	
	* @desc Speichert IMAP Errors in der DB ab.	
	*/	
	function writeError() {		
		global $db;		
		$sql = "INSERT into error (user_id, do, ip, date) VALUES ('$_SESSION[user_id]','IMAP - "
		.@imap_last_error()."','$_SERVER[REMOTE_ADDR]',now())";		
		$db->query($sql, "imap.inc.php", 155);	
	}		

	function getNumnewmessages($user) {
		if(isset($_SESSION['user_id'])) {
			$imap = new imap($user->mail_username, $user->mail_userpw);	
			$imap->getMailboxStatus();		
			$numunreadmessages = $imap->status['ungelesen'];					
			if($numunreadmessages > 0) {				
				return '<a href="/messages.php">'.$numunreadmessages.' new Messages</a><br />';
			}
		}		
	}	
	
	/**	 
	* @return string	 
	* @param $imap object IMAP-Instanz	 
	* @desc Gibt die Mailbox?bersicht zur?ck	 
	*/	
	function getOverview($imap) {				
		$imap->getMailboxStatus();	
			
		$html = 	
		"<form action='$_SERVER[PHP_SELF]' method='post'>		
		<table width='80%' class='border' cellpadding='2' cellspacing='0'>			
		<tr><td align='center' class='title' colspan='4'>INBOX</td></tr>			
		<tr><td align='left'><b>Betreff</b></td>			
		<td align='left'><b>Absender</b></td>			
		<td align='left'><b>Datum</b></td>			
		<td align='left'><b>Gr&ouml;sse</b></td>			
		</tr>		
		";			
		
		
		$imap->getMails(1);
		for($i=0;$i < $imap->data['id'][0];$i++) {	
			if(($i % 2) == 0) {				
				$bgcol = " bgcolor=#".TABLEBACKGROUNDCOLOR." ";			
			} else {				
				$bgcol = "";			
			}			
			
			if($imap->data['fromname'][$i]) {
				$from = $imap->data['fromname'][$i];
			} else {				
				$from = $imap->data['from'][$i];
			}
			$add = "";
			$add_end = "";
			$mail_status = ImapStatic::getMailStatus($imap->data['id'][0] - $i,$imap); // db: $i durch $imap->data.... - $i ersetzt
			if($mail_status->seen == 0) {
				$add = "<b>";
				$add_end = "</b>";
			}
			$html .= 			
			"<tr>			
			<td align='left' $bgcol>			
			<a href='".$_SERVER['PHP_SELF']."?do=view&amp;uid=".$imap->data['id'][$i]."'>".$add			
			
			.($imap->data['subject'][$i] ? $imap->data['subject'][$i] : 'no subject').$add_end."</a></td>			
			<td align='left' $bgcol>".$add.$from.$add_end."</td>			
			<td align='left' $bgcol>".$add.datename($imap->data['date'][$i]).$add_end."</td>			
			<td align='left' $bgcol>".$add.round($imap->data['size'][$i] / 1024,1)."KB".$add_end."</td>
			<td align='left' $bgcol><input type='checkbox' name='check[]' value='".$imap->data['id'][$i]."'></td>			
			</tr>";		
		}				
		
		$html .= "<tr><td align='left'><B>Total: ".$imap->status['num']."</B></td>		
		<td align='left'><B>Ungelesen: ".$imap->status['ungelesen']."</B></td>		
		<td align='left'><B>Neu: ".$imap->status['neu']."</B></td>
		<td align='right' colspan='2'>
		<input type='submit' class='button' name='del' value='l&ouml;schen'></td></tr>";		
		$html .= "</table></form>";				
		return $html;	
	}		
	
	/**	
	* @return string	
	* @param $id int MessageID	
	* @param $imap object IMAP-Instanz	
	* @desc Gibt eine Message aus 	
	*/	
	function getMail($id,$imap) {				
		$imap->getMessage($id);		
		$html = 		"		
		<table width='80%' class='border' cellpadding='2' cellspacing='0'>		
		<tr><td align='left'>		
		<table><tr><td>		
		<form action='".$_SERVER['PHP_SELF']."?do=reply&message_id=".$id."' method='post'>		
		<input class='button' type='submit' value='antworten'></form>		
		</td><td>		
		<form action='".$_SERVER['PHP_SELF']."?do=forward&message_id=".$id."' method='post'>		
		<input class='button' type='submit' value='weiterleiten'></form>		
		</td><td>		
		<form action='".$_SERVER['PHP_SELF']."?do=delete&message_id=".$id."'  method='post'>		
		<input class='button' type='submit' value='l&ouml;schen'></form>		
		</td></tr></table>		
		</td></tr><tr><td align='left'>
		"		
		."<b>Subject: </b>".$imap->data['subject']		
		."</td></tr><tr><td align='left'><b>Absender: </b>"		
		.htmlentities("<").$imap->data['fromname'].htmlentities("> ")		
		.$imap->data['from']		
		."</td></tr><tr><td align='left'><b>Empf&auml;nger: </b>"		
		.$imap->data['to']		
		."</td></tr><tr><td align='left'><b>Datum: </b>"		
		.date("d.m.Y - H:i",$imap->data['date'])		
		."</td></tr><tr><td align='left' bgcolor='#".TABLEBACKGROUNDCOLOR."'>"		
		//.nl2br(imap_qprint($imap->data['body']))		
		.nl2br($imap->data['body'])		
		."</td></tr></table>";				
		return $html;	
	}		
	
	/**	
	* @param $message_id Falls reply, auf welche	
	* @return string	
	* @desc Gibt ein Form zum Message schreiben zur?ck 	
	*/	
	function newMail($toid="") {				
		$html =		"		<form action='".$_SERVER['PHP_SELF']."?do=send' method='post'>		
		<table width='550' class='border' cellpadding='2' cellspacing='0'>		
		<tr><td align='center' class='title' colspan='2'>Neue Message</td></tr>		
		<tr><td align='left'><b>Empf&auml;nger: </b>		
		</td><td align='left'>		";				
		if($toid) {			
			$html .= "				
			<input type='text' class='text' name='mailto' size='50' value='"				
			//.usersystem::id2mailuser($toid)." <".usersystem::id2mailuser($toid)."@zooomclan.org>'>				
			.usersystem::id2mailuser($toid)."@zooomclan.org'			";		
		} else {			
			$html .= "				
			<input type='text' class='text' name='mailto' size='50' value=''>			
			";		
		}				
		$html .= "		
		</td></tr><tr><td align='left'><b>Betreff: </b>		
		</td><td align='left'>		
		<input type='text' name='subject' class='text' size='50' value=''>		
		</td></tr><tr><td align='center' colspan='2'>		
		<textarea name='message' class='text' cols='100' rows='15'>"		
		."</textarea>		
		</td></tr><tr><td align='left' colspan='2'>		
		<input type='submit' value='senden' class='button'>		
		</td></tr></table>		
		</form>"
		;			
		return $html;	
	}		
	
	/**	
	* @param $message_id Falls reply, auf welche	
	* @return string	
	* @desc Gibt ein Form zum Message schreiben zur?ck 	
	*/	
	function replyMail($imap, $message_id="") {			
					
		if($message_id) {			
			$imap->getMessage($message_id);			
			//$mail = imap::getMessage(imap_msgno($imap->conn, $message_id));		
		}		
				
		if(substr($imap->data['subject'],0,3) != "Re:") {			
			$imap->data['subject'] = "Re: ".$imap->data['subject'];		
		}		
		
		$html =		"		
			<form action='".$_SERVER['PHP_SELF']."?do=send' method='post'>		
			<table width='550' class='border' cellpadding='2' cellspacing='0'>		
			<tr><td align='center' class='title' colspan='2'>Neue Message</td></tr>		
			<tr><td align='left'><b>Empf&auml;nger: </b>		</td><td align='left'>		
			<input type='text' class='text' name='mailto' size='50' value='".$imap->data['fromname']
			." <".$imap->data['from'].">'>		
			</td></tr><tr><td align='left'><b>Betreff: </b>		
			</td><td align='left'>		
			<input type='text' name='subject' class='text' size='50' value='".$imap->data['subject']."'>		
			</td></tr><tr><td align='center' colspan='2'>		
			<textarea name='message' class='text' cols='100' rows='15'>"		
			."> ".str_replace("\n", "\n> ", $imap->data['body'])		
			//.$imap->data['body']		
			."</textarea>		
			</td></tr><tr><td align='left' colspan='2'>		
			<input type='submit' value='senden' class='button'>		
			</td></tr></table>		
			</form>"
		;			
		return $html;	
	}
}
?>
