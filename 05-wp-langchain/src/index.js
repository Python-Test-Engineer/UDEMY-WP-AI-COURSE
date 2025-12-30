import { initOpenAI, createPromptTemplate, createContentChain, generateContent } from './langchain-utils';
import './style.css';
document.addEventListener('DOMContentLoaded', () => {
    const demoApp = document.getElementById('langchain-demo-app');

    if (!demoApp) {
        return; // Not on the demo page
    }

    // Create the demo UI
    demoApp.innerHTML = `
        <div id="demoAPP" >
            <h2>LangChain.js Content Generator Demo</h2>

            <div class="demo-form-group">
                <label for="api-key">OpenAI API Key:</label>
                <input type="password" id="api-key" class="demo-input" placeholder="sk-...">
            </div>

            <div class="demo-form-group">
                <label for="topic">Topic:</label>
                <input type="text" id="topic" class="demo-input" value="WordPress development">
            </div>

            <div class="demo-form-group">
                <label for="length">Length:</label>
                <br>
                <select id="length" class="demo-select">
                    <option value="short paragraph">Short paragraph</option>
                    <option value="medium article">Medium article</option>
                    <option value="detailed explanation">Detailed explanation</option>
                </select>
            </div>

            <div class="demo-form-group">
                <label for="style">Style:</label>
                <br>
                <select id="style" class="demo-select">
                    <option value="professional">Professional</option>
                    <option value="conversational">Conversational</option>
                    <option value="technical">Technical</option>
                </select>
            </div>

            <button id="generate-btn" class="demo-button">
                Generate Content
            </button>

            <div id="result" class="demo-result"></div>
        </div>
    `;

    const apiKeyInput = document.getElementById('api-key');
    const topicInput = document.getElementById('topic');
    const lengthSelect = document.getElementById('length');
    const styleSelect = document.getElementById('style');
    const generateBtn = document.getElementById('generate-btn');
    const resultDiv = document.getElementById('result');

    // Initialize LangChain components (but we'll do this on button click to use the provided API key)
    let llm, prompt, chain;

    generateBtn.addEventListener('click', async () => {
        const apiKey = apiKeyInput.value.trim();
        const topic = topicInput.value.trim();

        if (!apiKey) {
            resultDiv.textContent = 'Please provide an OpenAI API key.';
            return;
        }

        if (!topic) {
            resultDiv.textContent = 'Please provide a topic.';
            return;
        }

        generateBtn.disabled = true;
        generateBtn.textContent = 'Generating...';
        resultDiv.textContent = 'Generating content...';

        try {
            // Initialize components with the provided API key
            resultDiv.textContent = 'Generating response...';
            llm = initOpenAI(apiKey);
            prompt = createPromptTemplate();
            chain = createContentChain(llm, prompt);

            const inputs = {
                topic: topic,
                length: lengthSelect.value,
                style: styleSelect.value,
            };
            // ***************************************************

            const generatedContent = await generateContent(chain, inputs);

            // ***************************************************

            resultDiv.textContent = generatedContent;
        } catch (error) {
            resultDiv.textContent = `Error: ${error.message}`;
        } finally {
            generateBtn.disabled = false;
            generateBtn.textContent = 'Generate Content';
        }
    });
});
