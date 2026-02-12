<div class="ai-chat-widget glass-panel">
    <div class="ai-header">
        <div class="ai-status">
            <div class="ai-pulse"></div>
            <span>HopeAI Online</span>
        </div>
        <div class="ai-header-actions">
            <!-- Chat History Dropdown -->
            <div class="ai-history-dropdown">
                <button class="ai-history-btn" title="Chat History">
                    <i class="bi bi-clock-history"></i>
                </button>
                <div class="ai-history-menu">
                    <div class="ai-history-header">Recent Chats</div>
                    <div class="ai-history-list">
                        <div class="ai-history-item active">
                            <i class="bi bi-chat-left-text"></i>
                            <div class="item-details">
                                <span>Current Session</span>
                                <small>Today, <?php echo date('H:i'); ?></small>
                            </div>
                        </div>
                        <!-- will be loaded from the db -->
                    </div>
                </div>
            </div>
            <i class="bi bi-robot main-ai-icon"></i>
        </div>
    </div>

    <div class="ai-messages" id="aiMessages">
        <div class="ai-message system">
            <span class="ai-name">HopeAI</span>
            <p>Hello! I am HopeAI. How can I assist you with your donation journey today?</p>
            <span class="ai-time"><?php echo date('H:i'); ?></span>
        </div>
    </div>

    <div class="ai-input-area">
        <input type="text" placeholder="Ask HopeAI..." disabled>
        <button class="ai-send-btn" disabled>
            <i class="bi bi-send-fill"></i>
        </button>
    </div>
    <div class="ai-footer">
        <span>AI can make mistakes double check it</span>
    </div>
</div>

<script>
    console.log("✅ AI Chatbot Widget Loaded");

    // Chat History Dropdown Toggle
    document.addEventListener('DOMContentLoaded', () => {
        const historyBtn = document.querySelector('.ai-history-btn');
        const historyMenu = document.querySelector('.ai-history-menu');

        if (historyBtn && historyMenu) {
            historyBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                historyMenu.classList.toggle('show');
            });

            // Close menu when clicking outside
            document.addEventListener('click', (e) => {
                if (!historyMenu.contains(e.target) && !historyBtn.contains(e.target)) {
                    historyMenu.classList.remove('show');
                }
            });
        }
    });
</script>