/**
 * LangGraph-Style WordPress Integration
 * 
 * This is a simple demonstration of a graph-like workflow in a WordPress plugin.
 * 
 * Uses OpenAI's GPT-4o-mini model.
 * 
 * NOTE: We use a simple sequential approach instead of LangGraph's StateGraph
 * because StateGraph requires Node.js modules (like async_hooks) that don't
 * work in browser environments. This approach achieves the same result!
 */

import { ChatOpenAI } from '@langchain/openai';
import './style.css';

// Enable detailed console logging for debugging
console.log('🚀 LangGraph-style WordPress plugin loaded');

/**
 * Initialize the application when DOM is ready
 */
document.addEventListener('DOMContentLoaded', () => {
    console.log('📄 DOM Content Loaded - Initializing LangGraph-style app');

    // Find the container where we'll inject our app
    const appContainer = document.getElementById('langgraph-app');

    if (!appContainer) {
        console.error('❌ Could not find #langgraph-app container');
        return;
    }

    console.log('✅ Found app container, rendering UI');
    renderApp(appContainer);
});

/**
 * Render the main application UI
 */
function renderApp(container) {
    console.log('🎨 Rendering application UI');

    container.innerHTML = `
        <div class="langgraph-demo">
            <h2>LangGraph-Style Multi-Step Processing with Tools Demo</h2>

            <div class="tool-info" style="background: #e8f4fd; border: 1px solid #1e88e5; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
                <h3 style="margin-top: 0; color: #1e88e5;">🛠️ Available Tools:</h3>
                <ul style="margin: 0; padding-left: 20px;">
                    <li><strong>Get statistics for categories and tags</strong> - Lists all categories and tags with their counts</li>
                    <li><strong>Get a random post and translate to french</strong> - Gets a random post and translates it to French</li>
                    <li><strong>Deep research tool</strong> - Summarizes all posts for a particular category</li>
                </ul>
                <p style="margin: 10px 0 0 0; font-size: 14px; color: #666;">
                    Try queries like: "Show me category statistics", "Give me a random post in French", or "Summarize all posts in the 'news' category"
                </p>
            </div>

            <div class="input-section">
                <label for="user-input">Enter your query</label>
                <textarea style="font-size:1.5rem;"
                    id="user-input"
                    rows="4"
                    placeholder="Type something interesting here... (e.g., 'Tell me about WordPress' or 'Show me category statistics')"
                >Show me category statistics</textarea>

                <button id="process-btn" class="process-button">
                    Process with Graph Workflow
                </button>
            </div>

            <div id="status" class="status-message"></div>

            <div id="tool-result" class="tool-result" style="display: none;">
                <!-- Tool results will be displayed here -->
            </div>

            <div id="graph-steps" class="graph-steps">
                <!-- Steps will be displayed here -->
            </div>

            <div id="final-result" class="final-result">
                <!-- Final result will be displayed here -->
            </div>
        </div>
    `;

    // Attach event listener to the button
    const processBtn = document.getElementById('process-btn');
    processBtn.addEventListener('click', handleProcessClick);

    console.log('✅ UI rendered and event listeners attached');
}

/**
 * Handle the "Process" button click
 */
