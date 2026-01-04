<?php
/**
 * Template Name: Agent with Rag
 */

get_header(); ?>

<style>
* {
   box-sizing: border-box;
}

body {
   font-family: 'Raleway', sans-serif;
   background: #f5f5f5;
}

.agent-container {
   max-width: 800px;
   margin: 40px auto;
   padding: 40px;
   background: white;
   border-radius: 12px;
   box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

h1 {
   font-size: 32px;
   color: #333;
   margin-bottom: 30px;
   text-align: center;
   font-weight: 600;
}

input[type="text"] {
   width: 100%;
   padding: 14px 18px;
   margin-bottom: 15px;
   border: 2px solid #e0e0e0;
   border-radius: 8px;
   font-size: 16px;
   transition: border-color 0.3s ease;
}

input[type="text"]:focus {
   outline: none;
   border-color: #4a90e2;
}

input[type="text"]::placeholder {
   color: #999;
}

#sendBtn {
   width: 100%;
   padding: 14px;
   background: #4a90e2;
   color: white;
   border: none;
   border-radius: 8px;
   font-size: 18px;
   font-weight: 600;
   cursor: pointer;
   transition: background 0.3s ease;
   margin-top: 10px;
}

#sendBtn:hover {
   background: #357abd;
}

#sendBtn:active {
   transform: translateY(1px);
}

#result {
   margin-top: 30px;
   padding: 20px;
   background: #f9f9f9;
   border-radius: 8px;
   border: 2px solid #e0e0e0;
   min-height: 100px;
   font-size: 16px;
   line-height: 1.6;
   color: #333;
}

#result p {
   margin: 10px 0;
}

#result strong {
   color: #4a90e2;
   font-weight: 600;
}

#result p[style*="color: red"] {
   color: #e74c3c !important;
   font-weight: 500;
}

.loading {
   color: #4a90e2;
   font-style: italic;
}

@media (max-width: 768px) {
   .agent-container {
      margin: 20px;
      padding: 25px;
   }
   
   h1 {
      font-size: 26px;
   }
}
</style>

<div class="agent-container">
   <h1>OpenAI API in Browser</h1>
   <input type="text" id="apiKey" placeholder="Enter your OpenAI API key" value="" autocomplete="off"
      data-form-type="other">
   <input type="text" id="prompt" placeholder="Enter your prompt" value="When was the first meetup held?"
      autocomplete="off">

   <button id="sendBtn" type="submit">Send Request</button>

   <div id="result"></div>
</div>

<script>
   document.getElementById('sendBtn').addEventListener('click', async () => {
      const apiKey = document.getElementById('apiKey').value;
      const prompt = document.getElementById('prompt').value;
      const resultDiv = document.getElementById('result');

      if (!apiKey) {
         resultDiv.innerHTML = '<p style="color: red;">Please enter your API key</p>';
         return;
      }

      resultDiv.innerHTML = '<p class="loading">Loading...</p>';
      system_prompt = `
      You are a helpful assistant. You always answer in a concise manner. 
      You never refuse to answer but do not make things up - just say 'I don't know'. You always try to help the user as much as possible. 
      Convert markdown to HTML when needed so that ouput is properly formatted.Ensure there is a new line after each sentence.

      Include relevant context from the information provided below when answering the user's question.
  
      `;

      RAG = `
         Brighton Web Development Meet Up is taking place on October 29th.
         The venue is the Skiff.
         The event starts at 6:30 PM and finishes at 9:00 PM.
         The Speaker is Craig West and the topic is 'AI Agents and Evals for all languages'
         Brighton Web Development Meet Up is part of Silicon Brighton.
         The current organiser is Gavin and it has been running since <strong>1865 (3rd October 1865)</strong> the first meetup being organised by Peter.
         Similar meet ups are WordUp Brighton, BrightonPy, PHP Sussex and many more.
         Details at siliconbrighton.com.`

      system_prompt += RAG;
      console.log(system_prompt);

      try {
         const response = await fetch('https://api.openai.com/v1/chat/completions', {
            method: 'POST',
            headers: {
               'Content-Type': 'application/json',
               'Authorization': `Bearer ${apiKey}`
            },
            body: JSON.stringify({
               model: 'gpt-4o-mini',
               messages: [{ role: 'system', content: system_prompt }, { role: 'user', content: prompt }],
               max_tokens: 1024
            })
         });

         const data = await response.json();
         console.log('API Response:', data);
         const msg = data.choices[0].message.content;
         let output = "";

         if (data.error) {
            resultDiv.innerHTML = `<p style="color: red;">Error: ${data.error.message}</p>`;
         } else {
            resultDiv.innerHTML = `<p><strong>Response:</strong></p><p>${output + '<br>' + data.choices[0].message.content}</p>`;
         }
      } catch (error) {
         resultDiv.innerHTML = `<p style="color: red;">Error: ${error.message}</p>`;
      }
   });
</script>

<?php get_footer(); ?>