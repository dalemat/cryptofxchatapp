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
    console.log("📍 DOM Content Loaded");
    
    // Try multiple times with different delays
    setTimeout(() => addChatToAllLocations(), 500);
    setTimeout(() => addChatToAllLocations(), 1000);
    setTimeout(() => addChatToAllLocations(), 2000);
    setTimeout(() => addChatToAllLocations(), 3000);
    
    // Also try when page fully loads
    window.addEventListener("load", function() {
        console.log("📍 Window Loaded");
        setTimeout(() => addChatToAllLocations(), 500);
    });
    
    // Listen for Flarum route changes
    if (typeof app !== "undefined" && app.route) {
        const originalRoute = app.route;
        app.route = function() {
            const result = originalRoute.apply(this, arguments);
            setTimeout(() => addChatToAllLocations(), 1000);
            return result;
        };
    }
});

const ALLOWED_GROUPS = ["Admin", "Mod", "Recognised member"];

function addChatToAllLocations() {
    console.log("🔍 Attempting to add chat to all locations...");
    
    // Debug: Log all possible header elements
    console.log("Header elements found:");
    document.querySelectorAll("[class*=\"Header\"]").forEach(el => {
        console.log("- " + el.className);
    });
    
    addChatToHeader();
    addChatToMobile();
    addChatToAnyContainer();
}

function addChatToHeader() {
    console.log("🔍 Looking for header...");
    
    // Try multiple header selectors
    const headerSelectors = [
        ".Header-secondary .Header-controls",
        ".Header-secondary",
        ".Header-controls",
        ".header-secondary",
        ".header-controls",
        ".App-header .Header-secondary",
        ".App-header .Header-controls",
        "header .Header-secondary",
        "header .Header-controls",
        ".Header .Header-secondary",
        ".Header .Header-controls",
        "[class*=\"Header-secondary\"]",
        "[class*=\"Header-controls\"]"
    ];
    
    let headerContainer = null;
    
    for (const selector of headerSelectors) {
        headerContainer = document.querySelector(selector);
        if (headerContainer) {
            console.log("✅ Found header container:", selector);
            break;
        }
    }
    
    // If no specific container found, try to find any header-like element
    if (!headerContainer) {
        const headers = document.querySelectorAll("header, .Header, .header, [class*=\"header\"], [class*=\"Header\"]");
        console.log("🔍 Found " + headers.length + " header elements");
        
        headers.forEach((header, index) => {
            console.log(`Header ${index}:`, header.className);
            if (header.querySelector("ul, .nav, .controls") && !headerContainer) {
                headerContainer = header.querySelector("ul") || header.querySelector(".nav") || header.querySelector(".controls") || header;
                console.log("✅ Using header element at index", index);
            }
        });
    }
    
    if (headerContainer && !headerContainer.querySelector(".item-chat-header")) {
        console.log("✅ Adding chat to header");
        
        const chatItem = document.createElement("li");
        chatItem.className = "item-chat item-chat-header";
        chatItem.innerHTML = `
            <button onclick="checkChatAccess(); return false;" class="Button Button--link header-chat-btn" type="button">
                <i class="icon fas fa-comments Button-icon"></i>
                <span class="Button-label">💬 Chat</span>
            </button>
        `;
        
        // Add to beginning of container
        if (headerContainer.tagName === "UL") {
            headerContainer.insertBefore(chatItem, headerContainer.firstChild);
        } else {
            // Create UL if container is not UL
            const ul = document.createElement("ul");
            ul.appendChild(chatItem);
            headerContainer.appendChild(ul);
        }
        
        console.log("✅ Chat added to header successfully");
    } else if (!headerContainer) {
        console.log("❌ No header container found");
    } else {
        console.log("ℹ️ Chat already exists in header");
    }
}

