<?php
// Copyright 2015 Las Venturas Playground. All rights reserved.
// Use of this source code is governed by the MIT license, a copy of which can
// be found in the LICENSE file.

define('VERSION_STR', 'Nuwani v2.0');

require_once __DIR__ . '/config.private.php';

$aConfiguration = [
    'Networks' => [
        'GTANet' => [
            // GTANet servers the bot will connect to.
            '85.17.3.182:16697'
        ],
    ],
    'Bots' => [
        [
            'Nuwani' => 'Sonium',
            'Username' => '',
            'Realname' => '',

            'Network' => 'GTANet',

            'BindIP' => null,
            'SSL' => 16697,

            'OnConnect' => [
                'Channels' => [
                    // Las Venturas Playground
                    '#LVP.Crew',
                    '#LVP.Dev',
                    '#LVP.Echo',
                    '#LVP.Forum',
                    '#LVP',

                    // Other channels
                    '#Inforitus',
                    '#Tracker',

                    // Additional private channels will be appended here.
                ],

                'PRIVMSG NickServ :IDENTIFY ' . $soniumNickServPassword,
            ],

            'QuitMessage' => 'Las Venturas Playground: play.sa-mp.nl',
        ]
    ],
    'Owners' => [
        'Prefix' => '..',

        // The private list of owners will be appended here.
    ],
    'MySQL' => $soniumDatabase,
    'ErrorHandling' => ErrorExceptionHandler::ERROR_OUTPUT_ALL,
    'SleepTimer' => 40000,
];

if (isset ($soniumChannels)) {
    $aConfiguration['Bots'][0]['OnConnect']['Channels'] = array_merge(
        $aConfiguration['Bots'][0]['OnConnect']['Channels'], $soniumChannels);
}

if (isset ($soniumOwners))
    $aConfiguration['Owners'] = array_merge($aConfiguration['Owners'], $soniumOwners);
