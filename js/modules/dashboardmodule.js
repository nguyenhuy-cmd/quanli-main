/**
 * Dashboard Module
 * Displays statistics and overview
 */

import api from '../services/api.js';
import ui from '../utils/ui.js';

class DashboardModule {
    constructor() {
        this.stats = null;
    }

    /**
     * Render dashboard
     */
    async render() {
        ui.setPageTitle('Dashboard');
        const mainContent = document.getElementById('mainContent');
        
        mainContent.innerHTML = `
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-card primary">
                        <h3 id="totalEmployees">0</h3>
                        <p><i class="bi bi-people"></i> Tổng nhân viên</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card success">
                        <h3 id="activeDepartments">0</h3>
                        <p><i class="bi bi-building"></i> Phòng ban</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card warning">
                        <h3 id="pendingLeaves">0</h3>
                        <p><i class="bi bi-calendar-x"></i> Nghỉ phép chờ duyệt</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card danger">
                        <h3 id="averageRating">0</h3>
                        <p><i class="bi bi-star"></i> Đánh giá trung bình</p>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Chấm công hôm nay</h5>
                        </div>
                        <div class="card-body" id="todayAttendance">
                            <div class="text-center text-muted">
                                <i class="bi bi-clock-history" style="font-size: 3rem;"></i>
                                <p>Đang tải...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Nhân viên mới</h5>
                        </div>
                        <div class="card-body" id="recentEmployees">
                            <div class="text-center text-muted">
                                <i class="bi bi-person-plus" style="font-size: 3rem;"></i>
                                <p>Đang tải...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Thống kê theo phòng ban</h5>
                        </div>
                        <div class="card-body" id="departmentStats">
                            <div class="text-center text-muted">
                                <i class="bi bi-bar-chart" style="font-size: 3rem;"></i>
                                <p>Đang tải...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Load statistics
        await this.loadStatistics();
    }

    /**
     * Load all statistics
     */
    async loadStatistics() {
        try {
            ui.showLoading();
            
            // Load employee statistics
            const empResponse = await api.get('?resource=employees&action=statistics');
            if (empResponse.success) {
                document.getElementById('totalEmployees').textContent = empResponse.data.total_employees || 0;
            }

            // Load department count
            const deptResponse = await api.get('?resource=departments');
            if (deptResponse.success) {
                document.getElementById('activeDepartments').textContent = deptResponse.data.length || 0;
            }

            // Load pending leaves
            const leaveResponse = await api.get('?resource=leaves');
            if (leaveResponse.success) {
                const pending = leaveResponse.data.filter(l => l.status === 'pending');
                document.getElementById('pendingLeaves').textContent = pending.length || 0;
            }

            // Load average rating from performance reviews
            const perfResponse = await api.get('?resource=performance');
            if (perfResponse.success && perfResponse.data.length > 0) {
                const totalRating = perfResponse.data.reduce((sum, p) => sum + parseFloat(p.rating || 0), 0);
                const avgRating = totalRating / perfResponse.data.length;
                document.getElementById('averageRating').textContent = avgRating.toFixed(1);
            } else {
                document.getElementById('averageRating').textContent = '0';
            }

            // Load today's attendance
            await this.loadTodayAttendance();

            // Load recent employees
            await this.loadRecentEmployees();

            // Load department statistics
            await this.loadDepartmentStats();

        } catch (error) {
            console.error('Error loading statistics:', error);
            ui.showToast('Không thể tải thống kê', 'error');
        } finally {
            ui.hideLoading();
        }
    }

    /**
     * Load today's attendance
     */
    async loadTodayAttendance() {
        try {
            const today = new Date().toISOString().split('T')[0];
            const response = await api.get(`?resource=attendance&date=${today}`);
            
            if (response.success) {
                const todayRecords = response.data;
                const container = document.getElementById('todayAttendance');
                
                if (todayRecords.length === 0) {
                    container.innerHTML = '<p class="text-muted">Chưa có dữ liệu chấm công hôm nay</p>';
                } else {
                    const present = todayRecords.filter(a => a.status === 'present').length;
                    const late = todayRecords.filter(a => a.status === 'late').length;
                    const absent = todayRecords.filter(a => a.status === 'absent').length;
                    
                    container.innerHTML = `
                        <div class="row text-center">
                            <div class="col-4">
                                <h4 class="text-success">${present}</h4>
                                <p>Đúng giờ</p>
                            </div>
                            <div class="col-4">
                                <h4 class="text-warning">${late}</h4>
                                <p>Muộn</p>
                            </div>
                            <div class="col-4">
                                <h4 class="text-danger">${absent}</h4>
                                <p>Vắng</p>
                            </div>
                        </div>
                    `;
                }
            }
        } catch (error) {
            console.error('Error loading attendance:', error);
        }
    }

    /**
     * Load recent employees
     */
    async loadRecentEmployees() {
        try {
            const response = await api.get('?resource=employees');
            
            if (response.success) {
                const recent = response.data.slice(0, 5);
                const container = document.getElementById('recentEmployees');
                
                if (recent.length === 0) {
                    container.innerHTML = '<p class="text-muted">Chưa có nhân viên mới</p>';
                } else {
                    let html = '<ul class="list-group list-group-flush">';
                    recent.forEach(emp => {
                        html += `
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${emp.full_name}</strong>
                                    <br>
                                    <small class="text-muted">${emp.position_title || 'N/A'}</small>
                                </div>
                                <span class="badge bg-primary rounded-pill">${emp.employee_code}</span>
                            </li>
                        `;
                    });
                    html += '</ul>';
                    container.innerHTML = html;
                }
            }
        } catch (error) {
            console.error('Error loading recent employees:', error);
        }
    }

    /**
     * Load department statistics
     */
    async loadDepartmentStats() {
        try {
            const response = await api.get('?resource=departments');
            
            if (response.success) {
                const container = document.getElementById('departmentStats');
                
                if (response.data.length === 0) {
                    container.innerHTML = '<p class="text-muted">Chưa có dữ liệu phòng ban</p>';
                } else {
                    let html = '<table class="table table-striped"><thead><tr><th>Phòng ban</th><th>Số nhân viên</th></tr></thead><tbody>';
                    response.data.forEach(dept => {
                        html += `
                            <tr>
                                <td>${dept.name}</td>
                                <td><span class="badge bg-info">${dept.employee_count || 0}</span></td>
                            </tr>
                        `;
                    });
                    html += '</tbody></table>';
                    container.innerHTML = html;
                }
            }
        } catch (error) {
            console.error('Error loading department stats:', error);
        }
    }
}

export default new DashboardModule();
