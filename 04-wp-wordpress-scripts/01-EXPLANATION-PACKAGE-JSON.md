# Minimum Requirements for package.json

The absolute minimum valid `package.json` requires just two fields:

```json
{
  "name": "my-project",
  "version": "1.0.0"
}
```

However, a practical minimum usually includes:

```json
{
  "name": "my-project",
  "version": "1.0.0",
  "description": "A brief description",
  "main": "index.js",
  "scripts": {
    "start": "node index.js"
  },
  "author": "Your Name",
  "license": "MIT"
}
```