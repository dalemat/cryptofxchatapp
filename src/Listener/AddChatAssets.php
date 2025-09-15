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
console.log("🚀 Chat Extension Loading...");

document.addEventListener("DOMContentLoaded", function() {
    setTimeout(() => addChatButton(), 1000);
    setTimeout(() => addChatButton(), 2000);
    
    // Listen for page changes
    if (typeof app !== "undefined") {
        const observer = new MutationObserver(() => {
            setTimeout(() => addChatButton(), 500);
        });
        observer.observe(document.body, { childList: true, subtree: true });
    }
});

const ALLOWED_GROUPS = ["Admin", "Mod", "Recognised member"];

function addChatButton() {
    // Remove existing buttons to avoid duplicates
    document.querySelectorAll(".community-chat-btn").forEach(btn => btn.remove());
    
    addToDesktopHeader();
    addToMobileDrawer();
}

function addToDesktopHeader() {
    // Find the header controls (where notifications are)
    const headerControls = document.querySelector(".Header-secondary .Header-controls") || 
                          document.querySelector(".Header-controls") ||
                          document.querySelector(".header-controls");
    
    if (headerControls) {
        console.log("✅ Found header controls, adding chat button");
        
        const chatBtn = document.createElement("div");
        chatBtn.className = "community-chat-btn header-chat-wrapper";
        chatBtn.innerHTML = `
            <button class="Button Button--link header-chat-btn" onclick="openCommunityChat()">
                <span class="Button-icon">💬</span>
                <span class="Button-label">Chat</span>
            </button>
        `;
        
        // Insert before the first child (before notifications)
        headerControls.insertBefore(chatBtn, headerControls.firstChild);
    } else {
        console.log("❌ Header controls not found");
    }
}

function addToMobileDrawer() {
    // Find mobile drawer navigation
    const mobileNav = document.querySelector(".Drawer-content nav") ||
                     document.querySelector(".App-drawer nav") ||
                     document.querySelector("[class*=\"drawer\"] nav") ||
                     document.querySelector(".IndexPage-nav");
    
    if (mobileNav) {
        console.log("✅ Found mobile navigation, adding chat link");
        
        const chatLink = document.createElement("li");
        chatLink.className = "community-chat-btn mobile-chat-item";
        chatLink.innerHTML = `
            <a href="#" class="hasIcon mobile-chat-link" onclick="openCommunityChat(); return false;">
                <span class="icon">💬</span>
                Community Chat
            </a>
        `;
        
        // Add to the top of the navigation
        const navList = mobileNav.querySelector("ul");
        if (navList) {
            navList.insertBefore(chatLink, navList.firstChild);
        } else {
            mobileNav.appendChild(chatLink);
        }
    } else {
        console.log("❌ Mobile navigation not found");
        addFloatingButton(); // Fallback for mobile
    }
}

function addFloatingButton() {
    if (document.querySelector(".floating-chat-btn")) return;
    
    const floatingBtn = document.createElement("div");
    floatingBtn.className = "floating-chat-btn";
    floatingBtn.innerHTML = `
        <button class="float-chat-button" onclick="openCommunityChat()" title="Open Community Chat">
            💬 <span>Chat</span>
        </button>
    `;
    
    document.body.appendChild(floatingBtn);
    console.log("✅ Added floating chat button");
}

function openCommunityChat() {
    console.log("🎯 Opening community chat...");
    
    if (typeof app !== "undefined" && app.session && app.session.user) {
        const userGroups = app.session.user.data.attributes.groups || [];
        const userGroupNames = userGroups.map(group => group.nameSingular || group.name || "").filter(Boolean);
        
        console.log("User groups:", userGroupNames);
        
        const hasAccess = userGroupNames.some(groupName => 
            ALLOWED_GROUPS.some(allowedGroup => 
                groupName.toLowerCase().includes(allowedGroup.toLowerCase())
            )
        );
        
        if (hasAccess) {
            showCommunityChat();
        } else {
            showAccessDenied();
        }
    } else {
        showGuestMessage();
    }
}

