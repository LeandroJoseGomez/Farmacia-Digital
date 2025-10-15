// Helper para obtener y formatear el usuario actual desde localStorage
function getDisplayUser() {
  const raw = localStorage.getItem('loggedUser');
  if (!raw) return { displayName: 'Usuario', displayEmail: '' };
  try {
    const parsed = JSON.parse(raw);
    if (typeof parsed === 'object' && parsed !== null) {
      const email = parsed.correo || parsed.email || '';
      const name = parsed.nombre || parsed.name || email || 'Usuario';
      const displayName = (parsed.nombre || parsed.name) && email ? ( (parsed.nombre || parsed.name) + (email ? ` (${email})` : '') ) : name;
      return { displayName, displayEmail: email };
    }
  } catch (e) {
    // no era JSON
  }
  return { displayName: String(raw), displayEmail: '' };
}

function getRawUser() {
  const raw = localStorage.getItem('loggedUser');
  if (!raw) return null;
  try {
    return JSON.parse(raw);
  } catch (e) {
    return raw;
  }
}

// Devuelve solo el nombre del usuario (preferir 'nombre' o 'name').
function getNameOnly() {
  const raw = getRawUser();
  if (!raw) return 'Usuario';
  if (typeof raw === 'string') {
    // intentar extraer antes del espacio
    return raw.split(' ')[0] || raw;
  }
  const name = raw.nombre || raw.name || '';
  if (name) return name.split(' ')[0];
  // fallback: si solo hay correo, tomar la parte antes de @
  const email = raw.correo || raw.email || '';
  if (email) return email.split('@')[0];
  return 'Usuario';
}

// Conveniencia: función para forzar logout local
function localLogout(redirectUrl = '../index.html') {
  localStorage.removeItem('loggedUser');
  localStorage.removeItem('cart');
  if (redirectUrl) window.location.href = redirectUrl;
}
