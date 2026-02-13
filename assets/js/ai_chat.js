/**
 * 🔥 HopeAI — Premium Chat Engine
 * Persistent sessions, history, typewriter, animations
 */
document.addEventListener("DOMContentLoaded", () => {
  const chatWidget = document.querySelector(".ai-chat-widget");
  if (!chatWidget) return;

  const messagesContainer = document.getElementById("aiMessages");
  const inputField = chatWidget.querySelector('input[type="text"]');
  const sendBtn = chatWidget.querySelector(".ai-send-btn");
  const historyList = document.getElementById("aiHistoryList");
  const newChatBtn = document.getElementById("newChatBtn");

  let currentSessionId = null;
  let isTyping = false;

  // ── 1. Markdown Formatter ──
  function formatMessage(text) {
    if (!text) return "";
    text = text.replace(/```([\s\S]*?)```/gm, "<pre><code>$1</code></pre>");
    text = text.replace(/`([^`]+)`/g, "<code>$1</code>");
    text = text.replace(/^### (.*$)/gim, "<h3>$1</h3>");
    text = text.replace(/^## (.*$)/gim, "<h2>$1</h2>");
    text = text.replace(/^# (.*$)/gim, "<h1>$1</h1>");
    text = text.replace(/\*\*(.*?)\*\*/gim, "<strong>$1</strong>");
    text = text.replace(/\*(.*?)\*/gim, "<em>$1</em>");
    text = text.replace(/^\* (.*$)/gim, "<li>$1</li>");
    if (text.includes("<li>")) {
      text = text.replace(/(<li>.*<\/li>)/gms, "<ul>$1</ul>");
    }
    text = text.replace(/\n/gim, "<br>");
    return text.trim();
  }

  // ── 2. Typewriter Effect ──
  function typeEffect(element, text, speed = 12) {
    let i = 0;
    element.innerHTML = "";
    isTyping = true;
    function type() {
      if (i < text.length) {
        element.innerHTML = formatMessage(text.substring(0, i + 1));
        i++;
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        setTimeout(type, speed);
      } else {
        isTyping = false;
      }
    }
    type();
  }

  // ── 3. Append Message to UI ──
  function appendMessage(sender, text, type = "user", animate = false) {
    const time = new Date().toLocaleTimeString([], {
      hour: "2-digit",
      minute: "2-digit",
    });
    const messageDiv = document.createElement("div");
    messageDiv.className = `ai-message ${type}`;

    const nameSpan = document.createElement("span");
    nameSpan.className = "ai-name";

    // Icon Branding
    if (type === "system") {
      nameSpan.innerHTML = `<img src="/redhope/assets/imgs/favicon.png" alt="AI" class="ai-avatar">`;
    } else {
      nameSpan.innerHTML = `<i class="bi bi-person-circle" style="font-size: 1.1rem;"></i>`;
    }

    const messageContent = document.createElement("div");
    messageContent.className = "message-content";

    const timeSpan = document.createElement("span");
    timeSpan.className = "ai-time";
    timeSpan.textContent = time;

    messageDiv.appendChild(nameSpan);
    messageDiv.appendChild(messageContent);
    messageDiv.appendChild(timeSpan);
    messagesContainer.appendChild(messageDiv);

    if (animate && type === "system") {
      typeEffect(messageContent, text);
    } else {
      messageContent.innerHTML = type === "system" ? formatMessage(text) : text;
    }
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
  }

  // ── 4. Load Chat History ──
  async function loadHistory() {
    try {
      const res = await fetch("/redhope/apis/ai/get_sessions.php");
      const data = await res.json();
      if (data.success) {
        historyList.innerHTML = "";
        if (data.sessions.length === 0) {
          historyList.innerHTML =
            '<div class="p-3 text-center text-muted small">No past chats yet</div>';
          return;
        }
        data.sessions.forEach((session) => {
          const item = document.createElement("div");
          item.className = `ai-history-item ${session.session_id == currentSessionId ? "active" : ""}`;
          item.dataset.id = session.session_id;
          item.innerHTML = `
            <i class="bi bi-chat-left-text"></i>
            <div class="item-details">
              <span>${session.title || "Untitled Chat"}</span>
              <small>${new Date(session.created_at).toLocaleDateString()}</small>
            </div>
            <div class="history-actions">
              <button class="history-action-btn rename-btn" title="Rename">
                <i class="bi bi-pencil"></i>
              </button>
              <button class="history-action-btn delete-btn" title="Delete">
                <i class="bi bi-trash3"></i>
              </button>
            </div>
          `;

          // Click on item body to switch session
          item.addEventListener("click", (e) => {
            if (e.target.closest(".history-actions")) return;
            switchSession(session.session_id);
          });

          // Rename
          item.querySelector(".rename-btn").addEventListener("click", (e) => {
            e.stopPropagation();
            const newTitle = prompt(
              "Rename chat:",
              session.title || "Untitled Chat",
            );
            if (newTitle && newTitle.trim()) {
              renameSession(session.session_id, newTitle.trim());
            }
          });

          // Delete
          item.querySelector(".delete-btn").addEventListener("click", (e) => {
            e.stopPropagation();
            if (confirm("Delete this chat? This cannot be undone.")) {
              deleteSession(session.session_id);
            }
          });

          historyList.appendChild(item);
        });
      }
    } catch (e) {
      console.error("History Error:", e);
      historyList.innerHTML =
        '<div class="p-3 text-center text-muted small">Could not load history</div>';
    }
  }

  // ── Delete Session ──
  async function deleteSession(id) {
    try {
      const res = await fetch("/redhope/apis/ai/delete_session.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ sessionId: id }),
      });
      const data = await res.json();
      if (data.success) {
        if (currentSessionId == id) {
          startNewChat();
        } else {
          loadHistory();
        }
      }
    } catch (e) {
      console.error("Delete Error:", e);
    }
  }

  // ── Rename Session ──
  async function renameSession(id, title) {
    try {
      const res = await fetch("/redhope/apis/ai/rename_session.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ sessionId: id, title: title }),
      });
      const data = await res.json();
      if (data.success) {
        loadHistory();
      }
    } catch (e) {
      console.error("Rename Error:", e);
    }
  }

  // ── 5. Switch/Load Session Messages ──
  async function switchSession(id) {
    currentSessionId = id;
    messagesContainer.innerHTML = "";
    showTypingIndicator("Loading conversation...");
    try {
      const res = await fetch(
        `/redhope/apis/ai/get_messages.php?sessionId=${id}`,
      );
      const data = await res.json();
      removeTypingIndicator();
      if (data.success) {
        data.messages.forEach((msg) => {
          appendMessage(
            msg.sender === "AI" ? "HopeAI" : "You",
            msg.message_content,
            msg.sender === "AI" ? "system" : "user",
            false,
          );
        });
        loadHistory();
      }
    } catch (e) {
      removeTypingIndicator();
      console.error("Switch Session Error:", e);
    }

    // Close history menu after selection
    const historyMenu = document.getElementById("aiHistoryMenu");
    if (historyMenu) historyMenu.classList.remove("show");
  }

  // ── 6. New Chat ──
  function startNewChat() {
    currentSessionId = null;
    messagesContainer.innerHTML = "";
    appendMessage(
      "HopeAI",
      "Hello! 👋 I'm **HopeAI**, your personal health assistant. How can I help you today?",
      "system",
      true,
    );
    loadHistory();

    // Close history menu
    const historyMenu = document.getElementById("aiHistoryMenu");
    if (historyMenu) historyMenu.classList.remove("show");
  }

  // ── 7. Send Message ──
  async function handleSendMessage() {
    const message = inputField.value.trim();
    if (!message || isTyping) return;

    appendMessage("You", message, "user");
    inputField.value = "";
    inputField.focus();
    showTypingIndicator();

    try {
      const res = await fetch("/redhope/apis/ai/chat.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          message: message,
          sessionId: currentSessionId,
        }),
      });
      const data = await res.json();
      removeTypingIndicator();

      if (data.success) {
        appendMessage("HopeAI", data.reply, "system", true);
        if (!currentSessionId) {
          currentSessionId = data.sessionId;
          loadHistory();
        }
      } else {
        console.error("🔴 AI Chat Error:", data);
        if (data.debug) {
          console.table(data.debug);
        }
        appendMessage(
          "HopeAI",
          `⚠️ ${data.message || "Something went wrong. Please try again."}`,
          "system",
        );
      }
    } catch (e) {
      removeTypingIndicator();
      appendMessage(
        "HopeAI",
        "⚠️ Connection error. Please check your network.",
        "system",
      );
    }
  }

  // ── Typing Indicator ──
  function showTypingIndicator(text = "Thinking") {
    removeTypingIndicator();
    const indicator = document.createElement("div");
    indicator.className = "ai-message system typing-indicator";
    indicator.id = "aiTyping";
    indicator.innerHTML = `
      <span class="ai-name">
        <img src="/redhope/assets/imgs/favicon.png" alt="AI" class="ai-avatar">
      </span>
      <div class="message-content">
        <div class="typing-dots">
          <span></span><span></span><span></span>
          <em>${text}</em>
        </div>
      </div>
    `;
    messagesContainer.appendChild(indicator);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
  }

  function removeTypingIndicator() {
    const ind = document.getElementById("aiTyping");
    if (ind) ind.remove();
  }

  // ── 8. UI Interactions ──
  const historyBtn = chatWidget.querySelector(".ai-history-btn");
  const historyMenu = document.getElementById("aiHistoryMenu");

  if (historyBtn && historyMenu) {
    historyBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      historyMenu.classList.toggle("show");
    });

    document.addEventListener("click", (e) => {
      if (!historyBtn.contains(e.target) && !historyMenu.contains(e.target)) {
        historyMenu.classList.remove("show");
      }
    });
  }

  // Send button active state
  inputField.addEventListener("input", () => {
    const hasValue = inputField.value.trim().length > 0;
    sendBtn.style.opacity = hasValue ? "1" : "0.6";
    sendBtn.style.cursor = hasValue ? "pointer" : "default";
  });

  // Init
  sendBtn.addEventListener("click", handleSendMessage);
  inputField.addEventListener("keypress", (e) => {
    if (e.key === "Enter") handleSendMessage();
  });
  newChatBtn.addEventListener("click", startNewChat);
  loadHistory();

  console.log("🔥 HopeAI Premium Engine Online");
});