function showCommunityChat() {
    const modal = document.createElement("div");
    modal.id = "community-chat-modal";
    modal.innerHTML = `
        <div class="chat-overlay" onclick="closeCommunityChat()"></div>
        <div class="chat-modal">
            <div class="chat-header">
                <div class="chat-title">
                    <h3>💬 Community Chat</h3>
                    <div id="chat-status" class="chat-status">🔄 Connecting...</div>
                </div>
                <button onclick="closeCommunityChat()" class="chat-close">&times;</button>
            </div>
            <div class="chat-content">
                <div id="loading-message" class="loading-message">
                    <div class="spinner"></div>
                    <p>Loading community chat...</p>
                </div>
                <iframe 
                    id="chat-iframe" 
                    src="https://element.io/app/" 
                    style="display:none; width:100%; height:100%; border:none;">
                </iframe>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    document.body.style.overflow = "hidden";
    
    const iframe = document.getElementById("chat-iframe");
    const loadingMessage = document.getElementById("loading-message");
    const statusElement = document.getElementById("chat-status");
    
    iframe.onload = function() {
        statusElement.textContent = "✅ Connected";
        statusElement.style.color = "#4CAF50";
        loadingMessage.style.display = "none";
        iframe.style.display = "block";
    };
}

function showAccessDenied() {
    const modal = document.createElement("div");
    modal.id = "access-denied-modal";
    modal.innerHTML = `
        <div class="chat-overlay" onclick="closeAccessDenied()"></div>
        <div class="access-denied-modal">
            <div class="access-denied-header">
                <h3>🔒 Community Chat Access</h3>
                <button onclick="closeAccessDenied()" class="chat-close">&times;</button>
            </div>
            <div class="access-denied-body">
                <div class="access-denied-icon">🎯</div>
                <h4>Upgrade Your Access Level</h4>
                <p>Community Chat is available to active members with special recognition.</p>
                
                <div class="allowed-groups-list">
                    <h4>👥 Groups with Chat Access:</h4>
                    <ul>
                        <li>🛡️ Admin - Full access</li>
                        <li>⚡ Moderators - Full access</li>
                        <li>🌟 Recognised Members - Chat access</li>
                    </ul>
                </div>
                
                <div class="upgrade-info">
                    <h4>💎 How to become a Recognised Member:</h4>
                    <div class="participation-tips">
                        <ul>
                            <li>📝 Create quality discussions and posts</li>
                            <li>🤝 Help other community members</li>
                            <li>💡 Share valuable insights and tips</li>
                            <li>⭐ Maintain positive community engagement</li>
                        </ul>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <a href="/d/new" class="btn-primary">
                        📝 Start Contributing
                    </a>
                    <button onclick="closeAccessDenied()" class="btn-secondary">
                        Got It
                    </button>
                </div>
                
                <div class="contact-info">
                    <p>Questions? <a href="/u/moderator">Contact a moderator</a></p>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    document.body.style.overflow = "hidden";
}

