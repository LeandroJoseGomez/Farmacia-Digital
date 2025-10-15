// ===== Manejo de usuarios (solo si usas localStorage) =====
function getUsers() {
  const stored = localStorage.getItem('users');
  if (!stored) {
    localStorage.setItem('users', JSON.stringify([]));
    return [];
  }
  return JSON.parse(stored);
}

function saveUsers(users) {
  localStorage.setItem('users', JSON.stringify(users));
}

// ===== Mostrar/Ocultar formularios =====
function toggleForms() {
  const loginForm = document.getElementById("loginForm");
  const registerForm = document.getElementById("registerForm");

  if (!loginForm || !registerForm) return;

  if (loginForm.style.display === "none") {
    loginForm.style.display = "block";
    registerForm.style.display = "none";
  } else {
    loginForm.style.display = "none";
    registerForm.style.display = "block";
  }
  clearAlerts();
}

// ===== Manejo de alertas =====
function showAlert(elementId, message, type = "info") {
  const alert = document.getElementById(elementId);
  if (!alert) return;

  alert.textContent = message;
  alert.className = `alert alert-${type} show`;

  setTimeout(() => {
    alert.classList.remove('show');
  }, 5000);
}

function clearAlerts() {
  document.querySelectorAll('.alert').forEach(alert => {
    alert.classList.remove('show');
  });
  document.querySelectorAll('input, select').forEach(input => {
    input.classList.remove('error-input');
  });
}

// ===== Validaciones =====
function validateEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email);
}

function checkPasswordStrength() {
  const password = document.getElementById("registerPassword").value;
  const strengthDiv = document.getElementById("passwordStrength");
  if (!strengthDiv) return;

  if (password.length === 0) {
    strengthDiv.classList.remove('show');
    return;
  }

  strengthDiv.classList.add('show');

  let strength = 0;
  if (password.length >= 8) strength++;
  if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
  if (/\d/.test(password)) strength++;
  if (/[^a-zA-Z0-9]/.test(password)) strength++;

  if (strength <= 1) {
    strengthDiv.textContent = "⚠️ Contraseña débil";
    strengthDiv.className = "password-strength show strength-weak";
  } else if (strength === 2) {
    strengthDiv.textContent = "⚡ Contraseña media";
    strengthDiv.className = "password-strength show strength-medium";
  } else {
    strengthDiv.textContent = "✓ Contraseña fuerte";
    strengthDiv.className = "password-strength show strength-strong";
  }
}

// ===== Helpers para auth/usuario
function getLoggedUserId() {
  try {
    const raw = localStorage.getItem('loggedUser');
    if (!raw) return null;
    const user = JSON.parse(raw);
    return user && (user.id || user.usuario_id || user.usuarioId) ? (user.id || user.usuario_id || user.usuarioId) : null;
  } catch (e) {
    return null;
  }
}

