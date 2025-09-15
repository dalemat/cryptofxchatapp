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
        addChatToMultipleLocations();
    }, 1000);
    
    // Re-add on navigation
    if (typeof app !== "undefined" && app.history) {
        app.history.register(function() {
            setTimeout(() => addChatToMultipleLocations(), 500);
        });
    }
});

const ALLOWED_GROUPS = ["Admin", "Mod", "Recognised member"];

function addChatToMultipleLocations() {
    addChatToHeader();
    addChatToMobileMenu();
    
    const isLoggedIn = document.body.classList.contains("loggedIn") || 
                      document.querySelector(".SessionDropdown");
    if (isLoggedIn) {
        addChatToUserMenu();
    }
}

function addChatToHeader() {
    const headerSecondary = document.querySelector(".Header-secondary") ||
                           document.querySelector(".Header-controls");
    
    if (headerSecondary && !headerSecondary.querySelector(".item-chat-header")) {
        const chatItem = document.createElement("li");
        chatItem.className = "item-chat item-chat-header";
        chatItem.innerHTML = `
            <button onclick="checkChatAccess(); return false;" class="Button Button--link header-chat-btn">
                <i class="icon fas fa-comments Button-icon"></i>
                <span class="Button-label">💬 Chat</span>
            </button>
        `;
        
        // Insert before notifications or at the beginning
        const notifications = headerSecondary.querySelector(".item-notifications");
        if (notifications) {
            headerSecondary.insertBefore(chatItem, notifications);
        } else {
            headerSecondary.insertBefore(chatItem, headerSecondary.firstChild);
        }
        
        console.log("✅ Chat added to header");
    }
}

function addChatToUserMenu() {
    setTimeout(() => {
        const dropdownTrigger = document.querySelector(".SessionDropdown .Dropdown-toggle");
        if (dropdownTrigger) {
            dropdownTrigger.addEventListener("click", function() {
                setTimeout(() => {
                    const dropdownMenu = document.querySelector(".SessionDropdown .Dropdown-menu");
                    if (dropdownMenu && !dropdownMenu.querySelector(".item-chat-user")) {
                        const chatItem = document.createElement("li");
                        chatItem.className = "item-chat item-chat-user";
                        chatItem.innerHTML = `
                            <a href="#" onclick="checkChatAccess(); return false;" class="chat-dropdown-link">
                                <i class="icon fas fa-comments"></i>
                                💬 Community Chat
                            </a>
                        `;
                        
                        dropdownMenu.insertBefore(chatItem, dropdownMenu.firstChild);
                        console.log("✅ Chat added to user menu");
                    }
                }, 100);
            });
        }
    }, 500);
}

function addChatToMobileMenu() {
    // Target the mobile drawer navigation
    const mobileNav = document.querySelector(".App-drawer .Navigation") ||
                     document.querySelector(".drawer .Navigation") ||
                     document.querySelector("[data-drawer] .Navigation");
    
    if (mobileNav && !mobileNav.querySelector(".item-chat-mobile")) {
        const chatItem = document.createElement("li");
        chatItem.className = "item-chat item-chat-mobile";
        chatItem.innerHTML = `
            <a href="#" onclick="checkChatAccess(); return false;" class="mobile-chat-btn hasIcon">
                <i class="icon fas fa-comments Button-icon"></i>
                <span class="mobile-chat-label">💬 Community Chat</span>
            </a>
        `;
        
        // Add after "All Discussions" if it exists
        const allDiscussions = mobileNav.querySelector(".item-allDiscussions") ||
                              mobileNav.querySelector("[data-route=\\"index\\"]");
        if (allDiscussions) {
            allDiscussions.parentNode.insertBefore(chatItem, allDiscussions.nextSibling);
        } else {
            mobileNav.insertBefore(chatItem, mobileNav.firstChild);
        }
        
        console.log("✅ Chat added to mobile navigation");
    }
}

function checkChatAccess() {
    const isLoggedIn = document.body.classList.contains("loggedIn") || 
                      document.querySelector(".SessionDropdown");
    
    if (!isLoggedIn) {
        showGuestAccessMessage();
        return;
    }
    
    const userData = getUserData();
    
    if (hasAccess(userData)) {
        openCommunityChat();
    } else {
        showAccessDeniedMessage();
    }
}

function getUserData() {
    if (typeof app !== "undefined" && app.session && app.session.user) {
        const groups = app.session.user.groups();
        return {
            groups: groups.map(g => ({
                name: g.nameSingular() || g.name() || g.displayName()
            })),
            username: app.session.user.username()
        };
    }
    
    const isAdmin = document.querySelector(".AdminNav") || 
                   document.body.classList.contains("admin") ||
                   window.location.href.includes("/admin");
    
    if (isAdmin) {
        return { groups: [{ name: "Admin" }] };
    }
    
    return { groups: [] };
}

