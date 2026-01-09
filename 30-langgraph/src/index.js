/**
 * LangGraph-Style WordPress Integration
 * 
 * This is a simple demonstration of a graph-like workflow in a WordPress plugin.
 * It creates a sequential workflow with 3 steps that mimics LangGraph concepts:
 * 1. Analyzer - Analyzes user input
 * 2. Processor - Processes the analysis
 * 3. Responder - Generates final response
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
            <h2>LangGraph-Style Multi-Step Processing Demo</h2>
            
            <div class="input-section">
                <label for="user-input">Enter your text to process:</label>
                <textarea 
                    id="user-input" 
                    rows="4" 
                    placeholder="Type something interesting here... (e.g., 'Tell me about WordPress')"
                >Tell me about WordPress</textarea>
                
                <button id="process-btn" class="process-button">
                    Process with Graph Workflow
                </button>
            </div>
            
            <div id="status" class="status-message"></div>
            
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
 * Run a graph-style workflow
 * 
 * This simulates a LangGraph workflow with sequential nodes.
 * Each "node" is a function that processes the state and returns updated state.
 * This achieves the same result as LangGraph's StateGraph but works in browsers!
 */
async function runGraphWorkflow(userInput, apiKey, stepsDiv) {
    console.log('🏗️ Building graph-style workflow');
    
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
    