async function handleProcessClick() {
    console.log('🖱️ Process button clicked');

    const userInput = document.getElementById('user-input').value.trim();
    const statusDiv = document.getElementById('status');
    const stepsDiv = document.getElementById('graph-steps');
    const resultDiv = document.getElementById('final-result');
    const processBtn = document.getElementById('process-btn');

    if (!userInput) {
        console.warn('⚠️ No input provided');
        statusDiv.innerHTML = '<p class="error">Please enter some text to process.</p>';
        return;
    }

    console.log('📝 User input:', userInput);

    // Disable button and clear previous results
    processBtn.disabled = true;
    processBtn.textContent = 'Processing...';
    statusDiv.innerHTML = '<p class="info">⏳ Fetching API key from WordPress database...</p>';
    stepsDiv.innerHTML = '';
    resultDiv.innerHTML = '';

    try {
        // Step 1: Get API key from WordPress database (via AJAX)
        console.log('🔑 Fetching API key from WordPress...');
        const apiKey = await getApiKey();
        console.log('✅ API key retrieved successfully');

        statusDiv.innerHTML = '<p class="info">⏳ Running graph workflow...</p>';

        // Step 2: Run the graph-style workflow
        console.log('🔄 Starting graph workflow');
        await runGraphWorkflow(userInput, apiKey, stepsDiv);

        // Success!
        console.log('✅ Workflow completed successfully');
        statusDiv.innerHTML = '<p class="success">✅ Processing complete!</p>';

    } catch (error) {
        console.error('❌ Error during processing:', error);
        statusDiv.innerHTML = `<p class="error">❌ Error: ${error.message}</p>`;
    } finally {
        // Re-enable button
        processBtn.disabled = false;
        processBtn.textContent = 'Process with Graph Workflow';
    }
}

/**
 * Get API key from WordPress database via AJAX
 * This avoids using dotenv - the key is stored in WP options
 */
async function getApiKey() {
    console.log('📡 Making AJAX request to get API key');

    const response = await fetch(ajaxurl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'wplg_get_api_key'
        })
    });

    console.log('📡 AJAX response received:', response.status);

    const data = await response.json();
    console.log('📦 AJAX data:', data);

    if (!data.success) {
        throw new Error(data.data.message || 'Failed to get API key');
    }

    return data.data.api_key;
}

/**
 * Check if the user input requires using a tool
 */

//region TOOLS
async function checkForToolUsage(userInput, apiKey) {
    console.log('🔍 Checking if tools are needed for input:', userInput);

    // Define available tools
    const tools = [
        {
            name: 'get_categories_tags_stats',
            description: 'Get statistics for categories and tags - lists number and name of all categories and tags. Anytime data or statistics are asked for, use this tool.'
        },
        {
            name: 'get_random_post_french',
            description: 'Get a random post and translate to french - gets a random post and returns it translated to French - useful for samples or examples needed.'
        },
        {
            name: 'deep_research_category',
            description: 'Deep research tool that summarizes all posts for a particular category - provides comprehensive insights and analysis of all posts in a category. Finds trends, common topics, and key points. Use the closest matching category name from user input and ensure a response is provided with the closest matching category. It does not need to be an exact lexical match - a close semantic match is fine.'
        }
    ];

    // Create a prompt to determine if a tool should be used
    const toolCheckPrompt = `Given the user's input, determine if any of the available tools should be used.
//endregion TOOLS
Available tools:
${tools.map(tool => `- ${tool.name}: ${tool.description}`).join('\n')}

User input: "${userInput}"

If a tool should be used, respond with ONLY the tool name (e.g., "get_categories_tags_stats").
If no tool is needed, respond with "none".

Look for keywords and intent that match the tool descriptions.`;
    //region AI BIT
    try {
        const response = await fetch('https://api.openai.com/v1/chat/completions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${apiKey}`
            },
            body: JSON.stringify({
                model: 'gpt-4o-mini',
                messages: [{ role: 'user', content: toolCheckPrompt }],
                max_tokens: 50,
                temperature: 0.1
            })
        });

        const data = await response.json();

        //endregion AI BIT
        const toolDecision = data.choices[0].message.content.trim().toLowerCase();

        console.log('🔍 Tool decision:', toolDecision);

        // Check if it's one of our tools
        const matchedTool = tools.find(tool => toolDecision.includes(tool.name.toLowerCase()));

        if (matchedTool) {
            console.log('✅ Tool matched:', matchedTool.name);
            return matchedTool.name;
        }

        console.log('❌ No tool needed, proceeding with normal workflow');
        return null;

    } catch (error) {
        console.error('❌ Error checking for tool usage:', error);
        return null; // Fall back to normal workflow
    }
}

/**
 * Execute a tool and return the result
 */
