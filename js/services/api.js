/**
 * API Service Module
 * Handles all API requests using fetch
 */

const API_BASE_URL = '/quanli-main/backend/api.php';

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
        };

        if (this.token) {
            headers['Authorization'] = `Bearer ${this.token}`;
        }

        const config = {
            method,
            headers,
        };

        if (data && (method === 'POST' || method === 'PUT')) {
            config.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(`${API_BASE_URL}${endpoint}`, config);
            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Request failed');
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
