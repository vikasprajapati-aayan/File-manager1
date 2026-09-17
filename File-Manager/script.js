
// SweetAlert 2 को डायनामिकली लोड करने का सही तरीका
const swalScript = document.createElement('script');
swalScript.src = "https://cdn.jsdelivr.net/npm/sweetalert2@11";
document.head.appendChild(swalScript);



const currentUser = {
  name: "Vikas Prajapati",
  email: "vikas@example.com"
};

function getdata() {
  fetch("get-api.php")
    .then(res => res.json())
    .then(data => {
      folders = data.folders;
      files = data.files;
      renderTree();
      renderBreadcrumb();
      renderContent();
    })
    .catch(error => console.error(error));
}

getdata();






let activeFolderId = null; // null = root / Home

/* ===================== ICONS ===================== */
const folderIconSVG = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>`;
const fileIconSVG = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/></svg>`;
const emptyIconSVG = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>`;
const chevronSVG = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>`;



/* ===================== HELPERS ===================== */
function getChildFolders(parentId) {
  return folders.filter(f => f.parent_id === parentId);
}


function getFolderById(id) {
  return folders.find(f => f.folder_id === id);
}

function getFilesInFolder(folderId) {
  return files.filter(f => f.folder_id === folderId);
}



function formatDate(dateStr) {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function getFileType(file) {
  const ext = (file.extension || file.name.split('.').pop() || '').toUpperCase();
  return ext || 'File';
}

function formatSizeKB(bytes) {
  if (!bytes || bytes === 0) return '-';
  return (bytes / 1024).toFixed(1) + ' KB';
}

let currentView = 'grid'; // 'grid' ya 'list'

document.querySelectorAll('.icon-btn[data-view]').forEach(btn => {
  btn.addEventListener('click', () => {
    currentView = btn.dataset.view;
    document.querySelectorAll('.icon-btn[data-view]').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    renderContent();
  });
});


// walk parent_id chain to build breadcrumb (mirrors the recursive-CTE idea, done client side here)
function getPathTo(folderId) {
  const path = [];
  let current = getFolderById(folderId);
  while (current) {
    path.unshift(current);
    current = current.parent_id ? getFolderById(current.parent_id) : null;
  }
  return path;
}

/* ===================== SIDEBAR TREE ===================== */
function renderTree() {
  const treeEl = document.getElementById('folderTree');
  const rootFolders = getChildFolders(null);

  if (rootFolders.length === 0) {
    treeEl.innerHTML = `<li class="empty-sidebar">No folders yet.<br>Click "Add Folder" to create one.</li>`;
    return;
  }

  treeEl.innerHTML = rootFolders.map(f => renderTreeNode(f)).join('');
  attachTreeEvents();
}

function renderTreeNode(folder) {
  const children = getChildFolders(folder.folder_id);
  const hasChildren = children.length > 0;
  const isActive = folder.folder_id === activeFolderId;
  const isExpanded = isFolderInActivePath(folder.folder_id);

  return `
    <li class="${isExpanded ? 'expanded' : ''}" data-id="${folder.folder_id}">
      <div class="tree-row ${isActive ? 'active' : ''}" data-id="${folder.folder_id}">
        <span class="tree-toggle ${hasChildren ? '' : 'no-children'}">${chevronSVG}</span>
        <span class="folder-icon">${folderIconSVG}</span>
        <span class="name">${escapeHTML(folder.name)}</span>
      </div>
      ${hasChildren ? `<ul>${children.map(c => renderTreeNode(c)).join('')}</ul>` : ''}
    </li>
  `;
}

function isFolderInActivePath(folderId) {
  if (activeFolderId === null) return false;
  return getPathTo(activeFolderId).some(f => f.id === folderId);
}

function attachTreeEvents() {
  document.querySelectorAll('.tree-row').forEach(row => {
    row.addEventListener('click', (e) => {
      const id = parseInt(row.dataset.id, 10);

      // clicking the chevron only toggles expand/collapse
      if (e.target.closest('.tree-toggle') && getChildFolders(id).length) {
        const li = row.parentElement;
        li.classList.toggle('expanded');
        return;
      }
      openFolder(id);
    });
  });
}

/* ===================== BREADCRUMB ===================== */
function renderBreadcrumb() {
  const bc = document.getElementById('breadcrumb');
  const path = activeFolderId === null ? [] : getPathTo(activeFolderId);

  let html = `<span class="crumb ${activeFolderId === null ? 'current' : ''}" data-id="root">Home</span>`;
  path.forEach((folder, i) => {
    const isLast = i === path.length - 1;
    html += `<span class="sep">></span>`;
    html += `<span class="crumb ${isLast ? 'current' : ''}" data-id="${folder.folder_id}">${escapeHTML(folder.name)}</span>`;
  });

  bc.innerHTML = html;

  bc.querySelectorAll('.crumb').forEach(el => {
    el.addEventListener('click', () => {
      const id = el.dataset.id;
      openFolder(id === 'root' ? null : parseInt(id, 10));
    });
  });
}



/* ===================== MAIN CONTENT ===================== */

function renderContent() {
  const content = document.getElementById('content');
  const childFolders = getChildFolders(activeFolderId);
  const childFiles = getFilesInFolder(activeFolderId);
  const totalItems = childFolders.length + childFiles.length;

  document.getElementById('currentFolderName').textContent =
    activeFolderId === null ? 'Home' : getFolderById(activeFolderId).name;
  document.getElementById('itemCount').textContent = `${totalItems} item${totalItems === 1 ? '' : 's'}`;

  if (totalItems === 0) {
    content.innerHTML = `
      <div class="empty-state">
        ${emptyIconSVG}
        <p class="empty-title">This folder is empty</p>
        <p>Create a subfolder or upload a file to get started.</p>
      </div>`;
    return;
  }

  content.innerHTML = currentView === 'list'
    ? renderListView(childFolders, childFiles)
    : renderGridView(childFolders, childFiles);

  attachItemEvents();
}

function renderGridView(childFolders, childFiles) {
  let html = '';
  if (childFolders.length) {
    html += `<div class="section-label">Folders</div><div class="grid">`;
    html += childFolders.map(f => `
      <div class="item-card folder-card" data-id="${f.folder_id}">
        <div class="item-icon folder">${folderIconSVG}</div>
        <div class="item-name">${escapeHTML(f.name)}</div>
        <div class="item-meta">${getChildFolders(f.folder_id).length + getFilesInFolder(f.id).length} items</div>
      </div>`).join('');
    html += `</div>`;
  }
  if (childFiles.length) {
    html += `<div class="section-label">Files</div><div class="grid">`;
    html += childFiles.map(f => `
      <div class="item-card file-card" data-id="${f.id}">
        <div class="item-icon file">${fileIconSVG}</div>
        <div class="item-name">${escapeHTML(f.name)}</div>
        <div class="item-meta">${formatSizeKB(f.size)}</div>
      </div>`).join('');
    html += `</div>`;
  }
  return html;
}

function renderListView(childFolders, childFiles) {
  let html = `
    <div class="list-header">
      <div>Name</div>
      <div>Date modified</div>
      <div>Type</div>
      <div class="align-right">Size</div>
    </div>`;

  // pehle sare folders
  childFolders.forEach(f => {
    html += `
      <div class="list-row folder-card" data-id="${f.folder_id}">
        <div class="list-name">
          <span class="item-icon folder">${folderIconSVG}</span>
          <span>${escapeHTML(f.name)}</span>
        </div>
        <div>${formatDate(f.updated_at || f.created_at)}</div>
        <div>Folder</div>
        <div class="align-right">-</div>
      </div>`;
  });

  // fir sari files
  childFiles.forEach(f => {
    html += `
      <div class="list-row file-card" data-id="${f.id}">
        <div class="list-name">
          <span class="item-icon file">${fileIconSVG}</span>
          <span>${escapeHTML(f.name)}</span>
        </div>
        <div>${formatDate(f.updated_at || f.created_at)}</div>
        <div>${getFileType(f)}</div>
        <div class="align-right">${formatSizeKB(f.size)}</div>
      </div>`;
  });

  return `<div class="list-view">${html}</div>`;
}

function attachItemEvents() {
  document.querySelectorAll('.folder-card').forEach(card => {
    card.addEventListener('click', () => openFolder(parseInt(card.dataset.id, 10)));
    card.addEventListener('contextmenu', (e) => openContextMenu(e, 'folder', parseInt(card.dataset.id, 10)));
  });

  document.querySelectorAll('.file-card').forEach(card => {
    card.addEventListener('click', () => {
      const fileId = parseInt(card.dataset.id, 10);
      const file = files.find(f => f.id === fileId);
      openFile(file);
    });
    card.addEventListener('contextmenu', (e) => openContextMenu(e, 'file', parseInt(card.dataset.id, 10)));
  });
}







/* ===================== NAVIGATION ===================== */
function openFolder(id) {
  // alert(id);
  activeFolderId = id;
  renderTree();
  renderBreadcrumb();
  renderContent();
}

//OPEN FILE
function openFile(file) {
  const fileurl = "http://localhost/test/File-Manager/uploads/" + file.storage_path;
  window.open(fileurl, '_blank');
}

/* ===================== ADD FOLDER MODAL ===================== */
const modalOverlay = document.getElementById('modalOverlay');
const newFolderInput = document.getElementById('newFolderInput');
const modalHint = document.getElementById('modalHint');

document.getElementById('addFolderBtn').addEventListener('click', () => {
  modalHint.textContent = activeFolderId === null
    ? 'This folder will be created at the root level.'
    : `This folder will be created inside "${getFolderById(activeFolderId).name}".`;
  newFolderInput.value = '';
  modalOverlay.classList.add('show');
  setTimeout(() => newFolderInput.focus(), 50);
});


document.getElementById('addFileBtn').addEventListener('click', () => {
  document.getElementById('fileInput').click();
});

document.getElementById('fileInput').addEventListener('change', (e) => {
  const selectedFiles = e.target.files;
  if (!selectedFiles.length) return;

  const formData = new FormData();
  for (let i = 0; i < selectedFiles.length; i++) {
    formData.append('files[]', selectedFiles[i]);
  }
  formData.append('folder_id', activeFolderId === null ? '' : activeFolderId);

  fetch("upload-file.php", { method: "POST", body: formData })
    .then(res => res.json())
    .then(response => {

      if (response.error) {
        console.log('STEP 2 - error block chal raha hai:', response.error);
        alert(response.error);
        return;
      }


      files.push(...response.files);


      if (response.failed?.length) {
        alert('Failed to upload ' + response.failed.join(', '));
      }

      renderContent();
      e.target.value = '';
    })


    .catch(err => console.error(err));
});



document.getElementById('cancelFolderBtn').addEventListener('click', closeModal);
modalOverlay.addEventListener('click', (e) => { if (e.target === modalOverlay) closeModal(); });

function closeModal() {
  modalOverlay.classList.remove('show');
}

document.getElementById('createFolderBtn').addEventListener('click', createFolder);
newFolderInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') createFolder(); });

function createFolder() {
  const name = newFolderInput.value.trim();
  if (!name) return;

  fetch("get-api.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      new_folder: name,
      parent_id: activeFolderId   // ID yahan se nahi bhejni, DB khud banayegi
    })
  })
    .then(res => res.json())
    .then(newFolder => {

      folders.push(newFolder);


      closeModal();
      renderTree();
      renderContent();
    })
    .catch(err => console.error(err));
}

