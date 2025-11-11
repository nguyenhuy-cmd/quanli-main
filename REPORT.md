# BÁO CÁO DỰ ÁN HRM SYSTEM

## Thông tin dự án
- **Tên dự án**: Human Resource Management System (HRM)
- **Người thực hiện**: Nguyễn Huy
- **Thời gian**: Tháng 11/2025
- **Công nghệ**: Vanilla JavaScript, PHP 8+, MySQL, Bootstrap 5

---

## 1. TỔNG QUAN DỰ ÁN

### 1.1. Mục tiêu
Xây dựng một ứng dụng quản lý nhân sự hoàn chỉnh với:
- Frontend sử dụng JavaScript thuần (ES6+), không framework
- Backend PHP theo mô hình MVC và OOP
- Database MySQL với các bảng liên kết
- RESTful API cho giao tiếp frontend-backend
- CI/CD tự động với GitHub Actions

### 1.2. Phạm vi
Ứng dụng bao gồm 12+ modules chính:
1. **Authentication Module**: Đăng nhập/Đăng ký/Đăng xuất
2. **Dashboard Module**: Hiển thị thống kê tổng quan
3. **Employee Management**: CRUD nhân viên, tìm kiếm
4. **Department Management**: Quản lý phòng ban
5. **Position Management**: Quản lý vị trí công việc
6. **Salary Management**: Quản lý lương, tính toán
7. **Attendance Management**: Chấm công, báo cáo
8. **Leave Management**: Quản lý nghỉ phép, phê duyệt
9. **Performance Management**: Đánh giá hiệu suất

### 1.3. Kiến trúc hệ thống
```
┌─────────────────────────────────────────────┐
│           Frontend (Browser)                │
│   Vanilla JS + ES6+ + Bootstrap 5          │
│   - Modules (import/export)                │
│   - Async/await (fetch API)                │
│   - DOM Manipulation                       │
└──────────────┬──────────────────────────────┘
               │ HTTP/JSON
               ▼
┌─────────────────────────────────────────────┐
│        Backend API (PHP 8+)                │
│   - MVC Pattern                            │
│   - OOP (Classes, Inheritance)             │
│   - RESTful API                            │
│   - JWT Authentication                     │
└──────────────┬──────────────────────────────┘
               │ PDO
               ▼
┌─────────────────────────────────────────────┐
│         Database (MySQL)                    │
│   - 8 tables with relationships            │
│   - Foreign Keys                           │
│   - Views for reporting                    │
└─────────────────────────────────────────────┘
```

---

## 2. TRIỂN KHAI CÁC MODULE

### 2.1. Frontend Modules

#### 2.1.1. AuthModule (js/modules/authModule.js)
**Chức năng**:
- Xử lý đăng nhập/đăng ký
- Quản lý JWT token (localStorage)
- Kiểm tra authentication status
- Modal switching (login ↔ register)

**Kỹ thuật sử dụng**:
- ES6 Class
- Async/await cho API calls
- Event listeners
- Bootstrap Modal API
- Form validation

**Thách thức**:
- Xử lý token expiration
- Secure token storage (hiện tại localStorage, production cần HttpOnly cookies)
- Cross-tab authentication sync

#### 2.1.2. EmployeeModule (js/modules/employeeModule.js)
**Chức năng**:
- Hiển thị danh sách nhân viên (table)
- Tìm kiếm real-time
- CRUD operations (Create, Read, Update, Delete)
- View employee details

**Kỹ thuật sử dụng**:
- Dynamic table generation
- Debouncing cho search input
- Confirmation dialogs
- Toast notifications

**Thách thức**:
- Pagination cho dataset lớn (chưa implement)
- Form validation phức tạp
- File upload cho avatar (chưa implement)

#### 2.1.3. DashboardModule (js/modules/dashboardModule.js)
**Chức năng**:
- Hiển thị thống kê: tổng nhân viên, phòng ban, nghỉ phép chờ duyệt
- Chấm công hôm nay
- Nhân viên mới
- Thống kê theo phòng ban

**Kỹ thuật sử dụng**:
- Multiple parallel API calls
- Data aggregation
- Dynamic card rendering
- Stat cards với gradient backgrounds

**Thách thức**:
- Performance với nhiều API calls
- Real-time data update (chưa implement WebSocket)
- Chart visualization (chưa có, có thể dùng Chart.js)

#### 2.1.4. API Service (js/services/api.js)
**Chức năng**:
- Centralized API handling
- Automatic token injection
- Error handling
- Request/response interceptors

**Kỹ thuật sử dụng**:
- Fetch API
- Promise-based
- Singleton pattern

#### 2.1.5. UI Utility (js/utils/ui.js)
**Chức năng**:
- Toast notifications (Bootstrap Toast)
- Loading spinner
- Confirm dialogs
- Date/Currency formatting
- Table generation helpers
- Empty state rendering

**Kỹ thuật sử dụng**:
- Helper functions
- Reusable components
- Intl API for formatting

