/**
 * Authentication Module
 * Handles login, register, logout
 */

import api from '../services/api.js';
import ui from '../utils/ui.js';

class AuthModule {
    constructor() {
        this.currentUser = null;
        this.init();
    }

    /**
     * Initialize authentication
     */
    init() {
        this.setupEventListeners();
        this.checkAuthStatus();
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Login form
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleLogin();
            });
        }

        // Register form
        const registerForm = document.getElementById('registerForm');
        if (registerForm) {
            registerForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleRegister();
            });
        }

        // Logout button
        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleLogout();
            });
        }

        // Modal switching
        const showRegisterLink = document.getElementById('showRegisterLink');
        if (showRegisterLink) {
            showRegisterLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.showRegisterModal();
            });
        }

        const showLoginLink = document.getElementById('showLoginLink');
        if (showLoginLink) {
            showLoginLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.showLoginModal();
            });
        }
    }

    /**
     * Check authentication status
     */
    async checkAuthStatus() {
        const token = api.getToken();
        
        if (!token) {
            console.log('User not authenticated');
            this.showLoginModal();
            return;
        }

        try {
            const response = await api.get('?resource=auth&action=me');
            if (response.success) {
                this.currentUser = response.data;
                this.updateUserInfo();
                this.hideLoginModal();
            } else {
                console.log('Auth failed:', response.message);
                api.removeToken(); // Remove invalid token
                this.showLoginModal();
            }
        } catch (error) {
            console.error('Auth check failed:', error);
            api.removeToken(); // Remove invalid token
            this.showLoginModal();
        }
    }

    /**
     * Handle login
     */
    async handleLogin() {
        const email = document.getElementById('loginEmail').value;
        const password = document.getElementById('loginPassword').value;

        if (!email || !password) {
            ui.showToast('Vui lòng nhập đầy đủ thông tin', 'error');
            return;
        }

        try {
            ui.showLoading();
            const response = await api.post('?resource=auth&action=login', {
                email,
                password
            });

            if (response.success) {
                api.setToken(response.data.token);
                this.currentUser = response.data.user;
                this.updateUserInfo();
                this.hideLoginModal();
                ui.showToast('Đăng nhập thành công!', 'success');
                
                // Reload dashboard
                window.location.reload();
            } else {
                ui.showToast(response.message || 'Đăng nhập thất bại', 'error');
            }
        } catch (error) {
            ui.showToast(error.message || 'Đăng nhập thất bại', 'error');
        } finally {
            ui.hideLoading();
        }
    }

    /**
     * Handle register
     */
    async handleRegister() {
        const name = document.getElementById('registerName').value;
        const email = document.getElementById('registerEmail').value;
        const password = document.getElementById('registerPassword').value;
        const passwordConfirm = document.getElementById('registerPasswordConfirm').value;

        if (!name || !email || !password || !passwordConfirm) {
            ui.showToast('Vui lòng nhập đầy đủ thông tin', 'error');
            return;
        }

        if (password !== passwordConfirm) {
            ui.showToast('Mật khẩu xác nhận không khớp', 'error');
            return;
        }

        if (password.length < 6) {
            ui.showToast('Mật khẩu phải có ít nhất 6 ký tự', 'error');
            return;
        }

        try {
            ui.showLoading();
            const response = await api.post('?resource=auth&action=register', {
                name,
                email,
                password
            });

            if (response.success) {
                api.setToken(response.data.token);
                this.currentUser = response.data.user;
                this.updateUserInfo();
                this.hideRegisterModal();
                ui.showToast('Đăng ký thành công!', 'success');
                
                // Reload dashboard
                window.location.reload();
            } else {
                ui.showToast(response.message || 'Đăng ký thất bại', 'error');
            }
        } catch (error) {
            ui.showToast(error.message || 'Đăng ký thất bại', 'error');
        } finally {
            ui.hideLoading();
        }
    }

    /**
     * Handle logout
     */
    async handleLogout() {
        if (!ui.confirm('Bạn có chắc muốn đăng xuất?')) {
            return;
        }

        try {
            api.removeToken();
            this.currentUser = null;
            ui.showToast('Đã đăng xuất', 'success');
            this.showLoginModal();
        } catch (error) {
            ui.showToast('Đăng xuất thất bại', 'error');
        }
    }

    /**
     * Update user info in UI
     */
    updateUserInfo() {
        const usernameEl = document.getElementById('username');
        if (usernameEl && this.currentUser) {
            usernameEl.textContent = this.currentUser.name;
        }
    }

    /**
     * Show login modal
     */
    showLoginModal() {
        const modal = new bootstrap.Modal(document.getElementById('loginModal'));
        modal.show();
    }

    /**
     * Hide login modal
     */
    hideLoginModal() {
        const modalEl = document.getElementById('loginModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) {
            modal.hide();
        }
    }

    /**
     * Show register modal
     */
    showRegisterModal() {
        this.hideLoginModal();
        const modal = new bootstrap.Modal(document.getElementById('registerModal'));
        modal.show();
    }

    /**
     * Hide register modal
     */
    hideRegisterModal() {
        const modalEl = document.getElementById('registerModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) {
            modal.hide();
        }
    }

    /**
     * Get current user
     * @returns {object|null}
     */
    getCurrentUser() {
        return this.currentUser;
    }

    /**
     * Check if user is authenticated
     * @returns {boolean}
     */
    isAuthenticated() {
        return !!this.currentUser && !!api.getToken();
    }
}

export default new AuthModule();