/* ===================== SEARCH (simple client-side filter demo) ===================== */
document.getElementById('searchInput').addEventListener('input', (e) => {
  const q = e.target.value.trim().toLowerCase();
  document.querySelectorAll('.item-card').forEach(card => {
    const name = card.querySelector('.item-name').textContent.toLowerCase();
    card.style.display = name.includes(q) ? '' : 'none';
  });
});

/* ===================== UTIL ===================== */
function escapeHTML(str) {
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}

/* ===================== INIT ===================== */
function initUser() {
  document.getElementById('userName').textContent = currentUser.name;
  document.getElementById('userEmail').textContent = currentUser.email;
  document.getElementById('userInitial').textContent = currentUser.name.charAt(0).toUpperCase();
}

initUser();






// RIGHT CLICK actions on files and folder

const contextMenu = document.getElementById('contextMenu');
const contextMenuList = document.getElementById('contextMenuList');
let contextTarget = null; // { type: 'folder'|'file', id: number }
let payload = null;

const icons = {
  copy: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>`,
  paste: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="2" width="8" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/></svg>`,
  move: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 9l-3 3 3 3M9 5l3-3 3 3M15 19l-3 3-3-3M19 9l3 3-3 3M2 12h20M12 2v20"/></svg>`,
  rename: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>`,
  delete: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6h14Z"/></svg>`
};

