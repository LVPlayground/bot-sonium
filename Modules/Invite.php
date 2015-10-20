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

class Invite extends ModuleBase
{
	/**
	 * Function: onInvite
	 * Argument: pBot (Bot) - The bot who received the invite message.
	 * Argument: sInviter (string) - Nickname of the person who invites someone.
	 * Argument: sInvitee (string) - Nickname of the person being invited.
	 * Argument: sChannel (string) - Channel in which the invitation occurs.
	 *
	 * This function gets invoked when someone invites a person or a bot in to
	 * a channel. This could be us, and after all, it'd be fancy if the bot
	 * owner would be capable of inviting his/her bots in various channels.
	 */
	
	public function onInvite (Bot $pBot, $sInviter, $sInvitee, $sChannel)
	{
		if ($pBot ['Nickname'] != $sInvitee)
			return false ;
	
		$pEvaluationModule = ModuleManager :: getInstance () -> offsetGet ('Evaluation');
		if ($pEvaluationModule !== false)
		{
			if (!$pEvaluationModule -> checkSecurity ($pBot, ISecurityProvider :: BOT_OWNER))
				return false ;
			
			$pBot -> send ('JOIN ' . $sChannel);
			return true ;
		}
		
		return false ;
	}

};

?>