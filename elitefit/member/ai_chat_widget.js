// AI Chat Widget for EliteFit Member Dashboard
(function(){
    const chatBtn = document.createElement('div');
    chatBtn.id = 'ai-chat-btn';
    chatBtn.innerHTML = '💬 AI Gym Assistant';
    chatBtn.style = 'position:fixed;bottom:24px;right:24px;background:#4361ee;color:white;padding:12px 18px;border-radius:24px;cursor:pointer;z-index:9999;font-weight:600;box-shadow:0 2px 8px #2222';
    document.body.appendChild(chatBtn);

    const chatBox = document.createElement('div');
    chatBox.id = 'ai-chat-box';
    chatBox.style = 'display:none;position:fixed;bottom:70px;right:24px;width:340px;max-width:96vw;background:white;border-radius:18px;box-shadow:0 4px 24px #2223;padding:0;z-index:10000;font-family:Inter,sans-serif;';
    chatBox.innerHTML = `
        <div style="background:#4361ee;color:white;padding:16px 18px 12px 18px;border-radius:18px 18px 0 0;font-weight:700;font-size:1.1em;">EliteFit AI Gym Assistant <span id='ai-chat-close' style='float:right;cursor:pointer;font-weight:400;'>✖</span></div>
        <div id='ai-chat-messages' style='padding:16px;max-height:240px;overflow-y:auto;font-size:1em;background:#f8f9fa;'></div>
        <form id='ai-chat-form' style='display:flex;gap:6px;padding:12px 12px 12px 12px;background:#f8f9fa;border-radius:0 0 18px 18px;'>
            <input id='ai-chat-input' type='text' placeholder='Ask me anything gym-related...' autocomplete='off' style='flex:1;padding:8px 12px;border-radius:8px;border:1px solid #eee;font-size:1em;'>
            <button type='submit' style='background:#4361ee;color:white;border:none;padding:8px 16px;border-radius:8px;font-weight:600;cursor:pointer;'>Send</button>
        </form>
    `;
    document.body.appendChild(chatBox);

    let isAnimating = false;
    // --- Animated Gradient Glow on Borders Only & Faster Trigger ---
    // Inject CSS for animated border-only glow
    if (!document.getElementById('ai-chat-glow-style')) {
        const style = document.createElement('style');
        style.id = 'ai-chat-glow-style';
        style.innerHTML = `
        #ai-chat-box.glow-animate::after {
            content: '';
            position: absolute;
            top: -3px; left: -3px; right: -3px; bottom: -3px;
            border-radius: 18px;
            pointer-events: none;
            z-index: 10001;
            opacity: 0.85;
            animation: chatGlowMove 0.7s cubic-bezier(.4,1.8,.7,1);
            background: border-box;
            background-image: conic-gradient(from 0deg, #4361ee, #5f8fff, #00f2fe, #4361ee 100%);
            border: 3px solid transparent;
            -webkit-mask:
                linear-gradient(#fff 0 0) padding-box, 
                linear-gradient(#fff 0 0) border-box;
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            filter: blur(0px);
        }
        @keyframes chatGlowMove {
            0% { opacity: 0; filter: blur(8px); background-position: 0% 50%; }
            15% { opacity: 1; filter: blur(0px); }
            60% { opacity: 0.85; background-position: 100% 50%; }
            100% { opacity: 0; filter: blur(8px); background-position: 200% 50%; }
        }
        #ai-chat-box { position: fixed; overflow: visible !important; }
        `;
        document.head.appendChild(style);
    }

    function triggerGlow() {
        chatBox.classList.add('glow-animate');
        setTimeout(() => chatBox.classList.remove('glow-animate'), 700);
    }

    chatBtn.onclick = function() {
        if(isAnimating) return;
        isAnimating = true;
        chatBox.style.display = 'block';
        chatBox.style.pointerEvents = 'none';
        chatBox.style.transformOrigin = 'bottom right';
        chatBox.style.transition = 'transform 0.32s cubic-bezier(.4,1.8,.7,1), opacity 0.27s';
        chatBox.style.opacity = '0';
        chatBox.style.transform = 'scale(0.3)';
        triggerGlow();
        setTimeout(()=>{
            chatBox.style.opacity = '1';
            chatBox.style.transform = 'scale(1)';
        }, 10);
        setTimeout(()=>{
            chatBtn.style.display = 'none';
            chatBox.style.pointerEvents = '';
            isAnimating = false;
        }, 340);
    };
    document.getElementById('ai-chat-close').onclick = function() {
        if(isAnimating) return;
        isAnimating = true;
        triggerGlow();
        chatBox.style.pointerEvents = 'none';
        chatBox.style.transition = 'transform 0.28s cubic-bezier(.4,1.8,.7,1), opacity 0.22s';
        chatBox.style.opacity = '0';
        chatBox.style.transform = 'scale(0.3)';
        setTimeout(()=>{
            chatBox.style.display = 'none';
            chatBtn.style.display = 'block';
            isAnimating = false;
        }, 270);
    };

    const msgDiv = document.getElementById('ai-chat-messages');
    const form = document.getElementById('ai-chat-form');
    const input = document.getElementById('ai-chat-input');
    function addMsg(msg, sender) {
        const msgEl = document.createElement('div');
        msgEl.style = 'margin-bottom:10px;text-align:'+(sender==='user'?'right':'left')+';';
        msgEl.innerHTML = `<span style='background:${sender==='user'?'#e6e9ff':'#f1f3fa'};color:#222;padding:7px 12px;border-radius:10px;display:inline-block;max-width:90%;'>${msg}</span>`;
        msgDiv.appendChild(msgEl);
        msgDiv.scrollTop = msgDiv.scrollHeight;
    }
    form.onsubmit = function(e){
        e.preventDefault();
        const val = input.value.trim();
        if(!val) return;
        addMsg(val, 'user');
        input.value = '';
        addMsg('...', 'ai');
        fetch('/elitefit/member/ai_chat.php', {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body:JSON.stringify({message:val})
        })
        .then(r=>r.json())
        .then(data=>{
            msgDiv.removeChild(msgDiv.lastChild);
            addMsg(data.reply, 'ai');
        })
        .catch(()=>{
            msgDiv.removeChild(msgDiv.lastChild);
            addMsg('Sorry, there was an error contacting the AI.', 'ai');
        });
    };
})();