// Folder ke options
function getFolderMenuOptions() {
  return [
    { label: "Copy", action: "copy", icon: icons.copy, disabled: true },
    { label: "Paste", action: "paste", icon: icons.paste, disabled: !clipboard },
    { label: "Move to", action: "moveto", icon: icons.move },

    { label: "Rename", action: "rename", icon: icons.rename },
    { label: "Delete", action: "delete", icon: icons.delete, danger: true }
  ];
}

// File ke options
function getFileMenuOptions() {
  return [
    { label: "Copy", action: "copy", icon: icons.copy },
    { label: "Move to", action: "moveto", icon: icons.move },
    { label: "Rename", action: "rename", icon: icons.rename },
    { label: "Delete", action: "delete", icon: icons.delete, danger: true }
  ];
}

let clipboard = null; // baad mein copy/paste logic ke liye use hoga

function openContextMenu(e, type, id) {
  e.preventDefault();
  contextTarget = { type, id };

  const options = type === 'folder' ? getFolderMenuOptions() : getFileMenuOptions();

  contextMenuList.innerHTML = options.map(opt => {
    return `
      <li class="${opt.danger ? 'danger' : ''} ${opt.disabled ? 'disabled' : ''}" data-action="${opt.action}">
        ${opt.icon}
        <span>${opt.label}</span>
      </li>`;
  }).join('');

  // menu ko cursor ke position pe dikhao, screen se bahar na jaye uska bhi khayal
  const menuWidth = 180;
  const menuHeight = options.length * 36;
  let x = e.clientX;
  let y = e.clientY;
  if (x + menuWidth > window.innerWidth) x = window.innerWidth - menuWidth - 10;
  if (y + menuHeight > window.innerHeight) y = window.innerHeight - menuHeight - 10;

  contextMenu.style.left = x + 'px';
  contextMenu.style.top = y + 'px';
  contextMenu.classList.add('show');
}