function hasAccess(userData) {
    if (!userData || !userData.groups || userData.groups.length === 0) {
        return false;
    }
    
    return userData.groups.some(userGroup => {
        return ALLOWED_GROUPS.some(allowedGroup => {
            return userGroup.name === allowedGroup ||
                   (userGroup.name && userGroup.name.toLowerCase() === allowedGroup.toLowerCase());
        });
    });
}

function openCommunityChat() {
    const existingModal = document.getElementById("community-chat-modal");
    if (existingModal) {
        existingModal.remove();
    }
    
    const modal = document.createElement("div");
    modal.id = "community-chat-modal";
    modal.innerHTML = `
        <div class="chat-overlay" onclick="closeCommunityChat()"></div>
        <div class="chat-modal">
            <div class="chat-header">
                <h3>🚀 Community Chat</h3>
                <div class="chat-status" id="chat-status">⏳ Loading...</div>
                <button onclick="closeCommunityChat()" class="chat-close">&times;</button>
            </div>
            <div class="chat-content">
                <div class="loading-message" id="loading-message">
                    <div class="spinner"></div>
                    <p>Loading chat... Please wait</p>
                </div>
                <iframe id="chat-iframe" 
                        src="https://chat.cryptoforex.space/" 
                        width="100%" 
                        height="100%" 
                        frameborder="0"
                        allow="microphone; camera; encrypted-media"
                        style="display: none;">
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
    
    iframe.onerror = function() {
        statusElement.textContent = "❌ Connection failed";
        statusElement.style.color = "#f44336";
        loadingMessage.innerHTML = `
            <div style="text-align: center;">
                <h4>Unable to load chat</h4>
                <p>Please try again or contact support</p>
                <button onclick="closeCommunityChat()" class="btn-secondary">Close</button>
            </div>
        `;
    };
    
    // ESC key to close
    document.addEventListener("keydown", function(e) {
        if (e.key === "Escape" && document.getElementById("community-chat-modal")) {
            closeCommunityChat();
        }
    });
}

function showGuestAccessMessage() {
    const existingModal = document.getElementById("guest-access-modal");
    if (existingModal) {
        existingModal.remove();
    }
    
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
                <h4>Ready to dive into live discussions?</h4>
                <p>Create your free account to access our community chat and connect with traders worldwide!</p>
                <div class="action-buttons">
                    <a href="/register" class="btn-primary">🚀 Create Account</a>
                    <a href="/login" class="btn-secondary">📋 Login</a>
                </div>
                <div class="contact-info">
                    <p>Questions? Contact us at <a href="mailto:support@cryptoforex.space">support@cryptoforex.space</a></p>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    document.body.style.overflow = "hidden";
}

function showAccessDeniedMessage() {
    const existingModal = document.getElementById("access-denied-modal");
    if (existingModal) {
        existingModal.remove();
    }
    
    const modal = document.createElement("div");
    modal.id = "access-denied-modal";
    modal.innerHTML = `
        <div class="chat-overlay" onclick="closeAccessDeniedModal()"></div>
        <div class="access-denied-modal">
            <div class="access-denied-header">
                <h3>🔐 Chat Access</h3>
                <button onclick="closeAccessDeniedModal()" class="chat-close">&times;</button>
            </div>
            <div class="access-denied-body">
                <div class="access-denied-icon">⭐</div>
                <h4>Premium Feature Access Required</h4>
                <p>Our community chat is available to valued members who contribute to our trading community.</p>
                
                <div class="allowed-groups-list">
                    <h4>🎖️ Groups with Chat Access:</h4>
                    <ul>
                        <li>👑 <strong>Admin</strong> - Full access</li>
                        <li>🛡️ <strong>Moderators</strong> - Community guides</li>
                        <li>🌟 <strong>Recognised Members</strong> - Active contributors</li>
                    </ul>
                </div>
                
                <div class="upgrade-info">
                    <h4>🚀 How to Get Access:</h4>
                    <div class="participation-tips">
                        <ul>
                            <li>📝 Participate actively in forum discussions</li>
                            <li>💡 Share valuable trading insights</li>
                            <li>🤝 Help other community members</li>
                            <li>📊 Contribute quality analysis</li>
                            <li>⏰ Be consistent and engaged</li>
                        </ul>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <a href="/" class="btn-primary">🏠 Explore Forum</a>
                    <a href="/t/membership" class="btn-secondary">ℹ️ Learn More</a>
                </div>
                
                <div class="contact-info">
                    <p>Questions? Contact us at <a href="mailto:support@cryptoforex.space">support@cryptoforex.space</a></p>
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

function closeGuestModal() {
    const modal = document.getElementById("guest-access-modal");
    if (modal) {
        modal.remove();
        document.body.style.overflow = "";
    }
}

function closeAccessDeniedModal() {
    const modal = document.getElementById("access-denied-modal");
    if (modal) {
        modal.remove();
        document.body.style.overflow = "";
    }
}

// ESC key support for all modals
document.addEventListener("keydown", function(e) {
    if (e.key === "Escape") {
        closeCommunityChat();
        closeGuestModal();
        closeAccessDeniedModal();
    }
});
</script>';
    }

    private function getChatStyles()
    {
        return '
<style>
/* === HEADER POSITIONING === */
.item-chat-header {
    display: flex !important;
    align-items: center !important;
    margin: 0 8px !important;
    order: -1 !important; /* Position before other items */
}

.header-chat-btn {
    background: linear-gradient(135deg, #4CAF50, #45a049) !important;
    color: white !important;
    border: none !important;
    padding: 8px 15px !important;
    border-radius: 20px !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    transition: all 0.3s ease !important;
    display: flex !important;
    align-items: center !important;
    gap: 6px !important;
    box-shadow: 0 2px 8px rgba(76, 175, 80, 0.3) !important;
}

.header-chat-btn:hover {
    background: linear-gradient(135deg, #45a049, #3d8b40) !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 12px rgba(76, 175, 80, 0.4) !important;
    color: white !important;
}

.header-chat-btn .Button-icon {
    font-size: 14px !important;
    margin-right: 0 !important;
}

/* === MOBILE NAVIGATION === */
.item-chat-mobile {
    border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
}

.mobile-chat-btn {
    display: flex !important;
    align-items: center !important;
    width: 100% !important;
    padding: 15px 20px !important;
    color: #fff !important;
    text-decoration: none !important;
    transition: all 0.3s ease !important;
    font-size: 15px !important;
    font-weight: 500 !important;
    gap: 12px !important;
}

.mobile-chat-btn:hover {
    background: rgba(76, 175, 80, 0.2) !important;
    color: #4CAF50 !important;
    text-decoration: none !important;
}

.mobile-chat-btn .Button-icon {
    font-size: 16px !important;
    width: 20px !important;
    text-align: center !important;
}

.mobile-chat-label {
    font-weight: 600 !important;
}

/* === USER DROPDOWN MENU === */
.item-chat-user {
    border-bottom: 1px solid #e9ecef !important;
}

.chat-dropdown-link {
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
    padding: 12px 20px !important;
    color: #333 !important;
    text-decoration: none !important;
    font-weight: 500 !important;
    transition: all 0.3s ease !important;
}

.chat-dropdown-link:hover {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef) !important;
    color: #4CAF50 !important;
    text-decoration: none !important;
}

.chat-dropdown-link .icon {
    color: #4CAF50 !important;
    font-size: 14px !important;
}

/* === MODAL STYLES === */
#community-chat-modal,
#access-denied-modal,
#guest-access-modal {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    z-index: 10000 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 20px !important;
    box-sizing: border-box !important;
}

.chat-overlay {
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    background: rgba(0, 0, 0, 0.7) !important;
    backdrop-filter: blur(5px) !important;
}

.chat-modal {
    position: relative !important;
    background: white !important;
    border-radius: 12px !important;
    overflow: hidden !important;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3) !important;
    max-width: 900px !important;
    width: 100% !important;
    height: 80vh !important;
    display: flex !important;
    flex-direction: column !important;
    animation: modalSlideIn 0.3s ease-out !important;
}

