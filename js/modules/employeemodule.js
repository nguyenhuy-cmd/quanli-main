/**
 * Employee Module
 * Handles employee management
 */

import api from '../services/api.js';
import ui from '../utils/ui.js';
import modal from '../utils/modal.js';

class EmployeeModule {
    constructor() {
        this.employees = [];
    }

    /**
     * Render employee list
     */
    async render() {
        ui.setPageTitle('Quản lý Nhân viên');
        const mainContent = document.getElementById('mainContent');
        
        mainContent.innerHTML = `
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Danh sách Nhân viên</h5>
                    <button class="btn btn-primary" id="addEmployeeBtn">
                        <i class="bi bi-plus-circle"></i> Thêm nhân viên
                    </button>
                </div>
                <div class="card-body">
                    <div id="employeeTableContainer"></div>
                </div>
            </div>
        `;

        // Setup event listeners
        this.setupEventListeners();
        
        // Load employees
        await this.loadEmployees();
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        const addBtn = document.getElementById('addEmployeeBtn');
        if (addBtn) {
            addBtn.addEventListener('click', () => this.showAddModal());
        }
    }

    /**
     * Load all employees
     */
    async loadEmployees() {
        try {
            ui.showLoading();
            const response = await api.get('?resource=employees');
            
            if (response.success) {
                this.employees = response.data;
                this.renderTable(this.employees);
            }
        } catch (error) {
            ui.showToast('Không thể tải danh sách nhân viên', 'error');
        } finally {
            ui.hideLoading();
        }
    }

