import * as Api from '../api.js';
import { el, setInlineError, clearInlineError, showToast, validate } from '../utils.js';

function saveAuth(token, user){
  if(token) localStorage.setItem('hrm_token', token);
  if(user) localStorage.setItem('hrm_user', JSON.stringify(user));
  window.updateUserPanel && window.updateUserPanel();
}

function clearAuth(){
  localStorage.removeItem('hrm_token');
  localStorage.removeItem('hrm_user');
  window.updateUserPanel && window.updateUserPanel();
}

export async function renderAuthModule(container){
  container.innerHTML = '';
  // If already logged in, redirect to employees instead of showing auth form
  const tokenNow = localStorage.getItem('hrm_token');
  if(tokenNow){
    window.updateUserPanel && window.updateUserPanel();
    location.hash = '#/employees';
    if(window.routeTo) window.routeTo('employees');
    return;
  }
  // card wrapper
  const cardWrap = el('div', {class:'auth-card-wrap'},
    el('div', {class:'auth-card'},
      el('div', {class:'auth-tabs'},
        el('button', {class:'tab tab-login active', type:'button'}, 'Đăng nhập'),
        el('button', {class:'tab tab-register', type:'button'}, 'Đăng ký')
      ),
      el('div', {class:'auth-body'},
        // login view
        el('form', {class:'auth-form auth-login'},
          el('h3', {}, 'Đăng nhập'),
          el('p', {class:'muted'}, 'Đăng nhập để quản lý thông tin nhân sự'),
          el('div', {class:'input-with-icon'}, el('span', {class:'input-icon'}, '✉️'), el('input', {type:'text', placeholder:'Email', name:'email'})),
          el('div', {class:'input-with-icon'}, el('span', {class:'input-icon'}, '🔒'), el('input', {type:'password', placeholder:'Mật khẩu', name:'password'}), el('button', {type:'button', class:'password-toggle'}, '👁')),
          el('div', {style:'display:flex;gap:8px;align-items:center;justify-content:space-between'},
            el('div', {style:'display:flex;gap:8px;align-items:center'}, el('input', {type:'checkbox', name:'remember', id:'remember'}), el('label', {for:'remember'}, 'Ghi nhớ đăng nhập')),
            el('div', {}, el('button', {type:'submit', class:'btn'}, 'Đăng nhập'))
          ),
          el('div', {style:'margin-top:8px'}, el('button', {type:'button', class:'btn btn-link btn-switch-register'}, 'Chưa có tài khoản? Đăng ký'))
        ),
        // register view (hidden)
        el('form', {class:'auth-form auth-register', style:'display:none'},
          el('h3', {}, 'Đăng ký'),
          el('p', {class:'muted'}, 'Tạo tài khoản để bắt đầu sử dụng hệ thống'),
          el('div', {class:'input-with-icon'}, el('span', {class:'input-icon'}, '👤'), el('input', {type:'text', placeholder:'Tên', name:'name'})),
          el('div', {class:'input-with-icon'}, el('span', {class:'input-icon'}, '✉️'), el('input', {type:'text', placeholder:'Email', name:'email'})),
          el('div', {class:'input-with-icon'}, el('span', {class:'input-icon'}, '🔒'), el('input', {type:'password', placeholder:'Mật khẩu', name:'password'}), el('button', {type:'button', class:'password-toggle'}, '👁')),
          el('div', {style:'display:flex;gap:8px;align-items:center;justify-content:flex-start'}, el('button', {type:'submit', class:'btn'}, 'Tạo tài khoản'), el('button', {type:'button', class:'btn btn-link btn-switch-login'}, 'Đã có tài khoản? Đăng nhập'))
        )
      )
    )
  );

  container.appendChild(cardWrap);

  const loginForm = container.querySelector('.auth-login');
  const registerForm = container.querySelector('.auth-register');
  const tabLogin = container.querySelector('.tab-login');
  const tabRegister = container.querySelector('.tab-register');

  function showLogin(){
    tabLogin.classList.add('active'); tabRegister.classList.remove('active');
    loginForm.style.display = '';
    registerForm.style.display = 'none';
  }
  function showRegister(){
    tabLogin.classList.remove('active'); tabRegister.classList.add('active');
    loginForm.style.display = 'none';
    registerForm.style.display = '';
  }

  // toggle handlers
  tabLogin.addEventListener('click', showLogin);
  tabRegister.addEventListener('click', showRegister);
  container.querySelector('.btn-switch-register').addEventListener('click', showRegister);
  container.querySelector('.btn-switch-login').addEventListener('click', showLogin);

  // login submit
  loginForm.addEventListener('submit', async e=>{
    e.preventDefault();
    ['email','password'].forEach(n=>{ const eln = loginForm.querySelector('[name="'+n+'"]'); if(eln) clearInlineError(eln); });
    const fd = new FormData(loginForm);
    const email = fd.get('email'); const password = fd.get('password'); const remember = fd.get('remember') === 'on';
    if(!validate.required(email)){ setInlineError(loginForm.querySelector('[name="email"]'), 'Email là bắt buộc'); return; }
    if(!validate.required(password)){ setInlineError(loginForm.querySelector('[name="password"]'), 'Mật khẩu là bắt buộc'); return; }
    try{
      const res = await Api.post('auth/login', {email, password, remember});
      saveAuth(res.token, res.user);
      window.updateUserPanel && window.updateUserPanel();
      showToast('Đăng nhập thành công', {type:'success'});
      // Redirect to employees page after successful login
      location.hash = '#/employees';
      if(window.routeTo) window.routeTo('employees');
    }catch(err){
      if(err.body && err.body.details){ Object.entries(err.body.details).forEach(([f,m])=>{ const eln = loginForm.querySelector('[name="'+f+'"]'); if(eln) setInlineError(eln,m); }); }
      showToast(err.message || 'Đăng nhập thất bại', {type:'error'});
    }
  });

  // register submit
  registerForm.addEventListener('submit', async e=>{
    e.preventDefault();
    ['name','email','password'].forEach(n=>{ const eln = registerForm.querySelector('[name="'+n+'"]'); if(eln) clearInlineError(eln); });
    const fd = new FormData(registerForm);
    const name = fd.get('name'); const email = fd.get('email'); const password = fd.get('password');
    if(!validate.required(name)){ setInlineError(registerForm.querySelector('[name="name"]'), 'Tên là bắt buộc'); return; }
    if(!validate.required(email) || !validate.email(email)){ setInlineError(registerForm.querySelector('[name="email"]'), 'Email không hợp lệ'); return; }
    if(!validate.required(password)){ setInlineError(registerForm.querySelector('[name="password"]'), 'Mật khẩu là bắt buộc'); return; }
    try{
      const res = await Api.post('auth/register', {name,email,password});
      showToast('Đăng ký thành công. Vui lòng đăng nhập', {type:'success'});
      showLogin();
    }catch(err){
      if(err.body && err.body.details){ Object.entries(err.body.details).forEach(([f,m])=>{ const eln = registerForm.querySelector('[name="'+f+'"]'); if(eln) setInlineError(eln,m); }); }
      showToast(err.message || 'Đăng ký thất bại', {type:'error'});
    }
  });

  // password toggle buttons
  container.querySelectorAll('.password-toggle').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const input = btn.previousElementSibling;
      if(!input) return;
      if(input.type === 'password'){
        input.type = 'text'; btn.textContent = '🙈';
      }else{
        input.type = 'password'; btn.textContent = '👁';
      }
    });
  });
}
