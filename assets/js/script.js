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

// ===== LOGIN =====
async function login() {
  const correo = document.getElementById('loginEmail').value.trim();
  const contrasena = document.getElementById('loginPassword').value.trim();
  const alert = document.getElementById('loginAlert');

  clearAlerts();

  if (!correo || !contrasena) {
    showAlert('loginAlert', 'Completa todos los campos.', 'warning');
    return;
  }

  try {
    const data = new URLSearchParams();
    data.append("correo", correo);
    data.append("contrasena", contrasena);

    const response = await fetch('php/login.php', {
      method: 'POST',
      body: data
    });

    const result = (await response.text()).trim();

    if (result === "ok") {
      showAlert('loginAlert', 'Acceso exitoso. Redirigiendo...', 'success');
      setTimeout(() => {
        window.location.href = "modules/dashboard.html";
      }, 1500);
    } else if (result === "incorrecto") {
      showAlert('loginAlert', 'Contraseña incorrecta.', 'danger');
    } else {
      showAlert('loginAlert', 'Usuario no encontrado.', 'danger');
    }

  } catch (error) {
    showAlert('loginAlert', 'Error de conexión con el servidor.', 'danger');
    console.error(error);
  }
}

// ===== REGISTRO =====
async function register() {
  const nombre = document.getElementById('registerName').value.trim();
  const correo = document.getElementById('registerEmail').value.trim();
  const contrasena = document.getElementById('registerPassword').value.trim();
  const alert = document.getElementById('registerAlert');

  clearAlerts();

  if (!nombre || !correo || !contrasena) {
    showAlert('registerAlert', 'Completa todos los campos.', 'warning');
    return;
  }

  if (!validateEmail(correo)) {
    showAlert('registerAlert', 'Correo inválido.', 'warning');
    return;
  }

  try {
    const data = new URLSearchParams();
    data.append("nombre", nombre);
    data.append("correo", correo);
    data.append("contrasena", contrasena);

    const response = await fetch('php/registro.php', {
      method: 'POST',
      body: data
    });

    const result = (await response.text()).trim();

    if (result === "ok") {
      showAlert('registerAlert', 'Registro exitoso. Ahora inicia sesión.', 'success');
      setTimeout(() => toggleForms(), 1000);
    } else if (result === "existe") {
      showAlert('registerAlert', 'El correo ya está registrado.', 'danger');
    } else {
      showAlert('registerAlert', 'Error al registrarse.', 'danger');
    }

  } catch (error) {
    showAlert('registerAlert', 'Error de conexión con el servidor.', 'danger');
    console.error(error);
  }
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