// ===== LOGIN (usa endpoint PHP, fallback a localStorage)
async function login() {
  const correo = document.getElementById('loginEmail').value.trim();
  const contrasena = document.getElementById('loginPassword').value.trim();

  clearAlerts();

  if (!correo || !contrasena) {
    showAlert('loginAlert', 'Completa todos los campos.', 'warning');
    return;
  }

  // Intentar autenticar en el servidor
  try {
  const resp = await fetch('/php/login.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ correo, contrasena })
    });

    if (resp.ok) {
      const text = await resp.text();
      let json = null;
      try {
        json = text ? JSON.parse(text) : null;
      } catch (parseErr) {
        console.warn('No se pudo parsear JSON de login.php', parseErr, text);
      }
      if (json && json.status === 'ok' && json.usuario) {
        // Guardar sesión local
        const u = json.usuario;
        const sessionUser = { id: u.id || u.usuario_id || u.id_usuario || null, nombre: u.nombre || u.name || u.correo, correo: u.correo || u.email, role: u.role || u.rol };
        localStorage.setItem('loggedUser', JSON.stringify(sessionUser));
        showAlert('loginAlert', 'Acceso exitoso. Redirigiendo...', 'success');
        setTimeout(() => {
          const isAdmin = (sessionUser.role && String(sessionUser.role).toLowerCase() === 'admin') || (String(sessionUser.correo).toLowerCase() === 'admin@farmacia.com');
          if (isAdmin) window.location.href = 'modules/dashboard.html';
          else window.location.href = 'modules/usuario-dashboard.html';
        }, 700);
        return;
      }
      // Si el servidor respondió pero credenciales inválidas o no devolvió JSON
      showAlert('loginAlert', (json && json.message) ? json.message : 'Credenciales inválidas o respuesta inesperada del servidor', 'danger');
      return;
    }
  } catch (err) {
    // Caída del servidor: fallback a localStorage
    console.warn('Login server error, falling back to localStorage', err);
  }

  // Fallback localStorage (modo offline)
  const users = getUsers();
  const user = users.find(u => u.correo === correo);
  if (!user) {
    showAlert('loginAlert', 'Usuario no encontrado.', 'danger');
    return;
  }
  if (user.contrasena !== contrasena) {
    showAlert('loginAlert', 'Contraseña incorrecta.', 'danger');
    return;
  }
  const sessionUser = { id: user.id, nombre: user.nombre || user.name, correo: user.correo, role: user.role };
  localStorage.setItem('loggedUser', JSON.stringify(sessionUser));
  showAlert('loginAlert', 'Acceso exitoso. Redirigiendo (modo offline)...', 'success');
  setTimeout(() => window.location.href = 'modules/usuario-dashboard.html', 700);
}

// ===== REGISTRO (intenta servidor, fallback a localStorage)
async function register() {
  const nombre = document.getElementById('registerName').value.trim();
  const correo = document.getElementById('registerEmail').value.trim();
  const contrasena = document.getElementById('registerPassword').value.trim();

  clearAlerts();

  if (!nombre || !correo || !contrasena) {
    showAlert('registerAlert', 'Completa todos los campos.', 'warning');
    return;
  }

  if (!validateEmail(correo)) {
    showAlert('registerAlert', 'Correo inválido.', 'warning');
    return;
  }

  // Intentar registrar en servidor
  try {
  const resp = await fetch('/php/registro.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ nombre, correo, contrasena })
    });

    if (resp.ok) {
      const text = await resp.text();
      let json = null;
      try {
        json = text ? JSON.parse(text) : null;
      } catch (parseErr) {
        console.warn('No se pudo parsear JSON de registro.php', parseErr, text);
      }
      if (json && json.status === 'ok') {
        showAlert('registerAlert', 'Registro exitoso. Ahora inicia sesión.', 'success');
        setTimeout(() => toggleForms(), 900);
        return;
      }
      showAlert('registerAlert', (json && json.message) ? json.message : 'Error en registro o respuesta inesperada del servidor', 'danger');
      return;
    }
  } catch (err) {
    console.warn('Registro server error, falling back to localStorage', err);
  }

  // Fallback localStorage
  const users = getUsers();
  if (users.some(u => u.correo === correo)) {
    showAlert('registerAlert', 'El correo ya está registrado.', 'danger');
    return;
  }
  const id = users.length > 0 ? users[users.length - 1].id + 1 : 1;
  const nuevo = { id, nombre, correo, contrasena };
  users.push(nuevo);
  saveUsers(users);

  showAlert('registerAlert', 'Registro exitoso (modo offline). Ahora inicia sesión.', 'success');
  setTimeout(() => toggleForms(), 1000);
}

// ===== EVENTOS =====
document.addEventListener('DOMContentLoaded', function () {
  const loginPassword = document.getElementById('loginPassword');
  const registerPassword = document.getElementById('registerPassword');

  if (loginPassword) {
    loginPassword.addEventListener('keypress', e => {
      if (e.key === 'Enter') login();
    });
  }

  if (registerPassword) {
    registerPassword.addEventListener('keypress', e => {
      if (e.key === 'Enter') register();
    });
  }
});
