/**
 * Leave Module
 */
import api from '../services/api.js';
import ui from '../utils/ui.js';
import modal from '../utils/modal.js';

class LeaveModule {
    constructor() {
        this.leaves = [];
    }

    async render() {
        ui.setPageTitle('Quản lý Nghỉ phép');
        const mainContent = document.getElementById('mainContent');
        
        mainContent.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Danh sách đơn nghỉ phép</h4>
                <button class="btn btn-primary" onclick="leaveModule.showAddModal()">
                    <i class="bi bi-plus-circle"></i> Tạo đơn nghỉ phép
                </button>
            </div>

            <div class="card">
                <div class="card-body">
                    <div id="leaveTableContainer">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status"></div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        await this.loadLeaves();
    }

    async loadLeaves() {
        try {
            ui.showLoading();
            const response = await api.get('?resource=leaves');
            
            if (response.success) {
                this.leaves = response.data;
                this.renderTable();
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('leaveTableContainer').innerHTML = `
                <div class="text-center text-muted py-5">
                    <i class="bi bi-calendar-x" style="font-size: 3rem;"></i>
                    <p>Chưa có đơn nghỉ phép nào</p>
                </div>
            `;
        } finally {
            ui.hideLoading();
        }
    }

    renderTable() {
        const container = document.getElementById('leaveTableContainer');
        
        if (this.leaves.length === 0) {
            container.innerHTML = `
                <div class="text-center text-muted py-5">
                    <i class="bi bi-calendar-x" style="font-size: 3rem;"></i>
                    <p>Chưa có đơn nghỉ phép nào</p>
                </div>
            `;
            return;
        }

        const headers = ['ID', 'Nhân viên', 'Loại nghỉ phép', 'Từ ngày', 'Đến ngày', 'Số ngày', 'Trạng thái', 'Thao tác'];
        const rows = this.leaves.map(leave => {
            let statusBadge = '';
            if (leave.leave_status === 'approved') statusBadge = '<span class="badge bg-success">Đã duyệt</span>';
            else if (leave.leave_status === 'pending') statusBadge = '<span class="badge bg-warning">Chờ duyệt</span>';
            else if (leave.leave_status === 'rejected') statusBadge = '<span class="badge bg-danger">Từ chối</span>';
            else statusBadge = '<span class="badge bg-secondary">Khác</span>';

            return [
                leave.id,
                leave.full_name || 'N/A',
                leave.leave_type || 'N/A',
                ui.formatDate(leave.start_date),
                ui.formatDate(leave.end_date),
                leave.total_days || '-',
                statusBadge,
                `
                    <button class="btn btn-sm btn-success" onclick="leaveModule.approve(${leave.id})" ${leave.leave_status !== 'pending' ? 'disabled' : ''}>
                        <i class="bi bi-check"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="leaveModule.reject(${leave.id})" ${leave.leave_status !== 'pending' ? 'disabled' : ''}>
                        <i class="bi bi-x"></i>
                    </button>
                `
            ];
        });

        container.innerHTML = ui.generateTable(headers, rows);
    }

    async showAddModal() {
        const empResponse = await api.get('?resource=employees');
        
        const fields = [
            { 
                name: 'employee_id', 
                label: 'Nhân viên', 
                type: 'select', 
                required: true,
                options: empResponse.success ? empResponse.data.map(e => ({ 
                    value: e.id, 
                    label: `${e.employee_code} - ${e.full_name}` 
                })) : []
            },
            { 
                name: 'leave_type', 
                label: 'Loại nghỉ phép', 
                type: 'select', 
                required: true,
                options: [
                    { value: 'annual', label: 'Nghỉ phép năm' },
                    { value: 'sick', label: 'Nghỉ ốm' },
                    { value: 'unpaid', label: 'Nghỉ không lương' },
                    { value: 'maternity', label: 'Nghỉ thai sản' },
                    { value: 'other', label: 'Khác' }
                ]
            },
            { name: 'start_date', label: 'Từ ngày', type: 'date', required: true },
            { name: 'end_date', label: 'Đến ngày', type: 'date', required: true },
            { name: 'days', label: 'Số ngày', type: 'number', required: true, placeholder: '1' },
            { name: 'reason', label: 'Lý do', type: 'textarea', required: true, rows: 3 }
        ];

        modal.createFormModal('Tạo đơn nghỉ phép', fields, async (formData) => {
            try {
                ui.showLoading();
                const response = await api.post('?resource=leaves', formData);
                
                if (response.success) {
                    ui.showToast('Tạo đơn nghỉ phép thành công', 'success');
                    await this.loadLeaves();
                    return true;
                }
                ui.showToast(response.message || 'Tạo đơn thất bại', 'error');
                return false;
            } catch (error) {
                ui.showToast('Lỗi: ' + error.message, 'error');
                return false;
            } finally {
                ui.hideLoading();
            }
        });
    }

    async approve(id) {
        const confirmed = await modal.confirm('Bạn có chắc muốn duyệt đơn nghỉ phép này?', 'Xác nhận duyệt');
        if (!confirmed) return;

        try {
            ui.showLoading();
            const response = await api.put(`?resource=leaves&id=${id}&action=approve`, {});
            
            if (response.success) {
                ui.showToast('Duyệt đơn thành công', 'success');
                await this.loadLeaves();
            } else {
                ui.showToast(response.message || 'Duyệt đơn thất bại', 'error');
            }
        } catch (error) {
            ui.showToast('Lỗi: ' + error.message, 'error');
        } finally {
            ui.hideLoading();
        }
    }

    async reject(id) {
        const confirmed = await modal.confirm('Bạn có chắc muốn từ chối đơn nghỉ phép này?', 'Xác nhận từ chối');
        if (!confirmed) return;

        try {
            ui.showLoading();
            const response = await api.put(`?resource=leaves&id=${id}&action=reject`, {});
            
            if (response.success) {
                ui.showToast('Từ chối đơn thành công', 'success');
                await this.loadLeaves();
            } else {
                ui.showToast(response.message || 'Từ chối đơn thất bại', 'error');
            }
        } catch (error) {
            ui.showToast('Lỗi: ' + error.message, 'error');
        } finally {
            ui.hideLoading();
        }
    }
}

const leaveModule = new LeaveModule();
window.leaveModule = leaveModule;
export default leaveModule;
