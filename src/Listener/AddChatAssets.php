<?php

namespace CryptoFXChatApp\CommunityChat\Listener;

use Flarum\Frontend\Document;

class AddChatAssets
{
    public function __invoke(Document $document)
    {
        $document->head[] = $this->getChatStyles();
        $document->foot[] = $this->getChatScript();
    }

    private function getChatScript()
    {
        return '
<script>
document.addEventListener("DOMContentLoaded", function() {
    setTimeout(function() {
        addChatToFooter();
    }, 1000);
    
    // Also try after a longer delay to catch dynamic content
    setTimeout(function() {
        addChatToFooter();
    }, 3000);
});

function addChatToFooter() {
    // Check if chat link already exists
    if (document.querySelector(".chat-footer-link")) {
        return;
    }

    // Find the footer container
    const footer = document.querySelector("footer") || 
                   document.querySelector(".App-footer") ||
                   document.querySelector("[class*=\"footer\"]") ||
                   document.querySelector(".container");

    if (footer) {
        const chatFooter = document.createElement("div");
        chatFooter.className = "chat-footer-container";
        chatFooter.innerHTML = `
            <div class="chat-footer-link" onclick="toggleChatModal()">
                <span class="chat-icon">💬</span>
                <span class="chat-text">Community Chat</span>
            </div>
        `;
        
        footer.appendChild(chatFooter);
        console.log("Chat link added to footer");
    } else {
        console.log("Footer not found, adding fixed position button");
        // Fallback: add fixed position button
        const fixedChat = document.createElement("div");
        fixedChat.className = "chat-footer-container fixed-chat";
        fixedChat.innerHTML = `
            <div class="chat-footer-link" onclick="toggleChatModal()">
                <span class="chat-icon">💬</span>
                <span class="chat-text">Chat</span>
            </div>
        `;
        document.body.appendChild(fixedChat);
    }
}

function toggleChatModal() {
    // Check if user is logged in
    if (typeof app !== "undefined" && app.session && app.session.user) {
        // Get user groups
        const userGroups = app.session.user.data.attributes.groups || [];
        const userGroupNames = userGroups.map(group => group.nameSingular || group.name || "").filter(Boolean);
        
        // Check if user has access (Admin, Mod, or Recognised member)
        const allowedGroups = ["Admin", "Mod", "Recognised member"];
        const hasAccess = userGroupNames.some(groupName => 
            allowedGroups.some(allowedGroup => 
                groupName.toLowerCase().includes(allowedGroup.toLowerCase())
            )
        );
        
        if (hasAccess) {
            window.open("https://element.io/app/", "_blank", "width=1200,height=800");
        } else {
            showAccessModal();
        }
    } else {
        // User not logged in
        showLoginModal();
    }
}

function showAccessModal() {
    // Remove existing modal if any
    const existingModal = document.getElementById("chat-access-modal");
    if (existingModal) {
        existingModal.remove();
    }

    const modal = document.createElement("div");
    modal.id = "chat-access-modal";
    modal.className = "chat-modal-overlay";
    modal.onclick = function(e) {
        if (e.target === modal) closeChatModal();
    };

    modal.innerHTML = `
        <div class="chat-access-modal">
            <div class="modal-header">
                <div class="modal-title">
                    <span class="title-icon">🔒</span>
                    <span>Chat Access Restricted</span>
                </div>
                <button class="modal-close" onclick="closeChatModal()">×</button>
            </div>
            <div class="modal-body">
                <div class="access-info">
                    <div class="info-icon">⛔</div>
                    <p><strong>Thanks for being a member!</strong> Chat access is currently limited to specific groups.</p>
                </div>
                
                <div class="access-groups">
                    <p><strong>Who can access the chat:</strong></p>
                    <div class="group-list">
                        <div class="group-item admin">
                            <span class="group-icon">👑</span>
                            <div class="group-info">
                                <strong>Admin</strong>
                                <small>Forum administrators</small>
                            </div>
                        </div>
                        <div class="group-item mod">
                            <span class="group-icon">🛡️</span>
                            <div class="group-info">
                                <strong>Mod</strong>
                                <small>Forum moderators</small>
                            </div>
                        </div>
                        <div class="group-item recognized">
                            <span class="group-icon">⭐</span>
                            <div class="group-info">
                                <strong>Recognised member</strong>
                                <small>Trusted community members</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="upgrade-section">
                    <div class="upgrade-header">
                        <span class="upgrade-icon">💡</span>
                        <strong>Want to access the chat?</strong>
                    </div>
                    <p>Become more active in our community! Regular participation, helpful contributions, and positive engagement may lead to membership upgrades.</p>
                    
                    <div class="ways-to-recognize">
                        <div class="ways-header">
                            <span class="ways-icon">🌟</span>
                            <strong>Ways to get recognized:</strong>
                        </div>
                        <ul class="ways-list">
                            <li>
                                <span class="way-icon">📝</span>
                                Create valuable discussions
                            </li>
                            <li>
                                <span class="way-icon">💬</span>
                                Provide helpful replies
                            </li>
                            <li>
                                <span class="way-icon">👍</span>
                                Engage positively with others
                            </li>
                            <li>
                                <span class="way-icon">🏆</span>
                                Follow community guidelines
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <a href="/discussions" class="btn-primary">
                        <span class="btn-icon">📝</span>
                        Join Discussions
                    </a>
                    <a href="/u" class="btn-secondary">
                        <span class="btn-icon">👥</span>
                        Browse Members
                    </a>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    document.body.style.overflow = "hidden";
}

function showLoginModal() {
    // Remove existing modal if any
    const existingModal = document.getElementById("chat-access-modal");
    if (existingModal) {
        existingModal.remove();
    }

    const modal = document.createElement("div");
    modal.id = "chat-access-modal";
    modal.className = "chat-modal-overlay";
    modal.onclick = function(e) {
        if (e.target === modal) closeChatModal();
    };

    modal.innerHTML = `
        <div class="chat-access-modal">
            <div class="modal-header">
                <div class="modal-title">
                    <span class="title-icon">👋</span>
                    <span>Join Our Community!</span>
                </div>
                <button class="modal-close" onclick="closeChatModal()">×</button>
            </div>
            <div class="modal-body">
                <div class="login-info">
                    <div class="info-icon">🎯</div>
                    <p><strong>Ready to join live discussions?</strong></p>
                    <p>Create your free account or log in to access community features!</p>
                </div>
                
                <div class="action-buttons">
                    <a href="/register" class="btn-primary">
                        <span class="btn-icon">🚀</span>
                        Create Account
                    </a>
                    <a href="/login" class="btn-secondary">
                        <span class="btn-icon">🔑</span>
                        Login
                    </a>
                </div>
                
                <div class="help-text">
                    <p>Already have an account? <a href="/login" class="login-link">Sign in here</a></p>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    document.body.style.overflow = "hidden";
}

function closeChatModal() {
    const modal = document.getElementById("chat-access-modal");
    if (modal) {
        modal.remove();
        document.body.style.overflow = "";
    }
}

// Close modal on ESC key
document.addEventListener("keydown", function(e) {
    if (e.key === "Escape") {
        closeChatModal();
    }
});
</script>';
    }

