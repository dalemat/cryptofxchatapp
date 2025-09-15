<?php

namespace CryptoFXChatApp\ChatIframe\Command;

use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Console\Command;

class ChatConfigCommand extends Command
{
    protected $signature = 'chat:config {url} {--enabled=true} {--button-text=} {--allowed-groups=}';
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
        $buttonText = $this->option('button-text');
        $allowedGroups = $this->option('allowed-groups');

        // Set URL and enabled status
        $this->settings->set('cryptofxchatapp.chat_url', $url);
        $this->settings->set('cryptofxchatapp.chat_enabled', $enabled);

        $this->info("Chat URL set to: {$url}");
        $this->info("Chat enabled: " . ($enabled ? 'Yes' : 'No'));

        // Set button text if provided
        if ($buttonText) {
            $this->settings->set('cryptofxchatapp.button_text', $buttonText);
            $this->info("Button text set to: {$buttonText}");
        }

        // Set allowed groups if provided
        if ($allowedGroups) {
            $this->settings->set('cryptofxchatapp.allowed_groups', $allowedGroups);
            $this->info("Allowed groups set to: {$allowedGroups}");
        }

        $this->info("Settings updated successfully!");

        return 0;
    }
}
