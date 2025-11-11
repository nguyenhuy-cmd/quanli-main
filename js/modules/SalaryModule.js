/**
 * Salary Module
 */
import api from '../services/api.js';
import ui from '../utils/ui.js';
import modal from '../utils/modal.js';

class SalaryModule {
    constructor() {
        this.salaries = [];
    }

    async render() {
        ui.setPageTitle('Quản lý Lương');
        const mainContent = document.getElementById('mainContent');
        
        mainContent.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Bảng lương nhân viên</h4>
                <button class="btn btn-primary" onclick="salaryModule.showAddModal()">
                    <i class="bi bi-plus-circle"></i> Thêm bảng lương
                </button>
            </div>

            <div class="card">
                <div class="card-body">
                    <div id="salaryTableContainer">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status"></div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        await this.loadSalaries();
    }

    async loadSalaries() {
        try {
            ui.showLoading();
            const response = await api.get('?resource=salaries');
            
            if (response.success) {
                this.salaries = response.data;
                this.renderTable();
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('salaryTableContainer').innerHTML = `
                <div class="text-center text-muted py-5">
                    <i class="bi bi-cash-stack" style="font-size: 3rem;"></i>
                    <p>Chưa có dữ liệu lương</p>
                </div>
            `;
        } finally {
            ui.hideLoading();
        }
    }

    renderTable() {
        const container = document.getElementById('salaryTableContainer');
        
        if (this.salaries.length === 0) {
            container.innerHTML = `
                <div class="text-center text-muted py-5">
                    <i class="bi bi-cash-stack" style="font-size: 3rem;"></i>
                    <p>Chưa có bảng lương nào</p>
                </div>
            `;
            return;
        }

        const headers = ['ID', 'Nhân viên', 'Lương cơ bản', 'Phụ cấp', 'Thưởng', 'Tổng lương', 'Tháng/Năm', 'Thao tác'];
        const rows = this.salaries.map(salary => [
            salary.id,
            salary.employee_name || 'N/A',
            ui.formatCurrency(salary.base_salary || 0),
            ui.formatCurrency(salary.allowances || 0),
            ui.formatCurrency(salary.bonus || 0),
            `<strong>${ui.formatCurrency(salary.total_salary || 0)}</strong>`,
            `${salary.month}/${salary.year}`,
            `
                <button class="btn btn-sm btn-info" onclick="salaryModule.viewDetail(${salary.id})">
                    <i class="bi bi-eye"></i>
                </button>
                <button class="btn btn-sm btn-warning" onclick="salaryModule.showEditModal(${salary.id})">
                    <i class="bi bi-pencil"></i>
                </button>
            `
        ]);

        container.innerHTML = ui.generateTable(headers, rows);
    }

    async showAddModal() {
        // Load employees for select
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
            { name: 'base_salary', label: 'Lương cơ bản', type: 'number', required: true, placeholder: '20000000' },
            { name: 'allowances', label: 'Phụ cấp', type: 'number', defaultValue: '0', placeholder: '2000000' },
            { name: 'bonus', label: 'Thưởng', type: 'number', defaultValue: '0', placeholder: '3000000' },
            { name: 'deductions', label: 'Khấu trừ', type: 'number', defaultValue: '0', placeholder: '500000' },
            { name: 'salary_month', label: 'Tháng lương', type: 'date', required: true },
            { name: 'payment_date', label: 'Ngày trả lương', type: 'date', required: true },
            { 
                name: 'payment_status', 
                label: 'Trạng thái', 
                type: 'select', 
                required: true,
                options: [
                    { value: 'pending', label: 'Chờ thanh toán' },
                    { value: 'paid', label: 'Đã thanh toán' },
                    { value: 'cancelled', label: 'Đã hủy' }
                ]
            },
            { name: 'notes', label: 'Ghi chú', type: 'textarea', rows: 2 }
        ];

        modal.createFormModal('Thêm bảng lương', fields, async (formData) => {
            try {
                ui.showLoading();
                const response = await api.post('?resource=salaries', formData);
                
                if (response.success) {
                    ui.showToast('Thêm bảng lương thành công', 'success');
                    await this.loadSalaries();
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
        const salary = this.salaries.find(s => s.id === id);
        if (!salary) return;

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
            { name: 'base_salary', label: 'Lương cơ bản', type: 'number', required: true },
            { name: 'allowances', label: 'Phụ cấp', type: 'number', defaultValue: '0' },
            { name: 'bonus', label: 'Thưởng', type: 'number', defaultValue: '0' },
            { name: 'deductions', label: 'Khấu trừ', type: 'number', defaultValue: '0' },
            { name: 'salary_month', label: 'Tháng lương', type: 'date', required: true },
            { name: 'payment_date', label: 'Ngày trả lương', type: 'date', required: true },
            { 
                name: 'payment_status', 
                label: 'Trạng thái', 
                type: 'select', 
                required: true,
                options: [
                    { value: 'pending', label: 'Chờ thanh toán' },
                    { value: 'paid', label: 'Đã thanh toán' },
                    { value: 'cancelled', label: 'Đã hủy' }
                ]
            },
            { name: 'notes', label: 'Ghi chú', type: 'textarea', rows: 2 }
        ];

        modal.createFormModal('Cập nhật bảng lương', fields, async (formData) => {
            try {
                ui.showLoading();
                const response = await api.put(`?resource=salaries&id=${id}`, formData);
                
                if (response.success) {
                    ui.showToast('Cập nhật thành công', 'success');
                    await this.loadSalaries();
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
        }, salary);
    }

    viewDetail(id) {
        const salary = this.salaries.find(s => s.id === id);
        if (salary) {
            alert(`Chi tiết lương:\n\nNhân viên: ${salary.employee_name}\nLương cơ bản: ${ui.formatCurrency(salary.base_salary)}\nPhụ cấp: ${ui.formatCurrency(salary.allowances)}\nThưởng: ${ui.formatCurrency(salary.bonus)}\nTổng: ${ui.formatCurrency(salary.total_salary)}`);
        }
    }
}

const salaryModule = new SalaryModule();
window.salaryModule = salaryModule;
export default salaryModule;
