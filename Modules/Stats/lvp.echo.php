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

class LvpEchoStatistics
{
	/**
	 * Property: m_aPlayers
	 *
	 * This property contains an array of the players which currently are in-game.
	 * We'll check whether they are registered, and if they are, log it!
	 */
	
	private $m_aPlayers = array ();
	
	/**
	 * Property: m_aCachedNicks
	 *
	 * This array will contain a list of cached nicknames from people who have
	 * registered, to ease- and speed up detections on join.
	 */
	
	private $m_aCachedNicks = array ();
	
	/**
	 * Property: m_nLastCleared
	 *
	 * Defines the day the usernames in the cache got cleared for their ingame
	 * time, should happen at (or near) midnight.
	 */
	
	private $m_nLastCleared;
	
        private function stripFormat ($sMessage)
        {
                return preg_replace
                (
                        '/(' .
                        ModuleBase :: BOLD . '|' .
                        ModuleBase :: COLOUR . '\d{0,2}(?:,\d{1,2}|)|' .
                        ModuleBase :: CLEAR . '|' .
                        ModuleBase :: INVERSE . '|' .
                        ModuleBase :: ITALIC . '|' .
                        ModuleBase :: UNDERLINE . ')/',
                        '',
                        $sMessage
                );
        }

	/**
	 * Function: onPrivmsg
	 * Argument: pBot (Bot) - The bot who received the public channel message
	 * Argument: sChannel (string) - Channel in which we received the message
	 * Argument: sNickname (string) - The nickname associated with this message
	 * Argument: sMessage (string) - And of course the actual message we received
	 *
	 * This function will be invoked when someone chats in the channel we're
	 * interested in. We'll be parsing the message and handling it if needed.
	 */
	
	public function onPrivmsg (Bot $pBot, $sChannel, $sNickname, $sMessage)
	{
		if (!isset ($sNickname [5]) || $sNickname [0] != 'N' || $sNickname [2] != 'w' || $sNickname [4] != 'n' || $sNickname [5] != 'i')
			return ;
		
		if ($this -> m_nLastCleared != date ('j'))
		{
			$this -> m_nLastCleared = date ('j');
			echo 'Cleared the nick cache..' . "\n";
		}
		
		$aMessage = explode (' ', $this->stripFormat($sMessage));
		if (isset ($aMessage [4]) && $aMessage [4] == 'the' && substr ($aMessage [1], -3) == '***')
		{
			$nPlayerId   = substr ($aMessage [0], 1, -1);
			$sNickname   = $aMessage [2];
			$bRegistered = false;
			
			if ($aMessage [3] == 'joined')
			{
				if (isset ($this -> m_aCachedNicks [$sNickname]) || $this -> checkRegistered ($sNickname))
				{
					$this -> m_aPlayers [$sNickname] = array
					(
						'userid'	=> $this -> m_aCachedNicks [$sNickname],
						'playerid'	=> $nPlayerId,
						'joined'	=> time (),
					);
                                        
                                        //echo 'XXX Join' . PHP_EOL;
				}
				
				#file_put_contents ('/lvp/Sources/Sync/joins.txt', file_get_contents ('/lvp/Sources/Sync/joins.txt') + 1);
				return ;
			}
			else
			{
				if (!isset ($this -> m_aPlayers [$sNickname]) || $this -> m_aPlayers [$sNickname]['playerid'] != $nPlayerId)
					return ;
				
				$nUserId = (int) $this -> m_aPlayers [$sNickname]['userid'];
				$nIngame = (time() - $this -> m_aPlayers [$sNickname]['joined']);
				
				$pDatabase = Database :: getInstance ();
				$pDatabase -> query ('
					INSERT INTO
						samp_ingame
						(user_id, part_time, session_time)
					VALUES
						(' . $nUserId . ', NOW(), ' . $nIngame . ')');
				
				$this -> m_aPlayers [$sNickname] = array ();
                                
                                //echo 'XXX Part' . PHP_EOL;
			}
		}
		
		return ;
	}
	
	/**
	 * Function: checkRegistered
	 * Argument: sNickname (string) - Nickname of the person you wish to check for.
	 *
	 * This function will check whether a specific nickname has been registered
	 * or if it's random; a one time joining player who we'll never hear of again.
	 */
	
	public function checkRegistered ($sNickname)
	{
		$pDatabase = Database :: getInstance ();
		$pQuery    = $pDatabase -> query ('
			SELECT
				user_id
			FROM
				users
			WHERE
				nickname="' . $pDatabase -> real_escape_string ($sNickname) . '"');
		
		if ($pQuery !== false && $pQuery -> num_rows > 0)
		{
			$aFetchedRow = $pQuery -> fetch_assoc ();
			$this -> m_aCachedNicks [$sNickname] = $aFetchedRow ['user_id'];
			
			$pQuery -> close ();
			return true ;
		}
		
		return false ;
	}

}

?>
