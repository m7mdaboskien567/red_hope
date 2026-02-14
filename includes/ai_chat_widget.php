<div class="ai-chat-widget glass-panel">
    <div class="ai-header">
        <div class="ai-status">
            <div class="ai-pulse"></div>
            <span>HopeAI Online</span>
        </div>
        <div class="ai-header-actions">
            <div class="ai-history-dropdown">
                <button class="ai-history-btn" title="Chat History">
                    <i class="bi bi-clock-history"></i>
                </button>
                <div class="ai-history-menu" id="aiHistoryMenu">
                    <div class="ai-history-header d-flex justify-content-between align-items-center">
                        <span>Recent Chats</span>
                        <button class="btn btn-sm text-primary p-0" id="newChatBtn" title="New Conversation">
                            <i class="bi bi-plus-circle"></i> New
                        </button>
                    </div>
                    <div class="ai-history-list" id="aiHistoryList">
                        <div class="p-3 text-center text-muted small">Loading history...</div>
                    </div>
                </div>
            </div>
            <i class="bi bi-robot main-ai-icon"></i>
        </div>
    </div>

    <div class="ai-messages" id="aiMessages">
        <div class="ai-message system">
            <span class="ai-name">
                <img src="/redhope/assets/imgs/favicon.png" alt="AI" class="ai-avatar">
            </span>
            <div class="message-content">
                Hello! 👋 I'm <strong>HopeAI</strong>, your personal health assistant. How can I help you today?
            </div>
            <span class="ai-time"><?php echo date('H:i'); ?></span>
        </div>
    </div>

    <div class="ai-input-area">
        <input type="text" placeholder="Type your message...">
        <button class="ai-send-btn" title="Send">
            <i class="bi bi-send-fill"></i>
        </button>
    </div>
    <div class="ai-footer">
        <span>HopeAI may produce inaccurate info. Double-check important facts.</span>
    </div>
</div>

<script src="/redhope/assets/js/ai_chat.js"></script>