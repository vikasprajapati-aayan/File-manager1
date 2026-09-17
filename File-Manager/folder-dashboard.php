<?php

require_once('database/database.php');

if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>File Manager | Dashboard</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="app">

  <!-- ===== TOP BAR ===== -->
  <header class="topbar">
    <div class="brand">
      <div class="brand-mark">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>
      </div>
      <span class="brand-name">File Manager</span>
    </div>

    <div class="search-wrap">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
      <input type="text" id="searchInput" placeholder="Search files and folders" auto-complete="off" >
       <div class="search-dropdown" id="searchDropdown"></div>
    </div>

    <!-- PHP: echo logged-in user's name / email here -->
    <div class="user-block">
      <div class="user-avatar" id="userInitial">A</div>
      <div class="user-meta">
        <div class="user-name" id="userName">Aman Verma</div>
        <div class="user-email" id="userEmail">aman@example.com</div>
      </div>

       <button class="logout-btn" id="logoutBtn" title="Logout">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
      <path d="M16 17l5-5-5-5"/>
      <path d="M21 12H9"/>
    </svg>
  </button>
  
    </div>
  </header>

  <div class="layout">

    <!-- ===== SIDEBAR ===== -->
    <aside class="sidebar">
      <div class="sidebar-label">My Folders</div>
      <ul class="tree" id="folderTree">
        <!-- rendered by JS / will be a PHP loop -->
      </ul>
      
      <div class="sidebar-divider"></div>
      <div class="sidebar-label">Other</div>
      <div class="trash-section">
        <button class="trash-btn" id="trashBtn">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6h14Z"/><path d="M10 11v6M14 11v6"/>
          </svg>
          Trash
        </button>
      </div>
    </aside>

    <!-- ===== MAIN ===== -->
    <main class="main">
      <div class="main-header">
        <nav class="breadcrumb" id="breadcrumb"></nav>

        <div class="main-toolbar">
          <div class="folder-title">
            <h1 id="currentFolderName">Home</h1>
            <span class="item-count" id="itemCount">0 items</span>
          </div>



        <div class="toolbar-actions">
            <button class="btn-sm" id="addFolderBtn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                  New Folder
            </button>

            <button class="btn-sm btn-sm-outline" id="addFileBtn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M17 8l-5-5-5 5"/><path d="M12 3v12"/></svg>
                Upload
            </button>

          <input type="file" id="fileInput" hidden multiple>

          <span class="toolbar-divider"></span>

            <button class="icon-btn active"  data-view="grid" title="Grid view">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            </button>

            <button class="icon-btn" data-view="list" title="List view">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
            </button>
          </div>
        </div>
      </div>

      <div class="content" id="content">
        <!-- rendered by JS / will be PHP loops -->
      </div>
    </main>

  </div>
</div>

<!-- ===== ADD FOLDER MODAL ===== -->
<div class="modal-overlay" id="modalOverlay">
  <div class="modal">
    <h2>New folder</h2>
    <p class="hint" id="modalHint">This folder will be created at the root level.</p>
    <input type="text" id="newFolderInput" placeholder="Folder name" maxlength="255">
    <div class="modal-actions">
      <button class="btn btn-ghost" id="cancelFolderBtn">Cancel</button>
      <button class="btn btn-primary" id="createFolderBtn">Create</button>
    </div>
  </div>
</div>

<!-- OPEN RENAME MODEL -->
<div class="modal-overlay" id="renameModalOverlay">
  <div class="modal">
    <h2>Rename</h2>
    <input type="text" id="renameInput" maxlength="255">
    <div class="modal-actions">
      <button class="btn btn-ghost" id="cancelRenameBtn">Cancel</button>
      <button class="btn btn-primary" id="confirmRenameBtn">Rename</button>
    </div>
  </div>
</div>

<!-- ===== CONTEXT MENU ===== -->
<div class="context-menu" id="contextMenu">
  <ul id="contextMenuList">
    <!-- JS isko dynamically fill karega — folder ya file ke hisaab se -->
  </ul>
</div>


<!-- MOVE TO MODEL -->
<div class="modal-overlay" id="moveModalOverlay">
  <div class="modal">
    <h2>Move to</h2>
    <p class="hint">Select a destination folder.</p>

    <div class="move-tree-wrap">
      <ul class="move-tree" id="moveTree"></ul>
    </div>

    <div class="modal-actions">
      <button class="btn btn-ghost" id="cancelMoveBtn">Cancel</button>
      <button class="btn btn-primary" id="confirmMoveBtn">Move here</button>
    </div>
  </div>
</div>

<!-- TRASH MODAL -->
<div class="modal-overlay" id="trashModalOverlay">
  <div class="modal trash-modal">
    <div class="modal-header">
      <h2>Trash</h2>
      <button class="close-btn" id="closeTrashBtn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6l-12 12M6 6l12 12"/></svg>
      </button>
    </div>
    
    <div class="trash-content" id="trashContent">
      <!-- Trash items will be rendered here -->
    </div>

    <div class="modal-actions">
      <button class="btn btn-ghost" id="closeBtnBottom">Close</button>
      <button class="btn btn-danger" id="emptyTrashBtn" style="display: none;">Empty trash</button>
    </div>
  </div>
</div>

<body>
<script src="script.js"></script>    
</html>