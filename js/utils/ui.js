/**
 * UI Utility Module
 * Helper functions for UI operations
 */

class UIUtil {
    /**
     * Show toast notification
     * @param {string} message 
     * @param {string} type - success, error, warning, info
     */
    showToast(message, type = 'info') {
        const toastEl = document.getElementById('toastMessage');
        const toastBody = toastEl.querySelector('.toast-body');
        const toastHeader = toastEl.querySelector('.toast-header');
        
        // Set message
        toastBody.textContent = message;
        
        // Set color based on type
        toastHeader.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info', 'text-white');
        if (type === 'success') {
            toastHeader.classList.add('bg-success', 'text-white');
        } else if (type === 'error') {
            toastHeader.classList.add('bg-danger', 'text-white');
        } else if (type === 'warning') {
            toastHeader.classList.add('bg-warning');
        } else {
            toastHeader.classList.add('bg-info', 'text-white');
        }
        
        // Show toast
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
    }

    /**
     * Show loading spinner
     */
    showLoading() {
        let spinner = document.querySelector('.spinner-overlay');
        if (!spinner) {
            spinner = document.createElement('div');
            spinner.className = 'spinner-overlay';
            spinner.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
            document.body.appendChild(spinner);
        }
        spinner.style.display = 'flex';
    }

    /**
     * Hide loading spinner
     */
    hideLoading() {
        const spinner = document.querySelector('.spinner-overlay');
        if (spinner) {
            spinner.style.display = 'none';
        }
    }

    /**
     * Confirm dialog
     * @param {string} message 
     * @returns {boolean}
     */
    confirm(message) {
        return window.confirm(message);
    }

    /**
     * Format date
     * @param {string} dateString 
     * @returns {string}
     */
    formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('vi-VN');
    }

    /**
     * Format currency (VND)
     * @param {number} amount 
     * @returns {string}
     */
    formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount);
    }

    /**
     * Format number
     * @param {number} num 
     * @returns {string}
     */
    formatNumber(num) {
        return new Intl.NumberFormat('vi-VN').format(num);
    }

    /**
     * Clear element content
     * @param {string} elementId 
     */
    clearElement(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.innerHTML = '';
        }
    }

    /**
     * Set page title
     * @param {string} title 
     */
    setPageTitle(title) {
        const titleEl = document.getElementById('pageTitle');
        if (titleEl) {
            titleEl.textContent = title;
        }
    }

    /**
     * Update active menu
     * @param {string} module 
     */
    updateActiveMenu(module) {
        // Remove active class from all menu items
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.classList.remove('active');
        });
        
        // Add active class to current module
        const activeLink = document.querySelector(`.sidebar .nav-link[data-module="${module}"]`);
        if (activeLink) {
            activeLink.classList.add('active');
        }
    }

    /**
     * Create table from data
     * @param {Array} data 
     * @param {Array} columns 
     * @param {Function} actionsCallback 
     * @returns {HTMLElement}
     */
    createTable(data, columns, actionsCallback = null) {
        const table = document.createElement('table');
        table.className = 'table table-striped table-hover';
        
        // Create header
        const thead = document.createElement('thead');
        const headerRow = document.createElement('tr');
        
        columns.forEach(col => {
            const th = document.createElement('th');
            th.textContent = col.label;
            headerRow.appendChild(th);
        });
        
        if (actionsCallback) {
            const th = document.createElement('th');
            th.textContent = 'Thao tác';
            th.style.width = '150px';
            headerRow.appendChild(th);
        }
        
        thead.appendChild(headerRow);
        table.appendChild(thead);
        
        // Create body
        const tbody = document.createElement('tbody');
        
        if (data.length === 0) {
            const tr = document.createElement('tr');
            const td = document.createElement('td');
            td.colSpan = columns.length + (actionsCallback ? 1 : 0);
            td.className = 'text-center text-muted';
            td.innerHTML = '<i class="bi bi-inbox"></i> Không có dữ liệu';
            tr.appendChild(td);
            tbody.appendChild(tr);
        } else {
            data.forEach(item => {
                const tr = document.createElement('tr');
                
                columns.forEach(col => {
                    const td = document.createElement('td');
                    let value = item[col.field];
                    
                    if (col.formatter) {
                        value = col.formatter(value, item);
                    }
                    
                    td.innerHTML = value || '-';
                    tr.appendChild(td);
                });
                
                if (actionsCallback) {
                    const td = document.createElement('td');
                    td.innerHTML = actionsCallback(item);
                    tr.appendChild(td);
                }
                
                tbody.appendChild(tr);
            });
        }
        
        table.appendChild(tbody);
        return table;
    }

    /**
     * Create empty state
     * @param {string} message 
     * @param {string} icon 
     * @returns {HTMLElement}
     */
    createEmptyState(message, icon = 'bi-inbox') {
        const div = document.createElement('div');
        div.className = 'empty-state';
        div.innerHTML = `
            <i class="bi ${icon}"></i>
            <p>${message}</p>
        `;
        return div;
    }

    /**
     * Generate HTML table from headers and rows
     * @param {Array} headers - Table headers
     * @param {Array} rows - Table rows (array of arrays)
     * @returns {string} HTML table
     */
    generateTable(headers, rows) {
        let html = '<table class="table table-striped table-hover"><thead><tr>';
        
        headers.forEach(header => {
            html += `<th>${header}</th>`;
        });
        
        html += '</tr></thead><tbody>';
        
        if (rows.length === 0) {
            html += `<tr><td colspan="${headers.length}" class="text-center text-muted">Không có dữ liệu</td></tr>`;
        } else {
            rows.forEach(row => {
                html += '<tr>';
                row.forEach(cell => {
                    html += `<td>${cell}</td>`;
                });
                html += '</tr>';
            });
        }
        
        html += '</tbody></table>';
        return html;
    }
}

export default new UIUtil();
