import * as Api from './api.js';
import { renderEmployeeModule } from './modules/EmployeeDbModule.js';
import { renderAuthModule } from './modules/AuthModule.js';
import { renderDepartmentModule } from './modules/DepartmentModule.js';
import { renderPositionModule } from './modules/PositionModule.js';
import { renderAttendanceModule } from './modules/AttendanceModule.js';
import { renderSalaryModule } from './modules/SalaryModule.js';
import { renderLeaveModule } from './modules/LeaveModule.js';
import { renderPerformanceModule } from './modules/PerformanceModule.js';
import { showToast } from './utils.js';

const menu = document.querySelector('.sidebar');
const content = document.getElementById('app-content');
const userPanel = document.getElementById('user-panel');

window.updateUserPanel = function(){
  const user = JSON.parse(localStorage.getItem('hrm_user')||'null');
  userPanel.innerHTML = '';
  if(user){
    const avatar = document.createElement('span');
    avatar.className = 'avatar';
    // show first letter of email/name
    const nameHint = (user.name || user.email || '').trim();
    avatar.textContent = (nameHint && nameHint[0])? nameHint[0].toUpperCase() : 'U';
    const span = document.createElement('span');
    span.textContent = user.email;
    const btn = document.createElement('button');
    btn.textContent = 'Đăng xuất';
    btn.className = 'btn';
    btn.addEventListener('click', async ()=>{
      try{
        // try server-side logout, ignore errors
        await Api.post('auth/logout', {});
      }catch(e){
        // ignore
      }
      localStorage.removeItem('hrm_user'); localStorage.removeItem('hrm_token'); window.updateUserPanel(); routeTo('auth');
    });
  userPanel.appendChild(avatar);
  userPanel.appendChild(span);
  userPanel.appendChild(btn);
    // show menu when logged in
    if(menu) menu.style.display = '';
  }else{
    const a = document.createElement('a');
    a.textContent = 'Đăng nhập';
    a.href = '#';
    a.addEventListener('click', e=>{ e.preventDefault(); if(window.routeTo) window.routeTo('auth'); });
    userPanel.appendChild(a);
    // hide menu when not logged in
    if(menu) menu.style.display = 'none';
  }
}
window.updateUserPanel();

function setActiveItem(el){
  menu.querySelectorAll('li').forEach(li=>li.classList.remove('active'));
  el.classList.add('active');
}

menu.addEventListener('click', e=>{
  const li = e.target.closest('li');
  if(!li) return;
  // If clicked an <a href="#/..."> allow default hash navigation
  const a = e.target.closest('a');
  if(a && a.getAttribute('href') && a.getAttribute('href').startsWith('#')){
    // allow the hashchange handler to route; but prevent further handling here
    return;
  }
  // require authentication before navigating to modules other than auth
  const token = localStorage.getItem('hrm_token');
  const module = li.dataset.module;
  if(!token && module !== 'auth'){
    showToast('Vui lòng đăng nhập trước khi truy cập menu', {type:'error'});
    // force show auth screen
    if(window.routeTo) window.routeTo('auth');
    // update hash to auth
    location.hash = '#/auth';
    return;
  }
  // navigate by setting hash (this triggers hashchange -> routeTo)
  location.hash = '#/' + module;
});

function parseHash(){
  const h = location.hash || '';
  if(!h) return null;
  // accept formats: #/module or #module
  return h.replace(/^#\/?/, '') || null;
}

window.addEventListener('hashchange', ()=>{
  const name = parseHash();
  if(name) {
    // update active menu
    const li = document.querySelector('[data-module="' + name + '"]');
    if(li) setActiveItem(li);
    routeTo(name);
  }
});

async function routeTo(name){
  // fade-out/in effect
  content.classList.remove('fade-in');
  content.innerHTML = '';
  try{
    switch(name){
      case 'employees':
        await renderEmployeeModule(content);
        break;
      case 'departments':
        await renderDepartmentModule(content);
        break;
      case 'positions':
        await renderPositionModule(content);
        break;
      case 'attendance':
        await renderAttendanceModule(content);
        break;
      case 'salary':
        await renderSalaryModule(content);
        break;
      case 'leave':
        await renderLeaveModule(content);
        break;
      case 'performance':
        await renderPerformanceModule(content);
        break;
      case 'auth':
        await renderAuthModule(content);
        break;
      case 'dashboard':
      default:
        content.innerHTML = '<h3>Welcome to HRM Dashboard</h3><p>Chọn module ở menu trái.</p>'
    }
  }catch(err){
    console.error(err);
    content.innerHTML = `<div class="error">Lỗi: ${err.message||err}</div>`;
  }
  // trigger fade-in
  requestAnimationFrame(()=>{ content.classList.add('fade-in'); });
}

// initial route: prefer hash if present
const _token = localStorage.getItem('hrm_token');
let initial = parseHash();

// Validate token if exists
if(_token){
  // Quick validation: try to call a protected endpoint
  try{
    // We'll do this asynchronously and handle in the background
    Api.get('employees', 'limit=1').catch(err => {
      // If 401, token is invalid - clear it
      if(err.status === 401){
        localStorage.removeItem('hrm_token');
        localStorage.removeItem('hrm_user');
        window.updateUserPanel();
        // Redirect to auth
        routeTo('auth');
        location.hash = '#/auth';
      }
    });
  }catch(e){
    // Ignore validation errors
  }
}

if(initial){
  // if module requires auth and not logged in, redirect to auth
  const needsAuth = initial !== 'auth';
  if(needsAuth && !_token){
    if(menu) menu.style.display = 'none';
    routeTo('auth');
    location.hash = '#/auth';
  }else{
    // set active menu item and route
    const li = document.querySelector('[data-module="' + initial + '"]');
    if(li) setActiveItem(li);
    routeTo(initial);
  }
}else{
  if(!_token){
    if(menu) menu.style.display = 'none';
    routeTo('auth');
    location.hash = '#/auth';
  }else{
    routeTo('dashboard');
  }
}

// export for testing/debug
window.__API = Api;

// Global error reporting: send JS errors/unhandled rejections to backend logs endpoint.
// This is intentionally best-effort and won't interrupt app flow.
function sendClientLog(obj){
  try{
    // fire-and-forget
    Api.post('logs', obj).catch(()=>{});
  }catch(e){
    // ignore
  }
}

window.addEventListener('error', function(evt){
  const payload = {
    level: 'error',
    message: evt.message || String(evt),
    filename: evt.filename || null,
    lineno: evt.lineno || null,
    colno: evt.colno || null,
    stack: evt.error && evt.error.stack ? String(evt.error.stack) : null,
    url: location.href,
    user: JSON.parse(localStorage.getItem('hrm_user')||'null')
  };
  // also log to console for developer visibility
  console.error('Captured error', payload);
  sendClientLog(payload);
});

window.addEventListener('unhandledrejection', function(evt){
  const reason = evt.reason;
  const payload = {
    level: 'unhandledrejection',
    message: (reason && reason.message) ? reason.message : (String(reason) || 'Promise rejection'),
    stack: (reason && reason.stack) ? reason.stack : null,
    url: location.href,
    user: JSON.parse(localStorage.getItem('hrm_user')||'null')
  };
  console.warn('Captured unhandledrejection', payload);
  sendClientLog(payload);
});