async function executeTool(toolName, userInput, apiKey) {
    console.log('🔧 Executing tool:', toolName);

    const toolResultDiv = document.getElementById('tool-result');
    toolResultDiv.style.display = 'block';

    try {
        let result;

        if (toolName === 'get_categories_tags_stats') {
            toolResultDiv.innerHTML = '<p>⏳ Getting categories and tags statistics...</p>';

            const response = await fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'wplg_get_categories_tags_stats'
                })
            });

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.data.message || 'Failed to get statistics');
            }

            result = data.data;

            // Format the result
            let formattedResult = '<h3>📊 Categories and Tags Statistics</h3>';

            formattedResult += '<h4>Categories:</h4><ul>';
            result.categories.forEach(cat => {
                formattedResult += `<li><strong>${cat.name}</strong> (${cat.count} posts)</li>`;
            });
            formattedResult += '</ul>';

            formattedResult += '<h4>Tags:</h4><ul>';
            result.tags.forEach(tag => {
                formattedResult += `<li><strong>${tag.name}</strong> (${tag.count} posts)</li>`;
            });
            formattedResult += '</ul>';

            toolResultDiv.innerHTML = formattedResult;

        } else if (toolName === 'get_random_post_french') {
            toolResultDiv.innerHTML = '<p>⏳ Getting random post and translating to French...</p>';

            const response = await fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'wplg_get_random_post_french'
                })
            });

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.data.message || 'Failed to get random post');
            }

            result = data.data;

            // Format the result
            let formattedResult = '<h3>🇫🇷 Random Post Translated to French</h3>';
            formattedResult += '<div class="translation-result">';
            formattedResult += '<h4>Translated Content:</h4>';
            formattedResult += `<div class="translated-text">${result.translated_text.replace(/\n/g, '<br>')}</div>`;
            formattedResult += '</div>';

            toolResultDiv.innerHTML = formattedResult;

        } else if (toolName === 'deep_research_category') {
            toolResultDiv.innerHTML = '<p>⏳ Performing deep research on category posts...</p>';

            // Extract category name from user input using AI
            const categoryName = await extractCategoryName(userInput, apiKey);

            if (!categoryName) {
                throw new Error('Could not identify a category name in your request. Please specify a category like "news", "blog", etc.');
            }

            const response = await fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'wplg_deep_research_category',
                    category_name: categoryName
                })
            });

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.data.message || 'Failed to perform deep research');
            }

            result = data.data;

            // Format the result
            let formattedResult = `<h3>🔬 Deep Research: Category "${categoryName}"</h3>`;

            if (result.posts.length === 0) {
                formattedResult += '<p>No posts found in this category.</p>';
            } else {
                formattedResult += `<p><strong>Found ${result.posts.length} posts in this category</strong></p>`;

                // Create a summary using AI
                const summary = await generateCategorySummary(result.posts, categoryName, apiKey);
                formattedResult += '<h4>📊 Summary:</h4>';
                formattedResult += `<div class="research-summary">${summary.replace(/\n/g, '<br>')}</div>`;

                formattedResult += '<h4>📝 Post Titles:</h4><ul>';
                result.posts.forEach(post => {
                    formattedResult += `<li><strong>${post.title}</strong> (${new Date(post.date).toLocaleDateString()})</li>`;
                });
                formattedResult += '</ul>';
            }

            toolResultDiv.innerHTML = formattedResult;
        }

        console.log('✅ Tool executed successfully:', toolName);
        return result;

    } catch (error) {
        console.error('❌ Tool execution failed:', error);
        toolResultDiv.innerHTML = `<p class="error">❌ Error executing tool: ${error.message}</p>`;
        throw error;
    }
}

/**
 * Run a graph-style workflow
 *
 * This simulates a LangGraph workflow with sequential nodes.
 * Each "node" is a function that processes the state and returns updated state.
 * This achieves the same result as LangGraph's StateGraph but works in browsers!
 */