function closeContextMenu() {
  contextMenu.classList.remove('show');
  contextTarget = null;
}

// Menu ke bahar click karne pe band ho jaye
document.addEventListener('click', closeContextMenu);
document.addEventListener('scroll', closeContextMenu, true);

// Menu item click — abhi sirf console.log, functionality baad mein


contextMenuList.addEventListener('click', (e) => {
  const li = e.target.closest('li');
  if (!li || li.classList.contains('disabled')) return;

  const action = li.dataset.action;



  target = contextTarget;


  if (action === 'delete') {
    const confirmMsg = contextTarget.type === 'folder'
      ? 'MOVE this folder to Trash?'
      : 'MOVE THIS FILE to Trash?';

    
      Swal.fire({
      title: confirmMsg,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, move it!"
    }).then((result) => {
        if (result.isConfirmed){
              payload = {
            type: target.type,   // 'folder' ya 'file'
            id: target.id,
            action: action
          }
          sendAction(payload);
          closeContextMenu();
          return;
        } 
    });
  }


  if (action === 'rename') {
    const currentItem = target.type === 'folder'
      ? getFolderById(target.id)
      : files.find(f => f.id === target.id);

    
    renameItem(currentItem);

    async function renameItem(currentItem) {

            const { value: newName } = await Swal.fire({
                title: "Rename",
                input: "text",
                inputValue: currentItem.name,
                inputPlaceholder: "Enter new name",
                showCancelButton: true,
                confirmButtonText: "Rename",
                cancelButtonText: "Cancel",
                inputValidator: (value) => {
                    if (!value.trim()) {
                        return "Please enter a name";
                    }
                }
            });

            if (newName) {
                payload = { type: target.type, id: target.id, name: newName.trim(), action: action };
                sendAction(payload);
              closeContextMenu();
              return;
            }
    }

  }
  
  if (action === 'copy') {
    clipboard = { type: target.type, id: target.id };
    closeContextMenu();
    return;   // API call nahi, sirf memory mein save kiya
  }

  if (action === 'paste') {
    if (!clipboard) { closeContextMenu(); return; }

    payload = {
      action: 'paste',
      type: clipboard.type,        // 'file'
      id: clipboard.id,            // jo file copy hui thi
      target_folder_id: target.type === 'folder' ? target.id : target.folder_id
    };
    sendAction(payload);
    closeContextMenu();
    return;
  }

  if (action === 'moveto') {
    handleMoveTo(target);
    closeContextMenu();
    return;
  }


  if (!payload) return;


});




