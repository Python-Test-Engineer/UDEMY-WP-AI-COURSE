// Simplified JavaScript for OpenAI form handling
document.addEventListener('DOMContentLoaded', function () {
    // Handle OpenAI form submission
    const form = document.getElementById('openai-form');
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const resultDiv = document.getElementById('result');
            const loading = document.getElementById('loading');
            const submitBtn = form.querySelector('button[type="submit"]');
            const prompt = document.getElementById('prompt').value;
            
            if (!prompt) return;
            
            // Show loading
            loading.style.display = 'inline-block';
            submitBtn.disabled = true;
            resultDiv.innerHTML = '';
            
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
    
    // Toggle API Key Visibility
    const toggleBtn = document.getElementById('toggle_key_visibility');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            const keyInput = document.getElementById('openai_api_key_display');
            const icon = this.querySelector('.dashicons');
            const isShowing = keyInput.getAttribute('data-showing') === 'true';
            
            if (isShowing) {
                keyInput.value = keyInput.getAttribute('data-masked');
                keyInput.setAttribute('data-showing', 'false');
                icon.classList.remove('dashicons-hidden');
                icon.classList.add('dashicons-visibility');
            } else {
                toggleBtn.disabled = true;
                icon.classList.add('dashicons-update');
                icon.style.animation = 'rotation 2s infinite linear';
                
                fetch(ajaxurl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'get_full_openai_key',
                        nonce: wpBasicAgent.nonce
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        keyInput.value = data.data.full_key;
                        keyInput.setAttribute('data-showing', 'true');
                        icon.classList.remove('dashicons-visibility');
                        icon.classList.add('dashicons-hidden');
                    } else {
                        alert('Error: ' + data.data.message);
                    }
                })
                .finally(() => {
                    toggleBtn.disabled = false;
                    icon.classList.remove('dashicons-update');
                    icon.style.animation = '';
                });
            }
        });
    }
});
