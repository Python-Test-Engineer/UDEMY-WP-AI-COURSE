## What is .vscode/settings.json?

It's a configuration file that lets you customize VS Code settings for a specific workspace (project folder). Settings here override your user settings just for that project.

## Creating the file

1. Create a `.vscode` folder in your project root (if it doesn't exist)
2. Create a `settings.json` file inside it

Or use VS Code: `Ctrl+Shift+P` (or `Cmd+Shift+P` on Mac) → "Preferences: Open Workspace Settings (JSON)"

## Common Python settings

Here's a practical example:

```json
{
    "python.defaultInterpreterPath": "${workspaceFolder}/.venv/bin/python",
    "python.linting.enabled": true,
    "python.linting.pylintEnabled": true,
    "python.formatting.provider": "black",
    "python.linting.lintOnSave": true,
    "editor.formatOnSave": true,
    "editor.rulers": [88],
    "files.exclude": {
        "**/__pycache__": true,
        "**/*.pyc": true
    }
}
```

## Useful Python-specific settings

- **`python.defaultInterpreterPath`**: Points to your virtual environment
- **`python.linting.pylintEnabled`** or **`python.linting.flake8Enabled`**: Enable linters
- **`python.formatting.provider`**: Set formatter (black, autopep8, yapf)
- **`python.testing.pytestEnabled`**: Enable pytest
- **`python.analysis.typeCheckingMode`**: Set to "basic" or "strict" for type checking

## How changes take effect

Most settings apply immediately. Some (like interpreter path) may require reloading the window: `Ctrl+Shift+P` → "Developer: Reload Window"

This approach keeps your team's Python projects consistent and makes onboarding easier since everyone shares the same workspace configuration.