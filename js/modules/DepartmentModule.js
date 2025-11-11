/**
 * Department Module
 * Manages departments
 */

import api from '../services/api.js';
import ui from '../utils/ui.js';
import modal from '../utils/modal.js';

class DepartmentModule {
    constructor() {
        this.departments = [];
    }

    /**
     * Render department list
     */
    async render() {
        ui.setPageTitle('Quản lý Phòng ban');
        const mainContent = document.getElementById('mainContent');
        
        mainContent.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Danh sách Phòng ban</h4>
                <button class="btn btn-primary" onclick="departmentModule.showAddModal()">
                    <i class="bi bi-plus-circle"></i> Thêm phòng ban
                </button>
            </div>

            <div class="card">
                <div class="card-body">
                    <div id="departmentTableContainer">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Đang tải...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        await this.loadDepartments();
    }

    /**
     * Load departments from API
     */
    async loadDepartments() {
        try {
            ui.showLoading();
            const response = await api.get('?resource=departments');
            
            if (response.success) {
                this.departments = response.data;
                this.renderTable();
            } else {
                ui.showToast('Không thể tải danh sách phòng ban', 'error');
            }
        } catch (error) {
            console.error('Error loading departments:', error);
            ui.showToast('Lỗi khi tải dữ liệu', 'error');
        } finally {
            ui.hideLoading();
        }
    }

    /**
     * Render departments table
     */
    renderTable() {
        const container = document.getElementById('departmentTableContainer');
        
        if (this.departments.length === 0) {
            container.innerHTML = `
                <div class="text-center text-muted py-5">
                    <i class="bi bi-building" style="font-size: 3rem;"></i>
                    <p>Chưa có phòng ban nào</p>
                </div>
            `;
            return;
        }

        const headers = ['ID', 'Tên phòng ban', 'Mô tả', 'Số nhân viên', 'Thao tác'];
        const rows = this.departments.map(dept => [
            dept.id,
            dept.name,
            dept.description || 'N/A',
            `<span class="badge bg-info">${dept.employee_count || 0}</span>`,
            `
                <button class="btn btn-sm btn-warning" onclick="departmentModule.showEditModal(${dept.id})">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="departmentModule.deleteDepartment(${dept.id})">
                    <i class="bi bi-trash"></i>
                </button>
            `
        ]);

        container.innerHTML = ui.generateTable(headers, rows);
    }

    /**
     * Show add department modal
     */
    showAddModal() {
        const fields = [
            { name: 'name', label: 'Tên phòng ban', type: 'text', required: true, placeholder: 'VD: Công nghệ thông tin' },
            { name: 'description', label: 'Mô tả', type: 'textarea', rows: 3, placeholder: 'Mô tả về phòng ban...' }
        ];

        modal.createFormModal('Thêm phòng ban mới', fields, async (formData) => {
            try {
                ui.showLoading();
                const response = await api.post('?resource=departments', formData);
                
                if (response.success) {
                    ui.showToast('Thêm phòng ban thành công', 'success');
                    await this.loadDepartments();
                    return true;
                } else {
                    ui.showToast(response.message || 'Thêm thất bại', 'error');
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
     * Show edit department modal
     */
    async showEditModal(id) {
        const dept = this.departments.find(d => d.id === id);
        if (!dept) {
            ui.showToast('Không tìm thấy phòng ban', 'error');
            return;
        }

        const fields = [
            { name: 'name', label: 'Tên phòng ban', type: 'text', required: true },
            { name: 'description', label: 'Mô tả', type: 'textarea', rows: 3 }
        ];

        modal.createFormModal('Cập nhật phòng ban', fields, async (formData) => {
            try {
                ui.showLoading();
                const response = await api.put(`?resource=departments&id=${id}`, formData);
                
                if (response.success) {
                    ui.showToast('Cập nhật phòng ban thành công', 'success');
                    await this.loadDepartments();
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
        }, dept);
    }

    /**
     * Delete department
     */
    async deleteDepartment(id) {
        const confirmed = await modal.confirm('Bạn có chắc muốn xóa phòng ban này?', 'Xác nhận xóa');
        
        if (!confirmed) {
            return;
        }

        try {
            ui.showLoading();
            const response = await api.delete(`?resource=departments&id=${id}`);
            
            if (response.success) {
                ui.showToast('Xóa phòng ban thành công', 'success');
                await this.loadDepartments();
            } else {
                ui.showToast(response.message || 'Xóa thất bại', 'error');
            }
        } catch (error) {
            ui.showToast('Lỗi: ' + error.message, 'error');
        } finally {
            ui.hideLoading();
        }
    }
}

const departmentModule = new DepartmentModule();
window.departmentModule = departmentModule; // Make it globally accessible
export default departmentModule;