function sendAction(payload) {
  fetch("actions.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload)
  })
    .then(res => res.json())
    .then(response => {

      if (response.error) {
        alert(response.error);
        return;
      }

      if (response.action === 'delete' || response.action === 'moveto') {
        getdata();
      }
      if (response.action === 'rename') {

        if (response.type === 'folder') {
          folders.find(f => f.folder_id === response.id).name = response.newName;
        }

        if (response.type === 'file') {
          files.find(f => f.id === response.id).name = response.newName;
        }
      }
      if (response.action === 'paste') {
        files.push(response.newFile);   // PHP naya file record wapas bhejega, array mein daal do
      }


      renderTree();
      renderBreadcrumb();
      renderContent();
    })
    .catch(err => console.error(err));
}










//LOGOUT BUTTON
document.getElementById('logoutBtn').addEventListener('click', () => {

 Swal.fire({
  title: "Are you sure want to logout?",
  icon: "warning",
  showCancelButton: true,
  confirmButtonColor: "#3085d6",
  cancelButtonColor: "#d33",
  confirmButtonText: "Yes, logout!"
}).then((result) => {
  if (result.isConfirmed){

        fetch('get-api.php', {
        method: 'POST',
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ logout: 'logout' })
      }).then(res => res.json())
        .then(res => {
          if (res.success) {
            window.location.href = 'index.php';
          }
        })
        .catch(err => console.error(err));
      } 
  
});
});



// search input
const searchInput = document.getElementById('searchInput');
const searchDropdown = document.getElementById('searchDropdown');

function getPathText(folderId) {
  const path = getPathTo(folderId);
  return 'Home' + (path.length ? ' / ' + path.map(f => f.name).join(' / ') : '');
}

searchInput.addEventListener('input', (e) => {
  const query = e.target.value.trim().toLowerCase();

  if (!query) {
    searchDropdown.classList.remove('show');
    return;
  }

  const matchedFolders = folders.filter(f => f.name.toLowerCase().includes(query));
  const matchedFiles = files.filter(f => f.name.toLowerCase().includes(query));

  renderSearchResults(matchedFolders, matchedFiles);
});

function renderSearchResults(matchedFolders, matchedFiles) {
  const totalResults = matchedFolders.length + matchedFiles.length;

  if (totalResults === 0) {
    searchDropdown.innerHTML = `<div class="search-no-results">No results found</div>`;
    searchDropdown.classList.add('show');
    return;
  }

  let html = '';

  matchedFolders.forEach(f => {
    const pathText = getPathText(f.parent_id); // folder khud ke parent tak ka path
    html += `
      <div class="search-result-item" data-type="folder" data-id="${f.folder_id}">
        <div class="item-icon folder">${folderIconSVG}</div>
        <div class="search-result-text">
          <div class="search-result-name">${escapeHTML(f.name)}</div>
          <div class="search-result-path">${escapeHTML(pathText)}</div>
        </div>
      </div>`;
  });

  matchedFiles.forEach(f => {
    const pathText = getPathText(f.folder_id); // file jis folder ke andar hai uska path
    html += `
      <div class="search-result-item" data-type="file" data-id="${f.id}" data-folder="${f.folder_id ?? ''}">
        <div class="item-icon file">${fileIconSVG}</div>
        <div class="search-result-text">
          <div class="search-result-name">${escapeHTML(f.name)}</div>
          <div class="search-result-path">${escapeHTML(pathText)}</div>
        </div>
      </div>`;
  });

  searchDropdown.innerHTML = html;
  searchDropdown.classList.add('show');

  attachSearchResultEvents();
}