function addChatToMobile() {
    console.log("🔍 Looking for mobile navigation...");
    
    // Try multiple mobile nav selectors
    const mobileSelectors = [
        ".App-drawer .Drawer-content",
        ".Drawer-content",
        ".drawer-content",
        ".App-navigation",
        ".navigation",
        ".mobile-nav",
        ".mobile-navigation",
        ".drawer",
        ".Drawer",
        "[class*=\"drawer\"]",
        "[class*=\"Drawer\"]",
        ".App-drawer ul",
        ".Drawer ul"
    ];
    
    let mobileContainer = null;
    
    for (const selector of mobileSelectors) {
        mobileContainer = document.querySelector(selector);
        if (mobileContainer) {
            console.log("✅ Found mobile container:", selector);
            break;
        }
    }
    
    // Alternative: Look for any navigation-like elements
    if (!mobileContainer) {
        const navElements = document.querySelectorAll("nav, .nav, [role=\"navigation\"], [class*=\"nav\"]");
        console.log("🔍 Found " + navElements.length + " nav elements");
        
        navElements.forEach((nav, index) => {
            console.log(`Nav ${index}:`, nav.className);
            // Look for one that seems like mobile nav
            if ((nav.className.includes("drawer") || nav.className.includes("mobile") || nav.querySelector("ul")) && !mobileContainer) {
                mobileContainer = nav.querySelector("ul") || nav;
                console.log("✅ Using nav element at index", index);
            }
        });
    }
    
    if (mobileContainer && !mobileContainer.querySelector(".item-chat-mobile")) {
        console.log("✅ Adding chat to mobile nav");
        
        const chatItem = document.createElement("li");
        chatItem.className = "item-chat item-chat-mobile";
        chatItem.innerHTML = `
            <button onclick="checkChatAccess(); return false;" class="hasIcon mobile-chat-btn" type="button">
                <i class="icon fas fa-comments Button-icon"></i>
                <span class="mobile-chat-label">💬 Community Chat</span>
            </button>
        `;
        
        // Add to beginning
        if (mobileContainer.tagName === "UL") {
            mobileContainer.insertBefore(chatItem, mobileContainer.firstChild);
        } else {
            mobileContainer.appendChild(chatItem);
        }
        
        console.log("✅ Chat added to mobile nav successfully");
    } else if (!mobileContainer) {
        console.log("❌ No mobile container found");
    } else {
        console.log("ℹ️ Chat already exists in mobile nav");
    }
}

function addChatToAnyContainer() {
    console.log("🔍 Looking for any suitable container...");
    
    // If we still havent found anywhere, add to body as floating button
    if (!document.querySelector(".item-chat-header") && !document.querySelector(".item-chat-mobile")) {
        console.log("📍 Adding floating chat button");
        
        if (!document.querySelector(".floating-chat-btn")) {
            const floatingBtn = document.createElement("div");
            floatingBtn.className = "floating-chat-btn";
            floatingBtn.innerHTML = `
                <button onclick="checkChatAccess(); return false;" class="float-chat-button" type="button" title="Community Chat">
                    <i class="fas fa-comments"></i>
                    <span>Chat</span>
                </button>
            `;
            
            document.body.appendChild(floatingBtn);
            console.log("✅ Floating chat button added");
        }
    }
    
    // Also try to add to any visible button container
    const buttonContainers = document.querySelectorAll(".Button, .button, [class*=\"button\"], [class*=\"Button\"]");
    console.log("🔍 Found " + buttonContainers.length + " button elements");
    
    // Try to find a navigation or toolbar to add to
    const toolbars = document.querySelectorAll(".toolbar, .Toolbar, .controls, .Controls, .actions, .Actions");
    if (toolbars.length > 0 && !document.querySelector(".item-chat-toolbar")) {
        console.log("✅ Adding to toolbar");
        
        const chatBtn = document.createElement("button");
        chatBtn.className = "Button item-chat-toolbar toolbar-chat-btn";
        chatBtn.onclick = function() { checkChatAccess(); return false; };
        chatBtn.innerHTML = `
            <i class="icon fas fa-comments"></i>
            <span>💬 Chat</span>
        `;
        
        toolbars[0].appendChild(chatBtn);
        console.log("✅ Chat added to toolbar");
    }
}

