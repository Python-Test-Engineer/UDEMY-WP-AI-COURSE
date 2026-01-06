<?php
/**
 * Template Name: Basic Agent
 */

// This is for learning purposes only. In production enqueue CSS and JS scripts properly via functions.php or other methods.

get_header(); ?>

<style>
* {
   font-family: 'Raleway', sans-serif;
   font-size: 20px;
}

.basic-agent-wrapper {
   max-width: 800px;
   margin: 50px auto;
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

#result {
   margin-top: 20px;
   padding: 15px;
   background: #bcbeb6;
   border-radius: 5px;
   border: 4px solid #6c757d;
   min-height: 100px;
}
</style>

<div class="basic-agent-wrapper">
   <h1>OpenAI API in Browser</h1>

   <input type="text" id="apiKey" placeholder="Enter your OpenAI API key" value="" autocomplete="off" data-form-type="other">
   <input type="text" id="prompt" placeholder="Enter your prompt" value="Capital of England?">

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

      resultDiv.innerHTML = '<p>Loading...</p>';
      
      const system_prompt = `
      You are a helpful assistant. You always answer in a concise manner. 

      You never refuse to answer but do not make things up - just say 'I don't know'. You always try to help the user as much as possible. 

      Convert markdown to HTML when needed so that output is properly formatted. Ensure there is a new line after each sentence.`;

      // ******************** AI BIT ********************
      try {
         const response = await fetch('https://api.openai.com/v1/chat/completions', {
            method: 'POST',
            headers: {
               'Content-Type': 'application/json', // Tells the server we're sending JSON data.
               'Authorization': `Bearer ${apiKey}` // Authenticates our request using the API key.
            },
            body: JSON.stringify({
               model: 'gpt-4o-mini', // Specifies which AI model to use (a smaller, efficient version of GPT-4).
               messages: [
                  { role: 'system', content: system_prompt }, // Defines how the AI agent should behave (its "personality" and rules).
                  { role: 'user', content: prompt } // The user's input/query for the agent to respond to.
               ], // Models are trained to understand these roles to generate appropriate responses so we use the same format.

               max_tokens: 1024, // Limits the response length to prevent excessive output. A token in LLM (Large Language Model) terminology is the basic unit of text that the model processes. Think of it as the fundamental "chunk" that the AI reads and generates. You can typically estimate roughly 1 token ≈ 4 characters or 0.75 words in English, though this varies significantly based on the text.

               temperature: 0.7 // Controls randomness: 0.0 = very predictable/deterministic, 1.0 = very creative/random. 0.7 provides a good balance for natural, varied responses without being too unpredictable. !!! We will look at TEMPERATURE in another video...
            })
         });

         const data = await response.json();
         // ******************** AI BIT ********************

         // NB No memory every call is stateless
         const msg = data.choices[0].message.content; // We can drill down for the content of the first message choice to get the 'answer'.

         // const msg = data.choices[0].message
         // const msg = data.choices[0]
         // const msg = data
         console.log(msg);
         let output = "";

         if (data.error) {
            resultDiv.innerHTML = `<p style="color: red;">Error: ${data.error.message}</p>`;
         } else {
            resultDiv.innerHTML = `<strong>Response:</strong><p>${msg}</p>`;
         }
      } catch (error) {
         resultDiv.innerHTML = `<p style="color: red;">Error: ${error.message}</p>`;
      }
   });
</script>

<?php get_footer(); ?>