function attachSearchResultEvents() {
  document.querySelectorAll('.search-result-item').forEach(item => {
    item.addEventListener('click', () => {
      const type = item.dataset.type;
      const id = parseInt(item.dataset.id, 10);

      if (type === 'folder') {
        openFolder(id);
      } else {
        const folderId = item.dataset.folder ? parseInt(item.dataset.folder, 10) : null;
        openFolder(folderId); // us file ke parent folder mein le jao
      }

      searchDropdown.classList.remove('show');
      searchInput.value = '';
    });
  });
}

// dropdown ke bahar click karne pe band ho jaye
document.addEventListener('click', (e) => {
  if (!e.target.closest('.search-wrap')) {
    searchDropdown.classList.remove('show');
  }
});





// TREE RENDER AND MOVE TO SELECTION LOGIC

let moveTarget = null;
let selectedDestination = null;
let excludedMoveIds = [];

// const chevronSVG = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>`;

// Get all descendant folder IDs recursively
function getAllDescendantFolderIds(folderId) {
  const descendants = [];
  const children = getChildFolders(folderId);

  children.forEach(child => {
    descendants.push(child.folder_id);
    descendants.push(...getAllDescendantFolderIds(child.folder_id));
  });

  return descendants;
}

function handleMoveTo(target) {
  moveTarget = target;
  selectedDestination = undefined;

  excludedMoveIds = [];
  if (target.type === 'folder') {
    excludedMoveIds = getAllDescendantFolderIds(target.id);
    excludedMoveIds.push(target.id);
  }

  renderMoveTree();
  document.getElementById('moveModalOverlay').classList.add('show');
}

function renderMoveTree() {
  const treeEl = document.getElementById('moveTree');

  // "Home (root)" ko bhi ek selectable option ki tarah upar rakhte hain
  let html = `
    <li>
      <div class="move-tree-row" data-id="root">
        <span class="move-tree-toggle no-children"></span>
        <span>Home (root)</span>
      </div>
    </li>`;

  const rootFolders = getChildFolders(null);
  html += rootFolders.map(f => renderMoveTreeNode(f)).join('');

  treeEl.innerHTML = html;
  attachMoveTreeEvents();
}

function renderMoveTreeNode(folder) {
  const isExcluded = excludedMoveIds.includes(folder.folder_id);
  const children = isExcluded ? [] : getChildFolders(folder.folder_id); // excluded ke andar jaane ki zaroorat nahi
  const hasChildren = children.length > 0;

  return `
    <li data-id="${folder.folder_id}">
      <div class="move-tree-row ${isExcluded ? 'disabled' : ''}" data-id="${folder.folder_id}">
        <span class="move-tree-toggle ${hasChildren ? '' : 'no-children'}">${chevronSVG}</span>
        <span class="folder-icon">${folderIconSVG}</span>
        <span>${escapeHTML(folder.name)}</span>
      </div>
      ${hasChildren ? `<ul>${children.map(c => renderMoveTreeNode(c)).join('')}</ul>` : ''}
    </li>`;
}

function attachMoveTreeEvents() {
  document.querySelectorAll('.move-tree-row').forEach(row => {
    row.addEventListener('click', (e) => {
      // chevron pe click sirf expand/collapse kare, select na kare
      if (e.target.closest('.move-tree-toggle')) {
        const li = row.parentElement;
        if (li.querySelector('ul')) li.classList.toggle('expanded');
        return;
      }

      if (row.classList.contains('disabled')) return;

      document.querySelectorAll('.move-tree-row').forEach(r => r.classList.remove('selected'));
      row.classList.add('selected');

      const id = row.dataset.id;
      selectedDestination = id === 'root' ? null : parseInt(id, 10);
    });
  });
}



