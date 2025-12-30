import { createRoot } from '@wordpress/element';
import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import './style.scss'; // can be a CSS file as well

const App = () => {
    const [count, setCount] = useState(0);
    const [message, setMessage] = useState('');
    const [messageType, setMessageType] = useState('success');
    const [isLoading, setIsLoading] = useState(false);

    // Load initial count from WordPress
    useEffect(() => {
        if (window.myPluginData && window.myPluginData.currentCount) {
            setCount(parseInt(window.myPluginData.currentCount));
        }
    }, []);

    const handleIncrement = () => {
        setCount(count + 1);
        setMessage('');
    };

    const handleDecrement = () => {
        setCount(Math.max(0, count - 1));
        setMessage('');
    };

    const handleReset = () => {
        setCount(0);
        setMessage('');
    };

    const handleSave = async () => {
        setIsLoading(true);
        setMessage('');

        try {
            const response = await fetch(`${window.myPluginData.apiUrl}save`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': window.myPluginData.nonce,
                },
                body: JSON.stringify({ count }),
            });

            const data = await response.json();

            if (response.ok) {
                setMessage(__('Saved successfully!', 'my-custom-plugin'));
                setMessageType('success');
            } else {
                setMessage(__('Error saving data', 'my-custom-plugin'));
                setMessageType('error');
            }
        } catch (error) {
            setMessage(__('Network error occurred', 'my-custom-plugin'));
            setMessageType('error');
            console.error('Save error:', error);
        } finally {
            setIsLoading(false);
        }
    };

    const handleLoad = async () => {
        setIsLoading(true);
        setMessage('');

        try {
            const response = await fetch(`${window.myPluginData.apiUrl}get`, {
                method: 'GET',
                headers: {
                    'X-WP-Nonce': window.myPluginData.nonce,
                },
            });

            const data = await response.json();

            if (response.ok && data.success) {
                setCount(data.count);
                setMessage(__('Loaded successfully!', 'my-custom-plugin'));
                setMessageType('success');
            } else {
                setMessage(__('Error loading data', 'my-custom-plugin'));
                setMessageType('error');
            }
        } catch (error) {
            setMessage(__('Network error occurred', 'my-custom-plugin'));
            setMessageType('error');
            console.error('Load error:', error);
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <div className="my-custom-app">
            <div className="app-card">
                <h2>{__('Counter Application', 'my-custom-plugin')}</h2>
                <p className="app-description">
                    {__('A simple counter app demonstrating @wordpress/scripts integration', 'my-custom-plugin')}
                </p>

                <div className="counter-display">
                    <p className="counter-label">
                        {__('Current Count:', 'my-custom-plugin')}
                    </p>
                    <div className="counter-value">{count}</div>
                </div>

                <div className="button-group">
                    <button
                        className="button button-primary button-large"
                        onClick={handleIncrement}
                        disabled={isLoading}
                    >
                        {__('+ Increment', 'my-custom-plugin')}
                    </button>
                    <button
                        className="button button-secondary button-large"
                        onClick={handleDecrement}
                        disabled={isLoading || count === 0}
                    >
                        {__('- Decrement', 'my-custom-plugin')}
                    </button>
                    <button
                        className="button button-large"
                        onClick={handleReset}
                        disabled={isLoading || count === 0}
                    >
                        {__('Reset', 'my-custom-plugin')}
                    </button>
                </div>

                <div className="button-group button-group-secondary">
                    <button
                        className="button button-primary"
                        onClick={handleSave}
                        disabled={isLoading}
                    >
                        {isLoading ? __('Saving...', 'my-custom-plugin') : __('Save to Database', 'my-custom-plugin')}
                    </button>
                    <button
                        className="button button-secondary"
                        onClick={handleLoad}
                        disabled={isLoading}
                    >
                        {isLoading ? __('Loading...', 'my-custom-plugin') : __('Load from Database', 'my-custom-plugin')}
                    </button>
                </div>

                {message && (
                    <div className={`notice notice-${messageType} is-dismissible`}>
                        <p>{message}</p>
                    </div>
                )}

                <div className="app-footer">
                    <p>
                        {__('Built with', 'my-custom-plugin')} <strong>@wordpress/scripts</strong>
                    </p>
                </div>
            </div>
        </div>
    );
};

// Initialize the app
document.addEventListener('DOMContentLoaded', () => {
    const rootElement = document.getElementById('my-custom-app-root');

    if (rootElement) {
        const root = createRoot(rootElement);
        root.render(<App />);
    }
});
