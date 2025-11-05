import * as Api from '../api.js';
import { el, showConfirm, showToast } from '../utils.js';

export async function renderPositionModule(container){
  container.innerHTML = '';
  container.appendChild(el('h3', {}, 'Vị trí'));
  const listWrap = el('div');
  const form = el('form', {id:'pos-form'},
    el('input', {name:'name', placeholder:'Tên vị trí'}),
    el('button', {type:'submit', class:'btn'}, 'Lưu')
  );
  container.appendChild(form);
  container.appendChild(listWrap);

  let editingId = null;

  async function load(){
    const items = await Api.get('positions');
    listWrap.innerHTML = '';
    const table = el('table');
    const thead = el('thead', {}, el('tr', {}, el('th', {}, 'ID'), el('th', {}, 'Tên'), el('th', {}, 'Hành động')));
    table.appendChild(thead);
    const tbody = el('tbody');
    table.appendChild(tbody);
    items.forEach(it=>{
      const tr = el('tr', {},
        el('td', {}, it.id),
        el('td', {}, it.name),
        el('td', {},
          el('button', {class:'btn', onclick:()=>{ onEdit(it); }}, 'Sửa'), ' ',
          el('button', {class:'btn', onclick:()=>{ onDelete(it.id); }}, 'Xóa')
        )
      );
      tbody.appendChild(tr);
    });
    listWrap.appendChild(table);
  }

  function onEdit(item){
    editingId = item.id;
    form.querySelector('[name="name"]').value = item.name;
  }

  async function onDelete(id){
    const confirmed = await showConfirm('Bạn có chắc muốn xóa vị trí này không?', {
      title: 'Xóa vị trí',
      okText: 'Xóa',
      cancelText: 'Hủy'
    });
    
    if(!confirmed) return;
    
    try {
      await Api.del('positions', {id});
      showToast('Xóa vị trí thành công', {type:'success'});
      await load();
    } catch(err) {
      showToast(err.message || 'Không thể xóa vị trí', {type:'error'});
    }
  }

  form.addEventListener('submit', async e=>{
    e.preventDefault();
    const name = form.querySelector('[name="name"]').value.trim();
    if(!name){ 
      showToast('Vui lòng nhập tên vị trí', {type:'error'}); 
      return; 
    }
    
    try {
      if(editingId){
        await Api.put('positions', {id:editingId, name});
        showToast('Cập nhật vị trí thành công', {type:'success'});
        editingId = null;
      }else{
        await Api.post('positions', {name});
        showToast('Thêm vị trí thành công', {type:'success'});
      }
      form.reset();
      await load();
    } catch(err) {
      showToast(err.message || 'Có lỗi xảy ra', {type:'error'});
    }
  });

  await load();
}
