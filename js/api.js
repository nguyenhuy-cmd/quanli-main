// Auto-detect API base. Works both for root-hosted apps and apps served from a subfolder
// Example: if your app is at http://localhost/quanli-main/ this will point to
// http://localhost/quanli-main/backend/api.php
const BASE = (function(){
  // Force localhost for development (change this when deploying to production)
  return 'http://localhost/quanli-main/backend/api.php';
  
  /* Auto-detect (comment out the line above and uncomment this for production):
  try{
    const origin = window.location.origin; // protocol + host
    // derive the project root path from pathname (strip optional index.html and trailing slash)
    const pathname = window.location.pathname.replace(/\/(?:index\.html)?$/, '').replace(/\/$/, '');
    const root = pathname || '';
    return origin + root + '/backend/api.php';
  }catch(e){
    // fallback to common local dev host
    return 'http://127.0.0.1:8000/backend/api.php';
  }
  */
})();

async function handleResponse(res){
  const contentType = res.headers.get('Content-Type') || '';
  let body = null;
  if(contentType.includes('application/json')){
    try{ body = await res.json(); }catch(e){ body = null; }
  }else{
    try{ body = await res.text(); }catch(e){ body = null; }
  }
  if(!res.ok){
    // backend may return { error: '...', details: {...} } or { message: '...' }
    const message = (body && (body.error || body.message)) ? (body.error || body.message) : (typeof body === 'string' ? body : res.statusText);
    // default Vietnamese fallback
    const fallback = 'Yêu cầu thất bại';
    const err = new Error(message || fallback);
    err.status = res.status;
    err.body = body;
    throw err;
  }
  return body;
}

export async function get(resource, params=''){
  // If resource already contains query params, extract them
  let actualResource = resource;
  let queryString = params;
  
  if(resource.includes('?')){
    const [res, qs] = resource.split('?', 2);
    actualResource = res;
    // Merge with existing params
    queryString = qs + (params ? '&' + params : '');
  }
  
  const url = `${BASE}?resource=${encodeURIComponent(actualResource)}${queryString?'&'+queryString:''}`;
  const headers = {};
  const token = localStorage.getItem('hrm_token');
  if(token) headers['Authorization'] = 'Bearer '+token;
  const res = await fetch(url, {credentials:'include', headers});
  return handleResponse(res);
}

export async function post(resource, data){
  const url = `${BASE}?resource=${encodeURIComponent(resource)}`;
  const headers = {'Content-Type':'application/json'};
  const token = localStorage.getItem('hrm_token');
  if(token) headers['Authorization'] = 'Bearer '+token;
  const res = await fetch(url, {
    method:'POST',
    credentials:'include',
    headers,
    body: JSON.stringify(data)
  });
  return handleResponse(res);
}

export async function put(resource, data){
  const url = `${BASE}?resource=${encodeURIComponent(resource)}`;
  const headers = {'Content-Type':'application/json'};
  const token = localStorage.getItem('hrm_token');
  if(token) headers['Authorization'] = 'Bearer '+token;
  const res = await fetch(url, {
    method:'PUT',
    credentials:'include',
    headers,
    body: JSON.stringify(data)
  });
  return handleResponse(res);
}

export async function del(resource, data){
  const url = `${BASE}?resource=${encodeURIComponent(resource)}`;
  const headers = {'Content-Type':'application/json'};
  const token = localStorage.getItem('hrm_token');
  if(token) headers['Authorization'] = 'Bearer '+token;
  const res = await fetch(url, {
    method:'DELETE',
    credentials:'include',
    headers,
    body: JSON.stringify(data||{})
  });
  return handleResponse(res);
}
