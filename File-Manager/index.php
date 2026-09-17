<?php
require_once('database/database.php');
// header("Content-Type: application/json; charset=UTF-8");

if($_SERVER['REQUEST_METHOD'] === 'POST'){

  $data = json_decode(file_get_contents('php://input'), 1);

  $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
  $stmt->execute([$data['email']]);

  $res = $stmt->fetch(PDO::FETCH_ASSOC);

  if($stmt->rowCount() == 1){
     if($data['password'] === $res['password']){

        $_SESSION["user_id"] = $res['user_id'];
        $_SESSION['user_name'] = $res['name']; // for user file dashboard
        $_SESSION['email'] = $res['email']; // for user file dashboard
        
        $_SESSION['role'] = $res['role'];

        if(in_array($res['role'], [1, 2], true)){
           echo json_encode(['success' => true, 'msg' => 'Login successful', 'redirect' => "admin-panel/"]);
        exit;
        }
        else{
          echo json_encode(['success' => true, 'msg' => 'Login successful', 'redirect' => 'folder-dashboard.php']);
        exit;
        }

        
     }else {
        echo json_encode(['success' => false, 'msg' => 'password not matched']);
        exit;
     }
  }else{
    echo json_encode(['success' => false, 'msg'=> 'User not found' ]);
    exit;
  }

}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | Filebox</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="login-page">

<div class="login-card">

  <div class="brand">
    <div class="brand-mark">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>
    </div>
    <span class="brand-name">Filebox</span>
  </div>

  <div class="heading">
    <h1>Log in to your account</h1>
    <p>Enter your details to access your files</p>
  </div>

  <!-- server-side error (wrong password, account not found, etc.) -->
  <div class="banner" id="serverError">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
    <span id="serverErrorText">Invalid email or password.</span>
  </div>

  <form id="loginForm" novalidate>

    <div class="form-group">
      <label for="email">Email</label>
      <div class="input-wrap">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 6-10 7L2 6"/></svg>
        <input type="email" id="email" name="email" placeholder="you@example.com" autocomplete="email">
      </div>
      <span class="field-error" id="emailError">Please enter a valid email address.</span>
    </div>

    <div class="form-group">
      <label for="password">Password</label>
      <div class="input-wrap">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        <input type="password" id="password" name="password" placeholder="Enter your password" autocomplete="current-password" style="padding-right:38px;">
        <button type="button" class="toggle-pass" id="togglePass" aria-label="Show password">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
        </button>
      </div>
      <span class="field-error" id="passwordError">Password is required.</span>
    </div>

    <div class="form-meta">
      <label class="remember">
        <input type="checkbox" id="remember" name="remember">
        Remember me
      </label>
      <a href="#" class="forgot-link">Forgot password?</a>
    </div>

    <button type="submit" class="submit-btn" id="submitBtn">
      <span class="spinner"></span>
      <span class="btn-text">Log in</span>
    </button>
  </form>

  <div class="signup-row">
    Don't have an account? <a href="#">Sign up</a>
  </div>

</div>

<script>
    /* ===================================================================
    Minimal JS — client side field checks only.
    Real authentication happens in login.php (PHP) via fetch below.
    =================================================================== */

const form = document.getElementById('loginForm');
const emailInput = document.getElementById('email');
const passwordInput = document.getElementById('password');
const emailError = document.getElementById('emailError');
const passwordError = document.getElementById('passwordError');
const serverError = document.getElementById('serverError');
const serverErrorText = document.getElementById('serverErrorText');
const submitBtn = document.getElementById('submitBtn');

// show/hide password
document.getElementById('togglePass').addEventListener('click', () => {
  const isPassword = passwordInput.type === 'password';
  passwordInput.type = isPassword ? 'text' : 'password';
});

form.addEventListener('submit', (e) => {
  e.preventDefault();

  serverError.classList.remove('show');
  emailInput.classList.remove('error');
  passwordInput.classList.remove('error');
  emailError.classList.remove('show');
  passwordError.classList.remove('show');

  const email = emailInput.value.trim();
  const password = passwordInput.value;
  let hasError = false;

  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if(!emailPattern.test(email)){
    emailInput.classList.add('error');
    emailError.classList.add('show');
    hasError = true;
  }

  if(!password){
    passwordInput.classList.add('error');
    passwordError.classList.add('show');
    hasError = true;
  }

  if(hasError) return;

  submitBtn.classList.add('loading');
  submitBtn.disabled = true;

  fetch('', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      email: email,
      password: password,
      remember: document.getElementById('remember').checked
    })
  })
  .then(res => res.json())
  .then(response => {
    if(response.error){
      serverErrorText.textContent = response.error;
      serverError.classList.add('show');
      return;
    }

    if(response.success){
       Swal.fire( "Success", "login successful", "success" ).then((result) => {

        if (result.isConfirmed) {
          window.location.href =  response.redirect; 
        }
      });

  }else{
  swal.fire("error", response.msg, "error");
  }

  })
  .catch(() => {
    serverErrorText.textContent = 'Something went wrong. Please try again.';
    serverError.classList.add('show');
  })
  .finally(() => {
    submitBtn.classList.remove('loading');
    submitBtn.disabled = false;
  });
});
</script>

</body>
</html>