# Tool Use Plugin - Detailed Explanation

## Overview

This WordPress plugin demonstrates the concept of **AI Tool Use** (also known as function calling) in practical terms. Tool use allows AI models to interact with external functions, APIs, or systems to perform specific tasks that go beyond their training data.

This example is different to `COURSE-1-HTML-EXAMPLES\03-openai-tool-calling.html` where we have a loop that uses the tool call response to provide data to the AI to produce a final answer.

In this plugin we are just showing how AI can determine which tool/function our plugin needs to execute.

## What is Tool Use?

Tool use is a feature of advanced AI models that enables them to:
- Call external functions when needed
- Pass parameters to these functions
- Use the results in their responses
- Make decisions about when and how to use tools

Instead of just generating text responses, AI can now perform actions and gather real-time information.

## Plugin Structure

### Main Plugin File: `wp-tool-use-demo.php`

The plugin is contained in a single file for simplicity and clarity.

### Key Components:

#### 1. Tool Definitions

```php
// Weather Tool - No parameters needed
array(
    'type' => 'function',
    'function' => array(
        'name' => 'get_weather',
        'description' => 'Get the current weather temperature in Celsius. Returns a random temperature between -10°C and 40°C.',
        'parameters' => array(
            'type' => 'object',
            'properties' => array(),
            'required' => array()
        )
    )
)

// Math Tool - Requires two parameters
array(
    'type' => 'function',
    'function' => array(
        'name' => 'add_two_numbers',
        'description' => 'Add two numbers together and return their sum.',
        'parameters' => array(
            'type' => 'object',
            'properties' => array(
                'a' => array(
                    'type' => 'number',
                    'description' => 'First number to add'
                ),
                'b' => array(
                    'type' => 'number',
                    'description' => 'Second number to add'
                )
            ),
            'required' => array('a', 'b')
        )
    )
)
```

#### 2. Tool Functions

```php
/**
 * Weather Tool Implementation
 * Returns a fake random temperature
 */
function get_weather() {
    $temp = rand(-10, 40);
    return $temp;
}

/**
 * Math Tool Implementation
 * Simple addition function
 */
function add_two_numbers($a, $b) {
    return $a + $b;
}
```

#### 3. OpenAI Integration

The plugin sends requests to OpenAI's API with tool definitions:

```php
$body = array(
    'model' => 'gpt-4o-mini',
    'messages' => array(
        array('role' => 'system', 'content' => $system_prompt),
        array('role' => 'user', 'content' => $prompt)
    ),
    'tools' => $tools,  // Tool definitions
    'tool_choice' => 'auto', // AI decides when to use tools
    'max_tokens' => 1024
);
```

## How Tool Use Works

### 1. User Makes a Request
- User asks something like "What's the temperature?" or "Add 5 and 7"

### 2. AI Analyzes the Request
- AI determines if any available tools can help answer the question
- Weather questions → `get_weather()` tool
- Math addition → `add_two_numbers()` tool

### 3. AI Makes Tool Call
- If tool use is needed, AI responds with a `tool_calls` array
- Each tool call specifies the function name and arguments

### 4. Server Executes Functions
- WordPress server checks for requested tool calls
- Executes the corresponding PHP functions
- Returns results to the AI

### 5. AI Uses Results
- AI receives tool execution results
- Generates a natural language response incorporating the results

## Tool Calling Flow

```
User Prompt → OpenAI API → AI Analyzes → Tool Call Requested? → Execute Tool → Return Results → Final Response
```

## Example Interactions

### Weather Example:
- **User:** "What's the weather like today?"
- **AI:** Decides to call `get_weather()`
- **Tool Execution:** Returns random temperature (e.g., 23°C)
- **AI Response:** "Today the temperature is 23°C"

### Math Example:
- **User:** "Add 15 and 27 together"
- **AI:** Decides to call `add_two_numbers(15, 27)` as it is able to extract relevant information. It will extract the arguments supplied.
- **Tool Execution:** Returns 42. If we send it back to the LLM with this additonal context we can generate an AI response "15 + 27 equals 42"

## Technical Details

### OpenAI API Parameters

- **`tools`**: Array of available functions with their parameters
- **`tool_choice`**: "auto" (AI decides) or "none" (never use tools)
- **`max_tokens`**: Token limit for the response

### Tool Call Structure

When AI wants to use tools, the response includes:

```json
{
  "tool_calls": [
    {
      "function": {
        "name": "add_two_numbers",
        "arguments": "{\"a\": 5, \"b\": 7}"
      }
    }
  ]
}
```

### Error Handling

- API key validation
- Function parameter validation
- Tool execution error handling
- Network timeout handling

## Security Considerations

### WordPress Integration
- Uses WordPress nonce system for CSRF protection
- Capability checks (`manage_options`)
- Input sanitization
- Admin-only access

### API Security
- OpenAI API key stored securely in WordPress options
- Key visibility toggle in admin interface
- No key exposure in client-side code

## Educational Value

This plugin demonstrates:

1. **AI Tool Capabilities**: How AI can extend beyond text generation
2. **API Integration**: Proper OpenAI API usage with tools
3. **WordPress Plugin Development**: Security, AJAX, admin interfaces
4. **Real-world Applications**: Weather APIs, calculators, database queries, etc.

## Extending the Plugin

### Adding New Tools

To add more tools, follow this pattern:

1. **Create PHP function** with proper documentation
2. **Add tool definition** to the `$tools` array
3. **Add function case** in the `switch` statement
4. **Update system prompt** to mention the new tool

### Example: Database Query Tool

```php
// PHP Function
function query_posts_count() {
    return wp_count_posts()->publish;
}

// Tool Definition
array(
    'type' => 'function',
    'function' => array(
        'name' => 'query_posts_count',
        'description' => 'Get the count of published posts in WordPress',
        'parameters' => array(
            'type' => 'object',
            'properties' => array(),
            'required' => array()
        )
    )
)
```

## Use Cases in Real Applications

- **E-commerce**: Inventory checks, price calculations
- **Content Management**: Search, filtering, analytics
- **Business Applications**: CRM data queries, report generation
- **IoT Systems**: Device status checks, control commands
- **Educational Tools**: Problem solving, explanations
- **Customer Service**: Knowledge base searches, ticket handling

## Best Practices

1. **Clear Tool Descriptions**: Make tool purposes obvious to AI
2. **Proper Parameter Validation**: Sanitize all inputs
3. **Error Handling**: Graceful failure recovery
4. **Security**: Restrict function capabilities appropriately
5. **Documentation**: Comment all tool functions thoroughly
6. **Testing**: Test tool calls with various inputs

## Conclusion

Tool use represents a significant advancement in AI capabilities, enabling agents to perform practical tasks and provide more accurate, useful responses. This plugin provides a foundation for understanding and implementing tool use in WordPress applications.

The concepts demonstrated here can be applied to:
- Chatbots with access to databases
- AI assistants that can perform calculations
- Systems that integrate with external APIs
- Automated workflow tools
- Educational and training applications