function showGuestMessage() {
    const modal = document.createElement("div");
    modal.id = "guest-access-modal";
    modal.innerHTML = `
        <div class="chat-overlay" onclick="closeGuestModal()"></div>
        <div class="access-denied-modal">
            <div class="access-denied-header">
                <h3>👋 Join Our Community!</h3>
                <button onclick="closeGuestModal()" class="chat-close">&times;</button>
            </div>
            <div class="access-denied-body">
                <div class="guest-icon">🎯</div>
                <h4>Ready to join live discussions?</h4>
                <p>Create your free account to access our community features and work towards chat access!</p>
                
                <div class="action-buttons">
                    <a href="/register" class="btn-primary">
                        🚀 Create Account
                    </a>
                    <a href="/login" class="btn-secondary">
                        🔑 Login
                    </a>
                </div>
                
                <div class="contact-info">
                    <p>Already have an account? <a href="/login">Sign in here</a></p>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    document.body.style.overflow = "hidden";
}

function closeCommunityChat() {
    const modal = document.getElementById("community-chat-modal");
    if (modal) {
        modal.remove();
        document.body.style.overflow = "";
    }
}

function closeAccessDenied() {
    const modal = document.getElementById("access-denied-modal");
    if (modal) {
        modal.remove();
        document.body.style.overflow = "";
    }
}

function closeGuestModal() {
    const modal = document.getElementById("guest-access-modal");
    if (modal) {
        modal.remove();
        document.body.style.overflow = "";
    }
}

// ESC key to close modals
document.addEventListener("keydown", function(e) {
    if (e.key === "Escape") {
        closeCommunityChat();
        closeAccessDenied();
        closeGuestModal();
    }
});
</script>';
    }

    private function getChatStyles()
    {
        return '<style>
/* === HEADER CHAT BUTTON === */
.header-chat-wrapper {
    display: flex !important;
    align-items: center !important;
    margin-right: 8px !important;
}

.header-chat-btn {
    background: linear-gradient(135deg, #4CAF50, #45a049) !important;
    color: white !important;
    border: none !important;
    border-radius: 6px !important;
    padding: 8px 16px !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    transition: all 0.3s ease !important;
    cursor: pointer !important;
    display: flex !important;
    align-items: center !important;
    gap: 6px !important;
    text-decoration: none !important;
    box-shadow: 0 2px 4px rgba(76, 175, 80, 0.3) !important;
}

.header-chat-btn:hover {
    background: linear-gradient(135deg, #45a049, #3d8b40) !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 8px rgba(76, 175, 80, 0.4) !important;
    color: white !important;
    text-decoration: none !important;
}

.header-chat-btn .Button-icon {
    font-size: 14px !important;
    margin: 0 !important;
}

.header-chat-btn .Button-label {
    font-size: 13px !important;
    font-weight: 600 !important;
}

/* === MOBILE DRAWER LINK === */
.mobile-chat-item {
    list-style: none !important;
    margin: 0 !important;
    padding: 0 !important;
}

.mobile-chat-link {
    display: flex !important;
    align-items: center !important;
    padding: 12px 16px !important;
    color: #333 !important;
    text-decoration: none !important;
    font-weight: 600 !important;
    transition: all 0.3s ease !important;
    border-left: 3px solid transparent !important;
}

.mobile-chat-link:hover {
    background: linear-gradient(90deg, rgba(76, 175, 80, 0.1), transparent) !important;
    border-left-color: #4CAF50 !important;
    color: #4CAF50 !important;
    text-decoration: none !important;
}

.mobile-chat-link .icon {
    margin-right: 12px !important;
    font-size: 16px !important;
    width: 20px !important;
    text-align: center !important;
}

/* === FLOATING BUTTON === */
.floating-chat-btn {
    position: fixed !important;
    bottom: 20px !important;
    right: 20px !important;
    z-index: 1000 !important;
}

.float-chat-button {
    background: linear-gradient(135deg, #4CAF50, #45a049) !important;
    color: white !important;
    border: none !important;
    border-radius: 25px !important;
    padding: 12px 20px !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    cursor: pointer !important;
    box-shadow: 0 4px 12px rgba(76, 175, 80, 0.4) !important;
    transition: all 0.3s ease !important;
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
}

.float-chat-button:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 20px rgba(76, 175, 80, 0.5) !important;
    background: linear-gradient(135deg, #45a049, #3d8b40) !important;
}

/* === MODAL STYLES === */
.chat-overlay {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    background: rgba(0, 0, 0, 0.7) !important;
    z-index: 9998 !important;
    backdrop-filter: blur(4px) !important;
}

.chat-modal,
.access-denied-modal {
    position: fixed !important;
    top: 50% !important;
    left: 50% !important;
    transform: translate(-50%, -50%) !important;
    width: 90% !important;
    max-width: 900px !important;
    height: 80% !important;
    max-height: 600px !important;
    background: white !important;
    border-radius: 12px !important;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3) !important;
    z-index: 9999 !important;
    display: flex !important;
    flex-direction: column !important;
    animation: modalAppear 0.3s ease-out !important;
}

@keyframes modalAppear {
    from {
        opacity: 0;
        transform: translate(-50%, -50%) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
    }
}

.chat-header,
.access-denied-header {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    padding: 20px 25px !important;
    border-bottom: 1px solid #e9ecef !important;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef) !important;
    border-radius: 12px 12px 0 0 !important;
    flex-shrink: 0 !important;
}

.chat-title {
    display: flex !important;
    flex-direction: column !important;
    gap: 5px !important;
}

.chat-header h3,
.access-denied-header h3 {
    margin: 0 !important;
    font-size: 18px !important;
    font-weight: 700 !important;
    color: #333 !important;
}

.chat-status {
    font-size: 12px !important;
    color: #666 !important;
    font-weight: 600 !important;
}

.chat-close {
    width: 40px !important;
    height: 40px !important;
    border: none !important;
    background: transp[...]arent !important;
    font-size: 24px !important;
    font-weight: bold !important;
    cursor: pointer !important;
    color: #666 !important;
    border-radius: 50% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: all 0.3s ease !important;
}

.chat-close:hover {
    background: rgba(255, 255, 255, 0.2) !important;
    color: #333 !important;
}

/* === MOBILE RESPONSIVE === */
@media (max-width: 768px) {
    .header-chat-btn .Button-label {
        display: none !important;
    }
    
    .header-chat-btn {
        padding: 8px 12px !important;
        min-width: auto !important;
    }
    
    .chat-modal,
    .access-denied-modal {
        width: 95% !important;
        height: 90% !important;
        max-height: none !important;
    }
    
    .floating-chat-btn {
        bottom: 15px !important;
        right: 15px !important;
    }
    
    .float-chat-button {
        padding: 10px 16px !important;
        font-size: 13px !important;
    }
    
    .float-chat-button span {
        display: none !important;
    }
}

@media (max-width: 480px) {
    .header-chat-wrapper {
        margin-right: 4px !important;
    }
    
    .chat-modal,
    .access-denied-modal {
        width: 100% !important;
        height: 100% !important;
        border-radius: 0 !important;
        max-width: none !important;
    }
    
    .chat-header,
    .access-denied-header {
        padding: 15px 20px !important;
        border-radius: 0 !important;
    }
    
    .floating-chat-btn {
        bottom: 10px !important;
        right: 10px !important;
    }
}
</style>';
    }
}
