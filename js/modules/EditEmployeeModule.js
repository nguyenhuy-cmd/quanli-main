import * as Api from '../api.js';
import { el, validate, showToast, setInlineError, clearInlineError } from '../utils.js';

// renderEditEmployeeModule(container, id, onDone)
export async function renderEditEmployeeModule(container, id, onDone){
  container.innerHTML = '';
  const emp = await Api.get('employees', `id=${id}`);
  // fetch departments & positions for selects
  const [deps, poss] = await Promise.all([Api.get('departments'), Api.get('positions')]);
  const depSelect = el('select', {name:'department_id'}, el('option', {value:''}, '-- Chọn phòng ban --'));
  (deps||[]).forEach(d=> depSelect.appendChild(el('option', {value:d.id, selected: emp.department_id==d.id}, d.name)));
  const posSelect = el('select', {name:'position_id'}, el('option', {value:''}, '-- Chọn chức vụ --'));
  (poss||[]).forEach(p=> posSelect.appendChild(el('option', {value:p.id, selected: emp.position_id==p.id}, p.name)));

  const form = el('form', {},
    el('h3', {}, 'Sửa nhân viên'),
    el('div', {class:'form-row'}, el('input', {name:'name', value:emp.name})),
    el('div', {class:'form-row'}, el('input', {name:'email', value:emp.email})),
    el('div', {class:'form-row'}, depSelect),
    el('div', {class:'form-row'}, posSelect),
    el('div', {}, el('button', {type:'submit', class:'btn'}, 'Lưu'))
  );
  form.addEventListener('submit', async e=>{
    e.preventDefault();
    const fd = new FormData(form);
    const name = fd.get('name');
    const email = fd.get('email');
    // clear previous inline errors
    ['name','email','department_id','position_id'].forEach(n=>{ const elm = form.querySelector('[name="'+n+'"]'); if(elm) clearInlineError(elm); });
    if(!validate.required(name)){ setInlineError(form.querySelector('[name="name"]'), 'Tên là bắt buộc'); return; }
    if(email && !validate.email(email)){ setInlineError(form.querySelector('[name="email"]'), 'Email không hợp lệ'); return; }
    try{
      const department_id = fd.get('department_id') || null;
      const position_id = fd.get('position_id') || null;
      await Api.put('employees', {id, name, email, department_id: department_id?Number(department_id):null, position_id: position_id?Number(position_id):null});
      showToast('Cập nhật thành công', {type:'success'});
      if(typeof onDone==='function') onDone();
    }catch(err){
      if(err.body && err.body.details){
        Object.entries(err.body.details).forEach(([field,msg])=>{ const elm = form.querySelector('[name="'+field+'"]'); if(elm) setInlineError(elm, msg); });
        showToast(err.message || 'Lỗi nhập liệu', {type:'error'});
      } else {
        showToast(err.message || 'Lỗi hệ thống', {type:'error'});
      }
    }
  });
  container.appendChild(form);
}
