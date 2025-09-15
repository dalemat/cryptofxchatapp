<?php

use Flarum\Extend;

return [
    (new Extend\Frontend('forum'))
        ->content(function ($view) {
            try {
                $settings = app('flarum.settings');
                $chatUrl = $settings->get('cryptofxchatapp.chat_url', 'https://example.com/chat');
                $allowedGroups = $settings->get('cryptofxchatapp.allowed_groups', 'members');
                $buttonText = $settings->get('cryptofxchatapp.button_text', 'Chat');
                $restrictionMessage = $settings->get('cryptofxchatapp.restriction_message', 'This chat is restricted to members of specific groups.');
                $joinInstructions = $settings->get('cryptofxchatapp.join_instructions', 'Please contact an administrator to join the required group.');
            } catch (Exception $e) {
                // Fallback to defaults if settings fail
                $chatUrl = 'https://example.com/chat';
                $allowedGroups = 'members';
                $buttonText = 'Chat';
                $restrictionMessage = 'This chat is restricted to members of specific groups.';
                $joinInstructions = 'Please contact an administrator to join the required group.';
            }
            
            $view->addHeadString('
                <style>
                    .ChatModal .Modal-content { width: 90vw; max-width: 800px; height: 80vh; max-height: 600px; }
                    .chat-iframe-container { width: 100%; height: 70vh; max-height: 500px; position: relative; }
                    .chat-iframe { width: 100%; height: 100%; border: none; border-radius: 4px; background: #f8f9fa; }
                    .chat-button .Button { color: #fff !important; background: #0d6efd !important; border: 1px solid #0d6efd !important; }
                    .chat-button .Button:hover { opacity: 0.8; }
                    .mobile-chat-button .Button { width: 100%; text-align: left; padding: 12px 20px; color: inherit !important; }
                    .mobile-chat-button .Button:hover { background-color: rgba(0,0,0,0.1); }
                    .chat-access-denied { text-align: center; padding: 40px 20px; background: #f8f9fa; border-radius: 8px; margin: 20px; }
                    .chat-access-denied h3 { color: #dc3545; margin-bottom: 15px; font-size: 18px; }
                    .chat-access-denied p { color: #666; margin-bottom: 10px; line-height: 1.5; }
                    .chat-access-denied .required-groups { background: #fff; padding: 10px; border-radius: 4px; border: 1px solid #ddd; margin: 15px 0; font-family: monospace; }
                    .Alert--warning { background-color: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
                    .Alert--warning .Alert-icon { color: #f39c12; }
                    .AlertManager { position: fixed; top: 60px; right: 20px; z-index: 9999; max-width: 400px; }
                    @media (max-width: 768px) {
                        .ChatModal .Modal-content { width: 95vw; height: 85vh; margin: 20px auto; }
                        .chat-iframe-container { height: 75vh; }
                        .chat-button { display: none !important; }
                        .chat-access-denied { margin: 10px; padding: 30px 15px; }
                        .AlertManager { right: 10px; max-width: calc(100vw - 20px); }
                    }
                    @media (max-width: 480px) {
                        .ChatModal .Modal-content { width: 98vw; height: 90vh; margin: 10px auto; }
                        .chat-iframe-container { height: 80vh; }
                    }
                </style>
                
                <script>
                (function() {
                    var CHAT_CONFIG = {
                        url: "' . addslashes($chatUrl) . '",
                        allowedGroups: "' . addslashes($allowedGroups) . '",
                        buttonText: "' . addslashes($buttonText) . '",
                        restrictionMessage: "' . addslashes($restrictionMessage) . '",
                        joinInstructions: "' . addslashes($joinInstructions) . '"
                    };

                    function showAlert(message, type) {
                        type = type || "warning";
                        var alertsContainer = document.querySelector(".AlertManager");
                        
                        if (!alertsContainer) {
                            alertsContainer = document.createElement("div");
                            alertsContainer.className = "AlertManager";
                            document.body.appendChild(alertsContainer);
                        }

                        var alert = document.createElement("div");
                        alert.className = "Alert Alert--" + type + " Alert--dismissible";
                        alert.style.cssText = "margin-bottom: 10px; padding: 15px 20px; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); cursor: pointer; transition: opacity 0.3s ease;";
                        alert.innerHTML = \'<span class="Alert-icon fas fa-exclamation-triangle" style="margin-right: 10px;"></span>\' + message;
                        
                        alertsContainer.appendChild(alert);
                        
                        setTimeout(function() {
                            if (alert && alert.parentNode) {
                                alert.style.opacity = "0";
                                setTimeout(function() {
                                    if (alert && alert.parentNode) {
                                        alert.parentNode.removeChild(alert);
                                    }
                                }, 300);
                            }
                        }, 5000);
                        
                        alert.onclick = function() {
                            if (alert && alert.parentNode) {
                                alert.parentNode.removeChild(alert);
                            }
                        };
                    }

                    function checkUserAccess() {
                        if (!window.app || !window.app.session || !window.app.session.user) {
                            return { hasAccess: false, userGroups: [] };
                        }

                        var user = window.app.session.user;
                        var userGroups = [];
                        
                        try {
                            if (user.data && user.data.relationships && user.data.relationships.groups && user.data.relationships.groups.data) {
                                userGroups = user.data.relationships.groups.data.map(function(group) {
                                    var groupData = window.app.store.getById("groups", group.id);
                                    return groupData ? groupData.data.attributes.nameSingular : null;
                                }).filter(Boolean);
                            }
                        } catch (e) {
                            console.log("Error getting user groups:", e);
                        }

                        var allowedGroups = CHAT_CONFIG.allowedGroups.split(",").map(function(g) { return g.trim().toLowerCase(); });
                        var hasAccess = userGroups.some(function(group) {
                            return allowedGroups.includes(group.toLowerCase());
                        });

                        return { hasAccess: hasAccess, userGroups: userGroups };
                    }

                    function openChatModal() {
                        var accessInfo = checkUserAccess();
                        
                        if (!accessInfo.hasAccess) {
                            var requiredGroups = CHAT_CONFIG.allowedGroups.split(",").map(function(g) { return g.trim(); }).join(", ");
                            showAlert("Chat access restricted to: " + requiredGroups + ". " + CHAT_CONFIG.joinInstructions, "warning");
                            return;
                        }

                        // Create simple modal HTML
                        var modal = document.createElement("div");
                        modal.className = "Modal modal--shown";
                        modal.style.cssText = "position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center;";
                        
                        modal.innerHTML = \'<div class="Modal-content ChatModal" style="background: white; border-radius: 8px; max-width: 800px; width: 90vw; height: 80vh; max-height: 600px; position: relative;"><div style="padding: 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;"><h3 style="margin: 0;">\' + CHAT_CONFIG.buttonText + \'</h3><button class="close-modal" style="background: none; border: none; font-size: 24px; cursor: pointer; padding: 0; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">&times;</button></div><div style="padding: 0; height: calc(100% - 80px);"><iframe src="\' + CHAT_CONFIG.url + \'" style="width: 100%; height: 100%; border: none; border-radius: 0 0 8px 8px;"></iframe></div></div>\';
                        
                        document.body.appendChild(modal);
                        document.body.style.overflow = "hidden";
                        
                        modal.querySelector(".close-modal").onclick = function() {
                            document.body.removeChild(modal);
                            document.body.style.overflow = "";
                        };
                        
                        modal.onclick = function(e) {
                            if (e.target === modal) {
                                document.body.removeChild(modal);
                                document.body.style.overflow = "";
                            }
                        };
                    }

                    function addChatButton() {
                        setTimeout(function() {
                            // Remove existing buttons
                            var existingButtons = document.querySelectorAll(".chat-button, .mobile-chat-button");
                            existingButtons.forEach(function(btn) { btn.remove(); });

                            // Desktop button
                            var headerPrimary = document.querySelector(".Header-primary");
                            if (headerPrimary) {
                                var chatButton = document.createElement("div");
                                chatButton.className = "chat-button";
                                chatButton.style.cssText = "margin-left: 8px;";
                                chatButton.innerHTML = \'<button class="Button Button--primary" type="button"><i class="fas fa-comments" style="margin-right: 6px;"></i>\' + CHAT_CONFIG.buttonText + \'</button>\';
                                chatButton.onclick = openChatModal;
                                
                                var sessionDropdown = headerPrimary.querySelector(".SessionDropdown") || headerPrimary.querySelector(".Header-controls");
                                if (sessionDropdown) {
                                    headerPrimary.insertBefore(chatButton, sessionDropdown);
                                } else {
                                    headerPrimary.appendChild(chatButton);
                                }
                            }

                            // Mobile button
                            var drawerContent = document.querySelector(".Drawer-content .Header-controls");
                            if (drawerContent) {
                                var mobileChatButton = document.createElement("div");
                                mobileChatButton.className = "mobile-chat-button";
                                mobileChatButton.innerHTML = \'<button class="Button Button--block" type="button"><i class="fas fa-comments" style="margin-right: 8px;"></i>\' + CHAT_CONFIG.buttonText + \'</button>\';
                                mobileChatButton.onclick = openChatModal;
                                drawerContent.appendChild(mobileChatButton);
                            }
                        }, 100);
                    }

                    // Initialize
                    function init() {
                        addChatButton();
                        // Re-add button on navigation
                        setTimeout(addChatButton, 1000);
                    }

                    if (document.readyState === "loading") {
                        document.addEventListener("DOMContentLoaded", init);
                    } else {
                        init();
                    }

                    // Handle SPA navigation
                    setInterval(addChatButton, 2000);
                })();
                </script>
            ');
        }),
];
