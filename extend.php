<?php

use Flarum\Extend;
use CryptoFXChatApp\CommunityChat\Listener\AddChatAssets;

return [
    (new Extend\Frontend('forum'))
        ->content(AddChatAssets::class),

    (new Extend\Settings())
        ->default('cryptofxchatapp.chat_url', 'https://chat.cryptoforex.space/')
        ->default('cryptofxchatapp.allowed_groups', 'Admin,Mod,Recognised member')
        ->serializeToForum('cryptofxchatapp.chat_url', 'cryptofxchatapp.chat_url')
        ->serializeToForum('cryptofxchatapp.allowed_groups', 'cryptofxchatapp.allowed_groups'),
];
