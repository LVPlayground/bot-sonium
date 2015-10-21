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

class Output extends Nuwani \ ModuleBase
{
	/**
	 * Function: onChannelJoin
	 * Argument: pBot (Bot) - The bot which received this message.
	 * Argument: sChannel (string) - Where did this person join?
	 * Argument: sNickname (string) - Nickname who joined this channel.
	 *
	 * A function that detects when someone enters a certain channel,
	 * specified by the two arguments in this function.
	 */
	
	public function onChannelJoin (Bot $pBot, $sChannel, $sNickname)
	{
		echo '[' . $sChannel . '] * ' . $sNickname . ' has joined the channel.'. NL;
	}
	
	/**
	 * Function: onChannelPart
	 * Argument: pBot (Bot) - The bot which received this message.
	 * Argument: sChannel (string) - The channel that he/she left.
	 * Argument: sNickname (string) - Who is parting the channel?
	 * Argument: sReason (string) - Reason why this person left the channel.
	 *
	 * This function gets invoked when someone leaves a channel, possibly
	 * with a defined reason, otherwise just.. a silent part.
	 */
	
	public function onChannelPart (Bot $pBot, $sChannel, $sNickname, $sReason)
	{
		echo '[' . $sChannel . '] * ' . $sNickname . ' has left the channel (' . $sReason . ')'. NL;
	}
	
	/**
	 * Function: onChannelKick
	 * Argument: pBot (Bot) - The bot which received this message.
	 * Argument: sChannel (string) - Channel this kick occured in.
	 * Argument: sKicked (string) - Nickname of the one who got kicked.
	 * Argument: sKicker (string) - The person who kicked the former nickname.
	 * Argument: sReason (string) - Why did this person get kicked?
	 *
	 * Of course we want to be able to catch FiXeR's being kicked from the
	 * channel for various reasons, which is what this function does.
	 */
	 
	public function onChannelKick (Bot $pBot, $sChannel, $sKicked, $sKicker, $sReason)
	{
		echo '[' . $sChannel . '] * ' . $sKicked . ' has been kicked by ' . $sKicker . ' (' . $sReason . ')' . NL;
	}
	
	/**
	 * Function: onChannelPrivmsg
	 * Argument: pBot (Bot) - The bot which received this message.
	 * Argument: sChannel (string) - The channel this message was spammed in
	 * Argument: sNickname (string) - Nickname who is messaging us (or the channel).
	 * Argument: sMessage (string) - The message being send to us.
	 *
	 * When we receive a normal message, in a normal channel, this is the function
	 * that will be called by the Bot-core system.
	 */
	 
	public function onChannelPrivmsg (Bot $pBot, $sChannel, $sNickname, $sMessage)
	{
		echo '[' . $sChannel . '] <' . $sNickname . '> ' . $sMessage . NL;
	}
	
	/**
	 * Function: onPrivmsg
	 * Argument: pBot (Bot) - The bot which received this message.
	 * Argument: sNickname (string) - Nickname who is PM'ing us.
	 * Argument: sMessage (string) - The message being send to us.
	 * 
	 * This function will receive the private messages received by us which
	 * did not occur in a channel, which could be an upset estroe.
	 */
	 
	public function onPrivmsg (Bot $pBot, $sNickname, $sMessage)
	{
		echo '[' .$sNickname . '] private: ' . $sMessage . NL;
	}
	
	/**
	 * Function: onCTCP
	 * Argument: sDestination (string) - Where did this CTCP come from?
	 * Argument: sNickname (string) - Who did send us this CTCP message?
	 * Argument: sType (string) - Type of CTCP message that has been received.
	 * Argument: sMessage (string) - The actual CTCP message.
	 *
	 * This function gets invoked when someone sends us a CTCP message, which
	 * could, for example, be an ACTION.
	 */
	 
	public function onCTCP (Bot $pBot, $sDestination, $sNickname, $sType, $sMessage)
	{
		if ($sDestination [0] != '#') // We only want channel messages;
			return ;
		
		echo '[' . $sDestination . '] * ' . $sNickname . ' ' . $sMessage . NL;
	}
	
	/**
	 * Function: onError
	 * Argument: nErrorType (integer) - Type of error that has occured, like a warning.
	 * Argument: sErrorString (string) - A textual representation of the error
	 * Argument: sErrorFile (string) - File in which the error occured
	 * Argument: nErrorLine (integer) - On which line did the error occur?
	 *
	 * An error could occur for various reasons. Not-initialised variables, deviding
	 * things by zero, or using older PHP functions which shouldn't be used.
	 */
	
	public function onError (Bot $pBot, $nErrorType, $sErrorString, $sErrorFile, $nErrorLine)
	{
		switch ($nErrorType)
		{
			case E_WARNING:		echo '[Warning]';	break;
			case E_USER_WARNING:	echo '[Warning]';	break;
			case E_NOTICE:		echo '[Notice]';	break;
			case E_USER_NOTICE:	echo '[Notice]';	break;
			case E_DEPRECATED:	echo '[Deprecated]';	break;
			case E_USER_DEPRECATED:	echo '[Deprecated]';	break;
		}
		
		echo ' Error occured in "' . $sErrorFile . '" on line ' . $nErrorLine . ': "';
		echo $sErrorString . '".' . NL;
	}
	
	/**
	 * Function: onException
	 * Argument: pBot (Bot) - The bot that was active while the exception occured.
	 * Argument: sSource (string) - Source of the place where the exception began.
	 * Argument: pException (Exception) - The exception that has occured.
	 *
	 * When exceptions occur, it would be quite convenient to be able and fix them
	 * up. That's why this function exists - output stuff about the exception.
	 */
	
	public function onException (Bot $pBot, $sSource, Exception $pException)
	{
		$sMessage  = '[Exception] Exception occured in "' . $pException -> getFile () . '" on line ';
		$sMessage .= $pException -> getLine () . ': "' . $pException -> getMessage () . '".' . NL;
		
		if ($sSource !== null && $pBot instanceof Bot)
		{
			$pBot -> send ('PRIVMSG ' . $sSource . ' :' . $sMessage);
		}
		
		echo $sMessage;
	}
	
	/**
	 * Function: onRawSend
	 * Argument: pBot (Bot) - The bot that is sending the message
	 * Argument: sCommand (string) - The message that's being send.
	 *
	 * This function gets invoked when a bot sends out some command. This could
	 * be a message, but also a ping, connect or anything else. Use appropriate!
	 */
	
	public function onRawSend (Bot $pBot, $sCommand)
	{
		if (strtoupper (substr ($sCommand, 0, 7)) == 'PRIVMSG')
		{
			list ($_foo, $sChannel, $sMessage) = explode (' ', $sCommand, 3);
			if (substr ($sMessage, 0, 1) == ':') // Remove the column;
				$sMessage = substr ($sMessage, 1);
			
			echo '[' . $sChannel . '] <' . $pBot ['Nickname'] . '> ' . $sMessage . NL;
		}
	}
};

?>