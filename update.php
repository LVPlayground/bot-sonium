<?php
// Copyright 2015 Las Venturas Playground. All rights reserved.
// Use of this source code is governed by the MIT license, a copy of which can
// be found in the LICENSE file.

// Run this command every minute through a cron-tab to enable auto-deploy of
// Sonium. It builds upon the auto-deploy mechanism included in sa-mp.nl.

$file = '/home/lvp/domains/sa-mp.nl/private_html/tools/auto-deploy/bot-sonium.push';
if (!file_exists($file))
    exit;

unlink($file);

// Do not start pulling updates every minute if the |$file| becomes undeletable for some reason.
// Instead, fail silently, at some point somebody might notice that auto-deploy failed.
if (file_exists($file))
    exit;

$directory = __DIR__;
$commands = [
    'git fetch --all',
    'git reset --hard origin/master',
    'composer update',
    'git rev-parse HEAD > VERSION'
];

foreach ($commands as $command)
    echo shell_exec('cd ' . $directory . ' && ' . $command);
