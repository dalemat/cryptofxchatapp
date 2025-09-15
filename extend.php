<?php

use Flarum\Extend;

return [
    (new Extend\Frontend('forum'))
        ->content(function ($view) {
            $settings = app('flarum.settings');
            $chatUrl = $settings->get('cryptofxchatapp-chat-iframe.url', 'https://example.com/chat');
            $allowedGroups = $settings->get('cryptofxchatapp-chat-iframe.allowed_groups', 'members');
            $buttonText = $settings->get('cryptofxchatapp-chat-iframe.button_text', 'Chat');
            $restrictionMessage = $settings->get('cryptofxchatapp-chat-iframe.restriction_message', 'This chat is restricted to members of specific groups.');
            $joinInstructions = $settings->get('cryptofxchatapp-chat-iframe.join_instructions', 'Please contact an administrator to join the required group.');
            
            $view->addHeadString('
                <style>
                    .ChatModal .Modal-content { width: 90vw; max-width: 800px; height: 80vh; max-height: 600px; }
                    .chat-iframe-container { width: 100%; height: 70vh; max-height: 500px; position: relative; }
                    .chat-iframe { width: 100%; height: 100%; border: none; border-radius: 4px; background: #f8f9fa; }
                    .chat-button .Button { color: #fff !important; }
                    .chat-button .Button:hover { opacity: 0.8; }
                    .mobile-chat-button .Button { width: 100%; text-align: left; padding: 12px 20px; color: inherit !important; }
                    .mobile-chat-button .Button:hover { background-color: rgba(0,0,0,0.1); }
                    .chat-access-denied { text-align: center; padding: 40px 20px; background: #f8f9fa; border-radius: 8px; margin: 20px; }
                    .chat-access-denied h3 { color: #dc3545; margin-bottom: 15px; font-size: 18px; }
                    .chat-access-denied p { color: #666; margin-bottom: 10px; line-height: 1.5; }
                    .chat-access-denied .required-groups { background: #fff; padding: 10px; border-radius: 4px; border: 1px solid #ddd; margin: 15px 0; font-family: monospace; }
                    .Alert--warning { background-color: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
                    .Alert--warning .Alert-icon { color: #f39c12; }
                    @media (max-width: 768px) {
                        .ChatModal .Modal-content { width: 95vw; height: 85vh; margin: 20px auto; }
                        .chat-iframe-container { height: 75vh; }
                        .chat-button { display: none !important; }
                        .chat-access-denied { margin: 10px; padding: 30px 15px; }
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
                        var alertsContainer = document.querySelector(".AlertManager") || document.querySelector(".alerts");
                        
                        if (!alertsContainer) {
                            alertsContainer = document.createElement("div");
                            alertsContainer.className = "AlertManager alerts";
                            alertsContainer.style.cssText = "position: fixed; top: 60px; right: 20px; z-index: 9999; max-width: 400px;";
                            document.body.appendChild(alertsContainer);
                        }

                        var alert = document.createElement("div");
                        alert.className = "Alert Alert--" + type + " Alert--dismissible";
                        alert.style.cssText = "margin-bottom: 10px; padding: 15px 20px; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); position: relative; cursor: pointer;";
                        alert.innerHTML = \'<span class="Alert-icon fas fa-exclamation-triangle" style="margin-right: 10px;"></span>\' + message;
                        
                        alertsContainer.appendChild(alert);
                        
                        setTimeout(function() {
                            if (alert && alert.parentNode) {
                                alert.style.opacity = "0";
                                alert.style.transition = "opacity 0.3s ease";
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

                    function ChatModal() {
                        this.className = "ChatModal Modal--large";
                        this.isDismissible = true;
                    }

                    ChatModal.prototype.view = function(hasAccess, userGroups) {
                        if (!hasAccess) {
                            var requiredGroups = CHAT_CONFIG.allowedGroups.split(",").map(function(g) { return g.trim(); }).join(", ");
                            return [
                                m("div", { className: "Modal-content" }, [
                                    m("div", { className: "Modal-header" }, [
                                        m("h3", { className: "App-titleControl App-titleControl--text" }, "Chat Access Restricted")
                                    ]),
                                    m("div", { className: "Modal-body" }, [
                                        m("div", { className: "chat-access-denied" }, [
                                            m("h3", [
                                                m("i", { className: "fas fa-lock", style: "margin-right: 8px;" }),
                                                "Access Denied"
                                            ]),
                                            m("p", CHAT_CONFIG.restrictionMessage),
                                            m("div", { className: "required-groups" }, [
                                                m("strong", "Required Groups: "), requiredGroups
                                            ]),
                                            m("p", { style: "margin-top: 20px;" }, CHAT_CONFIG.joinInstructions),
                                            userGroups.length > 0 ? [
                                                m("p", { style: "margin-top: 15px; font-size: 14px; color: #999;" }, [
                                                    "Your current groups: ",
                                                    m("span", { style: "font-family: monospace; background: #f0f0f0; padding: 2px 6px; border-radius: 3px;" }, userGroups.join(", "))
                                                ])
                                            ] : null
                                        ])
                                    ])
                                ])
                            ];
                        }
                        
                        return [
                            m("div", { className: "Modal-content" }, [
                                m("div", { className: "Modal-header" }, [
                                    m("h3", { className: "App-titleControl App-titleControl--text" }, CHAT_CONFIG.buttonText)
                                ]),
                                m("div", { className: "Modal-body" }, [
                                    m("div", { className: "chat-iframe-container" }, [
                                        m("iframe", {
                                            src: CHAT_CONFIG.url,
                                            className: "chat-iframe",
                                            frameborder: "0",
                                            allowfullscreen: true,
                                            loading: "lazy"
                                        })
                                    ])
                                ])
                            ])
                        ];
                    };

                    function checkUserAccess() {
                        if (!window.app || !window.app.session || !window.app.session.user) {
                            return { hasAccess: false, userGroups: [] };
                        }

                        var user = window.app.session.user;
                        var userGroups = [];
                        
                        if (user.data && user.data.relationships && user.data.relationships.groups && user.data.relationships.groups.data) {
                            userGroups = user.data.relationships.groups.data.map(function(group) {
                                var groupData = window.app.store.getById("groups", group.id);
                                return groupData ? groupData.data.attributes.nameSingular : null;
                            }).filter(Boolean);
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
                            showAlert("Chat access is restricted to: " + requiredGroups + ". " + CHAT_CONFIG.joinInstructions, "warning");
                        }

                        if (window.app && window.app.modal && window.app.modal.show) {
                            var modal = new ChatModal();
                            modal.content = function() {
                                return modal.view(accessInfo.hasAccess, accessInfo.userGroups);
                            };
                            window.app.modal.show(modal);
                        }
                    }

                    function addChatButton() {
                        setTimeout(function() {
                            var existingButton = document.querySelector(".chat-button");
                            var existingMobileButton = document.querySelector(".mobile-chat-button");
                            
                            if (existingButton || existingMobileButton) return;

                            // Desktop button
                            var headerPrimary = document.querySelector(".Header-primary");
                            if (headerPrimary) {
                                var chatButton = document.createElement("div");
                                chatButton.className = "chat-button";
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
                            var drawerContent = document.querySelector(".Drawer-content");
                            if (drawerContent) {
                                var headerList = drawerContent.querySelector(".Header-controls") || drawerContent;
                                if (headerList) {
                                    var mobileChatButton = document.createElement("div");
                                    mobileChatButton.className = "mobile-chat-button";
                                    mobileChatButton.innerHTML = \'<button class="Button Button--block Button--icon" type="button"><i class="icon fas fa-comments Button-icon" style="margin-right: 8px;"></i><span class="Button-label">\' + CHAT_CONFIG.buttonText + \'</span></button>\';
                                    mobileChatButton.onclick = openChatModal;
                                    headerList.appendChild(mobileChatButton);
                                }
                            }
                        }, 100);
                    }

                    function initializeChat() {
                        if (typeof m === "undefined") {
                            setTimeout(initializeChat, 100);
                            return;
                        }
                        addChatButton();
                    }

                    // Initialize on page load and route changes
                    document.addEventListener("DOMContentLoaded", initializeChat);
                    
                    // Handle route changes in SPA
                    var originalPushState = history.pushState;
                    history.pushState = function() {
                        originalPushState.apply(history, arguments);
                        setTimeout(addChatButton, 200);
                    };
                    
                    window.addEventListener("popstate", function() {
                        setTimeout(addChatButton, 200);
                    });

                    initializeChat();
                })();
                </script>
            ');
        }),

    (new Extend\Frontend('admin'))
        ->content(function ($view) {
            $view->addHeadString('
                <script>
                app.initializers.add("cryptofxchatapp-chat-iframe-admin", function() {
                    app.extensionData.for("cryptofxchatapp-flarum-chat-iframe")
                        .registerSetting(function() {
                            return [
                                m(".Form-group", [
                                    m("label", "Chat App URL"),
                                    m("input.FormControl", {
                                        type: "url",
                                        bidi: this.setting("cryptofxchatapp-chat-iframe.url", "https://example.com/chat"),
                                        placeholder: "https://example.com/chat"
                                    }),
                                    m(".helpText", "Enter the URL of your chat application")
                                ]),
                                m(".Form-group", [
                                    m("label", "Allowed Groups"),
                                    m("input.FormControl", {
                                        type: "text",
                                        bidi: this.setting("cryptofxchatapp-chat-iframe.allowed_groups", "members"),
                                        placeholder: "members,moderators,admins"
                                    }),
                                    m(".helpText", "Comma-separated list of group names that can access the chat")
                                ]),
                                m(".Form-group", [
                                    m("label", "Button Text"),
                                    m("input.FormControl", {
                                        type: "text",
                                        bidi: this.setting("cryptofxchatapp-chat-iframe.button_text", "Chat"),
                                        placeholder: "Chat"
                                    }),
                                    m(".helpText", "Text to display on the chat button")
                                ]),
                                m(".Form-group", [
                                    m("label", "Restriction Message"),
                                    m("textarea.FormControl", {
                                        bidi: this.setting("cryptofxchatapp-chat-iframe.restriction_message", "This chat is restricted to members of specific groups."),
                                        placeholder: "This chat is restricted to members of specific groups."
                                    }),
                                    m(".helpText", "Message shown to users without access")
                                ]),
                                m(".Form-group", [
                                    m("label", "Join Instructions"),
                                    m("textarea.FormControl", {
                                        bidi: this.setting("cryptofxchatapp-chat-iframe.join_instructions", "Please contact an administrator to join the required group."),
                                        placeholder: "Please contact an administrator to join the required group."
                                    }),
                                    m(".helpText", "Instructions on how users can get access to the chat")
                                ])
                            ];
                        });
                });
                </script>
            ');
        }),
];
