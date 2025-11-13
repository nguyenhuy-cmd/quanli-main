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

        const config = {
            method,
            headers,
            cache: 'no-cache',
        };

        if (data && (method === 'POST' || method === 'PUT')) {
            config.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(`${API_BASE_URL}${endpoint}`, config);
            
            // Kiểm tra content-type trước khi parse JSON
            const contentType = response.headers.get('content-type');
            
            // Lấy text response trước
            const text = await response.text();
            
            // Nếu response chứa HTML anti-bot của InfinityFree
            if (text.includes('slowAES.decrypt') || text.includes('toNumbers')) {
                console.warn('InfinityFree anti-bot detected, retrying...');
                // Đợi 2 giây rồi thử lại
                await new Promise(resolve => setTimeout(resolve, 2000));
                // Thử lại một lần nữa
                return this.request(endpoint, method, data);
            }
            
            // Nếu không phải JSON
            if (!contentType || !contentType.includes('application/json')) {
                console.error('Non-JSON response:', text);
                throw new Error('Server returned non-JSON response. Please check your hosting configuration.');
            }

            // Parse JSON
            let result;
            try {
                result = JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error:', text.substring(0, 500));
                console.error('Full response:', text);
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
            console.error('API Error:', error);
            throw error;
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
