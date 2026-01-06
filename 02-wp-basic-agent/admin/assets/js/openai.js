
// Toggle API Key Visibility function
function toggleApiKeyVisibility() {
    console.log('Toggle function called!');
    const keyInput = document.getElementById('wp_basic_agent_api_key');
    const toggleBtn = document.getElementById('toggle_api_key_visibility');

    console.log('Key input element:', keyInput);
    console.log('Toggle button element:', toggleBtn);

    if (!keyInput) {
        console.log('Key input not found!');
        return;
    }
    if (!toggleBtn) {
        console.log('Toggle button not found!');
        return;
    }

    // Toggle the input type
    const currentType = keyInput.getAttribute('type');
    console.log('Current type:', currentType);

    const newType = currentType === 'password' ? 'text' : 'password';
    console.log('Setting new type to:', newType);

    keyInput.setAttribute('type', newType);

    // Update button text and title
    if (newType === 'password') {
        toggleBtn.textContent = 'Show';
        toggleBtn.title = 'Show API Key';
        console.log('Switched to password mode');
    } else {
        toggleBtn.textContent = 'Hide';
        toggleBtn.title = 'Hide API Key';
        console.log('Switched to text mode');
    }
}

// Simplified JavaScript for OpenAI form handling
document.addEventListener('DOMContentLoaded', function () {
    console.log('DOM loaded, setting up toggle button');

    // Handle API Key visibility toggle
    const toggleBtn = document.getElementById('toggle_api_key_visibility');
    console.log('Found toggle button:', toggleBtn);

    if (toggleBtn) {
        console.log('Adding click listener to toggle button');
        toggleBtn.addEventListener('click', toggleApiKeyVisibility);
        console.log('Click listener added successfully');
    } else {
        console.log('Toggle button not found in DOM!');
    }

    // Handle OpenAI form submission
    const form = document.getElementById('openai-form');
    if (form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const resultDiv = document.getElementById('result');
            const loading = document.getElementById('loading');
            const submitBtn = form.querySelector('button[type="submit"]');
            const prompt = document.getElementById('prompt').value;

            if (!prompt) return;

            // Show loading
            loading.style.display = 'inline-block';
            submitBtn.disabled = true;
            resultDiv.innerHTML = 'Getting response...';

            try {
                const response = await fetch(ajaxurl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'openai_proxy',
                        nonce: wpBasicAgent.nonce,
                        prompt: prompt
                    })
                });

                const data = await response.json();

                // Extract just the message text from the response
                if (data.success && data.data && data.data.choices && data.data.choices[0]) {
                    const message = data.data.choices[0].message.content;
                    resultDiv.innerHTML = '<div style="margin-top: 15px; font-size: 1.1em; line-height: 1.8; color: #333;">' + message + '</div>';
                } else {
                    resultDiv.innerHTML = '<div style="padding: 15px; background: #f8d7da; border: 2px solid #dc3545; border-radius: 8px; color: #721c24; margin-top: 15px;">Error: Unable to get response</div>';
                }
            } catch (error) {
                resultDiv.innerHTML = '<div style="padding: 15px; background: #f8d7da; border: 2px solid #dc3545; border-radius: 8px; color: #721c24;">Error: ' + error.message + '</div>';
            } finally {
                loading.style.display = 'none';
                submitBtn.disabled = false;
            }
        });
    }
});
