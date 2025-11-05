import * as Api from '../api.js';
import { el, setInlineError, clearInlineError, showToast } from '../utils.js';

export async function renderSalaryModule(container){
  container.innerHTML = '';
  container.appendChild(el('h3', {}, 'Lương'));

  // use select to choose employee to avoid manual ID errors
  const employeeSelect = el('select', {name:'employee_id'}, el('option', {value:''}, '-- chọn nhân viên --'));
  const form = el('form', {},
      el('div', {class:'form-row'}, 
        employeeSelect,
        el('input', {name:'amount', placeholder:'Số tiền', type:'number', step:'0.01'})
    ),
    el('div', {class:'form-row'}, el('input', {name:'month', placeholder:'YYYY-MM', type:'text'})),
    el('div', {}, el('button', {type:'submit', class:'btn'}, 'Thêm lương'))
  );

  const list = el('div');
  container.appendChild(form);
  container.appendChild(list);

  async function load(){
    const data = await Api.get('salaries');
    list.innerHTML = '';
    const table = el('table');
    table.appendChild(el('thead', {}, el('tr', {}, el('th', {}, 'ID'), el('th', {}, 'Mã NV'), el('th', {}, 'Số tiền'), el('th', {}, 'Tháng'))));
    const tbody = el('tbody');
    if(!Array.isArray(data) || data.length===0){
      // show empty row with colspan
      tbody.appendChild(el('tr', {}, el('td', {colspan:4, style:'text-align:center;padding:12px'}, 'Không có dữ liệu')));
    }else{
      data.forEach(s=>{
        const amountText = (typeof s.amount !== 'undefined' && s.amount !== null) ? Number(s.amount).toLocaleString('vi-VN', {style:'currency', currency:'VND'}) : '';
        const monthText = s.pay_date ? (s.pay_date.substring(0,7)) : '';
        tbody.appendChild(el('tr', {}, el('td', {}, s.id), el('td', {}, s.employee_name || s.employee_id), el('td', {}, amountText), el('td', {}, monthText)));
      });
    }
    table.appendChild(tbody);
    list.appendChild(table);
  }

  // populate employee select
  try{
    const employees = await Api.get('employees');
    employees.forEach(emp=> employeeSelect.appendChild(el('option',{value:emp.id}, emp.name || emp.email || emp.id)));
  }catch(e){ /* ignore */ }

  form.addEventListener('submit', async e=>{
    e.preventDefault();
    ['employee_id','amount','month'].forEach(n=>{ const eln = form.querySelector('[name="'+n+'"]'); if(eln) clearInlineError(eln); });
    const fd = new FormData(form);
    const payload = {employee_id: fd.get('employee_id'), amount: fd.get('amount'), month: fd.get('month')};
    try{
      await Api.post('salaries', payload);
      form.reset();
      await load();
      showToast('Lương đã được thêm', {type:'success'});
    }catch(err){
      if(err && err.body && err.body.details){ Object.entries(err.body.details).forEach(([f,m])=>{ const eln = form.querySelector('[name="'+f+'"]'); if(eln) setInlineError(eln,m); }); }
      showToast(err.message || 'Không thể thêm lương', {type:'error'});
    }
  });

  await load();
}