    private function getChatStyles()
    {
        return '<style>
/* === FOOTER CHAT LINK === */
.chat-footer-container {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px 0;
    margin-top: 20px;
    border-top: 1px solid #e9ecef;
}

.chat-footer-container.fixed-chat {
    position: fixed;
    bottom: 20px;
    right: 20px;
    padding: 0;
    margin: 0;
    border: none;
    z-index: 1000;
}

.chat-footer-link {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: linear-gradient(135deg, #4CAF50, #45a049);
    color: white;
    border-radius: 25px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
}

.chat-footer-link:hover {
    background: linear-gradient(135deg, #45a049, #3d8b40);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
    color: white;
    text-decoration: none;
}

.chat-icon {
    font-size: 16px;
}

.chat-text {
    font-size: 14px;
    font-weight: 600;
}

/* === MODAL STYLES === */
.chat-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 10000;
    padding: 20px;
    box-sizing: border-box;
    backdrop-filter: blur(4px);
}

.chat-access-modal {
    background: white;
    border-radius: 12px;
    width: 100%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* === MODAL HEADER === */
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    border-bottom: 1px solid #e9ecef;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-radius: 12px 12px 0 0;
}

.modal-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 18px;
    font-weight: 700;
    color: #333;
}

.title-icon {
    font-size: 20px;
}

.modal-close {
    width: 40px;
    height: 40px;
    border: none;
    background: transparent;
    font-size: 24px;
    font-weight: bold;
    cursor: pointer;
    color: #666;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.3s ease;
}

.modal-close:hover {
    background: rgba(0, 0, 0, 0.1);
    color: #333;
}

/* === MODAL BODY === */
.modal-body {
    padding: 25px;
}

.access-info,
.login-info {
    text-align: center;
    margin-bottom: 25px;
    padding: 20px;
    background: linear-gradient(135deg, #fff5f5, #ffe8e8);
    border-radius: 10px;
    border-left: 4px solid #f56565;
}

.info-icon {
    font-size: 32px;
    margin-bottom: 10px;
}

.access-info p,
.login-info p {
    margin: 8px 0;
    color: #333;
    line-height: 1.5;
}

/* === ACCESS GROUPS === */
.access-groups {
    margin-bottom: 25px;
}

.access-groups > p {
    font-weight: 600;
    margin-bottom: 15px;
    color: #333;
}

.group-list {
    background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
    border-radius: 10px;
    padding: 20px;
    border-left: 4px solid #4CAF50;
}

.group-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid rgba(76, 175, 80, 0.1);
}

.group-item:last-child {
    border-bottom: none;
}

.group-icon {
    font-size: 18px;
    width: 25px;
    text-align: center;
}

.group-info strong {
    display: block;
    color: #2e7d32;
    font-weight: 600;
    margin-bottom: 2px;
}

.group-info small {
    color: #666;
    font-size: 12px;
}

/* === UPGRADE SECTION === */
.upgrade-section {
    background: linear-gradient(135deg, #f3e5f5, #e8f5e8);
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 25px;
    border-left: 4px solid #9c27b0;
}

.upgrade-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
    color: #7b1fa2;
}

.upgrade-icon {
    font-size: 16px;
}

.upgrade-section > p {
    color: #333;
    line-height: 1.5;
    margin-bottom: 15px;
}

.ways-to-recognize {
    background: linear-gradient(135deg, #fff8e1, #fff3c4);
    border-radius: 8px;
    padding: 15px;
    border-left: 3px solid #ff9800;
}

.ways-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
    color: #e65100;
    font-weight: 600;
}

.ways-icon {
    font-size: 16px;
}

.ways-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.ways-list li {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    color: #333;
    font-size: 14px;
}

.way-icon {
    font-size: 14px;
    width: 20px;
    text-align: center;
}

/* === ACTION BUTTONS === */
.action-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.btn-primary,
.btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    border-radius: 25px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    cursor: pointer;
    border: none;
    min-width: 140px;
    justify-content: center;
}

.btn-primary {
    background: linear-gradient(135deg, #4CAF50, #45a049);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #45a049, #3d8b40);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(76, 175, 80, 0.4);
    color: white;
    text-decoration: none;
}

.btn-secondary {
    background: linear-gradient(135deg, #2196F3, #1976D2);
    color: white;
}

.btn-secondary:hover {
    background: linear-gradient(135deg, #1976D2, #1565C0);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(33, 150, 243, 0.4);
    color: white;
    text-decoration: none;
}

.btn-icon {
    font-size: 14px;
}

/* === HELP TEXT === */
.help-text {
    text-align: center;
    color: #666;
    font-size: 14px;
    border-top: 1px solid #e9ecef;
    padding-top: 20px;
}

.login-link {
    color: #4CAF50;
    text-decoration: none;
    font-weight: 600;
}

.login-link:hover {
    text-decoration: underline;
}

/* === MOBILE RESPONSIVE === */
@media (max-width: 768px) {
    .chat-modal-overlay {
        padding: 10px;
        align-items: flex-start;
        padding-top: 20px;
    }
    
    .chat-access-modal {
        max-height: 95vh;
        width: 100%;
        margin: 0;
    }
    
    .modal-header {
        padding: 15px 20px;
    }
    
    .modal-title {
        font-size: 16px;
    }
    
    .modal-body {
        padding: 20px 15px;
    }
    
    .action-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .btn-primary,
    .btn-secondary {
        width: 100%;
        max-width: 280px;
    }
    
    .chat-footer-container.fixed-chat {
        bottom: 15px;
        right: 15px;
    }
    
    .chat-footer-link {
        padding: 10px 18px;
    }
    
    .chat-text {
        display: none;
    }
}

@media (max-width: 480px) {
    .chat-modal-overlay {
        padding: 0;
        align-items: stretch;
    }
    
    .chat-access-modal {
        border-radius: 0;
        height: 100vh;
        max-height: none;
    }
    
    .modal-header {
        border-radius: 0;
        padding: 12px 15px;
    }
    
    .modal-body {
        padding: 15px;
    }
    
    .group-list,
    .upgrade-section,
    .ways-to-recognize {
        padding: 15px;
    }
    
    .chat-footer-container {
        padding: 15px 0;
    }
    
    .chat-footer-container.fixed-chat {
        bottom: 10px;
        right: 10px;
    }
}

/* === DARK MODE SUPPORT === */
@media (prefers-color-scheme: dark) {
    .chat-access-modal {
        background: #2d3748;
        color: #e2e8f0;
    }
    
    .modal-header {
        background: linear-gradient(135deg, #374151, #4b5563);
        border-bottom-color: #4a5568;
    }
    
    .modal-title {
        color: #f7fafc;
    }
    
    .modal-close {
        color: #a0aec0;
    }
    
    .modal-close:hover {
        background: rgba(255, 255, 255, 0.1);
        color: #f7fafc;
    }
    
    .access-info,
    .login-info {
        background: linear-gradient(135deg, #4a3a5c, #5d4e75);
        border-left-color: #f56565;
    }
    
    .group-list {
        background: linear-gradient(135deg, #2d5a3d, #3d6b4d);
    }
    
    .upgrade-section {
        background: linear-gradient(135deg, #4a3a5c, #5d4e75);
    }
    
    .ways-to-recognize {
        background: linear-gradient(135deg, #5d4e37, #6b5a42);
    }
    
    .help-text {
        border-top-color: #4a5568;
        color: #a0aec0;
    }
}

/* === ACCESSIBILITY === */
@media (prefers-reduced-motion: reduce) {
    .chat-access-modal {
        animation: none;
    }
    
    .chat-footer-link:hover,
    .btn-primary:hover,
    .btn-secondary:hover {
        transform: none;
    }
}

/* === PRINT STYLES === */
@media print {
    .chat-footer-container,
    .chat-modal-overlay {
        display: none !important;
    }
}
</style>';
    }
}
