/**
 * Position Module
 * Manages positions/job titles
 */

import api from '../services/api.js';
import ui from '../utils/ui.js';
import modal from '../utils/modal.js';

class PositionModule {
    constructor() {
        this.positions = [];
    }

    async render() {
        ui.setPageTitle('Quản lý Vị trí');
        const mainContent = document.getElementById('mainContent');
        
        mainContent.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Danh sách Vị trí</h4>
                <button class="btn btn-primary" onclick="positionModule.showAddModal()">
                    <i class="bi bi-plus-circle"></i> Thêm vị trí
                </button>
            </div>

            <div class="card">
                <div class="card-body">
                    <div id="positionTableContainer">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status"></div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        await this.loadPositions();
    }

    async loadPositions() {
        try {
            ui.showLoading();
            const response = await api.get('?resource=positions');
            
            if (response.success) {
                this.positions = response.data;
                this.renderTable();
            }
        } catch (error) {
            console.error('Error:', error);
            ui.showToast('Lỗi khi tải dữ liệu', 'error');
        } finally {
            ui.hideLoading();
        }
    }

    renderTable() {
        const container = document.getElementById('positionTableContainer');
        
        if (this.positions.length === 0) {
            container.innerHTML = `
                <div class="text-center text-muted py-5">
                    <i class="bi bi-briefcase" style="font-size: 3rem;"></i>
                    <p>Chưa có vị trí nào</p>
                </div>
            `;
            return;
        }

        const headers = ['ID', 'Tên vị trí', 'Lương cơ bản', 'Số nhân viên', 'Thao tác'];
        const rows = this.positions.map(pos => [
            pos.id,
            pos.title,
            ui.formatCurrency(pos.base_salary || 0),
            `<span class="badge bg-info">${pos.employee_count || 0}</span>`,
            `
                <button class="btn btn-sm btn-warning" onclick="positionModule.showEditModal(${pos.id})">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="positionModule.deletePosition(${pos.id})">
                    <i class="bi bi-trash"></i>
                </button>
            `
        ]);

        container.innerHTML = ui.generateTable(headers, rows);
    }

    showAddModal() {
        const fields = [
            { name: 'title', label: 'Tên vị trí', type: 'text', required: true, placeholder: 'VD: Lập trình viên Senior' },
            { name: 'description', label: 'Mô tả công việc', type: 'textarea', rows: 3 },
            { name: 'base_salary', label: 'Lương cơ bản', type: 'number', required: true, placeholder: '15000000' }
        ];

        modal.createFormModal('Thêm vị trí mới', fields, async (formData) => {
            try {
                ui.showLoading();
                const response = await api.post('?resource=positions', formData);
                
                if (response.success) {
                    ui.showToast('Thêm vị trí thành công', 'success');
                    await this.loadPositions();
                    return true;
                }
                ui.showToast(response.message || 'Thêm thất bại', 'error');
                return false;
            } catch (error) {
                ui.showToast('Lỗi: ' + error.message, 'error');
                return false;
            } finally {
                ui.hideLoading();
            }
        });
    }

    async showEditModal(id) {
        const pos = this.positions.find(p => p.id === id);
        if (!pos) return;

        const fields = [
            { name: 'title', label: 'Tên vị trí', type: 'text', required: true },
            { name: 'description', label: 'Mô tả công việc', type: 'textarea', rows: 3 },
            { name: 'base_salary', label: 'Lương cơ bản', type: 'number', required: true }
        ];

        modal.createFormModal('Cập nhật vị trí', fields, async (formData) => {
            try {
                ui.showLoading();
                const response = await api.put(`?resource=positions&id=${id}`, formData);
                
                if (response.success) {
                    ui.showToast('Cập nhật vị trí thành công', 'success');
                    await this.loadPositions();
                    return true;
                }
                ui.showToast(response.message || 'Cập nhật thất bại', 'error');
                return false;
            } catch (error) {
                ui.showToast('Lỗi: ' + error.message, 'error');
                return false;
            } finally {
                ui.hideLoading();
            }
        }, pos);
    }

    async deletePosition(id) {
        const confirmed = await modal.confirm('Bạn có chắc muốn xóa vị trí này?');
        if (!confirmed) return;
        
        try {
            ui.showLoading();
            const response = await api.delete(`?resource=positions&id=${id}`);
            if (response.success) {
                ui.showToast('Xóa vị trí thành công', 'success');
                await this.loadPositions();
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

const positionModule = new PositionModule();
window.positionModule = positionModule;
export default positionModule;
