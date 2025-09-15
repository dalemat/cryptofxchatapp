<?php

namespace CryptoFXChatApp\ChatIframe\Command;

use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Console\Command;

class ChatConfigCommand extends Command
{
    protected $signature = 'chat:config {url} {--enabled=true}';
    protected $description = 'Configure chat iframe URL and settings';

    protected $settings;

    public function __construct(SettingsRepositoryInterface $settings)
    {
        parent::__construct();
        $this->settings = $settings;
    }

    public function handle()
    {
        $url = $this->argument('url');
        $enabled = $this->option('enabled') === 'true';

        $this->settings->set('cryptofxchatapp.chat_url', $url);
        $this->settings->set('cryptofxchatapp.chat_enabled', $enabled);

        $this->info("Chat URL set to: {$url}");
        $this->info("Chat enabled: " . ($enabled ? 'Yes' : 'No'));
        
        return 0;
    }
}