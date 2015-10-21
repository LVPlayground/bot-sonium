<?php
// Copyright 2015 Las Venturas Playground. All rights reserved.
// Use of this source code is governed by the MIT license, a copy of which can
// be found in the LICENSE file.

// The BotVersion module keeps track of the current version of the bot (using the VERSION file in
// its directory), and sends an announcement to a configured channel when an update has been found.
class BotVersion extends ModuleBase {
    // Number of milliseconds that should be between update checks for the bot.
    const UPDATE_FREQUENCY_MS = 60 * 1000;

    private $m_announcementChannel;

    private $m_initialVersion;
    private $m_availableVersion;

    private $m_updateTimer;

    public function __construct() {
        $this->m_initialVersion = $this->determineCurrentVersion();
        $this->m_availableVersion = $this->m_initialVersion;

        // Load and apply the configuration for this module from the main config file.
        $configuration = Configuration::getInstance()->get('BotVersion');
        if (array_key_exists('channel', $configuration))
            $this->m_announcementChannel = $configuration['channel'];

        // Create a timer that checks for updates to the version file at a given frequency. This
        // timer will be destroyed again when this instance goes away.
        $this->m_updateTimer =
            Timer::Create([$this, 'onUpdateCheck'], self::UPDATE_FREQUENCY_MS, true /* repeating */);
    }

    // Called when there are no further references to this class. Stops the update check timer.
    public function __destruct() {
        Timer::Stop($this->m_updateTimer);
    }

    // Called at the interval defined by |UPDATE_FREQUENCY_MS| to determine if updates are available
    // for the bot. When there are, an announcement will be send to a given channel.
    public function onUpdateCheck() {
        $availableVersion = $this->determineCurrentVersion();
        if ($availableVersion == $this->m_availableVersion)
            return;

        $message  = ModuleBase::COLOUR_BROWN . 'Update available!';
        $message .= ModuleBase::COLOUR . ' Currently running ';
        $message .= ModuleBase::COLOUR_PURPLE . substr($this->m_initialVersion, 0, 7);
        $message .= ModuleBase::COLOUR . ', source updated to ';
        $message .= ModuleBase::COLOUR_PURPLE . substr($availableVersion, 0, 7);
        $message .= ModuleBase::COLOUR . '. Use !restart to update.';

        $bot = BotManager::getInstance()->offsetGet('channel:' . $this->m_announcementChannel);
        if ($bot)
            $bot->send('PRIVMSG ' . $this->m_announcementChannel . ' :' . $message);

        $this->m_availableVersion = $availableVersion;
    }

    // Called when a message has been send to a channel. We implement the !botversion command here,
    // that will reply with the current version -and available version if different- of this bot.
    public function onChannelPrivmsg(Bot $bot, $channel, $nickname, $message) {
        if ($message != '!botversion')
            return;

        $reply  = 'Running version ';
        $reply .= ModuleBase::COLOUR_PURPLE . substr($this->m_initialVersion, 0, 7);
        $reply .= ModuleBase::COLOUR;

        if ($this->m_availableVersion != $this->m_initialVersion) {
            $reply .= ', however, version ';
            $reply .= ModuleBase::COLOUR_PURPLE . substr($this->m_availableVersion, 0, 7);
            $reply .= ModuleBase::COLOUR . ' is available';
        }

        $reply .= '.';

        $bot->send('PRIVMSG ' . $channel . ' :' . $reply);
    }

    // Reads and returns the latest sha hash from the VERSION file in the bot's root directory. If
    // the file does not exist, the string "UNKNOWN" will be returned instead.
    private function determineCurrentVersion() {
        if (!file_exists('VERSION'))
            return 'UNKNOWN';

        return trim(file_get_contents('VERSION'));
    }
};