    /**
     * Render employee table
     */
    renderTable(data) {
        const container = document.getElementById('employeeTableContainer');
        
        const columns = [
            { field: 'employee_code', label: 'Mã NV' },
            { field: 'full_name', label: 'Họ tên' },
            { field: 'email', label: 'Email' },
            { field: 'phone', label: 'Điện thoại' },
            { field: 'department_name', label: 'Phòng ban' },
            { field: 'position_title', label: 'Vị trí' },
            { 
                field: 'status', 
                label: 'Trạng thái',
                formatter: (value) => {
                    const badges = {
                        'active': '<span class="badge bg-success">Đang làm</span>',
                        'inactive': '<span class="badge bg-warning">Tạm nghỉ</span>',
                        'terminated': '<span class="badge bg-danger">Đã nghỉ</span>'
                    };
                    return badges[value] || value;
                }
            }
        ];

        const actionsCallback = (item) => `
            <div class="action-buttons">
                <button class="btn btn-sm btn-info" onclick="window.employeeModule.viewEmployee(${item.id})">
                    <i class="bi bi-eye"></i>
                </button>
                <button class="btn btn-sm btn-warning" onclick="window.employeeModule.editEmployee(${item.id})">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="window.employeeModule.deleteEmployee(${item.id})">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;

        const table = ui.createTable(data, columns, actionsCallback);
        container.innerHTML = '';
        container.appendChild(table);
    }

    /**
     * Handle search
     */
    handleSearch(keyword) {
        if (!keyword.trim()) {
            this.renderTable(this.employees);
            return;
        }

        const searchTerm = keyword.toLowerCase();
        const filtered = this.employees.filter(emp => 
            emp.full_name?.toLowerCase().includes(searchTerm) ||
            emp.employee_code?.toLowerCase().includes(searchTerm) ||
            emp.email?.toLowerCase().includes(searchTerm) ||
            emp.phone?.includes(searchTerm) ||
            emp.department_name?.toLowerCase().includes(searchTerm) ||
            emp.position_title?.toLowerCase().includes(searchTerm)
        );

        this.renderTable(filtered);
    }

    /**
     * Show add employee modal
     */
    async showAddModal() {
        // Load departments and positions for select options
        const [depts, positions] = await Promise.all([
            api.get('?resource=departments'),
            api.get('?resource=positions')
        ]);

        const fields = [
            { name: 'employee_code', label: 'Mã nhân viên', type: 'text', required: true, placeholder: 'VD: EMP001' },
            { name: 'full_name', label: 'Họ và tên', type: 'text', required: true },
            { name: 'email', label: 'Email', type: 'email', required: true },
            { name: 'phone', label: 'Số điện thoại', type: 'tel', required: true },
            { name: 'date_of_birth', label: 'Ngày sinh', type: 'date', required: true },
            { 
                name: 'gender', 
                label: 'Giới tính', 
                type: 'select', 
                required: true,
                options: [
                    { value: 'male', label: 'Nam' },
                    { value: 'female', label: 'Nữ' },
                    { value: 'other', label: 'Khác' }
                ]
            },
            { name: 'address', label: 'Địa chỉ', type: 'textarea', rows: 2 },
            { 
                name: 'department_id', 
                label: 'Phòng ban', 
                type: 'select', 
                required: true,
                options: depts.success ? depts.data.map(d => ({ value: d.id, label: d.name })) : []
            },
            { 
                name: 'position_id', 
                label: 'Vị trí', 
                type: 'select', 
                required: true,
                options: positions.success ? positions.data.map(p => ({ value: p.id, label: p.title })) : []
            },
            { name: 'hire_date', label: 'Ngày vào làm', type: 'date', required: true },
            { 
                name: 'employment_status', 
                label: 'Trạng thái', 
                type: 'select', 
                required: true,
                options: [
                    { value: 'active', label: 'Đang làm việc' },
                    { value: 'on_leave', label: 'Tạm nghỉ' },
                    { value: 'terminated', label: 'Đã nghỉ việc' }
                ]
            }
        ];

        modal.createFormModal('Thêm nhân viên mới', fields, async (formData) => {
            try {
                ui.showLoading();
                const response = await api.post('?resource=employees', formData);
                
                if (response.success) {
                    ui.showToast('Thêm nhân viên thành công', 'success');
                    await this.loadEmployees();
                    return true;
                } else {
                    ui.showToast(response.message || 'Thêm nhân viên thất bại', 'error');
                    return false;
                }
            } catch (error) {
                ui.showToast('Lỗi: ' + error.message, 'error');
                return false;
            } finally {
                ui.hideLoading();
            }
        });
    }

    /**
     * View employee details
     */
    async viewEmployee(id) {
        try {
            const response = await api.get(`?resource=employees&id=${id}`);
            if (response.success) {
                // Show employee details in modal
                ui.showToast('Đang xem chi tiết nhân viên #' + id, 'info');
            }
        } catch (error) {
            ui.showToast('Không thể tải thông tin nhân viên', 'error');
        }
    }

    /**
     * Edit employee
     */
    async editEmployee(id) {
        try {
            ui.showLoading();
            const [empResponse, depts, positions] = await Promise.all([
                api.get(`?resource=employees&id=${id}`),
                api.get('?resource=departments'),
                api.get('?resource=positions')
            ]);

            if (!empResponse.success) {
                ui.showToast('Không thể tải thông tin nhân viên', 'error');
                return;
            }

            const employee = empResponse.data;

            const fields = [
                { name: 'employee_code', label: 'Mã nhân viên', type: 'text', required: true },
                { name: 'full_name', label: 'Họ và tên', type: 'text', required: true },
                { name: 'email', label: 'Email', type: 'email', required: true },
                { name: 'phone', label: 'Số điện thoại', type: 'tel', required: true },
                { name: 'date_of_birth', label: 'Ngày sinh', type: 'date', required: true },
                { 
                    name: 'gender', 
                    label: 'Giới tính', 
                    type: 'select', 
                    required: true,
                    options: [
                        { value: 'male', label: 'Nam' },
                        { value: 'female', label: 'Nữ' },
                        { value: 'other', label: 'Khác' }
                    ]
                },
                { name: 'address', label: 'Địa chỉ', type: 'textarea', rows: 2 },
                { 
                    name: 'department_id', 
                    label: 'Phòng ban', 
                    type: 'select', 
                    required: true,
                    options: depts.success ? depts.data.map(d => ({ value: d.id, label: d.name })) : []
                },
                { 
                    name: 'position_id', 
                    label: 'Vị trí', 
                    type: 'select', 
                    required: true,
                    options: positions.success ? positions.data.map(p => ({ value: p.id, label: p.title })) : []
                },
                { name: 'hire_date', label: 'Ngày vào làm', type: 'date', required: true },
                { 
                    name: 'employment_status', 
                    label: 'Trạng thái', 
                    type: 'select', 
                    required: true,
                    options: [
                        { value: 'active', label: 'Đang làm việc' },
                        { value: 'on_leave', label: 'Tạm nghỉ' },
                        { value: 'terminated', label: 'Đã nghỉ việc' }
                    ]
                }
            ];

            modal.createFormModal('Cập nhật nhân viên', fields, async (formData) => {
                try {
                    ui.showLoading();
                    const response = await api.put(`?resource=employees&id=${id}`, formData);
                    
                    if (response.success) {
                        ui.showToast('Cập nhật nhân viên thành công', 'success');
                        await this.loadEmployees();
                        return true;
                    } else {
                        ui.showToast(response.message || 'Cập nhật thất bại', 'error');
                        return false;
                    }
                } catch (error) {
                    ui.showToast('Lỗi: ' + error.message, 'error');
                    return false;
                } finally {
                    ui.hideLoading();
                }
            }, employee);
        } catch (error) {
            ui.showToast('Lỗi: ' + error.message, 'error');
        } finally {
            ui.hideLoading();
        }
    }

    /**
     * Delete employee
     */
    async deleteEmployee(id) {
        const confirmed = await modal.confirm('Bạn có chắc muốn xóa nhân viên này? Hành động này không thể hoàn tác.', 'Xác nhận xóa');
        
        if (!confirmed) {
            return;
        }

        try {
            ui.showLoading();
            const response = await api.delete(`?resource=employees&id=${id}`);
            
            if (response.success) {
                ui.showToast('Xóa nhân viên thành công', 'success');
                await this.loadEmployees();
            } else {
                ui.showToast(response.message || 'Xóa nhân viên thất bại', 'error');
            }
        } catch (error) {
            ui.showToast('Lỗi: ' + error.message, 'error');
        } finally {
            ui.hideLoading();
        }
    }
}

const employeeModule = new EmployeeModule();
window.employeeModule = employeeModule; // Make it globally accessible for onclick handlers
export default employeeModule;
