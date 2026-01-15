# 🎯 Prompt Engineering Essentials

## The Anatomy of an Effective Prompt

```
┌────────────────────────────────────────────────┐
│ 1. ROLE (Optional but powerful)               │
│    "You are an expert backend developer..."    │
├────────────────────────────────────────────────┤
│ 2. TASK (Required - be specific)              │
│    "Create a REST API endpoint that..."        │
├────────────────────────────────────────────────┤
│ 3. CONTEXT (Provide relevant info)            │
│    "This is for a microservice handling..."    │
├────────────────────────────────────────────────┤
│ 4. CONSTRAINTS (Set boundaries)               │
│    "Use TypeScript, follow REST conventions"   │
├────────────────────────────────────────────────┤
│ 5. OUTPUT FORMAT (Specify structure)          │
│    "Provide code with inline comments and..."  │
├────────────────────────────────────────────────┤
│ 6. EXAMPLES (Show don't just tell)            │
│    "Similar to: [example code/pattern]"        │
└────────────────────────────────────────────────┘
```

---

## Spec-Driven Prompt Template

```
Act as a [ROLE] with expertise in [DOMAIN].

I need you to [SPECIFIC TASK].

Context:
- Current tech stack: [LIST]
- Problem we're solving: [DESCRIPTION]
- Existing patterns: [RELEVANT CODE/APPROACH]

Requirements:
• [REQUIREMENT 1]
• [REQUIREMENT 2]
• [REQUIREMENT 3]

Constraints:
- [CONSTRAINT 1]
- [CONSTRAINT 2]

Expected output:
[DESCRIBE FORMAT - code, documentation, explanation, etc.]

Example of similar solution:
[OPTIONAL REFERENCE]
```

---

## Real Example: Spec-Driven Approach

### ❌ Vague Prompt
```
Make a login system
```

### ✅ Spec-Driven Prompt
```
Act as a senior backend developer with expertise in Node.js security.

I need you to create a secure authentication system for a Node.js/Express API.

Context:
- Current tech stack: Node.js, Express, PostgreSQL
- Problem we're solving: User authentication for a SaaS application
- Existing patterns: We use async/await throughout the codebase

Requirements:
• JWT-based authentication
• Password hashing with bcrypt
• Rate limiting on login attempts (5 per 15 min)
• Refresh token rotation
• Email/password login only

Constraints:
- Use TypeScript with strict mode
- Follow OWASP security guidelines
- No external auth libraries (JWT and bcrypt are OK)

Expected output:
1. Auth middleware function with type definitions
2. Login endpoint handler
3. Token generation utility
4. Example usage in a protected route
All with inline comments explaining security decisions

Example of similar solution:
Like a standard JWT implementation but with refresh token rotation
```

---

## Few-Shot Prompting

Few-shot prompting teaches by example. Show the AI 2-4 examples of what you want, then give it a new input to process.

### Structure

```
Here are examples of [TASK]:

Example 1:
Input: [INPUT_1]
Output: [OUTPUT_1]

Example 2:
Input: [INPUT_2]
Output: [OUTPUT_2]

Example 3:
Input: [INPUT_3]
Output: [OUTPUT_3]

Now do the same for:
Input: [NEW_INPUT]
Output: ?
```

### Real Example 1: Function Naming Convention

```
Convert these function names to TypeScript method signatures:

Example 1:
Input: "getUserById"
Output: getUser(id: string): Promise<User>

Example 2:
Input: "deletePost"
Output: deletePost(id: string): Promise<void>

Example 3:
Input: "listActiveUsers"
Output: listActiveUsers(limit?: number): Promise<User[]>

Now convert:
Input: "updateUserProfile"
Output: ?
```

**Result**: `updateUserProfile(id: string, data: Partial<User>): Promise<User>`

### Real Example 2: Error Message Formatting

```
Format these errors according to our API standard:

Example 1:
Input: { error: "User not found", code: 404 }
Output: {
  "status": "error",
  "code": "USER_NOT_FOUND",
  "message": "The requested user does not exist",
  "httpStatus": 404
}

Example 2:
Input: { error: "Invalid email", code: 400 }
Output: {
  "status": "error",
  "code": "INVALID_EMAIL",
  "message": "The provided email address is not valid",
  "httpStatus": 400
}

Now format:
Input: { error: "Token expired", code: 401 }
Output: ?
```

### Real Example 3: Test Case Generation

```
Generate test cases following this pattern:

Example 1:
Function: validateEmail(email: string): boolean
Test cases:
- Valid email: "user@example.com" → true
- Missing @: "userexample.com" → false
- Missing domain: "user@" → false
- Empty string: "" → false

Example 2:
Function: calculateDiscount(price: number, percent: number): number
Test cases:
- Standard discount: (100, 10) → 90
- No discount: (100, 0) → 100
- Full discount: (100, 100) → 0
- Negative price: (-100, 10) → throws Error

Now generate test cases for:
Function: formatPhoneNumber(phone: string): string
Test cases: ?
```

---

## Combining Both Approaches

You can use spec-driven structure WITH few-shot examples for maximum effectiveness:

```
Act as a PHP developer specializing in data processing.

I need you to create data validation functions.

Context:
- Building a data pipeline for CSV imports
- Need consistent validation across multiple fields
- Using PHP 8.2 with strict types

Requirements:
• Return array with ['valid' => bool, 'error' => string|null]
• Handle null/empty values gracefully
• Provide clear error messages

Here are examples of the pattern to follow:

function validateEmail(?string $email): array {
    if (empty($email)) {
        return ['valid' => false, 'error' => 'Email is required'];
    }
    if (!str_contains($email, '@')) {
        return ['valid' => false, 'error' => 'Email must contain @'];
    }
    return ['valid' => true, 'error' => null];
}

function validateAge(?int $age): array {
    if ($age === null) {
        return ['valid' => false, 'error' => 'Age is required'];
    }
    if ($age < 0 || $age > 150) {
        return ['valid' => false, 'error' => 'Age must be between 0 and 150'];
    }
    return ['valid' => true, 'error' => null];
}

Now create a similar validation function for:
- Phone numbers (must be 10 digits, optional country code)
```

---

## Quick Reference

### When to Use Spec-Driven

Use when you need:
- Complex implementations
- Multiple requirements
- Specific constraints
- Particular output format

### When to Use Few-Shot

Use when you need:
- Pattern replication
- Consistent formatting
- Style matching
- Quick transformations

### Best Results: Use Both

```
Spec-Driven Structure
        +
Few-Shot Examples
        =
Precise, Consistent Output
```

---

## Key Takeaways

1. **Spec-Driven** = Define the complete specification upfront
   - Role, Task, Context, Requirements, Constraints, Output Format

2. **Few-Shot** = Show examples, get similar output
   - 2-4 examples minimum
   - Clear input/output pairs
   - Consistent pattern

3. **Combine them** for complex tasks requiring both precision and pattern matching

4. **Always be specific** - Vague prompts get vague results