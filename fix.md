## 🔴 API KEY LEAK FOUND!

**The leak is in: `UDEMY-WP-AI.wpress`**

This is a WordPress backup/migration file that contains a **complete database export** with multiple OpenAI API keys hardcoded in the SQL dump. The file is currently tracked in git and exists in your commit history.

### Leaked Keys Found:
The file contains at least **5 different OpenAI API keys** in database INSERT statements:
- `bif_basic_agent_api_key`
- `bif_rag_chatbot_api_key` 
- `ai_post_assistant_api_key` (full key exposed)
- `ai_chat_settings` (full key exposed)
- `openai_agents_api_key` (full key exposed)

### Why This Happens:
When you export a WordPress site using All-in-One WP Migration or similar tools, it creates a `.wpress` file containing the entire database - including any API keys stored in `wp_options` table. When this file is committed to git and pushed to GitHub, the keys are exposed publicly.

### Critical Steps to Fix:

**1. Immediately remove the file from git tracking:**
```powershell
git rm --cached UDEMY-WP-AI.wpress
git commit -m "Remove wpress file with exposed API keys"
```

**2. Add to .gitignore:**
```powershell
echo "*.wpress" >> .gitignore
git add .gitignore
git commit -m "Add wpress files to gitignore"
```

**3. Remove from git history (REQUIRED - this is the critical step):**
```powershell
git filter-branch --force --index-filter "git rm --cached --ignore-unmatch UDEMY-WP-AI.wpress" --prune-empty --tag-name-filter cat -- --all
```

**4. Force push to remote:**
```powershell
git push origin --force --all
git push origin --force --tags
```

**5. Regenerate ALL exposed API keys** on OpenAI's platform immediately.

The file exists in commits going back to at least January 2026 (commits 4b90470, d611607, 92f7e7e, and others), so the keys have been exposed in your git history multiple times.