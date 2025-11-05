import * as Api from '../api.js';
import { el, setInlineError, clearInlineError, showToast, showConfirm } from '../utils.js';

export async function renderLeaveModule(container){
  container.innerHTML = '';
  container.appendChild(el('h3', {}, 'Quản lý nghỉ phép'));

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
      employeeSelect
    ),
    el('div', {class:'form-row'}, 
      el('div', {style:'flex:1'}, 
        el('label', {style:'display:block;margin-bottom:4px;font-size:0.9em;color:#666'}, 'Ngày bắt đầu'),
        el('input', {name:'start_date', type:'date', required:true})
      ),
      el('div', {style:'flex:1'}, 
        el('label', {style:'display:block;margin-bottom:4px;font-size:0.9em;color:#666'}, 'Ngày kết thúc'),
        el('input', {name:'end_date', type:'date', required:true})
      )
    ),
    el('div', {class:'form-row'}, 
      el('select', {name:'status', required:true}, 
        el('option', {value:'pending'}, 'Chờ duyệt'),
        el('option', {value:'approved'}, 'Đã duyệt'),
        el('option', {value:'rejected'}, 'Từ chối')
      )
    ),
    el('div', {}, el('button', {type:'submit', class:'btn'}, 'Tạo yêu cầu nghỉ phép'))
  );

  const list = el('div');
  container.appendChild(form);
  container.appendChild(list);

  async function load(){
    try {
      const data = await Api.get('leaves');
      list.innerHTML = '';
      
      if(!Array.isArray(data) || data.length===0){ 
        list.appendChild(el('div', {style:'padding:20px;text-align:center;color:#666'}, 'Chưa có yêu cầu nghỉ phép nào')); 
        return; 
      }
      
      // Create employee map for quick lookup
      const empMap = {};
      employees.forEach(e => empMap[e.id] = e);
      
      const table = el('table');
      table.appendChild(el('thead', {}, 
        el('tr', {}, 
          el('th', {}, 'Nhân viên'),
          el('th', {}, 'Từ ngày'),
          el('th', {}, 'Đến ngày'),
          el('th', {}, 'Số ngày'),
          el('th', {}, 'Trạng thái'),
          el('th', {}, 'Thao tác')
        )
      ));
      const tbody = el('tbody');
      
      data.forEach(l=> {
        const emp = empMap[l.employee_id];
        const empName = emp ? emp.name : `NV #${l.employee_id}`;
        
        // Calculate number of days
        const startDate = new Date(l.start_date);
        const endDate = new Date(l.end_date);
        const daysDiff = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
        
        // Status badge with color
        let statusText = l.status;
        let statusClass = 'badge-info';
        
        if(l.status === 'pending') {
          statusText = 'Chờ duyệt';
          statusClass = 'badge-warning';
        } else if(l.status === 'approved') {
          statusText = 'Đã duyệt';
          statusClass = 'badge-success';
        } else if(l.status === 'rejected') {
          statusText = 'Từ chối';
          statusClass = 'badge-danger';
        }
        
        // Action buttons
        const actions = el('div', {style:'display:flex;gap:4px'});
        
        if(l.status === 'pending') {
          const approveBtn = el('button', {
            class:'btn-small btn-success',
            type:'button',
            title:'Duyệt'
          }, '✓');
          approveBtn.addEventListener('click', async () => {
            const confirmed = await showConfirm('Bạn có chắc muốn duyệt yêu cầu nghỉ phép này không?', {
              title: 'Duyệt yêu cầu',
              okText: 'Duyệt',
              cancelText: 'Hủy'
            });
            
            if(confirmed) {
              try {
                await Api.put(`leaves`, {id: l.id, status: 'approved'});
                showToast('Đã duyệt yêu cầu', {type:'success'});
                await load();
              } catch(err) {
                showToast(err.message || 'Không thể duyệt', {type:'error'});
              }
            }
          });
          
          const rejectBtn = el('button', {
            class:'btn-small btn-danger',
            type:'button',
            title:'Từ chối'
          }, '✗');
          rejectBtn.addEventListener('click', async () => {
            const confirmed = await showConfirm('Bạn có chắc muốn từ chối yêu cầu nghỉ phép này không?', {
              title: 'Từ chối yêu cầu',
              okText: 'Từ chối',
              cancelText: 'Hủy'
            });
            
            if(confirmed) {
              try {
                await Api.put(`leaves`, {id: l.id, status: 'rejected'});
                showToast('Đã từ chối yêu cầu', {type:'success'});
                await load();
              } catch(err) {
                showToast(err.message || 'Không thể từ chối', {type:'error'});
              }
            }
          });
          
          actions.appendChild(approveBtn);
          actions.appendChild(rejectBtn);
        } else {
          actions.appendChild(el('span', {style:'color:#999;font-size:0.85em'}, '-'));
        }
        
        tbody.appendChild(el('tr', {}, 
          el('td', {}, empName),
          el('td', {}, new Date(l.start_date).toLocaleDateString('vi-VN')),
          el('td', {}, new Date(l.end_date).toLocaleDateString('vi-VN')),
          el('td', {style:'text-align:center'}, daysDiff + ' ngày'),
          el('td', {}, el('span', {class:`badge ${statusClass}`}, statusText)),
          el('td', {}, actions)
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
    
    // Clear inline errors
    ['employee_id','start_date','end_date'].forEach(n=>{ 
      const eln = form.querySelector('[name="'+n+'"]'); 
      if(eln) clearInlineError(eln); 
    });
    
    const fd = new FormData(form);
    const startDate = fd.get('start_date');
    const endDate = fd.get('end_date');
    
    // Validate dates
    if(startDate && endDate && new Date(endDate) < new Date(startDate)) {
      setInlineError(form.querySelector('[name="end_date"]'), 'Ngày kết thúc phải sau ngày bắt đầu');
      showToast('Ngày kết thúc phải sau ngày bắt đầu', {type:'error'});
      return;
    }
    
    const payload = {
      employee_id: fd.get('employee_id'), 
      start_date: startDate, 
      end_date: endDate, 
      status: fd.get('status')
    };
    
    try{
      await Api.post('leaves', payload);
      showToast('Tạo yêu cầu nghỉ phép thành công', {type:'success'});
      form.reset();
      await load();
    }catch(err){
      if(err && err.body && err.body.details){ 
        Object.entries(err.body.details).forEach(([f,m])=>{ 
          const eln = form.querySelector('[name="'+f+'"]'); 
          if(eln) setInlineError(eln,m); 
        }); 
      }
      showToast(err.message || 'Không thể tạo yêu cầu nghỉ', {type:'error'});
    }
  });

  await load();
}
