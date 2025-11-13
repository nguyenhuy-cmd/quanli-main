/**
 * Performance Module
 */
import api from '../services/api.js';
import ui from '../utils/ui.js';
import modal from '../utils/modal.js';

class PerformanceModule {
    constructor() {
        this.performances = [];
    }

    async render() {
        ui.setPageTitle('Đánh giá Hiệu suất');
        const mainContent = document.getElementById('mainContent');
        
        mainContent.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Bảng đánh giá hiệu suất</h4>
                <button class="btn btn-primary" onclick="performanceModule.showAddModal()">
                    <i class="bi bi-plus-circle"></i> Thêm đánh giá
                </button>
            </div>

            <div class="card">
                <div class="card-body">
                    <div id="performanceTableContainer">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status"></div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        await this.loadPerformances();
    }

    async loadPerformances() {
        try {
            ui.showLoading();
            const response = await api.get('?resource=performance');
            
            if (response.success) {
                this.performances = response.data;
                this.renderTable();
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('performanceTableContainer').innerHTML = `
                <div class="text-center text-muted py-5">
                    <i class="bi bi-star" style="font-size: 3rem;"></i>
                    <p>Chưa có đánh giá hiệu suất nào</p>
                </div>
            `;
        } finally {
            ui.hideLoading();
        }
    }

    renderTable() {
        const container = document.getElementById('performanceTableContainer');
        
        if (this.performances.length === 0) {
            container.innerHTML = `
                <div class="text-center text-muted py-5">
                    <i class="bi bi-star" style="font-size: 3rem;"></i>
                    <p>Chưa có đánh giá hiệu suất nào</p>
                </div>
            `;
            return;
        }

        const headers = ['ID', 'Nhân viên', 'Kỳ đánh giá', 'Điểm', 'Xếp loại', 'Người đánh giá', 'Ngày đánh giá', 'Thao tác'];
        const rows = this.performances.map(perf => {
            let ratingBadge = '';
            const score = parseFloat(perf.rating || 0);
            if (score >= 9) ratingBadge = '<span class="badge bg-success">Xuất sắc</span>';
            else if (score >= 8) ratingBadge = '<span class="badge bg-primary">Giỏi</span>';
            else if (score >= 7) ratingBadge = '<span class="badge bg-info">Khá</span>';
            else if (score >= 5) ratingBadge = '<span class="badge bg-warning">Trung bình</span>';
            else ratingBadge = '<span class="badge bg-danger">Yếu</span>';

            return [
                perf.id,
                perf.employee_name || 'N/A',
                perf.review_period || 'N/A',
                `<strong>${score.toFixed(1)}/10</strong>`,
                ratingBadge,
                perf.reviewer_name || 'N/A',
                ui.formatDate(perf.review_date),
                `
                    <button class="btn btn-sm btn-info" onclick="performanceModule.viewDetail(${perf.id})">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="performanceModule.showEditModal(${perf.id})">
                        <i class="bi bi-pencil"></i>
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
                name: 'reviewer_id', 
                label: 'Người đánh giá (ID)', 
                type: 'number', 
                required: true,
                placeholder: '1'
            },
            { name: 'period_start', label: 'Từ ngày', type: 'date', required: true },
            { name: 'period_end', label: 'Đến ngày', type: 'date', required: true },
            { name: 'rating', label: 'Điểm tổng (1-5)', type: 'number', required: true, placeholder: '4' },
            { name: 'strengths', label: 'Điểm mạnh', type: 'textarea', rows: 3 },
            { name: 'weaknesses', label: 'Điểm cần cải thiện', type: 'textarea', rows: 3 },
            { name: 'goals', label: 'Mục tiêu phát triển', type: 'textarea', rows: 3 },
            { name: 'comments', label: 'Nhận xét', type: 'textarea', rows: 3 },
            { 
                name: 'status', 
                label: 'Trạng thái', 
                type: 'select', 
                required: true,
                options: [
                    { value: 'draft', label: 'Nháp' },
                    { value: 'completed', label: 'Hoàn thành' },
                    { value: 'acknowledged', label: 'Đã xác nhận' }
                ]
            }
        ];

        modal.createFormModal('Thêm đánh giá hiệu suất', fields, async (formData) => {
            try {
                ui.showLoading();
                const response = await api.post('?resource=performance', formData);
                
                if (response.success) {
                    ui.showToast('Thêm đánh giá thành công', 'success');
                    await this.loadPerformances();
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
        const perf = this.performances.find(p => p.id === id);
        if (!perf) return;

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
                name: 'reviewer_id', 
                label: 'Người đánh giá (ID)', 
                type: 'number', 
                required: true
            },
            { name: 'period_start', label: 'Từ ngày', type: 'date', required: true },
            { name: 'period_end', label: 'Đến ngày', type: 'date', required: true },
            { name: 'rating', label: 'Điểm tổng (1-5)', type: 'number', required: true },
            { name: 'strengths', label: 'Điểm mạnh', type: 'textarea', rows: 3 },
            { name: 'weaknesses', label: 'Điểm cần cải thiện', type: 'textarea', rows: 3 },
            { name: 'goals', label: 'Mục tiêu phát triển', type: 'textarea', rows: 3 },
            { name: 'comments', label: 'Nhận xét', type: 'textarea', rows: 3 },
            { 
                name: 'status', 
                label: 'Trạng thái', 
                type: 'select', 
                required: true,
                options: [
                    { value: 'draft', label: 'Nháp' },
                    { value: 'completed', label: 'Hoàn thành' },
                    { value: 'acknowledged', label: 'Đã xác nhận' }
                ]
            }
        ];

        modal.createFormModal('Cập nhật đánh giá', fields, async (formData) => {
            try {
                ui.showLoading();
                const response = await api.put(`?resource=performance&id=${id}`, formData);
                
                if (response.success) {
                    ui.showToast('Cập nhật đánh giá thành công', 'success');
                    await this.loadPerformances();
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
        }, perf);
    }

    viewDetail(id) {
        const perf = this.performances.find(p => p.id === id);
        if (perf) {
            alert(`Chi tiết đánh giá:\n\nNhân viên: ${perf.employee_name}\nKỳ đánh giá: ${perf.review_period}\nĐiểm: ${perf.rating}/10\nNhận xét: ${perf.comments || 'Không có'}`);
        }
    }
}

const performanceModule = new PerformanceModule();
window.performanceModule = performanceModule;
export default performanceModule;