### 2.2. Backend Modules

#### 2.2.1. Base Classes

**Model.php (backend/models/Model.php)**:
- Abstract base class cho tất cả models
- CRUD methods: `getAll()`, `getById()`, `create()`, `update()`, `delete()`
- Advanced methods: `search()`, `count()`, `query()`
- Transaction support

**Controller.php (backend/controllers/Controller.php)**:
- Abstract base class cho controllers
- Response helpers: `sendSuccess()`, `sendError()`
- Authentication: `checkAuth()`, `requireAuth()`
- JWT encode/decode (simple implementation)
- Input validation và sanitization

**Lợi ích của Base Classes**:
- Code reuse
- Consistency
- DRY principle
- Easy to extend

#### 2.2.2. Specific Models

**UserModel.php**:
- `findByEmail()`: Tìm user theo email
- `register()`: Đăng ký user mới (hash password)
- `verifyCredentials()`: Xác thực login
- `updatePassword()`: Đổi mật khẩu

**EmployeeModel.php**:
- `getAllWithDetails()`: JOIN với departments, positions
- `searchEmployees()`: Tìm kiếm theo keyword
- `generateEmployeeCode()`: Auto-generate mã NV
- `getStatistics()`: Thống kê nhân viên

**DepartmentModel.php, PositionModel.php, SalaryModel.php, AttendanceModel.php, LeaveModel.php, PerformanceModel.php**:
- Tương tự, mỗi model có methods đặc thù
- Kế thừa từ Base Model
- Implement business logic riêng

#### 2.2.3. Controllers

Mỗi controller xử lý HTTP requests cho resource tương ứng:
- **AuthController**: Login, Register, Logout, Get current user
- **EmployeeController**: CRUD employees, Search, Statistics
- **DepartmentController, PositionController, etc.**: Tương tự

**Pattern chung**:
```php
public function handle() {
    $method = $this->getMethod();
    $id = $_GET['id'] ?? null;
    
    $this->requireAuth(); // Require authentication
    
    switch ($method) {
        case 'GET': ...
        case 'POST': ...
        case 'PUT': ...
        case 'DELETE': ...
    }
}
```

#### 2.2.4. API Router (backend/api.php)

Routes requests đến appropriate controller:
- Parse `resource` parameter
- Load corresponding controller
- Handle exceptions
- Return JSON response

**URL Format**: `/backend/api.php?resource=employees&id=1&action=search`

### 2.3. Database Design

#### Bảng chính:
1. **users**: Tài khoản đăng nhập
2. **employees**: Thông tin nhân viên
3. **departments**: Phòng ban
4. **positions**: Vị trí công việc
5. **salaries**: Lương
6. **attendance**: Chấm công
7. **leaves**: Nghỉ phép
8. **performance_reviews**: Đánh giá hiệu suất

#### Foreign Keys:
- `employees.user_id` → `users.id`
- `employees.department_id` → `departments.id`
- `employees.position_id` → `positions.id`
- `salaries.employee_id` → `employees.id`
- etc.

#### Views:
- `vw_employee_details`: Thông tin nhân viên đầy đủ
- `vw_monthly_salary_report`: Báo cáo lương
- `vw_attendance_report`: Báo cáo chấm công
- `vw_performance_stats`: Thống kê hiệu suất

---

## 3. THÁCH THỨC & GIẢI PHÁP

### 3.1. Frontend Challenges

**Challenge 1: Module organization**
- **Vấn đề**: Tổ chức code thành modules mà không dùng bundler (Webpack, Vite)
- **Giải pháp**: Sử dụng ES6 modules với `<script type="module">`
- **Kết quả**: Code clean, dễ maintain

**Challenge 2: State management**
- **Vấn đề**: Không có Redux/Vuex
- **Giải pháp**: Singleton pattern cho services, localStorage cho auth
- **Kết quả**: Đơn giản nhưng đủ dùng

**Challenge 3: Routing**
- **Vấn đề**: Không có Vue Router, React Router
- **Giải pháp**: Manual routing qua event listeners và `loadModule()`
- **Kết quả**: Lightweight, hoạt động tốt

### 3.2. Backend Challenges

**Challenge 1: MVC without framework**
- **Vấn đề**: Implement MVC từ đầu
- **Giải pháp**: Tạo base classes (Model, Controller), structure thư mục rõ ràng
- **Kết quả**: MVC pattern hoạt động tốt

**Challenge 2: JWT implementation**
- **Vấn đề**: Implement JWT mà không dùng library
- **Giải pháp**: Simple JWT với HMAC-SHA256
- **Lưu ý**: Production nên dùng library như `firebase/php-jwt`

**Challenge 3: CORS**
- **Vấn đề**: CORS errors khi gọi API từ frontend
- **Giải pháp**: Thêm CORS headers trong `config.php`
- **Kết quả**: Frontend gọi API thành công

### 3.3. Database Challenges

