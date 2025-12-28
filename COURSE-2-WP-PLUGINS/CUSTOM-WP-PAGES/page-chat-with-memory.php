<?php
/**
 * Template Name: Chat with Memory
 */

get_header(); ?>

<style>
.chat-wrapper {
   max-width: 1200px;
   margin: 0 auto;
   padding: 20px;
}

input {
   width: 100%;
   padding: 10px;
   margin: 10px 0;
   box-sizing: border-box;
}

button {
   padding: 10px 20px;
   background: #007bff;
   color: white;
   border: none;
   cursor: pointer;
}

button:hover {
   background: #0056b3;
}

#chatContainer {
   margin-top: 20px;
   height: 600px;
   overflow-y: auto;
   border: 4px solid #6c757d;
   border-radius: 15px;
   padding: 15px;
   background: #e5ddd5;
   background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZGVmcz48cGF0dGVybiBpZD0iYSIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIj48cGF0aCBkPSJtMCAwaDQwdjQwSDB6IiBmaWxsPSJub25lIi8+PHBhdGggZD0iTTAgMGwyMCAyMHoiIGZpbGw9Im5vbmUiIHN0cm9rZT0iI2Y1ZjVmNSIgc3Ryb2tlLXdpZHRoPSIyIi8+PC9wYXR0ZXJuPjwvZGVmcz48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSJ1cmwoI2EpIi8+PC9zdmc+');
}

.message {
   margin: 5px 0;
   padding: 8px 12px;
   border-radius: 18px;
   max-width: 60%;
   word-wrap: break-word;
   display: flex;
   align-items: center;
   min-height: 36px;
}

.message.user {
   background: #dcf8c6;
   color: #303030;
   margin-left: auto;
   justify-content: flex-end;
}

.message.assistant {
   background: white;
   color: #303030;
   margin-right: auto;
}

#inputContainer {
   margin-top: 20px;
   display: flex;
   gap: 10px;
}

#inputContainer input {
   flex: 1;
   margin: 0;
}

#inputContainer button {
   padding: 10px 20px;
   margin: 0;
}
</style>

<div class="chat-wrapper">
   <h1>OpenAI API in Browser</h1>
   <input type="text" id="apiKey" placeholder="Enter your OpenAI API key">

   <div id="chatContainer">
      <div id="messages"></div>
   </div>

   <div id="inputContainer">
      <input type="text" id="messageInput" placeholder="Type your message..." value="Capital of England?">
      <button id="sendBtn">Send</button>
      <button id="clearBtn" style="background: #dc3545;">Clear Chat</button>
   </div>
</div>

<script>
   const systemPrompt = `
      You are a helpful assistant with access to the full conversation history. You always answer in a concise manner.

      IMPORTANT: When the user asks follow-up questions using words like "it", "that", "there", etc., refer to the previous messages in our conversation to understand what they're referring to. Use your knowledge to answer questions about topics we've discussed.

      If you genuinely don't know something, say 'I don't know', but always check the conversation context first before saying this.

      Convert markdown to HTML when needed so that output is properly formatted. Ensure there is a new line after each sentence.
   `;

   let messages = [{ role: 'system', content: systemPrompt }];

   function loadMessages() {
      const saved = localStorage.getItem('chatHistory');
      if (saved) {
         messages = JSON.parse(saved);
      }
      renderMessages();
   }

   function saveMessages() {
      localStorage.setItem('chatHistory', JSON.stringify(messages));
   }

   function renderMessages() {
      const messagesDiv = document.getElementById('messages');
      messagesDiv.innerHTML = '';
      messages.slice(1).forEach(msg => {
         const msgDiv = document.createElement('div');
         msgDiv.className = `message ${msg.role}`;
         msgDiv.textContent = msg.content;
         messagesDiv.appendChild(msgDiv);
      });
   }

   async function sendMessage(userMessage) {
      const apiKey = document.getElementById('apiKey').value;

      if (!apiKey) {
         alert('Please enter your API key');
         return;
      }

      messages.push({ role: 'user', content: userMessage });
      saveMessages();
      renderMessages();

      document.getElementById('messageInput').value = '';

      const loadingDiv = document.createElement('div');
      loadingDiv.className = 'message assistant loading';
      loadingDiv.textContent = 'Thinking...';
      document.getElementById('messages').appendChild(loadingDiv);

      document.getElementById('chatContainer').scrollTop = document.getElementById('chatContainer').scrollHeight;

      console.log('Sending to OpenAI - Total messages in conversation:', messages.length);
      console.log('Full conversation history:', messages);

      try {
         const response = await fetch('https://api.openai.com/v1/chat/completions', {
            method: 'POST',
            headers: {
               'Content-Type': 'application/json',
               'Authorization': `Bearer ${apiKey}`
            },
            body: JSON.stringify({
               model: 'gpt-4o-mini',
               messages: messages,
               max_tokens: 1024
            })
         });

         const data = await response.json();

         if (data.error) {
            loadingDiv.textContent = `Error: ${data.error.message}`;
            loadingDiv.style.color = 'red';
         } else {
            document.getElementById('messages').removeChild(loadingDiv);

            const assistantMessage = data.choices[0].message.content;
            messages.push({ role: 'assistant', content: assistantMessage });
            saveMessages();
            renderMessages();

            console.log('Assistant response added. Total messages now:', messages.length);

            document.getElementById('chatContainer').scrollTop = document.getElementById('chatContainer').scrollHeight;
         }
      } catch (error) {
         loadingDiv.textContent = `Error: ${error.message}`;
         loadingDiv.style.color = 'red';
      }
   }

   document.getElementById('sendBtn').addEventListener('click', () => {
      const input = document.getElementById('messageInput');
      const message = input.value.trim();
      if (message) {
         sendMessage(message);
      }
   });

   document.getElementById('messageInput').addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
         sendMessage(e.target.value.trim());
      }
   });

   document.getElementById('clearBtn').addEventListener('click', () => {
      if (confirm('Are you sure you want to clear the chat history?')) {
         messages = [{ role: 'system', content: systemPrompt }];
         saveMessages();
         renderMessages();
      }
   });

   window.addEventListener('load', loadMessages);

   console.log('Chat initialized. Messages array:', messages);
</script>

<?php get_footer(); ?>