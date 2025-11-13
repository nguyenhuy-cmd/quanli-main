/**
 * API Service Module
 * Handles all API requests using fetch
 */

// Detect if running on production
const isProduction = window.location.hostname !== 'localhost' && 
                     window.location.hostname !== '127.0.0.1';

// Use different endpoint for production to bypass anti-bot
const API_BASE_URL = isProduction 
    ? '/backend/api-endpoint.php'  // Production: use wrapper
    : '/backend/api.php';          // Local: use direct API

console.log('API Base URL:', API_BASE_URL);

class APIService {
    constructor() {
        this.token = localStorage.getItem('auth_token') || '';
        this.maxRetries = 3;
    }

    /**
     * Set authentication token
     * @param {string} token 
     */
    setToken(token) {
        this.token = token;
        localStorage.setItem('auth_token', token);
    }

    /**
     * Remove authentication token
     */
    removeToken() {
        this.token = '';
        localStorage.removeItem('auth_token');
    }

    /**
     * Get authentication token
     * @returns {string}
     */
    getToken() {
        return this.token;
    }

    /**
     * Make API request
     * @param {string} endpoint 
     * @param {string} method 
     * @param {object} data 
     * @returns {Promise}
     */
    async request(endpoint, method = 'GET', data = null) {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        };

        if (this.token) {
            headers['Authorization'] = `Bearer ${this.token}`;
        }

        // WORKAROUND: InfinityFree blocks PUT/DELETE - convert to POST with _method
        let actualMethod = method;
        let requestData = data;
        
        if (method === 'PUT' || method === 'DELETE') {
            actualMethod = 'POST';
            requestData = data ? { ...data, _method: method } : { _method: method };
        }

        const config = {
            method: actualMethod,
            headers,
            cache: 'no-cache',
        };

        if (requestData && (actualMethod === 'POST' || actualMethod === 'PUT')) {
            config.body = JSON.stringify(requestData);
        }

        let retries = 0;
        while (retries < this.maxRetries) {
            try {
                const response = await fetch(`${API_BASE_URL}${endpoint}`, config);
            
                // Kiểm tra content-type trước khi parse JSON
                const contentType = response.headers.get('content-type');
            
            // Lấy text response trước
            const text = await response.text();
            
            // Nếu response chứa HTML anti-bot của InfinityFree
            if (text.includes('slowAES.decrypt') || text.includes('toNumbers')) {
                if (retries >= this.maxRetries - 1) {
                    throw new Error('Anti-bot protection triggered after ' + this.maxRetries + ' retries');
                }
                console.warn(`InfinityFree anti-bot detected, retry ${retries + 1}/${this.maxRetries}...`);
                retries++;
                await new Promise(resolve => setTimeout(resolve, 1500 * retries));
                continue;
            }
            
            // Nếu không phải JSON
            if (!contentType || !contentType.includes('application/json')) {
                console.error('Non-JSON response:', text.substring(0, 200));
                throw new Error('Server returned non-JSON response. Please check your hosting configuration.');
            }

            // Parse JSON
            let result;
            try {
                result = JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error:', text.substring(0, 500));
                throw new Error('Invalid JSON response from server: ' + e.message);
            }

            if (!response.ok) {
                // Include error details if available
                const errorMsg = result.message || 'Request failed';
                const errorDetails = result.error ? 
                    ` (${result.error.file}:${result.error.line})` : '';
                throw new Error(errorMsg + errorDetails);
            }

            return result;
            
        } catch (error) {
            if (retries >= this.maxRetries - 1) {
                console.error('API Error:', error);
                throw error;
            }
            retries++;
            await new Promise(resolve => setTimeout(resolve, 1000 * retries));
        }
    }
}

    /**
     * GET request
     * @param {string} endpoint 
     * @returns {Promise}
     */
    async get(endpoint) {
        return this.request(endpoint, 'GET');
    }

    /**
     * POST request
     * @param {string} endpoint 
     * @param {object} data 
     * @returns {Promise}
     */
    async post(endpoint, data) {
        return this.request(endpoint, 'POST', data);
    }

    /**
     * PUT request
     * @param {string} endpoint 
     * @param {object} data 
     * @returns {Promise}
     */
    async put(endpoint, data) {
        return this.request(endpoint, 'PUT', data);
    }

    /**
     * DELETE request
     * @param {string} endpoint 
     * @returns {Promise}
     */
    async delete(endpoint) {
        return this.request(endpoint, 'DELETE');
    }
}

export default new APIService();