//region GRAPH
async function runGraphWorkflow(userInput, apiKey, stepsDiv) {
    console.log('🏗️ Building graph-style workflow');

    // First, check if a tool should be used
    const requiredTool = await checkForToolUsage(userInput, apiKey);

    if (requiredTool) {
        console.log('🛠️ Tool required, executing:', requiredTool);
        await executeTool(requiredTool, userInput, apiKey);
        return { tool_used: requiredTool };
    }

    // No tool needed, proceed with normal graph workflow
    console.log('🔄 No tool needed, proceeding with normal graph workflow');

    // Initialize OpenAI with gpt-4o-mini model
    console.log('🤖 Initializing ChatOpenAI with gpt-4o-mini');
    const llm = new ChatOpenAI({
        modelName: 'gpt-4o-mini',
        temperature: 0.7,
        openAIApiKey: apiKey,
    });
    console.log('✅ ChatOpenAI initialized');

    // Create initial state (like LangGraph's state)
    console.log('📊 Creating initial workflow state');
    let state = {
        input: userInput,
        analysis: '',
        processed: '',
        final_response: '',
        step_count: 0
    };
    console.log('   Initial state:', state);

    /**
     * NODE 1: ANALYZER
     * This is like a node in LangGraph - it receives state and returns updated state
     */
    console.log('📍 NODE 1: ANALYZER - Starting');
    displayStep(stepsDiv, 1, 'Analyzer', 'Analyzing your input...', 'processing');
    //endregion GRAPH
    //region DEEP
    const analyzerPrompt = `Analyze the following text and identify:
1. Main topic/theme
2. Key points or questions
3. Tone (formal, casual, technical, etc.)

Text to analyze: "${state.input}"

Provide a brief, structured analysis.`;

    console.log('   📤 Sending prompt to OpenAI (Analyzer)');
    const analyzerResponse = await llm.invoke(analyzerPrompt);
    state.analysis = analyzerResponse.content;
    state.step_count++;

    console.log('   📥 Analysis complete:', state.analysis);
    displayStep(stepsDiv, 1, 'Analyzer', state.analysis, 'complete');

    /**
     * NODE 2: PROCESSOR
     * Takes the state from Analyzer and processes it further
     */
    console.log('📍 NODE 2: PROCESSOR - Starting');
    displayStep(stepsDiv, 2, 'Processor', 'Processing the analysis...', 'processing');

    const processorPrompt = `Based on this analysis of user input:

Analysis: ${state.analysis}

Original input: "${state.input}"

Create a processing plan that outlines:
1. What information should be included in the response
2. The best format/structure for the response
3. Key points to emphasize

Keep it brief and actionable.`;

    console.log('   📤 Sending prompt to OpenAI (Processor)');
    const processorResponse = await llm.invoke(processorPrompt);
    state.processed = processorResponse.content;
    state.step_count++;

    console.log('   📥 Processing complete:', state.processed);
    displayStep(stepsDiv, 2, 'Processor', state.processed, 'complete');

    /**
     * NODE 3: RESPONDER
     * Final node that generates the end result
     */
    console.log('📍 NODE 3: RESPONDER - Starting');
    displayStep(stepsDiv, 3, 'Responder', 'Generating final response...', 'processing');

    const responderPrompt = `Based on the analysis and processing plan below, generate a helpful, comprehensive response to the user's original input.

Original input: "${state.input}"

Analysis: ${state.analysis}

Processing plan: ${state.processed}

Generate a well-structured, informative response that addresses the user's input directly.`;

    console.log('   📤 Sending prompt to OpenAI (Responder)');
    const responderResponse = await llm.invoke(responderPrompt);
    state.final_response = responderResponse.content;
    state.step_count++;

    console.log('   📥 Final response generated:', state.final_response);
    displayStep(stepsDiv, 3, 'Responder', state.final_response, 'complete');

    // Display the final result in a special section
    const resultDiv = document.getElementById('final-result');
    resultDiv.innerHTML = `
        <h3>📋 Final Result</h3>
        <div class="final-content">${state.final_response}</div>
    `;

    console.log('🎉 Graph workflow complete!');
    console.log('📊 Final state:', state);
    console.log('   Flow executed: INPUT -> Analyzer -> Processor -> Responder -> END');

    return state;
}

