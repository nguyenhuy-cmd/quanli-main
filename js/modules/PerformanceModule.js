import * as Api from '../api.js';
import { el, showToast } from '../utils.js';

export async function renderPerformanceModule(container){
  container.innerHTML = '';
  container.appendChild(el('h3', {}, 'Đánh giá hiệu suất'));

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
      el('input', {name:'score', placeholder:'Điểm (0-100)', type:'number', min:0, max:100, required:true})
    ),
    el('div', {}, 
      el('textarea', {name:'note', placeholder:'Ghi chú đánh giá...', rows:4, style:'width:100%;padding:8px;border:1px solid #ddd;border-radius:4px'})
    ),
    el('div', {}, el('button', {type:'submit', class:'btn'}, 'Thêm đánh giá'))
  );

  const list = el('div');
  container.appendChild(form);
  container.appendChild(list);

  async function load(){
    try {
      const data = await Api.get('reviews');
      list.innerHTML = '';
      
      // Create employee map for quick lookup
      const empMap = {};
      employees.forEach(e => empMap[e.id] = e);
      
      const table = el('table');
      table.appendChild(el('thead', {}, 
        el('tr', {}, 
          el('th', {}, 'Nhân viên'),
          el('th', {}, 'Điểm'),
          el('th', {}, 'Ghi chú'),
          el('th', {}, 'Ngày đánh giá')
        )
      ));
      const tbody = el('tbody');
      
      if(!Array.isArray(data) || data.length===0){
        tbody.appendChild(el('tr', {}, 
          el('td', {colspan:4, style:'text-align:center;padding:20px;color:#666'}, 'Chưa có dữ liệu đánh giá')
        ));
      } else {
        data.forEach(r=> {
          const emp = empMap[r.employee_id];
          const empName = emp ? emp.name : `NV #${r.employee_id}`;
          
          // Score badge with color
          const scoreClass = r.score >= 80 ? 'badge-success' : 
                           r.score >= 60 ? 'badge-warning' : 'badge-danger';
          
          tbody.appendChild(el('tr', {}, 
            el('td', {}, empName),
            el('td', {}, el('span', {class:`badge ${scoreClass}`, style:'font-size:14px;padding:4px 8px'}, r.score + '/100')),
            el('td', {}, r.note || '-'),
            el('td', {}, r.created_at ? new Date(r.created_at).toLocaleDateString('vi-VN') : '-')
          ));
        });
      }
      
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
      score: fd.get('score'), 
      note: fd.get('note')
    };
    
    try {
      await Api.post('reviews', payload);
      showToast('Thêm đánh giá thành công', {type:'success'});
      form.reset();
      await load();
    } catch(err) {
      showToast(err.message || 'Thêm đánh giá thất bại', {type:'error'});
    }
  });

  await load();
}
