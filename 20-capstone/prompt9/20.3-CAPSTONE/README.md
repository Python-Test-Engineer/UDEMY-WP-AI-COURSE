# 20.9 CAPSTONE - ChatGPT UI with Conversation Memory

## Overview
This plugin extends the ChatGPT-style RAG search interface with **conversation memory**, enabling the AI to remember previous exchanges and handle follow-up questions contextually.

## New Features

### 🧠 Conversation Memory
- **Client-side Storage**: Conversation history is stored in JavaScript (persists during the session)
- **Context Awareness**: The AI remembers previous questions and answers
- **Follow-up Questions**: Ask follow-up questions that reference earlier parts of the conversation
- **Token Management**: Only the last 10 messages are sent to OpenAI to manage token limits

### 🗑️ Clear Chat Button
- Red "Clear Chat" button to reset the conversation
- Clears all conversation history
- Resets the UI to the initial empty state

## How Memory Works

### Client-Side (JavaScript)
1. **conversationHistory Array**: Stores all user and assistant messages
2. **On Send**: 
   - User message is added to the array
   - History is sent with the AJAX request as JSON
   - Assistant response is added to the array
3. **On Clear**: The array is emptied and UI is reset

### Server-Side (PHP)
1. **Receives History**: The `handle_search_query()` method receives and parses conversation history
2. **Builds Messages**: The `generate_answer()` method constructs an OpenAI messages array:
   - System prompt (explains the assistant's role)
   - Last 10 conversation messages (for context)
   - Current question with RAG context
3. **Context-Aware Responses**: OpenAI uses the full conversation to answer follow-up questions

## Key Code Changes

### 1. Unique Identifiers
All names have been changed to avoid conflicts with the previous plugin:
- Plugin Name: `✅ 20.9 UDEMY CAPSTONE 09 CHATGPT UI WITH MEMORY`
- Class Name: `RAG_ChatGPT_Memory_Assistant`
- AJAX Action: `rag_chatgpt_memory_query`
- Nonce: `rag_chatgpt_memory_nonce`
- Menu Slug: `rag-chatgpt-memory`
- Text Domain: `rag-chatgpt-memory`
- Menu Position: `4.91` (vs 4.9 for previous plugin)

### 2. JavaScript Updates
```javascript
let conversationHistory = []; // Store conversation

// On successful response:
conversationHistory.push({
    role: 'user',
    content: query
});
conversationHistory.push({
    role: 'assistant',
    content: response.data.answer
});

// Send with AJAX:
conversation_history: JSON.stringify(conversationHistory)
```

### 3. PHP Updates
```php
// Receive history
$conversation_history = array();
if (isset($_POST['conversation_history'])) {
    $history_json = stripslashes($_POST['conversation_history']);
    $conversation_history = json_decode($history_json, true);
}

// Pass to generate_answer
$answer_data = $this->generate_answer($query, $context, $conversation_history);
```

### 4. OpenAI Integration
```php
// Build messages with history
$messages = array(
    array('role' => 'system', 'content' => '...'),
);

// Add last 10 messages from history
$recent_history = array_slice($conversation_history, -10);
foreach ($recent_history as $msg) {
    $messages[] = array(
        'role' => $msg['role'],
        'content' => $msg['content']
    );
}

// Add current query
$messages[] = array('role' => 'user', 'content' => "...");
```

## Example Usage

### First Question:
**User**: "What foam products do you have?"
**Assistant**: Lists foam products from RAG context

### Follow-up Question:
**User**: "Which one is best for packaging?"
**Assistant**: Refers to the previously mentioned foam products and recommends specific ones

### Another Follow-up:
**User**: "What are the dimensions of that one?"
**Assistant**: References "that one" from the conversation history

## Installation

1. Upload the plugin file to your WordPress plugins directory
2. Activate the plugin from the WordPress admin
3. Both plugins (20.8 and 20.9) can run simultaneously
4. Access from the admin menu: **20.9 MEMORY**

## Technical Details

- **Memory Storage**: Client-side JavaScript array (session-based)
- **Token Optimization**: Only last 10 messages sent to API
- **RAG Integration**: Combines conversation context with fresh RAG searches
- **Error Handling**: Validates conversation history structure before use

## Benefits

1. **Natural Conversation Flow**: Users can ask follow-up questions naturally
2. **Reduced Repetition**: No need to re-explain context in each question
3. **Better UX**: Mimics real ChatGPT conversation experience
4. **Token Efficient**: Limits history to avoid excessive API costs

## Limitations

- Memory is session-based (cleared on page refresh unless localStorage is added)
- Limited to last 10 messages to manage token costs
- No server-side persistence (could be added with user meta or custom tables)

## Future Enhancements

- Add localStorage to persist conversations across page refreshes
- Add conversation export/import functionality
- Add conversation threading/multiple chats
- Add server-side storage for long-term memory
- Add conversation summarization for very long chats
