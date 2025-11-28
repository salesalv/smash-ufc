/**
 * API Client para SMASH UFC
 * Maneja todas las peticiones al backend PHP
 */

const API_BASE_URL = 'api';

/**
 * Función genérica para hacer peticiones AJAX
 */
async function apiRequest(endpoint, method = 'GET', data = null) {
    const url = `${API_BASE_URL}/${endpoint}`;
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
        },
        credentials: 'same-origin' // Incluir cookies de sesión
    };
    
    if (data && (method === 'POST' || method === 'PUT')) {
        options.body = JSON.stringify(data);
    }
    
    try {
        const response = await fetch(url, options);
        
        // Si la respuesta no es JSON válido, intentar leer como texto
        let result;
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            result = await response.json();
        } else {
            const text = await response.text();
            console.error('Respuesta no JSON recibida:', text);
            throw new Error(`Error del servidor (${response.status}): ${text.substring(0, 200)}`);
        }
        
        if (!result.success) {
            throw new Error(result.message || 'Error en la petición');
        }
        
        return result;
    } catch (error) {
        console.error('Error en API:', error);
        // Si es un error de red o de parseo, proporcionar más información
        if (error instanceof TypeError && error.message.includes('fetch')) {
            throw new Error('Error de conexión. Verifica que el servidor esté corriendo.');
        }
        throw error;
    }
}

/**
 * Registrar nuevo usuario
 */
async function registerUser(nombre, email, password, passwordConfirm) {
    return await apiRequest('registro.php', 'POST', {
        nombre: nombre,
        email: email,
        password: password,
        password_confirm: passwordConfirm
    });
}

/**
 * Iniciar sesión
 */
async function loginUser(email, password) {
    return await apiRequest('login.php', 'POST', {
        email: email,
        password: password
    });
}

/**
 * Obtener perfil del usuario
 */
async function getUserProfile() {
    return await apiRequest('perfil.php', 'GET');
}

/**
 * Actualizar perfil del usuario
 */
async function updateUserProfile(nombre, email) {
    return await apiRequest('perfil.php', 'PUT', {
        nombre: nombre,
        email: email
    });
}

/**
 * Cerrar sesión
 */
async function logoutUser() {
    return await apiRequest('logout.php', 'POST');
}

/**
 * Verificar si hay sesión activa
 */
async function checkSession() {
    try {
        return await apiRequest('check-session.php', 'GET');
    } catch (error) {
        return { success: false };
    }
}

/**
 * Guardar datos del usuario en localStorage (para compatibilidad)
 */
function saveUserDataToLocalStorage(userData) {
    localStorage.setItem('userData', JSON.stringify(userData));
}

/**
 * Obtener datos del usuario desde localStorage
 */
function getUserDataFromLocalStorage() {
    const data = localStorage.getItem('userData');
    return data ? JSON.parse(data) : null;
}

/**
 * Eliminar datos del usuario de localStorage
 */
function removeUserDataFromLocalStorage() {
    localStorage.removeItem('userData');
}

// ==================== ADMIN - NOTICIAS ====================

/**
 * Obtener todas las noticias
 * @param {number|null} activas - 1 para solo activas (público), null para todas (requiere admin)
 */
async function getNoticias(activas = null) {
    const url = activas !== null ? `noticias.php?activas=${activas}` : 'noticias.php';
    return await apiRequest(url, 'GET');
}

/**
 * Obtener noticias activas (público, no requiere autenticación)
 */
async function getNoticiasActivas() {
    return await getNoticias(1);
}

/**
 * Obtener una noticia específica (admin)
 */
async function getNoticia(id) {
    return await apiRequest(`noticias.php?id=${id}`, 'GET');
}

/**
 * Crear nueva noticia (admin)
 */
async function createNoticia(titulo, contenido, imagenUrl = null) {
    return await apiRequest('noticias.php', 'POST', {
        titulo: titulo,
        contenido: contenido,
        imagen_url: imagenUrl
    });
}

/**
 * Actualizar noticia (admin)
 */
async function updateNoticia(id, data) {
    return await apiRequest('noticias.php', 'PUT', {
        id: id,
        ...data
    });
}

/**
 * Eliminar noticia (admin)
 */
async function deleteNoticia(id) {
    return await apiRequest(`noticias.php?id=${id}`, 'DELETE');
}

// ==================== ADMIN - USUARIOS ====================

/**
 * Obtener todos los usuarios (admin)
 */
async function getUsuarios() {
    return await apiRequest('usuarios-admin.php', 'GET');
}

/**
 * Obtener un usuario específico (admin)
 */
async function getUsuario(id) {
    return await apiRequest(`usuarios-admin.php?id=${id}`, 'GET');
}

/**
 * Actualizar usuario (admin)
 */
async function updateUsuario(id, data) {
    return await apiRequest('usuarios-admin.php', 'PUT', {
        id: id,
        ...data
    });
}

/**
 * Eliminar usuario (admin)
 */
async function deleteUsuario(id) {
    return await apiRequest(`usuarios-admin.php?id=${id}`, 'DELETE');
}

// ==================== USUARIOS - COMENTARIOS ====================

/**
 * Obtener comentarios de una noticia
 */
async function getComentarios(noticiaId, forceRefresh = false) {
    // Agregar timestamp para evitar cache si se fuerza la recarga
    const url = forceRefresh 
        ? `comentarios.php?noticia_id=${noticiaId}&_t=${Date.now()}`
        : `comentarios.php?noticia_id=${noticiaId}`;
    return await apiRequest(url, 'GET');
}

/**
 * Obtener un comentario específico
 */
async function getComentario(id) {
    return await apiRequest(`comentarios.php?id=${id}`, 'GET');
}

/**
 * Crear nuevo comentario
 */
async function createComentario(noticiaId, contenido) {
    return await apiRequest('comentarios.php', 'POST', {
        noticia_id: noticiaId,
        contenido: contenido
    });
}

/**
 * Actualizar comentario
 */
async function updateComentario(id, contenido) {
    return await apiRequest('comentarios.php', 'PUT', {
        id: id,
        contenido: contenido
    });
}

/**
 * Eliminar comentario
 */
async function deleteComentario(id) {
    return await apiRequest(`comentarios.php?id=${id}`, 'DELETE');
}


