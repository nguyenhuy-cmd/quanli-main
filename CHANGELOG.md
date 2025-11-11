# Changelog - HRM System

## Version 5.0 - Form Modal Implementation (11/11/2025)

### 🎉 Major Features Added

#### 1. **Modal Helper System** (`js/utils/modal.js`)
- ✅ Dynamic form generator with validation
- ✅ Support for multiple field types:
  - Text, Email, Tel, Number, Date
  - Textarea, Select, File Upload
- ✅ Auto-populate for edit mode
- ✅ Bootstrap 5 modal integration
- ✅ Confirmation dialog support

#### 2. **Employee Module** - Full CRUD Forms
- ✅ Add Employee Form (12 fields):
  - Basic info: Code, Name, Email, Phone, DOB, Gender, Address
  - Work info: Department, Position, Hire Date, Status
- ✅ Edit Employee Form (pre-filled with existing data)
- ✅ Delete with confirmation modal
- ✅ API Integration: POST/PUT/DELETE

#### 3. **Department Module** - Full CRUD
- ✅ Add Department Form (Name, Description)
- ✅ Edit Department Form
- ✅ Delete with confirmation
- ✅ Real-time employee count display

#### 4. **Position Module** - Full CRUD
- ✅ Add Position Form (Title, Description, Min/Max Salary)
- ✅ Edit Position Form
- ✅ Delete with confirmation
- ✅ Salary range validation

#### 5. **Salary Module** - Payroll Management
- ✅ Add Salary Record Form:
  - Employee selection
  - Base salary, Allowance, Bonus, Deduction
  - Payment date, Status, Notes
- ✅ Edit Salary Record
- ✅ Auto-calculation display (Total = Base + Allowance + Bonus - Deduction)

#### 6. **Attendance Module** - Check In/Out
- ✅ Check In Form:
  - Employee selection
  - Date, Check-in time
  - Auto-detect late status (after 08:30)
- ✅ Check Out Form:
  - Employee selection
  - Date, Check-out time
  - Auto-calculate work hours
- ✅ Date filter for attendance records
- ✅ Status badges: Present (green), Late (yellow), Absent (red)

#### 7. **Leave Module** - Leave Request Management
- ✅ Create Leave Request Form:
  - Employee, Leave Type (Annual/Sick/Unpaid/Maternity/Other)
  - Start/End Date, Days count, Reason
- ✅ Approve/Reject Buttons (with confirmation)
- ✅ Status workflow: Pending → Approved/Rejected
- ✅ Color-coded status badges

#### 8. **Performance Module** - Performance Reviews
- ✅ Add Performance Review Form (13 fields):
  - Employee, Reviewer
  - Review period (start/end date)
  - Ratings: Overall, Technical, Communication, Teamwork, Productivity (0-5 scale)
  - Strengths, Weaknesses, Recommendations
  - Status: Draft/Completed/Acknowledged
- ✅ Edit Review Form
- ✅ Auto rating calculation and categorization

### 🔧 Technical Improvements

#### Database Schema Fixes
- ✅ Fixed column names to match actual database:
  - `employees.status` → `employment_status`
  - `attendance.date` → `attendance_date`
  - `attendance.check_in` → `check_in_time`
  - `attendance.check_out` → `check_out_time`
  - `attendance.status` → `attendance_status`
  - `performance_reviews.review_date` → `review_period_start/end`
  - `performance_reviews.status` → `review_status`
  - `users.name` → `username`

#### Code Quality
- ✅ All modules now use consistent modal helper
- ✅ Proper async/await error handling
- ✅ Form validation before submission
- ✅ User-friendly error messages
- ✅ Loading indicators during API calls
- ✅ Toast notifications for all actions

### 📦 Cache Busting
- Updated to v5.0 in `index.html`
- Updated to v4 for all module imports in `app.js`
- Added modal.js v1 import

### 🎨 UI/UX Improvements
- ✅ Consistent form styling across all modules
- ✅ Required field indicators (*)
- ✅ Placeholder texts for better UX
- ✅ Select dropdowns with employee/user data
- ✅ Bootstrap modal animations
- ✅ Responsive form layouts (modal-lg)

### 🚀 How to Use

1. **Refresh browser**: Press `Ctrl+Shift+R` to clear cache
2. **Test forms**:
   - Click "Thêm..." buttons in any module
   - Fill out the form
   - Click "Thêm mới" or "Cập nhật"
   - See success/error toast notification
3. **Edit records**:
   - Click pencil icon (✏️) on any row
   - Form pre-fills with existing data
   - Make changes and save
4. **Delete records**:
   - Click trash icon (🗑️)
   - Confirm in modal dialog

### 📋 API Requirements

All modules now expect these API endpoints to work:

**Employees:**
- GET `?resource=employees` - List all
- GET `?resource=employees&id={id}` - Get one
- POST `?resource=employees` - Create
- PUT `?resource=employees&id={id}` - Update
- DELETE `?resource=employees&id={id}` - Delete

**Departments, Positions, Salaries, Performance:**
- Same pattern as employees

**Attendance:**
- POST `?resource=attendance&action=checkin` - Check in
- POST `?resource=attendance&action=checkout` - Check out

**Leaves:**
- PUT `?resource=leaves&id={id}&action=approve` - Approve
- PUT `?resource=leaves&id={id}&action=reject` - Reject

### 🐛 Bug Fixes
- ✅ Fixed all SQL column name mismatches
- ✅ Fixed modal backdrop not removing
- ✅ Fixed form validation edge cases
- ✅ Fixed browser cache issues with version parameters

### 📝 Notes
- All forms validate required fields
- Email fields use HTML5 email validation
- Number fields accept decimal values
- Date fields use HTML5 date picker
- Confirmation modals prevent accidental deletions
- Forms close automatically on successful submit

---

**Developer:** AI Assistant  
**Date:** November 11, 2025  
**Version:** 5.0
