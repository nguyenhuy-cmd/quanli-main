import * as Api from '../api.js';
import { el } from '../utils.js';

export async function renderDeleteEmployeeModule(container){
  container.appendChild(el('h3', {}, 'Xóa nhân viên'));
  container.appendChild(el('p', {}, 'Sử dụng module Danh sách để xóa nhanh.'));
}
