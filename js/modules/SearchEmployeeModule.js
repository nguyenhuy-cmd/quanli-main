import * as Api from '../api.js';
import { el } from '../utils.js';

export async function renderSearchEmployeeModule(container){
  const form = el('form', {}, el('input', {name:'q', placeholder:'Tìm theo tên hoặc email'}), el('button', {type:'submit', class:'btn'}, 'Tìm'));
  const results = el('div');
  form.addEventListener('submit', async e=>{
    e.preventDefault();
    const q = new FormData(form).get('q');
    const data = await Api.get('employees', `search=${encodeURIComponent(q)}`);
    results.innerHTML = JSON.stringify(data,null,2);
  });
  container.appendChild(form);
  container.appendChild(results);
}