function checkChatAccess() {
    console.log("🔐 Checking chat access...");
    
    const userData = getCurrentUserData();
    console.log("👤 User data:", userData);
    
    if (!userData.isLoggedIn) {
        console.log("👤 User not logged in - showing guest message");
        showGuestAccessMessage();
        return;
    }
    
    if (!hasAccess(userData)) {
        console.log("🚫 User doesnt have access - showing access denied");
        showAccessDeniedMessage();
        return;
    }
    
    console.log("✅ User has access - opening chat");
    openCommunityChat();
}

function getCurrentUserData() {
    // Check if user is logged in via multiple methods
    const isLoggedIn = document.body.classList.contains("loggedIn") || 
                      document.querySelector(".SessionDropdown") ||
                      document.querySelector(".loggedIn") ||
                      (typeof app !== "undefined" && app.session && app.session.user);
    
    if (!isLoggedIn) {
        return { isLoggedIn: false, groups: [] };
    }
    
    // Try to get user data from Flarum app
    if (typeof app !== "undefined" && app.session && app.session.user && app.session.user.data) {
        return {
            isLoggedIn: true,
            groups: app.session.user.data.relationships.groups.data || [],
            username: app.session.user.data.attributes.username
        };
    }
    
    // Fallback: assume admin if admin elements exist
    const isAdmin = document.querySelector(".AdminNav") || 
                   document.body.classList.contains("admin") ||
                   window.location.href.includes("/admin");
    
    if (isAdmin) {
        return { isLoggedIn: true, groups: [{ attributes: { name: "Admin" } }] };
    }
    
    // Default: logged in but unknown groups
    return { isLoggedIn: true, groups: [{ attributes: { name: "Member" } }] };
}

