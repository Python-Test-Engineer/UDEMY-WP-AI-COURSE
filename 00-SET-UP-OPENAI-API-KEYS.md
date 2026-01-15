# OpenAI API Key Setup Guide

This guide walks you through setting up OpenAI API keys from scratch, including account creation and best practices.

## Step 1: Create an OpenAI Account

1. Go to [platform.openai.com](https://platform.openai.com)
2. Click **Sign Up** in the top right corner
3. Choose one of the following sign-up methods:
   - Email address
   - Google account
   - Microsoft account
   - Apple account
4. Verify your email address if you signed up with email
5. Complete your profile information

## Step 2: Set Up Billing

Before you can use the API, you need to add a payment method:

1. Navigate to **Settings** (gear icon) → **Billing**
2. Click **Add payment method**
3. Enter your credit card information
4. Set up usage limits (recommended):
   - Set a **hard limit** to prevent unexpected charges
   - Set a **soft limit** to receive notifications
5. Note: New accounts may receive free credits to get started

## Step 3: Generate Your API Key

1. Go to the [API keys page](https://platform.openai.com/api-keys)
2. Click **+ Create new secret key**
3. Give your key a descriptive name (e.g., "Development Key" or "Production App")
4. Set permissions:
   - **All**: Full access (default)
   - **Restricted**: Limited access to specific endpoints
5. Click **Create secret key**
6. **IMPORTANT**: Copy your API key immediately and store it securely
   - You won't be able to see it again
   - The key starts with `sk-`

## Best Practices

### Security
- **Never** commit API keys to version control
- **Never** share your API keys publicly
- Use environment variables or secret management services
- Rotate keys regularly
- Use restricted keys for production with minimum required permissions

### Cost Management
- Set usage limits in the billing dashboard
- Monitor your usage regularly
- Start with cheaper models (e.g., `gpt-3.5-turbo`) for testing
- Use the `max_tokens` parameter to limit response length
- Implement rate limiting in your applications

### Development Workflow
- Use separate API keys for development and production
- Name your keys descriptively
- Delete unused keys
- Review the [API documentation](https://platform.openai.com/docs) for usage guidelines

## Common Issues

### "Invalid API Key" Error
- Verify the key is copied correctly (should start with `sk-`)
- Check that the key hasn't been deleted from your account
- Ensure there are no extra spaces or quotes

### "Insufficient Quota" Error
- Add a payment method to your account
- Check your usage limits in billing settings
- Verify your account has available credits or a valid payment method

### Rate Limiting
- The API has rate limits based on your tier
- Implement exponential backoff for retries
- Check your rate limits in the [account settings](https://platform.openai.com/account/rate-limits)

## Additional Resources

- [OpenAI API Documentation](https://platform.openai.com/docs)
- [API Reference](https://platform.openai.com/docs/api-reference)
- [Pricing Information](https://openai.com/pricing)
- [Usage Guidelines](https://openai.com/policies/usage-policies)

## Need Help?

- Check the [OpenAI Community Forum](https://community.openai.com)
- Review the [Help Center](https://help.openai.com)
- Contact OpenAI support through your account dashboard

