<?php
/**
 * Nuwani-v2 Bot Framework
 *
 * This file is part of the Nuwani v2 Bot Framework, a simple set of PHP classes which allow
 * you to set-up and run your own bot. It features advanced, PHP-5.3 based syntax for
 * optimal performance and security.
 *
 * Author: Peter Beverloo
 *         peter@lvp-media.com
 */

use Nuwani \ Bot;

class CTCP extends Nuwani \ ModuleBase
{
	/**
	 * Function: onCTCP
	 * Argument: pBot (Bot) - The bot who received this CTCP-message.
	 * Argument: sSource (string) - Source channel or nickname of the message.
	 * Argument: sType (string) - Type of message that has been received.
	 * Argument: sMessage (string) - The actual received message.
	 *
	 * To make the bot more interactive with the IRC server, as well as with
	 * other clients, we want a default set of replies to CTCP requests.
	 */
	
	public function onCTCP (Bot $pBot, $sSource, $sNickname, $sType, $sMessage)
	{
		switch (trim ($sType))
		{
			case 'VERSION':
			{
				$this -> sendCTCP ($pBot, $sNickname, 'VERSION', '"' . $pBot ['Nickname'] . '" running ' . VERSION_STR);
				break;
			}
			
			case 'PING':
			{
				$this -> sendCTCP ($pBot, $sNickname, 'PING', trim ($sMessage));
				break;
			}
			
			case 'TIME':
			{
				if (strlen ($sMessage))
					break ; /* reply from another client */
				
				$this -> sendCTCP ($pBot, $sNickname, 'TIME', date('D M d H:i:s Y'));
				break;
			}
			
			case 'URL':
			case 'FINGER':
			{
				$this -> sendCTCP ($pBot, $sNickname, $sType, 'Check out http://nuwani.googlecode.com!');
				break;
			}
		}
	}
	
	/**
	 * Function: sendCTCP
	 * Argument: pBot (Bot) - The bot that will be sending this message.
	 * Argument: sNickname (string) - Nickname to reply to, e.g. the destination.
	 * Argument: sType (string) - Type of CTCP message that will be send.
	 * Argument: sMessage (string) - The message associated with the CTCP.
	 *
	 * A small helper function to distribute the CTCP reply to the IRC
	 * Network, mainly because it needs all kinds of characters.
	 */
	
	private function sendCTCP (Bot $pBot, $sNickname, $sType, $sMessage)
	{
		$sCommand = 'NOTICE ' . $sNickname . ' :' . self :: CTCP . $sType;
		if (strlen ($sMessage) > 0)
			$sCommand .= ' ' . $sMessage;
		
		$pBot -> send ($sCommand . self :: CTCP);
	}
};

?>