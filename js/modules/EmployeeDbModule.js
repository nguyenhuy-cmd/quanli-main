import * as Api from '../api.js';
import { el, showConfirm, showToast } from '../utils.js';
import { renderAddEmployeeModule } from './AddEmployeeModule.js';
import { renderEditEmployeeModule } from './EditEmployeeModule.js';

export async function renderEmployeeModule(container){
  container.innerHTML = '';
  const header = el('div', {style:'display:flex;gap:8px;align-items:center'}, el('h3', {}, 'Danh sách nhân viên'), el('button', {class:'btn', onclick:()=>openAdd()}, 'Thêm nhân viên'));
  container.appendChild(header);

  const listWrap = el('div');
  container.appendChild(listWrap);

  async function load(){
    listWrap.innerHTML = '';
    // fetch employees and lookup maps for department/position names
    const [data, deps, poss] = await Promise.all([Api.get('employees'), Api.get('departments'), Api.get('positions')]);
    const depMap = (deps||[]).reduce((m,d)=> (m[d.id]=d.name, m), {});
    const posMap = (poss||[]).reduce((m,p)=> (m[p.id]=p.name, m), {});
    if(!Array.isArray(data) || data.length===0){
      listWrap.appendChild(el('div', {}, 'Không có dữ liệu'));
      return;
    }
    const table = el('table');
    table.appendChild(el('thead', {}, el('tr', {}, el('th', {}, 'ID'), el('th', {}, 'Tên'), el('th', {}, 'Email'), el('th', {}, 'Phòng ban'), el('th', {}, 'Chức vụ'), el('th', {}, 'Hành động'))));
    const tbody = el('tbody');
    data.forEach(emp=>{
      const tr = el('tr', {},
        el('td', {}, emp.id),
        el('td', {}, emp.name),
        el('td', {}, emp.email),
        el('td', {}, depMap[emp.department_id]||''),
        el('td', {}, posMap[emp.position_id]||''),
        el('td', {},
          el('button', {class:'btn', onclick:()=>onEdit(emp.id)}, 'Sửa'), ' ',
          el('button', {class:'btn', onclick:()=>onDelete(emp.id)}, 'Xóa')
        )
      );
      tbody.appendChild(tr);
    });
    table.appendChild(tbody);
    listWrap.appendChild(table);
  }

  function openAdd(){
    renderAddEmployeeModule(container, ()=>{ renderEmployeeModule(container); });
  }

  function onEdit(id){
    renderEditEmployeeModule(container, id, ()=>{ renderEmployeeModule(container); });
  }

  async function onDelete(id){
    const confirmed = await showConfirm('Bạn có chắc muốn xóa nhân viên này không? Tất cả dữ liệu liên quan (lương, chấm công, nghỉ phép, đánh giá) cũng sẽ bị xóa.', {
      title: 'Xóa nhân viên',
      okText: 'Xóa',
      cancelText: 'Hủy'
    });
    
    if(!confirmed) return;
    
    try {
      await Api.del('employees', {id});
      showToast('Xóa nhân viên thành công', {type:'success'});
      await load();
    } catch(err) {
      showToast(err.message || 'Không thể xóa nhân viên', {type:'error'});
    }
  }

  await load();
}
