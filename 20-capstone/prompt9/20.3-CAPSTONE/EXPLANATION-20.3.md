# ChatGPT UI with Memory - Technical Explanation

## Overview
The 20.3 CAPSTONE plugin implements a conversational AI assistant with memory capabilities, allowing users to ask follow-up questions that reference previous context. This is achieved through a conversation history system that maintains chat state across multiple interactions.

## Architecture Overview

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Frontend JS   │    │   WordPress     │    │   OpenAI API    │
│                 │    │   Backend       │    │                 │
│ conversationHistory│──►│ Process History│──►│ Chat Completion │
│   Array          │    │                 │    │   with Memory   │
│                 │    │ Build Messages  │    │                 │
│ Update History  │◄───│ Return Response │◄───│   Response      │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

## Key Components

### 1. Frontend Memory Management (JavaScript)

**Location**: Lines 221-225 in `rag_chatgpt_ui_with_memory.php`

```javascript
let conversationHistory = []; // Store conversation for follow-up questions
```

The conversation history is stored as an array of message objects:
```javascript
conversationHistory = [
    {role: 'user', content: 'What foam products do you have?'},
    {role: 'assistant', content: 'We have memory foam and latex foam mattresses...'},
    {role: 'user', content: 'Tell me more about the memory foam'}
];
```

**Clear Chat Functionality** (Lines 242-251):
```javascript
$('#chatgpt-clear-btn').on('click', function() {
    console.log('🗑️ Clear chat button clicked');
    conversationHistory = [];
    // Reset UI...
});
```

### 2. AJAX Request with Memory (JavaScript)

**Location**: Lines 265-275 in `rag_chatgpt_ui_with_memory.php`

```javascript
const ajaxData = {
    action: 'rag_chatgpt_memory_query',
    nonce: '<?php echo wp_create_nonce('rag_chatgpt_memory_nonce'); ?>',
    query: query,
    limit: 5,
    conversation_history: JSON.stringify(conversationHistory)  // Memory sent here
};
```

**History Update After Response** (Lines 286-297):
```javascript
// Add to conversation history
conversationHistory.push({
    role: 'user',
    content: query
});
conversationHistory.push({
    role: 'assistant',
    content: response.data.answer
});
console.log('💾 Updated conversation history:', conversationHistory);
```

## Backend Memory Processing (PHP)

### 3. Receiving Conversation History

**Location**: Lines 422-434 in `rag_chatgpt_ui_with_memory.php`

```php
// Get conversation history from request
$conversation_history = array();
if (isset($_POST['conversation_history'])) {
    $history_json = stripslashes($_POST['conversation_history']);
    $conversation_history = json_decode($history_json, true);
    if (!is_array($conversation_history)) {
        $conversation_history = array();
    }
}
```

### 4. AI Request to OpenAI with Memory

**Location**: Lines 520-589 in `rag_chatgpt_ui_with_memory.php`

The `generate_answer()` method has been enhanced to accept conversation history:

```php
private function generate_answer($query, $context, $conversation_history = array())
```

#### Message Building Process:

```
1. System Message (Personality/Instructions)
2. Conversation History (Previous Q&A pairs)
3. Current Query + RAG Context
```

**Code Breakdown**:

```php
// Build messages array with conversation history
$messages = array(
    array(
        'role' => 'system',
        'content' => "You are a helpful assistant that answers questions based on the provided RAG context. If the context doesn't contain enough information to answer the question, respond with 'My RAG does not have the answer.' Be conversational and friendly. You can refer to previous parts of the conversation when answering follow-up questions."
    )
);

// Add conversation history (up to last 10 messages to avoid token limits)
if (!empty($conversation_history) && is_array($conversation_history)) {
    $recent_history = array_slice($conversation_history, -10);
    foreach ($recent_history as $msg) {
        if (isset($msg['role']) && isset($msg['content'])) {
            $messages[] = array(
                'role' => $msg['role'],
                'content' => $msg['content']
            );
        }
    }
}

// Add current query with context
$messages[] = array(
    'role' => 'user',
    'content' => "RAG Context:\n{$context}\n\nQuestion: {$query}\n\nPlease provide a helpful answer based on the RAG context above. If this is a follow-up question, use our conversation history for context."
);
```

#### OpenAI API Request

**Location**: Lines 590-611 (the actual API call)

```php
$api_response = wp_remote_post('https://api.openai.com/v1/chat/completions', array(
    'headers' => array(
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer ' . $api_key
    ),
    'body' => json_encode(array(
        'model' => 'gpt-4o-mini',        // LLM Model specified here
        'messages' => $messages,         // Full conversation history
        'max_tokens' => 500,
        'temperature' => 0.7
    )),
    'timeout' => 30
));
```

## Data Flow Diagram

```
User Input
    ↓
Add to conversationHistory (JS)
    ↓
Send AJAX with conversation_history (JS)
    ↓
Receive and decode conversation_history (PHP)
    ↓
Build messages array:
├── System prompt
├── Previous conversation (from history)
└── Current query + RAG context
    ↓
POST to OpenAI Chat Completions API
    ↓
Receive AI response
    ↓
Add AI response to conversationHistory (JS)
    ↓
Display response in UI
```

## Memory Mechanism Details

### Why Memory Works

1. **Stateless HTTP**: Each AJAX request is stateless, but conversation history is explicitly passed
2. **OpenAI Context Window**: The full conversation history is sent with each request, giving OpenAI context for follow-ups
3. **Persistent Frontend State**: JavaScript maintains conversationHistory across multiple requests

### Memory Limitations

- **Token Limits**: Only last 10 messages are kept to avoid OpenAI token limits
- **Session-Based**: Memory is lost on page refresh (could be enhanced with localStorage)
- **Per-Conversation**: Each chat session has its own memory

## Key Differences from Non-Memory Version

| Aspect | Without Memory (Prompt 8) | With Memory (Prompt 9) |
|--------|---------------------------|-------------------------|
| **Messages to OpenAI** | System + Current Query | System + History + Current Query |
| **JavaScript State** | None | `conversationHistory` array |
| **AJAX Payload** | `query`, `limit` | `query`, `limit`, `conversation_history` |
| **Follow-up Questions** | No context | Full conversation context |
| **Clear Function** | N/A | Resets conversationHistory |

## Code Locations Summary

- **Frontend Memory Storage**: JavaScript lines 221-297
- **AJAX Memory Transmission**: JavaScript lines 265-275
- **Backend Memory Reception**: PHP lines 422-434
- **OpenAI Request with Memory**: PHP lines 520-611
- **Message Array Building**: PHP lines 534-571
- **Actual API Call**: PHP lines 590-611

This implementation enables true conversational AI by maintaining context across interactions, allowing natural follow-up questions that reference previous parts of the conversation.