//CLOSE AND SUBMIT MODEL

document.getElementById('cancelMoveBtn').addEventListener('click', closeMoveModal);
document.getElementById('moveModalOverlay').addEventListener('click', (e) => {
  if (e.target.id === 'moveModalOverlay') closeMoveModal();
});

function closeMoveModal() {
  document.getElementById('moveModalOverlay').classList.remove('show');
  moveTarget = null;
  selectedDestination = undefined;
}

document.getElementById('confirmMoveBtn').addEventListener('click', submitMove);

function submitMove() {
  if (selectedDestination === undefined) {
    alert('Please select a destination folder.');
    return;
  }

  const { type, id } = moveTarget;

  // agar wahi purani jagah pe hi move kar rahe ho, kuch mat karo
  const currentItem = type === 'folder' ? getFolderById(id) : files.find(f => f.id === id);
  const currentParent = type === 'folder' ? currentItem.parent_id : currentItem.folder_id;

  if (currentParent === selectedDestination) {
    closeMoveModal();
    return;
  }

  sendAction({
    action: 'moveto',
    type: type,
    id: id,
    target_folder_id: selectedDestination
  });

  closeMoveModal();
}

/* ===================== TRASH FUNCTIONALITY ===================== */

let trashFolders = [];
let trashFiles = [];

document.getElementById('trashBtn').addEventListener('click', openTrash);
document.getElementById('closeTrashBtn').addEventListener('click', closeTrash);
document.getElementById('closeBtnBottom').addEventListener('click', closeTrash);
document.getElementById('trashModalOverlay').addEventListener('click', (e) => {
  if (e.target.id === 'trashModalOverlay') closeTrash();
});

function openTrash() {
  fetch("get-api.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ get_trash: true })
  })
    .then(res => res.json())
    .then(data => {
      trashFolders = data.folders;
      trashFiles = data.files;
      renderTrashItems();
      document.getElementById('trashModalOverlay').classList.add('show');
    })
    .catch(err => console.error(err));
}

function closeTrash() {
  document.getElementById('trashModalOverlay').classList.remove('show');
}