**Challenge 1: Foreign key constraints**
- **Vấn đề**: Circular dependency (departments ↔ employees)
- **Giải pháp**: Tạo bảng trước, add FK sau
- **Kết quả**: Schema nhất quán

**Challenge 2: Sample data**
- **Vấn đề**: Tạo dữ liệu mẫu có quan hệ
- **Giải pháp**: INSERT theo thứ tự (users → departments → positions → employees → ...)
- **Kết quả**: Database có data để test

---

## 4. KIỂM TRA & TESTING

### 4.1. Manual Testing

**Đã test**:
- ✅ Đăng nhập/Đăng ký
- ✅ View dashboard
- ✅ Xem danh sách nhân viên
- ✅ Tìm kiếm nhân viên
- ✅ API responses (via browser DevTools)

**Chưa test đầy đủ**:
- ⏳ CRUD cho tất cả modules
- ⏳ Edge cases (empty data, invalid input)
- ⏳ Performance với large datasets

### 4.2. Testing Tools Used

- **Browser DevTools**: Console, Network tab
- **Postman/Thunder Client**: Test API endpoints
- **phpMyAdmin**: Check database

### 4.3. Known Issues

1. **File upload**: Chưa implement upload avatar cho employees
2. **Pagination**: Chưa có pagination, performance issue với nhiều records
3. **Real-time updates**: Chưa có WebSocket/polling
4. **Mobile responsive**: Cần test kỹ hơn trên mobile
5. **Security**: JWT lưu localStorage (production cần HttpOnly cookies)

---

## 5. CI/CD VỚI GITHUB ACTIONS

### 5.1. Workflow

File `.github/workflows/deploy.yml`:
- **Trigger**: Push to `main` or `new` branch
- **Steps**:
  1. Checkout code
  2. Deploy qua FTP (sử dụng `SamKirkland/FTP-Deploy-Action`)
  3. Exclude files không cần deploy (.git, README, etc.)

### 5.2. Secrets

Configured in GitHub:
- `FTP_SERVER`
- `FTP_USERNAME`
- `FTP_PASSWORD`

### 5.3. Deployment Target

- **Server**: ftpupload.net hoặc hosting khác
- **Directory**: `/htdocs/`
- **URL**: https://huy12345.click/

---

## 6. KẾT LUẬN

### 6.1. Đạt được

✅ **Frontend**:
- Vanilla JavaScript với ES6+ features
- Modules, async/await, DOM manipulation
- Bootstrap 5 UI
- 3+ feature modules

✅ **Backend**:
- PHP OOP với MVC pattern
- Base classes (Model, Controller)
- 8+ models, 8+ controllers
- RESTful API

✅ **Database**:
- MySQL với 8 tables
- Foreign keys, relationships
- Views for reporting
- Sample data

✅ **CI/CD**:
- GitHub Actions auto-deploy
- FTP deployment

### 6.2. Học được

1. **Frontend**:
   - ES6 modules organization
   - Fetch API và async/await
   - State management đơn giản
   - Bootstrap components

2. **Backend**:
   - MVC pattern implementation
   - OOP PHP (classes, inheritance)
   - PDO và prepared statements
   - RESTful API design
   - JWT authentication basics

3. **Full-stack**:
   - Frontend-backend communication
   - CORS handling
   - Error handling
   - Code organization

### 6.3. Cải thiện trong tương lai

1. **Frontend**:
   - Add framework (React/Vue) cho complex UI
   - Implement pagination
   - Add charts/visualization
   - Better form validation
   - File upload

2. **Backend**:
   - Use proper JWT library
   - Add middleware system
   - Implement caching (Redis)
   - Better error handling
   - API rate limiting

3. **Database**:
   - Optimize queries
   - Add indexes
   - Implement backup strategy

4. **Security**:
   - HTTPS
   - HttpOnly cookies for token
   - CSRF protection
   - Input sanitization nâng cao
   - SQL injection prevention (đã có với PDO)

5. **DevOps**:
   - Docker containerization
   - Automated testing (PHPUnit, Jest)
   - Monitoring & logging
   - Performance optimization

### 6.4. Kết luận

Dự án HRM System đã hoàn thành các mục tiêu chính:
- ✅ Full-stack application với vanilla JavaScript và PHP OOP
- ✅ MVC pattern
- ✅ RESTful API
- ✅ MySQL database với relationships
- ✅ Bootstrap UI
- ✅ CI/CD với GitHub Actions

Ứng dụng có thể chạy trên XAMPP local hoặc hosting, phục vụ mục đích học tập và demo. Với các cải thiện đề xuất, ứng dụng có thể scale lên production-ready system.

---

**Ngày hoàn thành**: 11/11/2025  
**Tổng thời gian**: ~16 giờ  
**Lines of code**: ~3000+ lines (Frontend JS + Backend PHP + SQL)

**Tác giả**: Nguyễn Huy  
**GitHub**: https://github.com/nguyenhuy-cmd/quanli-main
