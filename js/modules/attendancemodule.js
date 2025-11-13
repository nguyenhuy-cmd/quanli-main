/**
 * Attendance Module
 */
import api from '../services/api.js';
import ui from '../utils/ui.js';
import modal from '../utils/modal.js';

class AttendanceModule {
    constructor() {
        this.attendances = [];
    }

    async render() {
        ui.setPageTitle('Chấm công');
        const mainContent = document.getElementById('mainContent');
        
        const today = new Date().toISOString().split('T')[0];
        
        mainContent.innerHTML = `
            <div class="row mb-3">
                <div class="col-md-6">
                    <h4>Chấm công ngày ${ui.formatDate(today)}</h4>
                </div>
                <div class="col-md-6 text-end">
                    <button class="btn btn-success" onclick="attendanceModule.checkIn()">
                        <i class="bi bi-clock"></i> Check In
                    </button>
                    <button class="btn btn-warning" onclick="attendanceModule.checkOut()">
                        <i class="bi bi-clock-history"></i> Check Out
                    </button>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <label>Chọn ngày:</label>
                        <input type="date" id="attendanceDate" class="form-control" style="max-width: 200px; display: inline-block; margin-left: 10px;" value="${today}" onchange="attendanceModule.loadAttendances()">
                    </div>
                    <div id="attendanceTableContainer">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status"></div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        await this.loadAttendances();
    }

    async loadAttendances() {
        try {
            ui.showLoading();
            const date = document.getElementById('attendanceDate')?.value || new Date().toISOString().split('T')[0];
            const response = await api.get(`?resource=attendance&date=${date}`);
            
            if (response.success) {
                this.attendances = response.data;
                this.renderTable();
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('attendanceTableContainer').innerHTML = `
                <div class="text-center text-muted py-5">
                    <i class="bi bi-clock-history" style="font-size: 3rem;"></i>
                    <p>Chưa có dữ liệu chấm công</p>
                </div>
            `;
        } finally {
            ui.hideLoading();
        }
    }

    renderTable() {
        const container = document.getElementById('attendanceTableContainer');
        
        if (this.attendances.length === 0) {
            container.innerHTML = `
                <div class="text-center text-muted py-5">
                    <i class="bi bi-clock-history" style="font-size: 3rem;"></i>
                    <p>Chưa có ai chấm công hôm nay</p>
                </div>
            `;
            return;
        }

        const headers = ['ID', 'Nhân viên', 'Ngày', 'Giờ vào', 'Giờ ra', 'Trạng thái', 'Ghi chú'];
        const rows = this.attendances.map(att => {
            let statusBadge = '';
            if (att.status === 'present') statusBadge = '<span class="badge bg-success">Đúng giờ</span>';
            else if (att.status === 'late') statusBadge = '<span class="badge bg-warning">Muộn</span>';
            else if (att.status === 'absent') statusBadge = '<span class="badge bg-danger">Vắng</span>';
            else statusBadge = '<span class="badge bg-secondary">Khác</span>';

            return [
                att.id,
                att.employee_name || 'N/A',
                ui.formatDate(att.date),
                att.check_in || '-',
                att.check_out || '-',
                statusBadge,
                att.notes || '-'
            ];
        });

        container.innerHTML = ui.generateTable(headers, rows);
    }

    async checkIn() {
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
            { name: 'date', label: 'Ngày', type: 'date', required: true, defaultValue: new Date().toISOString().split('T')[0] },
            { name: 'check_in', label: 'Giờ vào', type: 'text', required: true, defaultValue: new Date().toTimeString().split(' ')[0].substring(0, 5), placeholder: 'HH:MM' },
            { name: 'notes', label: 'Ghi chú', type: 'textarea', rows: 2 }
        ];

        modal.createFormModal('Check In', fields, async (formData) => {
            try {
                ui.showLoading();
                const response = await api.post('?resource=attendance&action=checkin', formData);
                
                if (response.success) {
                    ui.showToast('Check in thành công', 'success');
                    await this.loadAttendances();
                    return true;
                }
                ui.showToast(response.message || 'Check in thất bại', 'error');
                return false;
            } catch (error) {
                ui.showToast('Lỗi: ' + error.message, 'error');
                return false;
            } finally {
                ui.hideLoading();
            }
        });
    }

    async checkOut() {
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
            { name: 'date', label: 'Ngày', type: 'date', required: true, defaultValue: new Date().toISOString().split('T')[0] },
            { name: 'check_out', label: 'Giờ ra', type: 'text', required: true, defaultValue: new Date().toTimeString().split(' ')[0].substring(0, 5), placeholder: 'HH:MM' },
            { name: 'notes', label: 'Ghi chú', type: 'textarea', rows: 2 }
        ];

        modal.createFormModal('Check Out', fields, async (formData) => {
            try {
                ui.showLoading();
                const response = await api.post('?resource=attendance&action=checkout', formData);
                
                if (response.success) {
                    ui.showToast('Check out thành công', 'success');
                    await this.loadAttendances();
                    return true;
                }
                ui.showToast(response.message || 'Check out thất bại', 'error');
                return false;
            } catch (error) {
                ui.showToast('Lỗi: ' + error.message, 'error');
                return false;
            } finally {
                ui.hideLoading();
            }
        });
    }
}

const attendanceModule = new AttendanceModule();
window.attendanceModule = attendanceModule;
export default attendanceModule;