function renderTrashItems() {
  const trashContent = document.getElementById('trashContent');
  const emptyTrashBtn = document.getElementById('emptyTrashBtn');
  const totalItems = trashFolders.length + trashFiles.length;

  if (totalItems === 0) {
    trashContent.innerHTML = `
      <div class="empty-state">
        ${emptyIconSVG}
        <p class="empty-title">Trash is empty</p>
        <p>Deleted items will appear here.</p>
      </div>`;
    emptyTrashBtn.style.display = 'none';
    return;
  }

  emptyTrashBtn.style.display = 'block';

  // Sort folders by deletion date (most recent first)
  const sortedFolders = [...trashFolders].sort((a, b) => {
    const dateA = new Date(a.updated_at || a.created_at);
    const dateB = new Date(b.updated_at || b.created_at);
    return dateB - dateA;
  });

  // Sort files by deletion date (most recent first)
  const sortedFiles = [...trashFiles].sort((a, b) => {
    const dateA = new Date(a.updated_at || a.created_at);
    const dateB = new Date(b.updated_at || b.created_at);
    return dateB - dateA;
  });

  let html = '<div class="trash-list">';

  if (sortedFolders.length) {
    html += '<div class="trash-section-label">Folders</div>';
    sortedFolders.forEach(f => {
      html += `
        <div class="trash-item" data-type="folder" data-id="${f.folder_id}">
          <div class="trash-item-icon">${folderIconSVG}</div>
          <div class="trash-item-info">
            <div class="trash-item-name">${escapeHTML(f.name)}</div>
            <div class="trash-item-date">Deleted: ${formatDate(f.updated_at || f.created_at)}</div>
          </div>
          <div class="trash-item-actions">
            <button class="btn-icon restore-trash-btn" title="Restore">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
            </button>
            <button class="btn-icon delete-trash-btn" title="Delete permanently">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6h14Z"/></svg>
            </button>
          </div>
        </div>`;
    });
  }

  if (sortedFiles.length) {
    html += '<div class="trash-section-label">Files</div>';
    sortedFiles.forEach(f => {
      html += `
        <div class="trash-item" data-type="file" data-id="${f.id}">
          <div class="trash-item-icon">${fileIconSVG}</div>
          <div class="trash-item-info">
            <div class="trash-item-name">${escapeHTML(f.name)}</div>
            <div class="trash-item-date">Deleted: ${formatDate(f.updated_at || f.created_at)} | ${formatSizeKB(f.size)}</div>
          </div>
          <div class="trash-item-actions">
            <button class="btn-icon restore-trash-btn" title="Restore">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
            </button>
            <button class="btn-icon delete-trash-btn" title="Delete permanently">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6h14Z"/></svg>
            </button>
          </div>
        </div>`;
    });
  }

  html += '</div>';
  trashContent.innerHTML = html;

  // Attach restore button events
  document.querySelectorAll('.restore-trash-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const item = e.target.closest('.trash-item');
      const type = item.dataset.type;
      const id = parseInt(item.dataset.id, 10);

      restoreTrashItem(type, id);
    });
  });

  // Attach delete button events
  document.querySelectorAll('.delete-trash-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const item = e.target.closest('.trash-item');
      const type = item.dataset.type;
      const id = parseInt(item.dataset.id, 10);

      
      swal.fire({
        title: "delete permanently?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!"
    })
    .then((result) => {
      if (result.isConfirmed){
            permanentlyDeleteItem(type, id);
      } 
    });

     
    });
  });
}

function permanentlyDeleteItem(type, id) {
  const payload = {
    action: 'permanent_delete',
    type: type,
    id: id
  };

  fetch("actions.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload)
  })
    .then(res => res.json())
    .then(response => {
      if (response.error) {
        alert(response.error);
        return;
      }

      // Remove from local array and re-render
      if (type === 'folder') {
        trashFolders = trashFolders.filter(f => f.folder_id !== id);
      } else {
        trashFiles = trashFiles.filter(f => f.id !== id);
      }

      renderTrashItems();
    })
    .catch(err => console.error(err));
}

function restoreTrashItem(type, id) {
  const payload = {
    action: 'restore',
    type: type,
    id: id
  };

  fetch("actions.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload)
  })
    .then(res => res.json())
    .then(response => {
      if (response.error) {
        alert(response.error);
        return;
      }

      // Remove from local trash array
      if (type === 'folder') {
        trashFolders = trashFolders.filter(f => f.folder_id !== id);
      } else {
        trashFiles = trashFiles.filter(f => f.id !== id);
      }

      // Refresh main view data to show restored items
      getdata();
      renderTrashItems();
     Swal.fire( "Success", "Item restored successfully!", "success" );

    })
    .catch(err => console.error(err));
    }

// Empty trash button
document.getElementById('emptyTrashBtn').addEventListener('click', () => {



    Swal.fire({
    title: "Empty Trash?",
    text: "You won't be able to revert this!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Yes, delete it!"
  }).then((result) => {
    if (result.isConfirmed) {
      deleteAllTrashItems();
    }
  }); 

 function deleteAllTrashItems(){
  const allIds = [...trashFolders.map(f => ({ type: 'folder', id: f.folder_id })), ...trashFiles.map(f => ({ type: 'file', id: f.id }))];

  if (allIds.length === 0) return;

  let deleted = 0;
  allIds.forEach(item => {
    const payload = {
      action: 'permanent_delete',
      type: item.type,
      id: item.id
    };

    fetch("actions.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
    })
      .then(res => res.json())
      .then(response => {
        deleted++;
        if (deleted === allIds.length) {
          trashFolders = [];
          trashFiles = [];
          renderTrashItems();
          
          Swal.fire("Success", "Trash emptied successfully!", "success");
        }
      })
      .catch(err => console.error(err));
  });
}
});

