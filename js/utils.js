export const el = (tag, attrs={}, ...children)=>{
  const node = document.createElement(tag);
  Object.entries(attrs).forEach(([k,v])=>{
    if(k.startsWith('on') && typeof v==='function') node.addEventListener(k.slice(2), v);
    else if(k==='html') node.innerHTML = v;
    else node.setAttribute(k,v);
  });
  children.flat().forEach(ch=>{
    // accept strings, numbers, booleans as text; accept DOM nodes
    if(typeof ch === 'string' || typeof ch === 'number' || typeof ch === 'boolean'){
      node.appendChild(document.createTextNode(String(ch)));
    }else if(ch && (ch.nodeType === 1 || ch.nodeType === 3 || ch instanceof Node)){
      node.appendChild(ch);
    }else if(ch == null){
      // skip null/undefined
    }else{
      // fallback to string representation for other types
      node.appendChild(document.createTextNode(String(ch)));
    }
  });
  return node;
}

export const qs = (s, root=document)=>root.querySelector(s);
export const qsa = (s, root=document)=>[...root.querySelectorAll(s)];

export function showAlert(msg){ alert(msg); }

export const validate = {
  required: (v)=>{ if(v===null||v===undefined) return false; if(typeof v==='string') return v.trim().length>0; return true; },
  email: (v)=>/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(v||''),
  positiveNumber: (v)=>{ const n = Number(v); return !isNaN(n) && n>0; }
};

// Toast notifications
export function showToast(message, {type='info', timeout=3500} = {}){
  let container = document.getElementById('toast-container');
  if(!container){
    container = document.createElement('div');
    container.id = 'toast-container';
    container.style.position = 'fixed';
    container.style.right = '16px';
    container.style.bottom = '16px';
    container.style.zIndex = 9999;
    document.body.appendChild(container);
  }
  const t = document.createElement('div');
  t.className = 'toast toast-' + type;
  t.setAttribute('role','status');
  t.style.marginTop = '8px';
  t.style.padding = '10px 14px';
  t.style.borderRadius = '6px';
  t.style.color = '#fff';
  t.style.background = type === 'error' ? '#e74c3c' : (type === 'success' ? '#27ae60' : '#34495e');
  t.style.boxShadow = '0 6px 18px rgba(0,0,0,0.12)';
  t.style.transition = 'opacity .3s ease';
  t.textContent = message;
  container.appendChild(t);
  setTimeout(()=>{ t.style.opacity = '0'; setTimeout(()=> t.remove(),300); }, timeout);
  return t;
}

// Inline field errors
export function setInlineError(inputEl, msg){
  if(!inputEl || !inputEl.parentNode) return;
  clearInlineError(inputEl);
  const span = document.createElement('div');
  span.className = 'inline-error';
  span.style.color = '#c62828';
  span.style.fontSize = '0.9em';
  span.style.marginTop = '4px';
  span.textContent = msg;
  inputEl.parentNode.appendChild(span);
}

export function clearInlineError(inputEl){
  if(!inputEl || !inputEl.parentNode) return;
  const old = inputEl.parentNode.querySelector('.inline-error');
  if(old) old.remove();
}

// Custom confirm dialog
export function showConfirm(message, {title = 'Xác nhận', okText = 'OK', cancelText = 'Hủy'} = {}) {
  return new Promise((resolve) => {
    // Create overlay
    const overlay = el('div', {
      style: 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:10000;display:flex;align-items:center;justify-content:center;animation:fadeIn 0.2s'
    });
    
    // Create modal
    const modal = el('div', {
      style: 'background:#fff;border-radius:12px;padding:24px;max-width:400px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.3);animation:slideUp 0.3s'
    });
    
    // Title
    if(title) {
      modal.appendChild(el('h3', {
        style: 'margin:0 0 12px;font-size:1.2em;color:#333'
      }, title));
    }
    
    // Message
    modal.appendChild(el('p', {
      style: 'margin:0 0 20px;color:#666;line-height:1.5'
    }, message));
    
    // Buttons
    const btnContainer = el('div', {
      style: 'display:flex;gap:8px;justify-content:flex-end'
    });
    
    const cancelBtn = el('button', {
      type: 'button',
      class: 'btn secondary',
      style: 'padding:8px 16px'
    }, cancelText);
    
    const okBtn = el('button', {
      type: 'button',
      class: 'btn',
      style: 'padding:8px 16px'
    }, okText);
    
    // Event handlers
    const cleanup = () => {
      overlay.style.opacity = '0';
      setTimeout(() => overlay.remove(), 200);
    };
    
    cancelBtn.addEventListener('click', () => {
      cleanup();
      resolve(false);
    });
    
    okBtn.addEventListener('click', () => {
      cleanup();
      resolve(true);
    });
    
    overlay.addEventListener('click', (e) => {
      if(e.target === overlay) {
        cleanup();
        resolve(false);
      }
    });
    
    btnContainer.appendChild(cancelBtn);
    btnContainer.appendChild(okBtn);
    modal.appendChild(btnContainer);
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
    
    // Focus OK button
    setTimeout(() => okBtn.focus(), 100);
  });
}
