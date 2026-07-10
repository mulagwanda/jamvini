(function () {
    const boot = window.JamViniAiAssistant;
    if (!boot) return;

    let config = null;
    let conversationId = window.localStorage.getItem('jv_ai_conversation_id');
    let conversationToken = window.localStorage.getItem('jv_ai_conversation_token');
    let pollTimer = null;

    const request = async (url, payload) => {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': boot.csrfToken
            },
            body: JSON.stringify(payload)
        });

        const data = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(data.message || 'Request failed.');
        return data;
    };

    const scrollToBottom = (body) => {
        body.scrollTop = body.scrollHeight;
    };

    const pageContext = () => ({
        page_url: window.location.href,
        page_title: document.title || '',
        referrer: document.referrer || '',
        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone || '',
        language: navigator.language || ''
    });

    const addMessage = (body, role, message) => {
        const row = document.createElement('div');
        row.className = 'jv-ai-msg-row ' + role;

        if (role === 'assistant' || role === 'staff') {
            const avatar = document.createElement('div');
            avatar.className = 'jv-ai-mini-avatar ' + role;
            avatar.textContent = role === 'staff' ? 'HM' : 'AI';
            row.appendChild(avatar);
        }

        const item = document.createElement('div');
        item.className = 'jv-ai-msg ' + role;
        item.textContent = message;
        row.appendChild(item);
        body.appendChild(row);
        scrollToBottom(body);

        return row;
    };

    const addTyping = (body) => {
        const row = document.createElement('div');
        row.className = 'jv-ai-msg-row assistant';
        row.innerHTML = `
            <div class="jv-ai-mini-avatar">AI</div>
            <div class="jv-ai-msg assistant jv-ai-typing"><span></span><span></span><span></span></div>
        `;
        body.appendChild(row);
        scrollToBottom(body);
        return row;
    };

    const addSources = (body, sources) => {
        if (!sources || !sources.length) return;

        const wrap = document.createElement('div');
        wrap.className = 'jv-ai-sources';

        sources.slice(0, 3).forEach((source) => {
            const link = document.createElement(source.url ? 'a' : 'span');
            link.textContent = source.title;
            if (source.url) {
                link.href = source.url;
                link.target = '_blank';
                link.rel = 'noopener';
            }
            wrap.appendChild(link);
        });

        body.appendChild(wrap);
        scrollToBottom(body);
    };

    const openContact = (contact) => {
        contact.classList.add('open');
        contact.querySelector('input')?.focus();
    };

    const closeContact = (contact) => {
        contact.classList.remove('open');
    };

    const forgetConversation = () => {
        conversationId = null;
        conversationToken = null;
        window.localStorage.removeItem('jv_ai_conversation_id');
        window.localStorage.removeItem('jv_ai_conversation_token');
        window.localStorage.removeItem('jv_ai_messages');
    };

    const storedMessages = () => {
        try {
            return JSON.parse(window.localStorage.getItem('jv_ai_messages') || '[]');
        } catch (error) {
            return [];
        }
    };

    const rememberMessage = (role, message, sources) => {
        const messages = storedMessages();
        messages.push({ role, message, sources: sources || [] });
        window.localStorage.setItem('jv_ai_messages', JSON.stringify(messages.slice(-30)));
    };

    const hydrateMessages = (body) => {
        storedMessages().forEach((item) => {
            addMessage(body, item.role, item.message);
            if (item.role === 'assistant') {
                addSources(body, item.sources);
            }
        });
    };

    const hydrateFromServer = async (body) => {
        if (!conversationId || !conversationToken || !boot.conversationUrl) return false;

        try {
            const url = `${boot.conversationUrl}/${conversationId}?token=${encodeURIComponent(conversationToken)}`;
            const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!response.ok) return false;

            const data = await response.json();
            const messages = data.messages || [];
            if (!messages.length) return false;

            window.localStorage.setItem('jv_ai_messages', JSON.stringify(messages.slice(-30)));
            body.innerHTML = '';
            hydrateMessages(body);
            return true;
        } catch (error) {
            return false;
        }
    };

    const sendMessage = async (body, input, message) => {
        if (!message) return;

        addMessage(body, 'user', message);
        rememberMessage('user', message);
        input.value = '';
        input.disabled = true;
        const typing = addTyping(body);

        try {
            const data = await request(boot.messageUrl, {
                conversation_id: conversationId,
                conversation_token: conversationToken,
                message,
                ...pageContext()
            });

            conversationId = data.conversation_id;
            conversationToken = data.conversation_token;
            window.localStorage.setItem('jv_ai_conversation_id', conversationId);
            window.localStorage.setItem('jv_ai_conversation_token', conversationToken);

            typing.remove();
            addMessage(body, 'assistant', data.reply);
            addSources(body, data.sources);
            rememberMessage('assistant', data.reply, data.sources);
            if (data.needs_human) startPolling(body);
        } catch (error) {
            typing.remove();
            addMessage(body, 'assistant', error.message);
        } finally {
            input.disabled = false;
            input.focus();
        }
    };

    const loadKnowledgeBase = async (panel) => {
        if (!config.knowledgeBaseEnabled || !config.knowledgeBaseUrl) return;

        const wrap = panel.querySelector('.jv-ai-kb-list');
        const search = panel.querySelector('.jv-ai-kb-search');
        if (!wrap || !search) return;

        const render = (articles) => {
            wrap.innerHTML = '';
            if (!articles.length) {
                wrap.innerHTML = '<div class="jv-ai-kb-empty">No matching articles yet.</div>';
                return;
            }

            articles.forEach((article) => {
                const item = document.createElement(article.url ? 'a' : 'button');
                item.className = 'jv-ai-kb-item';
                if (article.url) {
                    item.href = article.url;
                    item.target = '_blank';
                    item.rel = 'noopener';
                } else {
                    item.type = 'button';
                }
                item.innerHTML = `<strong>${escapeHtml(article.title)}</strong><span>${escapeHtml(article.excerpt || '')}</span>`;
                wrap.appendChild(item);
            });
        };

        const fetchArticles = async () => {
            try {
                const url = `${config.knowledgeBaseUrl}?q=${encodeURIComponent(search.value || '')}`;
                const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
                if (!response.ok) return;
                const data = await response.json();
                render(data.articles || []);
            } catch (error) {}
        };

        search.addEventListener('input', debounce(fetchArticles, 280));
        fetchArticles();
    };

    const startPolling = (body) => {
        if (pollTimer || !conversationId || !conversationToken) return;

        pollTimer = window.setInterval(async () => {
            if (!document.querySelector('.jv-ai-panel.open')) return;
            await hydrateFromServer(body);
        }, Math.max(5, Number(config.pollSeconds || 12)) * 1000);
    };

    const build = () => {
        document.documentElement.style.setProperty('--jv-ai-color', config.brandColor || '#2563eb');

        const launcher = document.createElement('button');
        launcher.className = 'jv-ai-launcher ' + (config.position || 'right');
        launcher.type = 'button';
        launcher.setAttribute('aria-label', 'Open chat');
        launcher.innerHTML = `
            <span class="jv-ai-launcher-badge">AI</span>
            <span class="jv-ai-launcher-text">Ask assistant</span>
        `;

        const panel = document.createElement('section');
        panel.className = 'jv-ai-panel ' + (config.position || 'right');
        panel.setAttribute('aria-label', config.title || 'Assistant');
        panel.innerHTML = `
            <div class="jv-ai-head">
                <div class="jv-ai-head-row">
                    <div class="jv-ai-identity">
                        <div class="jv-ai-avatar">AI</div>
                        <div style="min-width:0;">
                            <div class="jv-ai-title"></div>
                            <div class="jv-ai-status"><span class="jv-ai-dot"></span><span>Online support assistant</span></div>
                        </div>
                    </div>
                    <div class="jv-ai-tools">
                        <button class="jv-ai-clear" type="button" aria-label="Clear chat">New</button>
                        <button class="jv-ai-close" type="button" aria-label="Close chat">x</button>
                    </div>
                </div>
            </div>
            <div class="jv-ai-intro">
                <p class="jv-ai-intro-text"></p>
                <div class="jv-ai-kb">
                    <div class="jv-ai-kb-head">
                        <strong>Knowledge Base</strong>
                        <span>Try an article first</span>
                    </div>
                    <input class="jv-ai-input jv-ai-kb-search" placeholder="Search articles...">
                    <div class="jv-ai-kb-list"></div>
                </div>
                <div class="jv-ai-prompts">
                    <button class="jv-ai-prompt" type="button">Compare hosting plans</button>
                    <button class="jv-ai-prompt" type="button">Domain registration help</button>
                    <button class="jv-ai-prompt" type="button">Payment options</button>
                </div>
            </div>
            <div class="jv-ai-body"></div>
            <div class="jv-ai-actions"><button class="jv-ai-human" type="button">Talk to a human</button></div>
            <div class="jv-ai-contact">
                <p class="jv-ai-contact-title">Escalate to support</p>
                <p class="jv-ai-contact-note">We will include this chat transcript so the team has context.</p>
                <input class="jv-ai-input jv-ai-name" placeholder="Your name">
                <input class="jv-ai-input jv-ai-email" type="email" placeholder="Email for reply">
                <button class="jv-ai-send jv-ai-escalate" type="button">Open support ticket</button>
            </div>
            <form class="jv-ai-form">
                <input class="jv-ai-input" name="message" autocomplete="off" placeholder="Ask about hosting, domains, billing..." required>
                <button class="jv-ai-send" type="submit" aria-label="Send message">Send</button>
            </form>
        `;

        const body = panel.querySelector('.jv-ai-body');
        const form = panel.querySelector('.jv-ai-form');
        const input = form.querySelector('input[name="message"]');
        const contact = panel.querySelector('.jv-ai-contact');

        panel.querySelector('.jv-ai-title').textContent = config.title || 'Assistant';
        panel.querySelector('.jv-ai-intro-text').textContent = config.welcomeMessage || 'Hi, how can I help you today?';
        if (conversationId && conversationToken && storedMessages().length) {
            hydrateMessages(body);
            hydrateFromServer(body);
            startPolling(body);
        } else if (conversationId && conversationToken) {
            hydrateFromServer(body).then((loaded) => {
                if (!loaded && !body.children.length) {
                    const greeting = 'Ask me anything about services, domains, payments, or support. If I cannot help, I can open a ticket for a human.';
                    addMessage(body, 'assistant', greeting);
                    rememberMessage('assistant', greeting);
                }
            });
            startPolling(body);
        } else {
            const greeting = 'Ask me anything about services, domains, payments, or support. If I cannot help, I can open a ticket for a human.';
            addMessage(body, 'assistant', greeting);
            rememberMessage('assistant', greeting);
        }

        launcher.addEventListener('click', () => {
            panel.classList.toggle('open');
            if (panel.classList.contains('open')) {
                setTimeout(() => input.focus(), 120);
            }
        });

        panel.querySelector('.jv-ai-close').addEventListener('click', () => panel.classList.remove('open'));
        panel.querySelector('.jv-ai-clear').addEventListener('click', () => {
            forgetConversation();
            closeContact(contact);
            body.innerHTML = '';
            const greeting = 'Fresh chat started. What would you like help with?';
            addMessage(body, 'assistant', greeting);
            rememberMessage('assistant', greeting);
            input.focus();
        });
        panel.querySelector('.jv-ai-human').addEventListener('click', () => openContact(contact));

        panel.querySelectorAll('.jv-ai-prompt').forEach((button) => {
            button.addEventListener('click', () => {
                sendMessage(body, input, button.textContent.trim());
            });
        });

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            sendMessage(body, input, input.value.trim());
        });

        panel.querySelector('.jv-ai-escalate').addEventListener('click', async () => {
            if (!conversationId) {
                addMessage(body, 'assistant', 'Please send a message first so I can include the transcript.');
                return;
            }

            const button = panel.querySelector('.jv-ai-escalate');
            button.disabled = true;

            try {
                const data = await request(boot.escalateUrl, {
                    conversation_id: conversationId,
                    conversation_token: conversationToken,
                    visitor_name: panel.querySelector('.jv-ai-name').value,
                    visitor_email: panel.querySelector('.jv-ai-email').value,
                    message: 'Visitor requested human support from the chat widget.'
                });

                addMessage(body, 'assistant', data.ticket_number ? `${data.message} Ticket: ${data.ticket_number}` : data.message);
                closeContact(contact);
                startPolling(body);
            } catch (error) {
                addMessage(body, 'assistant', error.message);
                openContact(contact);
            } finally {
                button.disabled = false;
            }
        });

        document.body.appendChild(launcher);
        document.body.appendChild(panel);
        if (!config.knowledgeBaseEnabled) {
            panel.querySelector('.jv-ai-kb')?.remove();
        } else {
            loadKnowledgeBase(panel);
        }
    };

    const escapeHtml = (value) => String(value || '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    }[char]));

    const debounce = (fn, wait) => {
        let timeout = null;
        return (...args) => {
            window.clearTimeout(timeout);
            timeout = window.setTimeout(() => fn(...args), wait);
        };
    };

    fetch(boot.configUrl, { headers: { 'Accept': 'application/json' } })
        .then((response) => response.json())
        .then((data) => {
            config = data;
            if (config.enabled) build();
        })
        .catch(() => {});
})();