/**
 * Extract category name from user input using AI
 */
async function extractCategoryName(userInput, apiKey) {
    console.log('🔍 Extracting category name from input:', userInput);

    const extractionPrompt = `Extract the category name from the user's request. Look for words that could be category names.

User input: "${userInput}"

Common category names include: news, blog, tutorials, reviews, announcements, updates, etc.

Respond with ONLY the category name (lowercase), or "none" if no category is found.

Examples:
- "Summarize posts in the news category" → "news"
- "Show me blog posts" → "blog"
- "Research the tutorials section" → "tutorials"`;

    try {
        const response = await fetch('https://api.openai.com/v1/chat/completions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${apiKey}`
            },
            body: JSON.stringify({
                model: 'gpt-4o-mini',
                messages: [{ role: 'user', content: extractionPrompt }],
                max_tokens: 20,
                temperature: 0.1
            })
        });

        const data = await response.json();
        const categoryName = data.choices[0].message.content.trim().toLowerCase();

        console.log('🔍 Extracted category name:', categoryName);

        return categoryName === 'none' ? null : categoryName;

    } catch (error) {
        console.error('❌ Error extracting category name:', error);
        return null;
    }
}

/**
 * Generate a summary of all posts in a category using AI
 */
async function generateCategorySummary(posts, categoryName, apiKey) {
    console.log('📝 Generating category summary for:', categoryName, 'with', posts.length, 'posts');

    if (posts.length === 0) {
        return 'No posts found in this category.';
    }

    // Create a summary of post titles and content
    const postSummaries = posts.map(post =>
        `- ${post.title} (${new Date(post.date).toLocaleDateString()}): ${post.excerpt || post.content?.substring(0, 100) + '...' || 'No excerpt available'}`
    ).join('\n');

    const summaryPrompt = `Analyze and summarize all posts in the "${categoryName}" category.

Here are the posts:
${postSummaries}

Provide a comprehensive summary that includes:
1. Overall theme/trend of the category
2. Key topics covered
3. Most common themes or patterns
4. Any notable insights or conclusions
5. Publication timeline information

Keep the summary informative but concise.`;

    try {
        const response = await fetch('https://api.openai.com/v1/chat/completions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${apiKey}`
            },
            body: JSON.stringify({
                model: 'gpt-4o-mini',
                messages: [{ role: 'user', content: summaryPrompt }],
                max_tokens: 500,
                temperature: 0.3
            })
        });

        const data = await response.json();
        const summary = data.choices[0].message.content.trim();

        console.log('📝 Category summary generated');
        return summary;

    } catch (error) {
        console.error('❌ Error generating category summary:', error);
        return `Found ${posts.length} posts in the "${categoryName}" category, but could not generate a detailed summary.`;
    }
}

/**
 * Display a step in the UI
 * Shows the progress through each node in the graph
 */
function displayStep(container, stepNumber, nodeName, content, status) {
    console.log(`📺 Displaying step ${stepNumber} (${nodeName}): ${status}`);

    // Find or create the step div
    let stepDiv = document.getElementById(`step-${stepNumber}`);

    if (!stepDiv) {
        stepDiv = document.createElement('div');
        stepDiv.id = `step-${stepNumber}`;
        stepDiv.className = 'graph-step';
        container.appendChild(stepDiv);
    }

    // Update the step content
    const statusIcon = status === 'complete' ? '✅' : '⏳';
    const statusClass = status === 'complete' ? 'complete' : 'processing';

    stepDiv.className = `graph-step ${statusClass}`;
    stepDiv.innerHTML = `
        <div class="step-header">
            <span class="step-icon">${statusIcon}</span>
            <h3>Step ${stepNumber}: ${nodeName}</h3>
        </div>
        <div class="step-content">${content}</div>
    `;
}

