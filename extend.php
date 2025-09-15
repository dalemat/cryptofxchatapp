<?php

use Flarum\Extend;
use CryptoFXChatApp\ChatIframe\Command\ChatConfigCommand;

return [
    // Register console command
    (new Extend\Console())
        ->command(ChatConfigCommand::class),
    
    // Register settings with defaults
    (new Extend\Settings())
        ->default('cryptofxchatapp.chat_url', 'https://example.com/chat')
        ->default('cryptofxchatapp.chat_enabled', true)
        ->default('cryptofxchatapp.button_text', 'Chat')
        ->default('cryptofxchatapp.allowed_groups', 'members'),

    // Add frontend content
    (new Extend\Frontend('forum'))
        ->content(function ($document, $request) {
            $settings = resolve('flarum.settings');
            $chatUrl = $settings->get('cryptofxchatapp.chat_url', 'https://example.com/chat');
            $chatEnabled = $settings->get('cryptofxchatapp.chat_enabled', true);
            $buttonText = $settings->get('cryptofxchatapp.button_text', 'Chat');
            $allowedGroups = $settings->get('cryptofxchatapp.allowed_groups', 'members');
            
            if (!$chatEnabled) {
                return;
            }

            // Add CSS styles
            $document->head[] = '<style>
                .cryptofx-chat-button {
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    background: #0d6efd;
                    color: white;
                    border: none;
                    border-radius: 50px;
                    padding: 15px 20px;
                    cursor: pointer;
                    z-index: 1000;
                    font-size: 14px;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                    font-family: inherit;
                }
                .cryptofx-chat-button:hover {
                    background: #0b5ed7;
                    transform: translateY(-2px);
                    transition: all 0.3s ease;
                }
                .cryptofx-chat-modal {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.5);
                    z-index: 2000;
                    display: none;
                    align-items: center;
                    justify-content: center;
                }
                .cryptofx-chat-modal.active {
                    display: flex;
                }
                .cryptofx-chat-content {
                    background: white;
                    border-radius: 8px;
                    width: 90vw;
                    max-width: 800px;
                    height: 80vh;
                    max-height: 600px;
                    position: relative;
                    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
                }
                .cryptofx-chat-header {
                    padding: 20px;
                    border-bottom: 1px solid #eee;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                .cryptofx-chat-close {
                    background: none;
                    border: none;
                    font-size: 24px;
                    cursor: pointer;
                    padding: 0;
                    width: 30px;
                    height: 30px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border-radius: 50%;
                }
                .cryptofx-chat-close:hover {
                    background: #f0f0f0;
                }
                .cryptofx-chat-iframe {
                    width: 100%;
                    height: calc(100% - 80px);
                    border: none;
                    border-radius: 0 0 8px 8px;
                }
                @media (max-width: 768px) {
                    .cryptofx-chat-content {
                        width: 95vw;
                        height: 85vh;
                    }
                }
            </style>';

            // Add the chat HTML and JavaScript
            $document->foot[] = '
                <button class="cryptofx-chat-button" onclick="toggleCryptoFXChat()">
                    💬 ' . htmlspecialchars($buttonText) . '
                </button>
                
                <div id="cryptofx-chat-modal" class="cryptofx-chat-modal">
                    <div class="cryptofx-chat-content">
                        <div class="cryptofx-chat-header">
                            <h3 style="margin: 0;">' . htmlspecialchars($buttonText) . '</h3>
                            <button class="cryptofx-chat-close" onclick="closeCryptoFXChat()">&times;</button>
                        </div>
                        <iframe class="cryptofx-chat-iframe" src="' . htmlspecialchars($chatUrl) . '" frameborder="0"></iframe>
                    </div>
                </div>
                
                <script>
                    function toggleCryptoFXChat() {
                        var modal = document.getElementById("cryptofx-chat-modal");
                        modal.classList.add("active");
                        document.body.style.overflow = "hidden";
                    }
                    
                    function closeCryptoFXChat() {
                        var modal = document.getElementById("cryptofx-chat-modal");
                        modal.classList.remove("active");
                        document.body.style.overflow = "";
                    }
                    
                    // Close on background click
                    document.addEventListener("click", function(e) {
                        var modal = document.getElementById("cryptofx-chat-modal");
                        if (e.target === modal) {
                            closeCryptoFXChat();
                        }
                    });
                    
                    // Close on escape key
                    document.addEventListener("keydown", function(e) {
                        if (e.key === "Escape") {
                            closeCryptoFXChat();
                        }
                    });
                </script>
            ';
        }),
];
