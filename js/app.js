/**
 * Main Application
 * Initializes and manages the HRM application
 */

import auth from './modules/AuthModule.js?v=7';
import dashboard from './modules/dashboardModule.js?v=7';
import employeeModule from './modules/employeeModule.js?v=7';
import departmentModule from './modules/departmentModule.js?v=7';
import positionModule from './modules/positionModule.js?v=7';
import salaryModule from './modules/salaryModule.js?v=7';
import attendanceModule from './modules/attendanceModule.js?v=7';
import leaveModule from './modules/leaveModule.js?v=7';
import performanceModule from './modules/performanceModule.js?v=7';
import ui from './utils/ui.js?v=7';
import modal from './utils/modal.js?v=3';

class App {
    constructor() {
        this.currentModule = 'dashboard';
        this.modules = {
            dashboard,
            employees: employeeModule,
            departments: departmentModule,
            positions: positionModule,
            salaries: salaryModule,
            attendance: attendanceModule,
            leaves: leaveModule,
            performance: performanceModule
        };
        
        // Make modal globally accessible
        window.modal = modal;
        
        // Debug: Log all loaded modules
        console.log('🔧 Loaded modules:', Object.keys(this.modules));
        console.log('📝 departments module:', departmentModule);
        console.log('📝 positions module:', positionModule);
        
        this.init();
    }

    /**
     * Initialize application
     */
    init() {
        console.log('🚀 HRM Application Starting...');
        
        // Wait for DOM to load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setup());
        } else {
            this.setup();
        }
    }

    /**
     * Setup application
     */
    setup() {
        console.log('⚙️ Setting up application...');
        
        // Setup navigation
        this.setupNavigation();
        
        // Setup refresh button
        this.setupRefresh();
        
        // Check authentication
        this.checkAuth();
    }

    /**
     * Check authentication
     */
    async checkAuth() {
        if (!auth.isAuthenticated()) {
            console.log('🔒 User not authenticated');
            // Auth module will show login modal
            return;
        }
        
        console.log('✅ User authenticated');
        this.loadModule(this.currentModule);
    }

    /**
     * Setup navigation
     */
    setupNavigation() {
        const navLinks = document.querySelectorAll('.sidebar .nav-link[data-module]');
        
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const module = link.dataset.module;
                this.loadModule(module);
            });
        });
    }

    /**
     * Setup refresh button
     */
    setupRefresh() {
        const refreshBtn = document.getElementById('refreshBtn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                this.loadModule(this.currentModule);
                ui.showToast('Đã làm mới', 'success');
            });
        }
    }

    /**
     * Load module
     * @param {string} moduleName 
     */
    async loadModule(moduleName) {
        console.log(`📦 Loading module: ${moduleName}`);
        
        // Check if user is authenticated
        if (!auth.isAuthenticated() && moduleName !== 'auth') {
            console.log('🔒 Authentication required');
            return;
        }

        this.currentModule = moduleName;
        ui.updateActiveMenu(moduleName);

        try {
            const module = this.modules[moduleName];
            
            console.log(`Module found:`, module);
            console.log(`Has render method:`, typeof module?.render);
            
            if (module && typeof module.render === 'function') {
                await module.render();
                console.log(`✅ Module loaded: ${moduleName}`);
            } else {
                console.warn(`⚠️ Module not found or has no render method: ${moduleName}`);
                this.renderNotImplemented(moduleName);
            }
        } catch (error) {
            console.error(`❌ Error loading module ${moduleName}:`, error);
            ui.showToast('Không thể tải module', 'error');
        }
    }

    /**
     * Render not implemented message
     * @param {string} moduleName 
     */
    renderNotImplemented(moduleName) {
        const titles = {
            departments: 'Quản lý Phòng ban',
            positions: 'Quản lý Vị trí',
            salaries: 'Quản lý Lương',
            attendance: 'Chấm công',
            leaves: 'Quản lý Nghỉ phép',
            performance: 'Đánh giá Hiệu suất'
        };

        ui.setPageTitle(titles[moduleName] || 'Module');
        
        const mainContent = document.getElementById('mainContent');
        mainContent.innerHTML = `
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-tools" style="font-size: 4rem; color: #6c757d;"></i>
                    <h3 class="mt-3">Module đang phát triển</h3>
                    <p class="text-muted">
                        Module <strong>${titles[moduleName] || moduleName}</strong> đang được phát triển.
                        <br>Vui lòng quay lại sau.
                    </p>
                    <button class="btn btn-primary mt-3" onclick="app.loadModule('dashboard')">
                        <i class="bi bi-house"></i> Về Dashboard
                    </button>
                </div>
            </div>
        `;
    }

    /**
     * Get current module
     * @returns {string}
     */
    getCurrentModule() {
        return this.currentModule;
    }
}

// Create and export app instance
const app = new App();
window.app = app; // Make it globally accessible

export default app;
