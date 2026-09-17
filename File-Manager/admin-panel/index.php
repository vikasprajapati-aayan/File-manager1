
<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Panel | Filebox</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



<style>
  :root{
    --bg:#F5F6F9;
    --panel:#FFFFFF;
    --ink:#171A24;
    --ink-soft:#6C7288;
    --line:#E7E9F0;
    --accent:#3B4EE8;
    --accent-soft:#EEF0FF;
    --danger:#E24C4C;
    --danger-soft:#FDECEC;
    --success:#2E9E5B;
    --success-soft:#E9F7EF;
    --amber:#B8790A;
    --amber-soft:#FDF3E3;
    --shadow:0 1px 2px rgba(23,26,36,.04), 0 8px 24px -12px rgba(23,26,36,.10);
  }

  *{ box-sizing:border-box; margin:0; padding:0; }

  .app{
    display:grid;
    grid-template-rows:60px 1fr;
    height:100vh;
    overflow:hidden;
  }

  .layout{
    display:grid;
    grid-template-columns:230px 1fr;
    min-height:0;
  }

  /* ===== SIDEBAR ===== */
  .sidebar{
    background:var(--panel);
    border-right:1px solid var(--line);
    padding:18px 12px;
    display:flex;
    flex-direction:column;
  }

  .sidebar-label{
    font-size:11px;
    font-weight:600;
    letter-spacing:.06em;
    text-transform:uppercase;
    color:var(--ink-soft);
    padding:0 10px;
    margin-bottom:8px;
  }

  .nav-list{ list-style:none; margin-bottom:20px; }

  .nav-item{
    display:flex;
    align-items:center;
    gap:10px;
    padding:9px 10px;
    border-radius:8px;
    font-size:13.5px;
    font-weight:500;
    color:var(--ink-soft);
    cursor:pointer;
    transition:background .12s, color .12s;
    margin-bottom:2px;
  }
  .nav-item:hover{ background:var(--bg); color:var(--ink); }
  .nav-item.active{ background:var(--accent-soft); color:var(--accent); font-weight:600; }
  .nav-item svg{ width:16px; height:16px; flex-shrink:0; }

  .sidebar-bottom{
    margin-top:auto;
    padding-top:12px;
    border-top:1px solid var(--line);
  }

  .nav-item.logout{ color:var(--danger); }
  .nav-item.logout:hover{ background:var(--danger-soft); color:var(--danger); }

  .view-section{ display:none; }
  .view-section.active{ display:block; }

  .placeholder-view{
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    padding:80px 20px;
    text-align:center;
    color:var(--ink-soft);
  }
  .placeholder-view svg{ width:40px; height:40px; margin-bottom:14px; opacity:.4; }
  .placeholder-view h3{ font-size:15px; color:var(--ink); margin-bottom:6px; font-family:'Space Grotesk', sans-serif; }
  .placeholder-view p{ font-size:13px; max-width:280px; line-height:1.5; }

  /* ===== MAIN CONTENT WRAPPER ===== */
  .content-area{
    overflow-y:auto;
    padding:28px 24px 60px;
  }

  body{
    font-family:'Inter', sans-serif;
    background:var(--bg);
    color:var(--ink);
    -webkit-font-smoothing:antialiased;
    min-height:100vh;
  }

  /* ===== TOP BAR ===== */
  .topbar{
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:14px 28px;
    background:var(--panel);
    border-bottom:1px solid var(--line);
  }

  .brand{ display:flex; align-items:center; gap:10px; }
  .brand-mark{
    width:30px; height:30px;
    border-radius:8px;
    background:var(--ink);
    display:flex; align-items:center; justify-content:center;
    color:#fff;
    flex-shrink:0;
  }
  .brand-mark svg{ width:16px; height:16px; }
  .brand-name{
    font-family:'Space Grotesk', sans-serif;
    font-weight:700;
    font-size:16px;
  }
  .brand-tag{
    font-size:11px;
    font-weight:600;
    color:var(--ink-soft);
    background:var(--bg);
    padding:2px 8px;
    border-radius:20px;
    margin-left:2px;
  }

  .user-block{ display:flex; align-items:center; gap:10px; }
  .user-avatar{
    width:34px; height:34px;
    border-radius:50%;
    background:var(--accent-soft);
    color:var(--accent);
    display:flex; align-items:center; justify-content:center;
    font-weight:700;
    font-size:13px;
    font-family:'Space Grotesk', sans-serif;
  }
  .user-meta{ line-height:1.25; }
  .user-name{ font-size:13.5px; font-weight:600; }
  .user-role-tag{ font-size:11.5px; color:var(--ink-soft); }

  /* ===== MAIN ===== */
  .main{
    max-width:1080px;
  }

  .page-head{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    margin-bottom:22px;
    gap:16px;
    flex-wrap:wrap;
  }

  .page-head h1{
    font-family:'Space Grotesk', sans-serif;
    font-size:22px;
    font-weight:700;
    margin-bottom:4px;
    letter-spacing:-.01em;
  }
  .page-head p{ font-size:13.5px; color:var(--ink-soft); }

  .btn{
    padding:9px 16px;
    border-radius:8px;
    font-family:inherit;
    font-size:13.5px;
    font-weight:600;
    cursor:pointer;
    border:1px solid transparent;
    display:inline-flex;
    align-items:center;
    gap:7px;
  }
  .btn svg{ width:15px; height:15px; }
  .btn-primary{ background:var(--accent); color:#fff; }
  .btn-primary:hover{ background:#2E3FD1; }
  .btn-ghost{ background:var(--panel); border-color:var(--line); color:var(--ink); }
  .btn-ghost:hover{ background:var(--bg); }
  .btn:disabled{ opacity:.5; cursor:not-allowed; }

  .restricted-banner{
    display:flex;
    align-items:center;
    gap:9px;
    background:var(--amber-soft);
    color:var(--amber);
    font-size:12.5px;
    font-weight:500;
    padding:10px 14px;
    border-radius:9px;
    margin-bottom:20px;
  }
  .restricted-banner svg{ width:16px; height:16px; flex-shrink:0; }

  /* ===== TABLE CARD ===== */
  .table-card{
    background:var(--panel);
    border:1px solid var(--line);
    border-radius:12px;
    overflow:hidden;
  }

  table{ width:100%; border-collapse:collapse; }

  thead th{
    text-align:left;
    font-size:11px;
    font-weight:600;
    letter-spacing:.05em;
    text-transform:uppercase;
    color:var(--ink-soft);
    padding:12px 20px;
    border-bottom:1px solid var(--line);
    background:var(--bg);
  }
  thead th.col-actions{ text-align:right; }

  tbody td{
    padding:13px 20px;
    font-size:13.5px;
    border-bottom:1px solid var(--line);
    vertical-align:middle;
  }
  tbody tr:last-child td{ border-bottom:none; }
  tbody tr:hover{ background:#FAFAFC; }

  .user-cell{ display:flex; align-items:center; gap:10px; }
  .user-cell .avatar{
    width:32px; height:32px;
    border-radius:50%;
    background:var(--accent-soft);
    color:var(--accent);
    display:flex; align-items:center; justify-content:center;
    font-weight:700;
    font-size:12px;
    font-family:'Space Grotesk', sans-serif;
    flex-shrink:0;
  }
  .user-cell .name{ font-weight:600; color:var(--ink); }
  .user-cell .email{ font-size:12px; color:var(--ink-soft); }

  .badge{
    display:inline-flex;
    align-items:center;
    gap:5px;
    padding:4px 10px;
    border-radius:20px;
    font-size:11.5px;
    font-weight:600;
  }
  .badge-dot{ width:6px; height:6px; border-radius:50%; }

  .badge-admin{ background:var(--accent-soft); color:var(--accent); }
  .badge-admin .badge-dot{ background:var(--accent); }

  .badge-subadmin{ background:var(--amber-soft); color:var(--amber); }
  .badge-subadmin .badge-dot{ background:var(--amber); }

  .badge-user{ background:var(--bg); color:var(--ink-soft); }
  .badge-user .badge-dot{ background:var(--ink-soft); }

  .badge-active{ background:var(--success-soft); color:var(--success); }
  .badge-active .badge-dot{ background:var(--success); }

  .badge-inactive{ background:var(--danger-soft); color:var(--danger); }
  .badge-inactive .badge-dot{ background:var(--danger); }

  .col-actions{ text-align:right; white-space:nowrap; }

  .icon-btn{
    width:30px; height:30px;
    border:1px solid var(--line);
    background:var(--panel);
    border-radius:7px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    cursor:pointer;
    color:var(--ink-soft);
    transition:border-color .12s, color .12s, background .12s;
    margin-left:6px;
  }
  .icon-btn:hover{ border-color:var(--accent); color:var(--accent); }
  .icon-btn.danger:hover{ border-color:var(--danger); color:var(--danger); background:var(--danger-soft); }
  .icon-btn svg{ width:14px; height:14px; }
  .icon-btn:disabled{ opacity:.35; cursor:not-allowed; }
  .icon-btn:disabled:hover{ border-color:var(--line); color:var(--ink-soft); background:var(--panel); }

  .no-access-text{ font-size:12px; color:var(--ink-soft); font-style:italic; }

  /* ===== MODAL ===== */
  .modal-overlay{
    position:fixed; inset:0;
    background:rgba(23,26,36,.45);
    display:none;
    align-items:center;
    justify-content:center;
    z-index:100;
    padding:20px;
  }
  .modal-overlay.show{ display:flex; }

  .modal{
    background:var(--panel);
    border-radius:12px;
    width:100%;
    max-width:420px;
    padding:24px;
    box-shadow:0 20px 50px -12px rgba(23,26,36,.35);
  }
  .modal h2{
    font-family:'Space Grotesk', sans-serif;
    font-size:17px;
    margin-bottom:4px;
  }
  .modal p.hint{ font-size:12.5px; color:var(--ink-soft); margin-bottom:18px; }

  .form-group{ margin-bottom:14px; }
  .form-group label{
    display:block;
    font-size:12.5px;
    font-weight:600;
    margin-bottom:6px;
  }
  .form-group input,
  .form-group select{
    width:100%;
    padding:9px 12px;
    border:1px solid var(--line);
    border-radius:8px;
    font-family:inherit;
    font-size:13.5px;
    outline:none;
    background:var(--bg);
    color:var(--ink);
  }
  .form-group input:focus,
  .form-group select:focus{ border-color:var(--accent); background:var(--panel); }
  .form-group input:disabled,
  .form-group select:disabled{ opacity:.6; cursor:not-allowed; }

  .field-error{ font-size:11.5px; color:var(--danger); margin-top:4px; display:none; }
  .field-error.show{ display:block; }

  .modal-actions{
    display:flex;
    justify-content:flex-end;
    gap:8px;
    margin-top:18px;
  }

  .view-row{
    display:flex;
    justify-content:space-between;
    padding:10px 0;
    border-bottom:1px solid var(--line);
    font-size:13px;
  }
  .view-row:last-child{ border-bottom:none; }
  .view-row .label{ color:var(--ink-soft); }
  .view-row .value{ font-weight:600; }

  .empty-state{
    padding:50px 20px;
    text-align:center;
    color:var(--ink-soft);
    font-size:13.5px;
  }


  /* ===== ADMIN FOLDER BROWSER css===== */
    .admin-breadcrumb{
      display:flex;
      align-items:center;
      flex-wrap:wrap;
      gap:4px;
      font-size:13px;
      margin-bottom:14px;
    }
    .admin-breadcrumb .crumb{
      color:var(--ink-soft);
      cursor:pointer;
      padding:3px 6px;
      border-radius:5px;
      transition:background .12s, color .12s;
    }
    .admin-breadcrumb .crumb:hover{ background:var(--panel); color:var(--ink); }
    .admin-breadcrumb .crumb.current{ color:var(--ink); font-weight:600; cursor:default; }
    .admin-breadcrumb .crumb.current:hover{ background:none; }
    .admin-breadcrumb .sep{ color:var(--line); }

    .name-cell{ display:flex; align-items:center; gap:10px; }
    .name-cell .row-icon{
      width:28px; height:28px;
      border-radius:7px;
      display:flex; align-items:center; justify-content:center;
      flex-shrink:0;
    }
    .name-cell .row-icon.folder{ background:var(--amber-soft); color:var(--amber); }
    .name-cell .row-icon.file{ background:var(--accent-soft); color:var(--accent); }
    .name-cell .row-icon svg{ width:14px; height:14px; }
    .name-cell .row-name{ font-weight:600; color:var(--ink); }

    tr.folder-row{ cursor:pointer; }
    tr.folder-row:hover{ background:var(--bg); }
    tr.file-row{ cursor:default; }

    tr.file-row{ cursor:pointer; }
    tr.file-row:hover{ background:var(--bg); }
</style>
</head>
<body>
<?php 

if(!isset($_SESSION['user_id'])){

echo "<script> Swal.fire('warning', 'Login to access admin dashboard', 'warning').then( () => {window.location.href = '../index.php';} ); 
</script>";

exit;
}

if(!in_array($_SESSION['role'], [1,2])){
echo "<script> Swal.fire('warning', 'Access Denied', 'warning').then( () => {window.location.href = '../index.php';} ); 
   </script>";
exit;
}

?>
<div class="app">

<!-- ===== TOP BAR ===== -->
<header class="topbar">
  <div class="brand">
    <div class="brand-mark">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>
    </div>
    <span class="brand-name">File Manager</span>
    <span class="brand-tag">Admin</span>
  </div>

  <div class="user-block">
    <div class="user-avatar" id="currentUserInitial">A</div>
    <div class="user-meta">
      <div class="user-name" id="currentUserName">Aman Verma</div>
      <div class="user-role-tag" id="currentUserRoleTag">Admin</div>
    </div>
  </div>
</header>

<div class="layout">

  <!-- ===== SIDEBAR ===== -->
  <aside class="sidebar">
    <div class="sidebar-label">Admin</div>
    <ul class="nav-list">
      <li class="nav-item active" data-view="users">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        Manage users
      </li>
      <li class="nav-item" data-view="folders">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>
        Manage folders
      </li>
    </ul>

    <div class="sidebar-bottom">
      <div class="nav-item logout" id="logoutBtn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
        Logout
      </div>
    </div>
  </aside>

  <!-- ===== MAIN ===== -->
  <div class="content-area">
    <main class="main">

      <!-- ===== VIEW: MANAGE USERS ===== -->
      <div class="view-section active" id="view-users">

        <div class="page-head">
          <div>
            <h1>User management</h1>
            <p>View and manage everyone with access to Filebox.</p>
          </div>
          <button class="btn btn-primary" id="addUserBtn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
            Add user
          </button>
        </div>

        <!-- shown only when the logged-in user is NOT an admin -->
        <div class="restricted-banner" id="restrictedBanner" style="display:none;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          You're viewing this list as read-only. Only admins can add, edit, or delete users.
        </div>

        <div class="table-card">
          <table>
            <thead>
              <tr>
                <th>Name</th>
                <th>Role</th>
                <th>Status</th>
                <th class="col-actions">Actions</th>
              </tr>
            </thead>
            <tbody id="usersTableBody">
              <!-- rendered by JS / will be a PHP loop -->
            </tbody>
          </table>
          <div class="empty-state" id="emptyState" style="display:none;">No users found.</div>
        </div>

      </div>

      <!-- ===== VIEW: MANAGE FOLDERS (placeholder) ===== -->
      <div class="view-section" id="view-folders">
          <div class="page-head">
            <div>
              <h1>Folder management</h1>
              <p>Read-only view of every folder and file across all users.</p>
            </div>
          </div>

          <nav class="admin-breadcrumb" id="adminBreadcrumb"></nav>

          <div class="table-card">
            <table>
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Date modified</th>
                  <th>Type</th>
                  <th>Size</th>
                  <th>Created by</th>
                </tr>
              </thead>
              <tbody id="foldersTableBody"></tbody>
            </table>
            <div class="empty-state" id="foldersEmptyState" style="display:none;">This folder is empty.</div>
          </div>
</div>

    </main>
  </div>

</div>
</div>
<!-- ===== ADD / EDIT USER MODAL ===== -->
<div class="modal-overlay" id="userModalOverlay">
  <div class="modal">
    <h2 id="userModalTitle">Add user</h2>
    <p class="hint" id="userModalHint">Create a new account and set its initial access.</p>

    <form id="userForm">
      <div class="form-group">
        <label for="fieldName">Name</label>
        <input type="text" id="fieldName" placeholder="Full name">
        <span class="field-error" id="errorName">Name is required.</span>
      </div>

      <div class="form-group">
        <label for="fieldEmail">Email</label>
        <input type="email" id="fieldEmail" placeholder="name@example.com">
        <span class="field-error" id="errorEmail">Enter a valid email address.</span>
      </div>

      <div class="form-group" id="passwordGroup">
        <label for="fieldPassword">Password</label>
        <input type="password" id="fieldPassword" placeholder="Set a password" autocomplete="new-password">
        <span class="field-error" id="errorPassword">Password must be at least 6 characters.</span>
      </div>

      <div class="form-group">
        <label for="fieldRole">Role</label>
        <select id="fieldRole">
          <option value="1">Admin</option>
          <option value="2">Subadmin</option>
          <option value="3" selected>Normal user</option>
        </select>
      </div>

      <div class="form-group">
        <label for="fieldStatus">Status</label>
        <select id="fieldStatus">
          <option value="1" selected>Active</option>
          <option value="0">Inactive</option>
        </select>
      </div>
    </form>

    <div class="modal-actions">
      <button class="btn btn-ghost" id="cancelUserBtn">Cancel</button>
      <button class="btn btn-primary" id="saveUserBtn">Save</button>
    </div>
  </div>
</div>

<!-- ===== VIEW USER MODAL (read-only) ===== -->
<div class="modal-overlay" id="viewModalOverlay">
  <div class="modal">
    <h2>User details</h2>
    <p class="hint">Read-only overview.</p>

    <div class="view-row"><span class="label">Name</span><span class="value" id="viewName"></span></div>
    <div class="view-row"><span class="label">Email</span><span class="value" id="viewEmail"></span></div>
    <div class="view-row"><span class="label">Role</span><span class="value" id="viewRole"></span></div>
    <div class="view-row"><span class="label">Status</span><span class="value" id="viewStatus"></span></div>

    <div class="modal-actions">
      <button class="btn btn-ghost" id="closeViewBtn">Close</button>
    </div>
  </div>
</div>

<script>
/* ===================================================================
   Data comes from manages-users.php (session se currentUser, DB se users list)
   Each user: { user_id, name, email, role, status }
   role: 1 = admin, 2 = subadmin, 3 = normal user
   status: 1 = active, 0 = inactive
=================================================================== */

let currentUser = null;
let users = [];
let isAdmin = false;

function getdata(){
  fetch("manages-users.php")
    .then(res => res.json())
    .then(response => {
      if(response.error){
        alert(response.error);
        return;
      }
      currentUser = response.currentUser;
      users = response.users;
      isAdmin = currentUser.role === 1;

      initHeader();
      renderUsers();
    })
    .catch(err => console.error(err));
}

/* ===================== ICONS ===================== */
const viewIconSVG = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>`;
const editIconSVG = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>`;
const deleteIconSVG = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6h14Z"/></svg>`;

const roleLabels = { 1: "Admin", 2: "Subadmin", 3: "Normal user" };
const roleClasses = { 1: "badge-admin", 2: "badge-subadmin", 3: "badge-user" };

function escapeHTML(str){
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}

function initials(name){
  return name.trim().split(/\s+/).map(w => w[0]).slice(0,2).join('').toUpperCase();
}

/* ===================== INIT HEADER ===================== */
function initHeader(){
  document.getElementById('currentUserInitial').textContent = initials(currentUser.name);
  document.getElementById('currentUserName').textContent = currentUser.name;
  document.getElementById('currentUserRoleTag').textContent = roleLabels[currentUser.role];

  if(!isAdmin){
    document.getElementById('addUserBtn').style.display = 'none';
    document.getElementById('restrictedBanner').style.display = 'flex';
  }
}

/* ===================== RENDER TABLE ===================== */
function renderUsers(){
  const tbody = document.getElementById('usersTableBody');
  const emptyState = document.getElementById('emptyState');

  if(users.length === 0){
    tbody.innerHTML = '';
    emptyState.style.display = 'block';
    return;
  }
  emptyState.style.display = 'none';

  tbody.innerHTML = users.map(u => `
    <tr data-id="${u.user_id}">
      <td>
        <div class="user-cell">
          <div class="avatar">${initials(u.name)}</div>
          <div>
            <div class="name">${escapeHTML(u.name)}</div>
            <div class="email">${escapeHTML(u.email)}</div>
          </div>
        </div>
      </td>
      <td>
        <span class="badge ${roleClasses[u.role]}"><span class="badge-dot"></span>${roleLabels[u.role]}</span>
      </td>
      <td>
        <span class="badge ${u.status === 1 ? 'badge-active' : 'badge-inactive'}">
          <span class="badge-dot"></span>${u.status === 1 ? 'Active' : 'Inactive'}
        </span>
      </td>
      <td class="col-actions">
        ${isAdmin ? `
          <button class="icon-btn" data-action="view" title="View">${viewIconSVG}</button>
          <button class="icon-btn" data-action="edit" title="Edit">${editIconSVG}</button>
          <button class="icon-btn danger" data-action="delete" title="Delete">${deleteIconSVG}</button>
        ` : `<span class="no-access-text">No access</span>`}
      </td>
    </tr>
  `).join('');

  attachRowEvents();
}

function attachRowEvents(){
  document.querySelectorAll('#usersTableBody tr').forEach(row => {
    const id = parseInt(row.dataset.id, 10);

    row.querySelectorAll('[data-action]').forEach(btn => {
      btn.addEventListener('click', () => {
        const action = btn.dataset.action;
        if(action === 'view') openViewModal(id);
        if(action === 'edit') openUserModal('edit', id);
        if(action === 'delete') handleDeleteUser(id);
      });
    });
  });
}

/* ===================== VIEW MODAL ===================== */
function openViewModal(id){
  const user = users.find(u => u.user_id === id);
  if(!user) return;

  document.getElementById('viewName').textContent = user.name;
  document.getElementById('viewEmail').textContent = user.email;
  document.getElementById('viewRole').textContent = roleLabels[user.role];
  document.getElementById('viewStatus').textContent = user.status === 1 ? 'Active' : 'Inactive';

  document.getElementById('viewModalOverlay').classList.add('show');
}
document.getElementById('closeViewBtn').addEventListener('click', () => {
  document.getElementById('viewModalOverlay').classList.remove('show');
});
document.getElementById('viewModalOverlay').addEventListener('click', (e) => {
  if(e.target.id === 'viewModalOverlay') e.currentTarget.classList.remove('show');
});

/* ===================== ADD / EDIT MODAL ===================== */
let editingUserId = null;

const userModalOverlay = document.getElementById('userModalOverlay');
const fieldName = document.getElementById('fieldName');
const fieldEmail = document.getElementById('fieldEmail');
const fieldPassword = document.getElementById('fieldPassword');
const fieldRole = document.getElementById('fieldRole');
const fieldStatus = document.getElementById('fieldStatus');

function openUserModal(mode, id = null){
  if(!isAdmin) return; // guard, even though buttons are hidden for non-admins

  editingUserId = mode === 'edit' ? id : null;

  document.querySelectorAll('.field-error').forEach(el => el.classList.remove('show'));
  document.querySelectorAll('#userForm input, #userForm select').forEach(el => el.classList.remove('error'));

  if(mode === 'edit'){
    const user = users.find(u => u.user_id === id);
    document.getElementById('userModalTitle').textContent = 'Edit user';
    document.getElementById('userModalHint').textContent = 'Update this account\'s details.';
    fieldName.value = user.name;
    fieldEmail.value = user.email;
    fieldRole.value = user.role;
    fieldStatus.value = user.status;
    fieldPassword.value = '';
    fieldPassword.placeholder = 'Leave blank to keep current password';
    document.getElementById('passwordGroup').style.display = 'block';
  } else {
    document.getElementById('userModalTitle').textContent = 'Add user';
    document.getElementById('userModalHint').textContent = 'Create a new account and set its initial access.';
    fieldName.value = '';
    fieldEmail.value = '';
    fieldPassword.value = '';
    fieldPassword.placeholder = 'Set a password';
    fieldRole.value = '3';
    fieldStatus.value = '1';
    document.getElementById('passwordGroup').style.display = 'block';
  }

  userModalOverlay.classList.add('show');
}

document.getElementById('addUserBtn').addEventListener('click', () => openUserModal('add'));
document.getElementById('cancelUserBtn').addEventListener('click', closeUserModal);
userModalOverlay.addEventListener('click', (e) => { if(e.target === userModalOverlay) closeUserModal(); });

function closeUserModal(){
  userModalOverlay.classList.remove('show');
  editingUserId = null;
}

document.getElementById('saveUserBtn').addEventListener('click', saveUser);

function saveUser(){
  if(!isAdmin) return;

  let hasError = false;
  const name = fieldName.value.trim();
  const email = fieldEmail.value.trim();
  const password = fieldPassword.value;
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  if(!name){
    document.getElementById('errorName').classList.add('show');
    hasError = true;
  }
  if(!emailPattern.test(email)){
    document.getElementById('errorEmail').classList.add('show');
    hasError = true;
  }
  // password required only when adding a new user
  if(editingUserId === null && password.length < 6){
    document.getElementById('errorPassword').classList.add('show');
    hasError = true;
  }

  if(hasError) return;

  const payload = {
    action: editingUserId ? 'edit' : 'add',
    id: editingUserId,
    name: name,
    email: email,
    password: password, // edit ke waqt khaali bhej sakte ho, PHP ignore kar dega
    role: parseInt(fieldRole.value, 10),
    status: parseInt(fieldStatus.value, 10)
  };

  fetch("manages-users.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload)
  })
  .then(res => res.json())
  .then(response => {
    if(response.error){
      alert(response.error);
      return;
    }

    getdata();
    closeUserModal();
  })
  .catch(err => console.error(err));
}

/* ===================== DELETE ===================== */
function handleDeleteUser(id){
  if(!isAdmin) return;

  const user = users.find(u => u.user_id === id);
  if(!user) return;

  if(user.user_id === currentUser.user_id){
    Swal.fire("warning","You can't delete your own account", "warning");
    return;
  }

  
 // delete user
  Swal.fire({
  title: `Want to delete ${user.name}?`,
  text: "You won't be able to revert this!",
  icon: "warning",
  showCancelButton: true,
  confirmButtonColor: "#3085d6",
  cancelButtonColor: "#d33",
  confirmButtonText: "Yes, delete it!"
}).then((result) => {
  if (result.isConfirmed){

      const payload = { action: 'delete', id: id };
      fetch("manages-users.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
      })
      .then(res => res.json())
      .then(response => {
        if(response.error){
          alert(response.error);
          return;
        }
        getdata();
      })
      .catch(err => console.error(err));

  }
});

}

/* ===================== SIDEBAR NAVIGATION ===================== */
document.querySelectorAll('.nav-item[data-view]').forEach(item => {
  item.addEventListener('click', () => {
    const view = item.dataset.view;

    document.querySelectorAll('.nav-item[data-view]').forEach(i => i.classList.remove('active'));
    item.classList.add('active');

    document.querySelectorAll('.view-section').forEach(section => section.classList.remove('active'));
    document.getElementById('view-' + view).classList.add('active');

    if(view === 'folders' && !foldersLoaded){
      foldersLoaded = true;
      loadAdminFolders();
    }


  });
});

/* ===================== LOGOUT ===================== */
document.getElementById('logoutBtn').addEventListener('click', () => {
  

  Swal.fire({
  title: "Logout from account?",
  icon: "warning",
  showCancelButton: true,
  confirmButtonColor: "#3085d6",
  cancelButtonColor: "#d33",
  confirmButtonText: "Yes, logout"
}).then((result) => {
  if (result.isConfirmed){
       fetch('../get-api.php', { method: 'POST', body : JSON.stringify({ logout : 'logout'}) })
    .then(() => {
      window.location.href = '../index.php';
    })
    .catch(err => console.error(err));
  } 
});

  
});

/* ===================== INIT ===================== */
getdata();



/* ===================== MANAGE FOLDERS (admin, read-only) ===================== */

let adminFolders = [];
let adminFiles = [];
let adminActiveFolderId = null; // null = root
let foldersLoaded = false;

const folderIconSVG = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>`;
const fileIconSVG = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/></svg>`;

function loadAdminFolders(){
  fetch("get-all-folders.php")
    .then(res => res.json())
    .then(response => {
      if(response.error){ alert(response.error); return; }
      adminFolders = response.folders;
      adminFiles = response.files;
      adminActiveFolderId = null;
      renderAdminFolderView();
    })
    .catch(err => console.error(err));
}

function getAdminChildFolders(parentId){ return adminFolders.filter(f => f.parent_id === parentId); }
function getAdminFilesInFolder(folderId){ return adminFiles.filter(f => f.folder_id === folderId); }
function getAdminFolderById(id){ return adminFolders.find(f => f.id === id); }

function formatDate(dateStr){
  if(!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

// function for byte to kb convert
function formatSizeKB(bytes){
  if(!bytes || bytes === 0) return '-';
  const kb = bytes / 1024;
  return kb.toFixed(1) + ' KB';
}


function getFileType(file){
  const ext = (file.extension || file.name.split('.').pop() || '').toUpperCase();
  return ext || 'File';
}

function getAdminPathTo(folderId){
  const path = [];
  let current = getAdminFolderById(folderId);
  while(current){
    path.unshift(current);
    current = current.parent_id !== null ? getAdminFolderById(current.parent_id) : null;
  }
  return path;
}

function renderAdminBreadcrumb(){
  const bc = document.getElementById('adminBreadcrumb');
  const path = adminActiveFolderId === null ? [] : getAdminPathTo(adminActiveFolderId);

  let html = `<span class="crumb ${adminActiveFolderId === null ? 'current' : ''}" data-id="root">All folders</span>`;
  path.forEach((folder, i) => {
    const isLast = i === path.length - 1;
    html += `<span class="sep">/</span>`;
    html += `<span class="crumb ${isLast ? 'current' : ''}" data-id="${folder.id}">${escapeHTML(folder.name)}</span>`;
  });

  bc.innerHTML = html;

  bc.querySelectorAll('.crumb').forEach(el => {
    el.addEventListener('click', () => {
      const id = el.dataset.id;
      adminActiveFolderId = id === 'root' ? null : parseInt(id, 10);
      renderAdminFolderView();
    });
  });
}

function renderAdminFolderView(){
  renderAdminBreadcrumb();

  const tbody = document.getElementById('foldersTableBody');
  const emptyState = document.getElementById('foldersEmptyState');

  const childFolders = getAdminChildFolders(adminActiveFolderId);
  const childFiles = getAdminFilesInFolder(adminActiveFolderId);

  if(childFolders.length === 0 && childFiles.length === 0){
    tbody.innerHTML = '';
    emptyState.style.display = 'block';
    return;
  }
  emptyState.style.display = 'none';

  let html = '';

  childFolders.forEach(f => {
    html += `
      <tr class="folder-row" data-id="${f.id}">
        <td>
          <div class="name-cell">
            <div class="row-icon folder">${folderIconSVG}</div>
            <span class="row-name">${escapeHTML(f.name)}</span>
          </div>
        </td>
        <td>${formatDate(f.updated_at || f.created_at)}</td>
        <td>Folder</td>
        <td>-</td>
        <td>${escapeHTML(f.owner_name)}</td>
      </tr>`;
  });

  childFiles.forEach(f => {
    html += `
      <tr class="file-row" data-id="${f.id}" data-path="${escapeHTML(f.storage_path)}">
        <td>
          <div class="name-cell">
            <div class="row-icon file">${fileIconSVG}</div>
            <span class="row-name">${escapeHTML(f.name)}</span>
          </div>
        </td>
        <td>${formatDate(f.updated_at || f.created_at)}</td>
        <td>${getFileType(f)}</td>
        <td>${formatSizeKB(f.size)}</td>
        <td>${escapeHTML(f.owner_name)}</td>
      </tr>`;
  });

  tbody.innerHTML = html;

  document.querySelectorAll('#foldersTableBody tr.folder-row').forEach(row => {
    row.addEventListener('click', () => {
      adminActiveFolderId = parseInt(row.dataset.id, 10);
      renderAdminFolderView();
    });
  });

 //  file open by js
  document.querySelectorAll('#foldersTableBody tr.file-row').forEach(row => {
  row.addEventListener('click', () => {
    const fileurl = "http://localhost/test/File-Manager/uploads/" + row.dataset.path;
       window.open(fileurl, '_blank');
  });
});

}




</script>

</body>
</html>