/**
 * WP Pause Admin App - Entry Point
 *
 * @package PauseWP
 */

import { createRoot } from '@wordpress/element';
import App from './App';
import './index.scss';

// Wait for DOM ready.
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('pausewp-admin-app');

    if (container) {
        const root = createRoot(container);
        root.render(<App />);
    }
});
