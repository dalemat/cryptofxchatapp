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
});

const ALLOWED_GROUPS = [
    "Admin",
    "Mod", 
    "Recognised member"
];

function addChatToMultipleLocations() {
    addChatToHeader();
    
    const isLoggedIn = document.body.classList.contains("loggedIn") || 
                      document.querySelector(".SessionDropdown");
    if (isLoggedIn) {
        addChatToUserMenu();
    }
    
    addChatToMobileMenu();
    addChatToSidebar();
}

function addChatToHeader() {
    const headerSecondary = document.querySelector(".Header-secondary") || 
                           document.querySelector(".Header .container > .Header-secondary");
    
    if (headerSecondary && !headerSecondary.querySelector(".item-chat-header")) {
        const chatItem = document.createElement("li");
        chatItem.className = "item-chat item-chat-header";
        chatItem.innerHTML = `
            <a href="#" onclick="checkChatAccess(); return false;" class="Button header-chat-btn">
                <i class="icon fas fa-comments"></i>
                <span class="Button-label">💬 Chat</span>
            </a>
        `;
        
        headerSecondary.appendChild(chatItem);
        console.log("✅ Chat added to header");
    }
}

function addChatToUserMenu() {
    setTimeout(() => {
        const dropdownTrigger = document.querySelector(".SessionDropdown .Dropdown-toggle") ||
                               document.querySelector(".SessionDropdown-toggle");
        
        if (dropdownTrigger) {
            dropdownTrigger.addEventListener("click", function() {
                setTimeout(() => {
                    const dropdownMenu = document.querySelector(".Dropdown-menu") ||
                                       document.querySelector(".SessionDropdown .Dropdown-menu");
                    
                    if (dropdownMenu && !dropdownMenu.querySelector(".item-chat-usermenu")) {
                        const chatItem = document.createElement("li");
                        chatItem.className = "item-chat item-chat-usermenu";
                        chatItem.innerHTML = `
                            <a href="#" onclick="checkChatAccess(); return false;" class="usermenu-chat-btn">
                                <i class="icon fas fa-comments"></i>
                                <span class="usermenu-chat-label">💬 Community Chat</span>
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
    const mobileNav = document.querySelector(".App-drawer .Navigation") ||
                     document.querySelector(".HeaderPrimary .Navigation") ||
                     document.querySelector(".IndexPage-nav .Navigation");
    
    if (mobileNav && !mobileNav.querySelector(".item-chat-mobile")) {
        const chatItem = document.createElement("li");
        chatItem.className = "item-chat item-chat-mobile";
        chatItem.innerHTML = `
            <a href="#" onclick="checkChatAccess(); return false;" class="mobile-chat-btn">
                <i class="icon fas fa-comments"></i>
                <span class="mobile-chat-label">💬 Community Chat</span>
                <span class="chat-badge">Live</span>
            </a>
        `;
        
        mobileNav.insertBefore(chatItem, mobileNav.firstChild);
        console.log("✅ Chat added to mobile menu");
    }
}

function addChatToSidebar() {
    const sidebar = document.querySelector(".IndexPage-nav .Navigation") ||
                   document.querySelector(".Sidebar .Navigation") ||
                   document.querySelector(".ForumNav .Navigation");
    
    if (sidebar && !sidebar.querySelector(".item-chat-sidebar")) {
        const chatItem = document.createElement("li");
        chatItem.className = "item-chat item-chat-sidebar";
        chatItem.innerHTML = `
            <a href="#" onclick="checkChatAccess(); return false;" class="sidebar-chat-btn">
                <i class="icon fas fa-comments"></i>
                <span class="sidebar-chat-label">Community Chat</span>
                <span class="chat-badge">Live</span>
            </a>
        `;
        
        sidebar.insertBefore(chatItem, sidebar.firstChild);
        console.log("✅ Chat added to sidebar");
    }
}

function checkChatAccess() {
    const isLoggedIn = document.body.classList.contains("loggedIn") || 
                      document.querySelector(".SessionDropdown");
    
    if (!isLoggedIn) {
        console.log("🚫 User not logged in - showing registration message");
        showGuestAccessMessage();
        return;
    }
    
    const userData = getUserData();
    console.log("🔍 Checking access for user:", userData);
    
    if (hasAccess(userData)) {
        console.log("✅ Access granted - opening chat");
        openCommunityChat();
    } else {
        console.log("❌ Access denied - showing upgrade message");
        showAccessDeniedMessage();
    }
}

function getUserData() {
    if (typeof app !== "undefined" && app.session && app.session.user) {
        const groups = app.session.user.groups();
        const userData = {
            groups: groups.map(g => ({
                name: g.nameSingular() || g.name() || g.displayName()
            })),
            username: app.session.user.username()
        };
        return userData;
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
        statusElement.textContent = "❌ Failed to load";
        statusElement.style.color = "#ff6b6b";
    };
}

function showGuestAccessMessage() {
    const existingModal = document.getElementById("guest-access-modal");
    if (existingModal) {
        existingModal.remove();
    }
    
    const modal = document.createElement("div");
    modal.id = "guest-access-modal";
    modal.innerHTML = `
        <div class="access-denied-overlay" onclick="closeGuestAccess()"></div>
        <div class="access-denied-modal guest-modal">
            <div class="access-denied-header guest-header">
                <h3>👋 Welcome to Our Community!</h3>
                <button onclick="closeGuestAccess()" class="access-denied-close">&times;</button>
            </div>
            <div class="access-denied-body">
                <div class="guest-icon">🔐</div>
                <h4>Hi there! Chat access requires registration.</h4>
                
                <div class="guest-benefits">
                    <h4>🚀 Join our community to unlock:</h4>
                    <ul class="benefits-list">
                        <li>💬 <strong>Community Chat Access</strong></li>
                        <li>📝 <strong>Create & Reply to Discussions</strong></li>
                        <li>👍 <strong>Reactions & Voting</strong></li>
                        <li>🔔 <strong>Email Notifications</strong></li>
                        <li>👤 <strong>Custom Profile & Avatar</strong></li>
                        <li>⭐ <strong>Earn Recognition & Badges</strong></li>
                    </ul>
                </div>
                
                <div class="action-buttons">
                    <a href="/register" onclick="closeGuestAccess()" class="btn-primary">
                        🚀 Join Community
                    </a>
                    <a href="/login" onclick="closeGuestAccess()" class="btn-secondary">
                        👋 Login
                    </a>
                </div>
                
                <div class="guest-browse">
                    <p>Or <a href="/d">browse discussions</a> as a guest</p>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    document.body.style.overflow = "hidden";
    
    setTimeout(() => closeGuestAccess(), 20000);
}

function showAccessDeniedMessage() {
    const existingModal = document.getElementById("access-denied-modal");
    if (existingModal) {
        existingModal.remove();
    }
    
    const modal = document.createElement("div");
    modal.id = "access-denied-modal";
    modal.innerHTML = `
        <div class="access-denied-overlay" onclick="closeAccessDenied()"></div>
        <div class="access-denied-modal">
            <div class="access-denied-header">
                <h3>🔒 Chat Access Restricted</h3>
                <button onclick="closeAccessDenied()" class="access-denied-close">&times;</button>
            </div>
            <div class="access-denied-body">
                <div class="access-denied-icon">🚫</div>
                <h4>Sorry! Chat access is currently limited to specific member groups.</h4>
                
                <div class="allowed-groups-list">
                    <h4>🎯 Groups with chat access:</h4>
                    <ul>
                        <li>👑 <strong>Admin</strong> - Full access</li>
                        <li>🛡️ <strong>Moderators</strong> - Community helpers</li>
                        <li>⭐ <strong>Recognised Members</strong> - Active contributors</li>
                    </ul>
                </div>
                
                <div class="upgrade-info">
                    <h4>🌟 How to get access:</h4>
                    <p>Become more active in our community! Regular participation, helpful contributions, and positive engagement may lead to membership upgrades.</p>
                    
                    <div class="participation-tips">
                        <h4>🌟 Ways to get recognized:</h4>
                        <ul>
                            <li>📝 Create valuable discussions</li>
                            <li>💬 Provide helpful replies</li>
                            <li>👍 Engage positively with others</li>
                            <li>🏆 Follow community guidelines</li>
                        </ul>
                    </div>
                    
                    <div class="action-buttons">
                        <a href="/d" onclick="closeAccessDenied()" class="btn-primary">
                            📝 Join Discussions
                        </a>
                        <a href="/u" onclick="closeAccessDenied()" class="btn-secondary">
                            👥 Browse Members
                        </a>
                    </div>
                </div>
                
                <div class="contact-info">
                    <p><small>Questions about chat access? <a href="/d/new">Contact our moderators</a></small></p>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    document.body.style.overflow = "hidden";
    
    setTimeout(() => closeAccessDenied(), 15000);
}

function closeGuestAccess() {
    const modal = document.getElementById("guest-access-modal");
    if (modal) {
        modal.remove();
        document.body.style.overflow = "auto";
    }
}

function closeAccessDenied() {
    const modal = document.getElementById("access-denied-modal");
    if (modal) {
        modal.remove();
        document.body.style.overflow = "auto";
    }
}

function closeCommunityChat() {
    const modal = document.getElementById("community-chat-modal");
    if (modal) {
        modal.remove();
        document.body.style.overflow = "auto";
    }
}

document.addEventListener("keydown", function(e) {
    if (e.key === "Escape") {
        closeCommunityChat();
        closeAccessDenied();
        closeGuestAccess();
    }
});

if (typeof app !== "undefined" && app.history) {
    app.history.register(function() {
        setTimeout(function() {
            addChatToMultipleLocations();
        }, 500);
    });
}
</script>';
    }

    private function getChatStyles()
    {
        return '<style>
/* === LOCATION-SPECIFIC STYLES === */

/* Header Chat Button */
.item-chat-header .header-chat-btn {
    color: var(--header-controls-color, inherit);
    transition: all 0.3s ease;
    border-radius: 6px;
    padding: 8px 12px;
}

.item-chat-header .header-chat-btn:hover {
    color: #4CAF50;
    background: rgba(76, 175, 80, 0.1);
    transform: translateY(-1px);
}

/* User Menu Chat */
.item-chat-usermenu .usermenu-chat-btn {
    display: flex;
    align-items: center;
    padding: 10px 15px;
    color: #4CAF50;
    text-decoration: none;
    border-radius: 5px;
    transition: all 0.2s ease;
}

.item-chat-usermenu .usermenu-chat-btn:hover {
    background: rgba(76, 175, 80, 0.1);
    color: #45a049;
}

.usermenu-chat-label {
    margin-left: 10px;
}

/* Mobile Chat Button */
.item-chat-mobile .mobile-chat-btn {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 15px;
    color: #4CAF50;
    text-decoration: none;
    font-weight: 600;
    border-radius: 8px;
    margin: 5px;
    background: linear-gradient(135deg, rgba(76, 175, 80, 0.1), rgba(76, 175, 80, 0.05));
    border: 2px solid transparent;
    transition: all 0.3s ease;
}

.item-chat-mobile .mobile-chat-btn:hover {
    background: linear-gradient(135deg, #4CAF50, #45a049);
    color: white;
    border-color: #4CAF50;
    transform: scale(1.02);
    box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
}

.mobile-chat-label {
    margin-left: 12px;
    font-size: 16px;
}

/* Sidebar Chat */
.item-chat-sidebar .sidebar-chat-btn {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 15px;
    color: #4CAF50;
    text-decoration: none;
    font-weight: 600;
    border-radius: 8px;
    margin: 5px;
    background: linear-gradient(135deg, rgba(76, 175, 80, 0.08), rgba(76, 175, 80, 0.03));
    border-left: 4px solid #4CAF50;
    transition: all 0.3s ease;
}

.item-chat-sidebar .sidebar-chat-btn:hover {
    background: linear-gradient(135deg, #4CAF50, #45a049);
    color: white;
    transform: translateX(5px);
    box-shadow: 0 3px 10px rgba(76, 175, 80, 0.3);
}

.sidebar-chat-label {
    margin-left: 10px;
}

.chat-badge {
    background: #ff4757;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: bold;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

/* === RESPONSIVE DESIGN === */

/* Desktop: Show header and sidebar, hide mobile */
@media (min-width: 769px) {
    .item-chat-mobile {
        display: none !important;
    }
    
    .item-chat-header,
    .item-chat-sidebar {
        display: block;
    }
}

/* Tablet: Show header and mobile, hide sidebar */
@media (max-width: 768px) and (min-width: 481px) {
    .item-chat-sidebar {
        display: none !important;
    }
    
    .item-chat-header,
    .item-chat-mobile {
        display: block;
    }
    
    .item-chat-header .Button-label {
        display: none;
    }
    
    .item-chat-header .icon {
        margin-right: 0;
    }
}

/* Mobile: Show mobile navigation, hide others */
@media (max-width: 480px) {
    .item-chat-header,
    .item-chat-sidebar {
        display: none !important;
    }
    
    .item-chat-mobile {
        display: block;
    }
    
    .mobile-chat-btn {
        padding: 12px 15px !important;
        margin: 3px 5px !important;
    }
    
    .mobile-chat-label {
        font-size: 14px;
    }
}

/* === MODAL STYLES === */
#community-chat-modal, #access-denied-modal, #guest-access-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 10000;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.chat-overlay, .access-denied-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
}

.chat-modal {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 90%;
    max-width: 900px;
    height: 85%;
    max-height: 700px;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
    display: flex;
    flex-direction: column;
}

.access-denied-modal {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 90%;
    max-width: 600px;
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from { transform: translate(-50%, -60%); opacity: 0; }
    to { transform: translate(-50%, -50%); opacity: 1; }
}

.chat-header, .access-denied-header {
    color: white;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-shrink: 0;
}

.chat-header {
    background: linear-gradient(135deg, #4CAF50, #45a049);
}

.access-denied-header {
    background: linear-gradient(135deg, #ff6b6b, #ee5a52);
}

.guest-modal .guest-header {
    background: linear-gradient(135deg, #4CAF50, #45a049) !important;
}

.guest-icon {
    font-size: 48px;
    margin-bottom: 15px;
}

.guest-benefits {
    background: linear-gradient(135deg, #e8f5e8, #f0f8f0);
    border-radius: 10px;
    padding: 20px;
    margin: 20px 0;
    border-left: 5px solid #4CAF50;
}

.benefits-list {
    text-align: left;
    margin: 15px 0;
}

.benefits-list li {
    margin: 10px 0;
    font-size: 15px;
    list-style: none;
    padding: 5px 0;
}

.guest-browse {
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #e9ecef;
    color: #666;
}

.guest-browse a {
    color: #4CAF50;
    text-decoration: none;
    font-weight: 600;
}

.guest-browse a:hover {
    text-decoration: underline;
}

.chat-header h3, .access-denied-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
}

.chat-status {
    font-size: 14px;
    font-weight: 500;
}

.chat-close, .access-denied-close {
    background: none;
    border: none;
    color: white;
    font-size: 28px;
    cursor: pointer;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    transition: background 0.2s;
}

.chat-close:hover, .access-denied-close:hover {
    background: rgba(255, 255, 255, 0.2);
}

.chat-content {
    flex: 1;
    position: relative;
    display: flex;
    flex-direction: column;
}

.access-denied-body {
    padding: 30px;
    text-align: center;
    line-height: 1.6;
    color: #333;
}

.access-denied-icon {
    font-size: 48px;
    margin-bottom: 15px;
}

.access-denied-body h4 {
    color: #2c3e50;
    margin: 15px 0 20px 0;
    font-size: 18px;
}

.allowed-groups-list {
    text-align: left;
    background: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    margin: 20px 0;
    border-left: 5px solid #4CAF50;
}

.allowed-groups-list li {
    margin: 12px 0;
    font-size: 16px;
    list-style: none;
    padding: 8px 0;
    border-bottom: 1px solid #e9ecef;
}

.allowed-groups-list li:last-child {
    border-bottom: none;
}

.upgrade-info {
    background: linear-gradient(135deg, #e3f2fd, #f3e5f5);
    border-radius: 10px;
    padding: 20px;
    margin: 20px 0;
    border: 1px solid #e1bee7;
}

.upgrade-info h4 {
    color: #7b1fa2;
    margin-top: 0;
}

.participation-tips {
    background: #fff3cd;
    border-radius: 8px;
    padding: 15px;
    margin: 15px 0;
    border-left: 4px solid #ffc107;
}

.participation-tips h4 {
    color: #856404;
    margin-top: 0;
    font-size: 16px;
}

.participation-tips ul {
    text-align: left;
    margin: 10px 0;
}

.participation-tips li {
    margin: 8px 0;
    font-size: 14px;
}

.loading-message {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    height: 100%;
    padding: 40px 20px;
    text-align: center;
}

.spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #4CAF50;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
    margin-bottom: 20px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

#chat-iframe {
    flex: 1;
    border: none;
}

.action-buttons {
    margin-top: 20px;
    display: flex;
    gap: 10px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-primary, .btn-secondary {
    padding: 12px 20px;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-block;
    min-width: 140px;
    font-size: 14px;
}

.btn-primary {
    background: linear-gradient(135deg, #4CAF50, #45a049);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #45a049, #3d8b40);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(76, 175, 80, 0.4);
}

.btn-secondary {
    background: linear-gradient(135deg, #2196F3, #1976D2);
    color: white;
}

.btn-secondary:hover {
    background: linear-gradient(135deg, #1976D2, #1565C0);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(33, 150, 243, 0.4);
}

.contact-info {
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid #e9ecef;
    color: #666;
    font-size: 14px;
}

.contact-info a {
    color: #4CAF50;
    text-decoration: none;
    font-weight: 600;
}

.contact-info a:hover {
    text-decoration: underline;
}

/* === MOBILE RESPONSIVE === */
@media (max-width: 768px) {
    .chat-modal, .access-denied-modal {
        width: 95%;
        margin: 20px;
    }
    
    .chat-modal {
        height: 90%;
        max-height: none;
    }
    
    .access-denied-body {
        padding: 20px;
    }
    
    .action-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .btn-primary, .btn-secondary {
        width: 100%;
        max-width: 250px;
        margin: 5px 0;
    }
    
    .allowed-groups-list {
        padding: 15px;
        text-align: center;
    }
    
    .benefits-list {
        text-align: center;
    }
    
    .participation-tips ul {
        text-align: center;
    }
}

/* === VERY SMALL MOBILE === */
@media (max-width: 480px) {
    .chat-header h3, .access-denied-header h3 {
        font-size: 16px;
    }
    
    .access-denied-modal {
        width: 95%;
        max-height: 90vh;
        overflow-y: auto;
    }
    
    .access-denied-body {
        padding: 15px;
    }
    
    .access-denied-body h4 {
        font-size: 16px;
    }
    
    .allowed-groups-list {
        padding: 12px;
        margin: 15px 0;
    }
    
    .allowed-groups-list li {
        font-size: 14px;
        padding: 6px 0;
    }
    
    .guest-benefits {
        padding: 15px;
        margin: 15px 0;
    }
    
    .benefits-list li {
        font-size: 14px;
        margin: 8px 0;
    }
    
    .upgrade-info {
        padding: 15px;
        margin: 15px 0;
    }
    
    .participation-tips {
        padding: 12px;
    }
    
    .btn-primary, .btn-secondary {
        font-size: 13px;
        padding: 10px 15px;
        min-width: 120px;
    }
    
    .guest-icon, .access-denied-icon {
        font-size: 40px;
    }
}

/* === DARK MODE SUPPORT === */
@media (prefers-color-scheme: dark) {
    .access-denied-modal, .chat-modal {
        background: #2c3e50;
        color: #ecf0f1;
    }
    
    .access-denied-body {
        color: #ecf0f1;
    }
    
    .allowed-groups-list {
        background: #34495e;
        border-left-color: #4CAF50;
    }
    
    .guest-benefits {
        background: linear-gradient(135deg, #2c5530, #234a27);
    }
    
    .upgrade-info {
        background: linear-gradient(135deg, #3a4a5c, #2c3e50);
        border-color: #8e44ad;
    }
    
    .participation-tips {
        background: #5d4e37;
        border-left-color: #f39c12;
    }
    
    .contact-info {
        border-top-color: #4a6741;
        color: #bdc3c7;
    }
}

/* === ACCESSIBILITY === */
@media (prefers-reduced-motion: reduce) {
    .chat-modal, .access-denied-modal {
        animation: none;
    }
    
    .spinner {
        animation: none;
        border-top-color: #4CAF50;
    }
    
    .chat-badge {
        animation: none;
    }
    
    .item-chat-header .header-chat-btn:hover,
    .item-chat-mobile .mobile-chat-btn:hover,
    .item-chat-sidebar .sidebar-chat-btn:hover {
        transform: none;
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
