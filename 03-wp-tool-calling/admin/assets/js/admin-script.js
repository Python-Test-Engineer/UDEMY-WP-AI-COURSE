// Admin JavaScript for Tool Calling Plugin

// Tool implementation
function wptc_get_weather() {
    return "The current weather is 25°C, sunny with a light breeze.";
}

// Tool implementation
function wptc_get_sum(a, b) {
    return `The sum of ${a} and ${b} is ${a + b}.`;
}

// Process tool calls
function wptc_processToolCall(toolName, toolInput) {
    if (toolName === 'get_weather') {
        return wptc_get_weather();
    } else if (toolName === 'get_sum') {
        return wptc_get_sum(toolInput.a, toolInput.b);
    }
    return "Unknown tool";
}

// Display message in result div
function wptc_displayMessage(type, content) {
    const resultDiv = document.getElementById('wptc-result');
    const msgDiv = document.createElement('div');
    msgDiv.className = `wptc-message wptc-${type}`;
    msgDiv.innerHTML = content;
    resultDiv.appendChild(msgDiv);
    resultDiv.scrollTop = resultDiv.scrollHeight;
}

// Main function to handle tool calling loop
async function wptc_handleToolCalling(apiKey, userPrompt) {
    const resultDiv = document.getElementById('wptc-result');
    resultDiv.innerHTML = '';

    const messages = [
        {
            role: 'user',
            content: userPrompt
        }
    ];

    // We must define tools in a certain way as the model is trained to expect tools in this format.
    const tools = [
        {
            type: 'function',
            function: {
                name: 'get_weather',
                description: 'Get the current weather information',
                parameters: {
                    type: 'object',
                    properties: {},
                    required: []
                }
            }
        },
        {
            type: 'function',
            function: {
                name: 'get_sum',
                description: 'Calculate the sum of two numbers',
                parameters: {
                    type: 'object',
                    properties: {
                        a: {
                            type: 'number',
                            description: 'First number'
                        },
                        b: {
                            type: 'number',
                            description: 'Second number'
                        }
                    },
                    required: ['a', 'b']
                }
            }
        }
    ];

    let continueLoop = true;
    let iterations = 0;
    const maxIterations = 10;

    while (continueLoop && iterations < maxIterations) {
        iterations++;
        wptc_displayMessage('loading', '⏳ Calling OpenAI API...');

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
                    tools: tools,
                    tool_choice: 'auto'
                })
            });

            const data = await response.json();

            if (data.error) {
                wptc_displayMessage('error', `❌ Error: ${data.error.message}`);
                continueLoop = false;
                console.log('API Error:', data.error);
                break;
            }

            const choice = data.choices[0];
            const assistantMessage = choice.message;

            // Add assistant message to conversation
            messages.push(assistantMessage);

            // Display assistant's response if it has content
            if (assistantMessage.content) {
                wptc_displayMessage('assistant-message', `💬 Assistant: ${assistantMessage.content}`);
            }

            // Check if there are tool calls
            if (assistantMessage.tool_calls && assistantMessage.tool_calls.length > 0) {
                for (const toolCall of assistantMessage.tool_calls) {
                    const toolName = toolCall.function.name;
                    const toolInput = JSON.parse(toolCall.function.arguments);
                    console.log('🔧 Tool Call:', toolName, toolInput);

                    // Display tool call
                    wptc_displayMessage('tool-call',
                        `🔧 <span class="wptc-tool-name">${toolName}</span><div class="wptc-tool-args">Args: ${JSON.stringify(toolInput)}</div>`
                    );

                    // Execute tool
                    const toolResult = wptc_processToolCall(toolName, toolInput);
                    console.log('✅ Tool Result:', toolResult);

                    // Display tool result
                    wptc_displayMessage('tool-result',
                        `✅ <span class="wptc-result-label">${toolName} result:</span> ${toolResult}`
                    );

                    // Add tool result to messages
                    messages.push({
                        role: 'tool',
                        tool_use_id: toolCall.id,
                        tool_call_id: toolCall.id,
                        content: toolResult
                    });
                }
            } else {
                // No more tool calls, exit loop
                console.log('❌ No tool calls, finishing.');
                continueLoop = false;
            }

            if (choice.finish_reason === 'end_turn' || choice.finish_reason === 'stop') {
                console.log('🔚 Finish reason:', choice.finish_reason);
                continueLoop = false;
            }

        } catch (error) {
            wptc_displayMessage('error', `❌ Error: ${error.message}`);
            continueLoop = false;
        }
    }
}

// Event listener
document.addEventListener('DOMContentLoaded', function() {
    const sendBtn = document.getElementById('wptc-sendBtn');
    const promptInput = document.getElementById('wptc-prompt');

    if (sendBtn) {
        sendBtn.addEventListener('click', async () => {
            const apiKey = document.getElementById('wptc-apiKey').value;
            const prompt = document.getElementById('wptc-prompt').value;

            if (!apiKey) {
                alert('Please enter your OpenAI API key');
                return;
            }

            if (!prompt) {
                alert('Please enter a prompt');
                return;
            }

            sendBtn.disabled = true;
            await wptc_handleToolCalling(apiKey, prompt);
            sendBtn.disabled = false;
        });
    }

    // Allow Enter key to submit
    if (promptInput) {
        promptInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                sendBtn.click();
            }
        });
    }
});