function hasAccess(userData) {
    if (!userData || !userData.groups || userData.groups.length === 0) {
        return false;
    }
    
    return userData.groups.some(userGroup => {
        const groupName = userGroup.attributes ? userGroup.attributes.name : userGroup.name;
        return ALLOWED_GROUPS.some(allowedGroup => {
            return groupName === allowedGroup ||
                   (groupName && groupName.toLowerCase() === allowedGroup.toLowerCase());
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

// ESC key support
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
/* === FLOATING CHAT BUTTON (FALLBACK) === */
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
    padding: 12px 20px !important;
    border-radius: 25px !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    cursor: pointer !important;
    box-shadow: 0 4px 20px rgba(76, 175, 80, 0.4) !important;
    transition: all 0.3s ease !important;
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
}

.float-chat-button:hover {
    background: linear-gradient(135deg, #45a049, #3d8b40) !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 25px rgba(76, 175, 80, 0.5) !important;
}

.float-chat-button i {
    font-size: 16px !important;
}

/* === HEADER CHAT BUTTON === */
.item-chat-header {
    display: flex !important;
    align-items: center !important;
    margin: 0 8px !important;
    list-style: none !important;
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
    cursor: pointer !important;
}

.header-chat-btn:hover {
    background: linear-gradient(135deg, #45a049, #3d8b40) !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 12px rgba(76, 175, 80, 0.4) !important;
    color: white !important;
    text-decoration: none !important;
}

/* === MOBILE CHAT BUTTON === */
.item-chat-mobile {
    list-style: none !important;
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
    background: none !important;
    border: none !important;
    cursor: pointer !important;
    text-align: left !important;
}

.mobile-chat-btn:hover {
    background: rgba(76, 175, 80, 0.2) !important;
    color: #4CAF50 !important;
    text-decoration: none !important;
}

/* === TOOLBAR CHAT BUTTON === */
.toolbar-chat-btn {
    background: linear-gradient(135deg, #4CAF50, #45a049) !important;
    color: white !important;
    border: none !important;
    padding: 8px 15px !important;
    border-radius: 5px !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    cursor: pointer !important;
    margin: 0 5px !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
}

.toolbar-chat-btn:hover {
    background: linear-gradient(135deg, #45a049, #3d8b40) !important;
    text-decoration: none !important;
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
    cursor: pointer !important;
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

/* === MODAL HEADERS === */
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

/* === OTHER STYLES === */
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
}

.action-buttons {
    margin: 25px 0 !important;
    display: flex !important;
    gap: 15px !important;
    justify-content: center !important;
    flex-wrap: wrap !important;
}

.btn-primary,
.btn-secondary {
    padding: 12px 24px !important;
    border: none !important;
    border-radius: 25px !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    text-decoration: none !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    transition: all 0.3s ease !important;
    cursor: pointer !important;
}

.btn-primary {
    background: linear-gradient(135deg, #4CAF50, #45a049) !important;
    color: white !important;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #45a049, #3d8b40) !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 5px 15px rgba(76, 175, 80, 0.4) !important;
    color: white !important;
    text-decoration: none !important;
}

.btn-secondary {
    background: linear-gradient(135deg, #2196F3, #1976D2) !important;
    color: white !important;
}

.btn-secondary:hover {
    background: linear-gradient(135deg, #1976D2, #1565C0) !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 5px 15px rgba(33, 150, 243, 0.4) !important;
    color: white !important;
    text-decoration: none !important;
}

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

/* === LOADING STATES === */
.loading-message {
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    justify-content: center !important;
    height: 100% !important;
    padding: 40px !important;
    text-align: center !important;
    color: #666 !important;
}

.spinner {
    width: 40px !important;
    height: 40px !important;
    border: 4px solid #f3f3f3 !important;
    border-top: 4px solid #4CAF50 !important;
    border-radius: 50% !important;
    animation: spin 1s linear infinite !important;
    margin-bottom: 20px !important;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* === MOBILE RESPONSIVE === */
@media (max-width: 768px) {
    .chat-modal,
    .access-denied-modal {
        margin: 0 !important;
        width: 100% !important;
        height: 100% !important;
        max-width: 100% !important;
        max-height: 100% !important;
        border-radius: 0 !important;
    }
    
    .floating-chat-btn {
        bottom: 15px !important;
        right: 15px !important;
    }
    
    .float-chat-button {
        padding: 10px 16px !important;
        font-size: 13px !important;
    }
    
    .action-buttons {
        flex-direction: column !important;
        align-items: center !important;
    }
    
    .btn-primary,
    .btn-secondary {
        width: 100% !important;
        max-width: 200px !important;
        justify-content: center !important;
    }
    
    .header-chat-btn {
        padding: 6px 12px !important;
        font-size: 12px !important;
    }
    
    .header-chat-btn .Button-label {
        display: none !important;
    }
    
    .header-chat-btn .Button-icon {
        margin: 0 !important;
    }
}

@media (max-width: 480px) {
    .access-denied-body {
        padding: 20px !important;
    }
    
    .allowed-groups-list,
    .upgrade-info,
    .participation-tips {
        padding: 15px !important;
        margin: 15px 0 !important;
    }
    
    .chat-header h3,
    .access-denied-header h3 {
        font-size: 16px !important;
    }
    
    .access-denied-body h4 {
        font-size: 18px !important;
    }
}

/* === DARK MODE SUPPORT === */
@media (prefers-color-scheme: dark) {
    .chat-modal,
    .access-denied-modal {
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
    .float-chat-button:hover,
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
    .item-chat,
    .floating-chat-btn {
        display: none !important;
    }
}

/* === FORCE VISIBILITY === */
.item-chat-header,
.item-chat-mobile,
.item-chat-toolbar,
.floating-chat-btn {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}
</style>';
    }
}
