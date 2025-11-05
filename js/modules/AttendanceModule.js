import * as Api from '../api.js';
import { el, showToast } from '../utils.js';

export async function renderAttendanceModule(container){
  container.innerHTML = '';
  container.appendChild(el('h3', {}, 'Chấm công'));

  // Load employees for dropdown
  let employees = [];
  try {
    employees = await Api.get('employees');
  } catch(err) {
    showToast('Không thể tải danh sách nhân viên', {type:'error'});
  }

  const employeeSelect = el('select', {name:'employee_id', required:true});
  employeeSelect.appendChild(el('option', {value:'', disabled:true, selected:true}, '-- Chọn nhân viên --'));
  employees.forEach(emp => {
    employeeSelect.appendChild(el('option', {value:emp.id}, `${emp.name} (${emp.email || emp.id})`));
  });

  const form = el('form', {},
    el('div', {class:'form-row'}, 
      employeeSelect,
      el('input', {name:'date', type:'date', required:true, value: new Date().toISOString().split('T')[0]})
    ),
    el('div', {class:'form-row'}, 
      el('select', {name:'status', required:true}, 
        el('option', {value:'present'}, 'Có mặt'),
        el('option', {value:'absent'}, 'Vắng mặt'),
        el('option', {value:'leave'}, 'Nghỉ phép')
      )
    ),
    el('div', {}, el('button', {type:'submit', class:'btn'}, 'Thêm chấm công'))
  );

  const list = el('div');
  container.appendChild(form);
  container.appendChild(list);

  async function load(){
    try {
      const data = await Api.get('attendance');
      list.innerHTML = '';
      if(!Array.isArray(data) || data.length===0){ 
        list.appendChild(el('div', {style:'padding:20px;text-align:center;color:#666'}, 'Chưa có dữ liệu chấm công')); 
        return; 
      }
      
      // Create employee map for quick lookup
      const empMap = {};
      employees.forEach(e => empMap[e.id] = e);
      
      const table = el('table');
      table.appendChild(el('thead', {}, 
        el('tr', {}, 
          el('th', {}, 'Nhân viên'),
          el('th', {}, 'Ngày'),
          el('th', {}, 'Trạng thái')
        )
      ));
      const tbody = el('tbody');
      
      data.forEach(a=> {
        const emp = empMap[a.employee_id];
        const empName = emp ? emp.name : `NV #${a.employee_id}`;
        const statusText = a.status === 'present' ? 'Có mặt' : 
                          a.status === 'absent' ? 'Vắng mặt' : 
                          a.status === 'leave' ? 'Nghỉ phép' : a.status;
        const statusClass = a.status === 'present' ? 'badge-success' : 
                           a.status === 'absent' ? 'badge-danger' : 'badge-warning';
        
        tbody.appendChild(el('tr', {}, 
          el('td', {}, empName),
          el('td', {}, a.date),
          el('td', {}, el('span', {class:`badge ${statusClass}`}, statusText))
        ));
      });
      
      table.appendChild(tbody);
      list.appendChild(table);
    } catch(err) {
      list.innerHTML = `<div style="color:red;padding:20px">Lỗi: ${err.message}</div>`;
    }
  }

  form.addEventListener('submit', async e=>{
    e.preventDefault();
    const fd = new FormData(form);
    const payload = {
      employee_id: fd.get('employee_id'), 
      date: fd.get('date'), 
      status: fd.get('status')
    };
    try {
      await Api.post('attendance', payload);
      showToast('Thêm chấm công thành công', {type:'success'});
      form.reset();
      // Reset date to today
      form.querySelector('[name="date"]').value = new Date().toISOString().split('T')[0];
      await load();
    } catch(err) {
      showToast(err.message || 'Thêm chấm công thất bại', {type:'error'});
    }
  });

  await load();
}
