import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Laravel Echo: escucha en el navegador los eventos que el servidor transmite por WebSocket (Reverb).

import './echo';
