<?php
// Copyright 2015 Las Venturas Playground. All rights reserved.
// Use of this source code is governed by the MIT license, a copy of which can
// be found in the LICENSE file.

// The Playground module provides a number of convenience commands to help people on IRC. When
// adding a new command to Sonium, please consider adding it here and checking it in rather than
// using the ephemeral command system in the Command module, so we can version it.
class Playground extends ModuleBase {
    private $m_commands;

    public function __construct() {
        // Initializes the commands. Each key should be the name of a command, whereas each value
        // should be a callable that will be invoked when the command gets executed.
        $this->m_commands = [
            'regdate' => [$this, 'getRegistrationDate']
        ];

        // Do a sanity check to make sure that all included commands are callable - failing at
        // startup is better than failing during runtime in this case.
        foreach ($this->m_commands as $command => $handler) {
            if (!is_callable($handler))
                die('The handler for command "' . $command . '" is not callable.');
        }
    }

    // Called when a message has been send to a channel. Commands supported by Sonium will be
    // checked here, and the call will be dispatched if applicable.
    public function onChannelPrivmsg(Bot $bot, $channel, $nickname, $message) {
        if (substr($message, 0, 1) != '!')
            return;

        $command = trim(substr($message, 1));
        $arguments = '';

        if (strpos($command, ' ') !== false)
            list($command, $arguments) = preg_split('/\s+/', $command, 2);

        if (!array_key_exists($command, $this->m_commands))
            return;

        call_user_func($this->m_commands[$command], $bot, $channel, $nickname, $arguments);
    }

    // Implementation of the !regdate command. Will retrieve the registration date of a player from
    // the database and output it to the channel where the command was executed.
    //
    // @command /regdate [nickname]
    private function getRegistrationDate(Bot $bot, $channel, $nickname, $arguments) {
        if (!strlen($arguments)) {
            $bot->send('PRIVMSG ' . $channel . ' :4Usage: !regdate [nickname]');
            return;
        }

        $nickname = substr($arguments, 0, strpos($arguments, ' ') ?: strlen($arguments));
        $registrationDate = null;

        $database = Database::getInstance();

        $statement = $database->prepare('SELECT registered FROM users WHERE nickname = ?');
        $statement->bind_param('s', $nickname);

        if ($statement->execute()) {
            $statement->bind_result($registrationDate);
            if ($statement->fetch()) {
                $formattedDate = date('F jS, Y', strtotime($registrationDate));

                $bot->send('PRIVMSG ' . $channel . ' :07' . $nickname . ' registered on 7' . $formattedDate . '.');
                return;
            }
        }

        $bot->send('PRIVMSG ' . $channel . ' :4Error: Unable to find that player.');
    }
};