.access-denied-modal {
    position: relative !important;
    background: white !important;
    border-radius: 12px !important;
    overflow: hidden !important;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3) !important;
    max-width: 500px !important;
    width: 100% !important;
    max-height: 85vh !important;
    overflow-y: auto !important;
    animation: modalSlideIn 0.3s ease-out !important;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* === MODAL HEADER === */
.chat-header,
.access-denied-header {
    background: linear-gradient(135deg, #4CAF50, #45a049) !important;
    color: white !important;
    padding: 15px 20px !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
}

.chat-header h3,
.access-denied-header h3 {
    margin: 0 !important;
    font-size: 18px !important;
    font-weight: 600 !important;
}

.chat-status {
    font-size: 12px !important;
    background: rgba(255, 255, 255, 0.2) !important;
    padding: 4px 8px !important;
    border-radius: 10px !important;
    font-weight: 500 !important;
}

.chat-close {
    background: none !important;
    border: none !important;
    color: white !important;
    font-size: 24px !important;
    cursor: pointer !important;
    width: 30px !important;
    height: 30px !important;
    border-radius: 50% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: background 0.3s ease !important;
}

.chat-close:hover {
    background: rgba(255, 255, 255, 0.2) !important;
}

/* === MODAL CONTENT === */
.chat-content {
    flex: 1 !important;
    display: flex !important;
    flex-direction: column !important;
    background: #f8f9fa !important;
}

.access-denied-body {
    padding: 30px !important;
    text-align: center !important;
}

.access-denied-icon,
.guest-icon {
    font-size: 48px !important;
    margin-bottom: 15px !important;
}

.access-denied-body h4 {
    color: #333 !important;
    margin-bottom: 15px !important;
    font-size: 20px !important;
}

.access-denied-body p {
    color: #666 !important;
    margin-bottom: 20px !important;
    line-height: 1.6 !important;
}

/* === ALLOWED GROUPS LIST === */
.allowed-groups-list {
    background: linear-gradient(135deg, #e8f5e8, #f0f8f0) !important;
    border: 1px solid #c3e6c3 !important;
    border-left: 4px solid #4CAF50 !important;
    border-radius: 8px !important;
    padding: 20px !important;
    margin: 20px 0 !important;
    text-align: left !important;
}

.allowed-groups-list h4 {
    color: #2e7d32 !important;
    margin-top: 0 !important;
    font-size: 16px !important;
    margin-bottom: 15px !important;
}

.allowed-groups-list ul {
    list-style: none !important;
    padding: 0 !important;
    margin: 0 !important;
}

.allowed-groups-list li {
    padding: 8px 0 !important;
    display: flex !important;
    align-items: center !important;
    font-size: 14px !important;
    color: #2e7d32 !important;
}

.allowed-groups-list li strong {
    margin-left: 8px !important;
}

/* === UPGRADE INFO === */
.upgrade-info {
    background: linear-gradient(135deg, #f3e5f5, #fce4ec) !important;
    border: 1px solid #d1c4e9 !important;
    border-left: 4px solid #9c27b0 !important;
    border-radius: 8px !important;
    padding: 20px !important;
    margin: 20px 0 !important;
    text-align: left !important;
}

.upgrade-info h4 {
    color: #7b1fa2 !important;
    margin-top: 0 !important;
    font-size: 16px !important;
    margin-bottom: 15px !important;
}

.participation-tips {
    background: linear-gradient(135deg, #fff3e0, #fef7f0) !important;
    border: 1px solid #ffcc02 !important;
    border-left: 4px solid #ff9800 !important;
    border-radius: 8px !important;
    padding: 15px !important;
    margin: 15px 0 !important;
}

.participation-tips ul {
    list-style: none !important;
    padding: 0 !important;
    margin: 10px 0 !important;
}

.participation-tips li {
    padding: 5px 0 !important;
    font-size: 14px !important;
    color: #e65100 !important;
    display: flex !important;
    align-items: flex-start !important;
    gap: 8px !important;
}

/* === LOADING STATES === */
.loading-message {
    display: flex !important;
    flex-direction: column !important;
    justify-content: center !important;
    align-items: center !important;
    height: 100% !important;
    padding: 40px 20px !important;
    text-align: center !important;
}

.spinner {
    border: 4px solid #f3f3f3 !important;
    border-top: 4px solid #4CAF50 !important;
    border-radius: 50% !important;
    width: 40px !important;
    height: 40px !important;
    animation: spin 1s linear infinite !important;
    margin-bottom: 20px !important;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

#chat-iframe {
    flex: 1 !important;
    border: none !important;
    background: white !important;
}

/* === BUTTONS === */
.action-buttons {
    margin-top: 25px !important;
    display: flex !important;
    gap: 15px !important;
    justify-content: center !important;
    flex-wrap: wrap !important;
}

.btn-primary,
.btn-secondary {
    padding: 12px 24px !important;
    text-decoration: none !important;
    border-radius: 8px !important;
    font-weight: 600 !important;
    transition: all 0.3s ease !important;
    display: inline-block !important;
    min-width: 150px !important;
    font-size: 14px !important;
    text-align: center !important;
    border: none !important;
    cursor: pointer !important;
}

.btn-primary {
    background: linear-gradient(135deg, #4CAF50, #45a049) !important;
    color: white !important;
    box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3) !important;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #45a049, #3d8b40) !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4) !important;
    color: white !important;
    text-decoration: none !important;
}

.btn-secondary {
    background: linear-gradient(135deg, #2196F3, #1976D2) !important;
    color: white !important;
    box-shadow: 0 4px 15px rgba(33, 150, 243, 0.3) !important;
}

.btn-secondary:hover {
    background: linear-gradient(135deg, #1976D2, #1565C0) !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 20px rgba(33, 150, 243, 0.4) !important;
    color: white !important;
    text-decoration: none !important;
}

/* === CONTACT INFO === */
.contact-info {
    margin-top: 25px !important;
    padding-top: 20px !important;
    border-top: 1px solid #e9ecef !important;
    color: #666 !important;
    font-size: 14px !important;
}

.contact-info a {
    color: #4CAF50 !important;
    text-decoration: none !important;
    font-weight: 600 !important;
}

.contact-info a:hover {
    text-decoration: underline !important;
}

/* === MOBILE RESPONSIVE === */
@media (max-width: 768px) {
    /* Header adjustments */
    .item-chat-header {
        margin: 0 4px !important;
    }
    
    .header-chat-btn {
        padding: 6px 12px !important;
        font-size: 12px !important;
    }
    
    .header-chat-btn .Button-label {
        display: none !important; /* Hide text on small screens */
    }
    
    /* Modal adjustments */
    .chat-modal,
    .access-denied-modal {
        width: 95% !important;
        margin: 10px !important;
        border-radius: 8px !important;
    }
    
    .chat-modal {
        height: 90% !important;
        max-height: 90vh !important;
    }
    
    .access-denied-modal {
        max-height: 90vh !important;
    }
    
    .access-denied-body {
        padding: 20px !important;
    }
    
    .chat-header h3,
    .access-denied-header h3 {
        font-size: 16px !important;
    }
    
    .action-buttons {
        flex-direction: column !important;
        align-items: center !important;
        gap: 10px !important;
    }
    
    .btn-primary,
    .btn-secondary {
        width: 100% !important;
        max-width: 280px !important;
    }
    
    .allowed-groups-list,
    .upgrade-info {
        padding: 15px !important;
        margin: 15px 0 !important;
    }
}

/* === VERY SMALL MOBILE === */
@media (max-width: 480px) {
    .header-chat-btn .Button-icon {
        margin-right: 0 !important;
    }
    
    .access-denied-modal {
        width: 98% !important;
        margin: 5px !important;
    }
    
    .access-denied-body {
        padding: 15px !important;
    }
    
    .access-denied-icon,
    .guest-icon {
        font-size: 36px !important;
    }
    
    .access-denied-body h4 {
        font-size: 18px !important;
    }
    
    .allowed-groups-list,
    .upgrade-info,
    .participation-tips {
        padding: 12px !important;
    }
    
    .btn-primary,
    .btn-secondary {
        padding: 10px 15px !important;
        font-size: 13px !important;
        min-width: 120px !important;
    }
}

/* === DARK MODE SUPPORT === */
@media (prefers-color-scheme: dark) {
    .access-denied-modal,
    .chat-modal {
        background: #2c3e50 !important;
        color: #ecf0f1 !important;
    }
    
    .chat-content {
        background: #34495e !important;
    }
    
    .access-denied-body {
        color: #ecf0f1 !important;
    }
    
    .access-denied-body h4 {
        color: #ecf0f1 !important;
    }
    
    .access-denied-body p {
        color: #bdc3c7 !important;
    }
    
    .allowed-groups-list {
        background: linear-gradient(135deg, #2d5a2d, #3a6b3a) !important;
        border-color: #4CAF50 !important;
    }
    
    .upgrade-info {
        background: linear-gradient(135deg, #4a3a5c, #5d4e75) !important;
        border-color: #9c27b0 !important;
    }
    
    .participation-tips {
        background: linear-gradient(135deg, #5d4e37, #6b5a42) !important;
        border-color: #ff9800 !important;
    }
    
    .contact-info {
        border-top-color: #4a6741 !important;
        color: #bdc3c7 !important;
    }
}

/* === ACCESSIBILITY === */
@media (prefers-reduced-motion: reduce) {
    .chat-modal,
    .access-denied-modal {
        animation: none !important;
    }
    
    .spinner {
        animation: none !important;
    }
    
    .header-chat-btn:hover,
    .mobile-chat-btn:hover,
    .chat-dropdown-link:hover,
    .btn-primary:hover,
    .btn-secondary:hover {
        transform: none !important;
    }
}

/* === PRINT STYLES === */
@media print {
    #community-chat-modal,
    #access-denied-modal,
    #guest-access-modal,
    .item-chat {
        display: none !important;
    }
}
</style>';
    }
}
