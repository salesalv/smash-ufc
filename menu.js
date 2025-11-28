// Menú Hamburguesa
document.addEventListener('DOMContentLoaded', async function() {
    // Crear botón hamburguesa
    const hamburgerBtn = document.createElement('button');
    hamburgerBtn.className = 'hamburger-btn';
    hamburgerBtn.innerHTML = '☰';
    hamburgerBtn.setAttribute('aria-label', 'Menú');
    
    // Crear menú lateral
    const sidebar = document.createElement('div');
    sidebar.className = 'sidebar-menu';
    
    // Verificar si hay usuario logueado
    let isLoggedIn = false;
    try {
        // Verificar primero en el servidor
        const sessionCheck = await checkSession();
        if (sessionCheck.success) {
            isLoggedIn = true;
            // Actualizar localStorage con los datos del servidor
            if (sessionCheck.data) {
                saveUserDataToLocalStorage(sessionCheck.data);
            }
        } else {
            // Si no hay sesión en el servidor, verificar localStorage
            const userData = getUserDataFromLocalStorage();
            isLoggedIn = !!userData;
        }
    } catch (error) {
        // Si falla, usar localStorage como respaldo
        const userData = getUserDataFromLocalStorage();
        isLoggedIn = !!userData;
    }
    
    // Construir el HTML del menú
    let menuHTML = `
        <div class="sidebar-header">
            <h2>MENÚ</h2>
            <button class="close-btn" aria-label="Cerrar menú">×</button>
        </div>
        <nav class="sidebar-nav">
            <a href="index.html" class="sidebar-link">🏠 INICIO</a>
            <a href="eventos.html" class="sidebar-link">📰 BLOG</a>
            <a href="rankings.html" class="sidebar-link">📊 RANKINGS</a>
            <a href="peleadores.html" class="sidebar-link">🥊 PELEADORES</a>
            <a href="cv.html" class="sidebar-link">📄 CV</a>
            <a href="contenido.html" class="sidebar-link">📚 CURSO UFC</a>
            <a href="quienes-somos.html" class="sidebar-link">👥 ¿QUIÉNES SOMOS?</a>
            <a href="preguntas-frecuentes.html" class="sidebar-link">❓ PREGUNTAS FRECUENTES</a>
    `;
    
    // Solo mostrar login y registro si NO está logueado
    if (!isLoggedIn) {
        menuHTML += `
            <a href="login.html" class="sidebar-link">🔐 INICIAR SESIÓN</a>
            <a href="registro.html" class="sidebar-link">✍️ REGISTRO</a>
        `;
    }
    
    menuHTML += `
        </nav>
    `;
    
    sidebar.innerHTML = menuHTML;
    
    // Crear overlay
    const overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    
    // Insertar elementos
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        navbar.insertBefore(hamburgerBtn, navbar.firstChild);
        document.body.appendChild(sidebar);
        document.body.appendChild(overlay);
    }
    
    // Funcionalidad del menú
    hamburgerBtn.addEventListener('click', function() {
        sidebar.classList.add('active');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    });
    
    const closeBtn = sidebar.querySelector('.close-btn');
    closeBtn.addEventListener('click', function() {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    });
    
    // Cerrar al hacer click en el overlay
    overlay.addEventListener('click', function() {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    });
    
    // Cerrar con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar.classList.contains('active')) {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
});

// Botón de Perfil
document.addEventListener('DOMContentLoaded', async function() {
    // Verificar sesión en el servidor primero
    let userData = null;
    
    try {
        const result = await checkSession();
        if (result.success && result.data) {
            userData = result.data;
            saveUserDataToLocalStorage(userData);
        } else {
            // Si no hay sesión, intentar desde localStorage
            userData = getUserDataFromLocalStorage();
            // Si hay datos en localStorage pero no en servidor, limpiar localStorage
            if (userData) {
                removeUserDataFromLocalStorage();
                userData = null;
            }
        }
    } catch (error) {
        // Si falla, intentar desde localStorage
        userData = getUserDataFromLocalStorage();
    }
    
    // Solo mostrar botón de perfil si hay usuario logueado
    if (userData) {
        // Crear botón de perfil
        const profileBtn = document.createElement('button');
        profileBtn.className = 'profile-btn';
        profileBtn.innerHTML = '👤';
        profileBtn.setAttribute('aria-label', 'Perfil de usuario');
        
        // Crear dropdown de perfil
        const profileDropdown = document.createElement('div');
        profileDropdown.className = 'profile-dropdown';
        // Asegurarse de que fechaRegistro esté definido
        const fechaRegistro = userData.fechaRegistro || userData.fecha_registro || 'N/A';
        
        profileDropdown.innerHTML = `
            <div class="profile-header">
                <div class="profile-avatar">${(userData.nombre || 'Usuario').charAt(0).toUpperCase()}</div>
                <div class="profile-info">
                    <h3>${userData.nombre || 'Usuario'}</h3>
                    <p>${userData.email || ''}</p>
                    <small>Registrado: ${fechaRegistro}</small>
                </div>
            </div>
            <div class="profile-actions">
                <a href="perfil.html" class="profile-action">⚙️ Mi Perfil</a>
                ${userData.rol === 'admin' ? '<a href="admin-noticias.html" class="profile-action">📰 Administrar Noticias</a>' : ''}
                ${userData.rol === 'admin' ? '<a href="admin-usuarios.html" class="profile-action">👥 Administrar Usuarios</a>' : ''}
                <button class="profile-action logout-btn">🚪 Cerrar Sesión</button>
            </div>
        `;
        
        // Insertar elementos
        const navbar = document.querySelector('.navbar');
        if (navbar) {
            const navLinks = navbar.querySelector('.nav-links');
            if (navLinks) {
                navLinks.appendChild(profileBtn);
            }
            document.body.appendChild(profileDropdown);
        }
        
        // Funcionalidad del dropdown
        profileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            profileDropdown.classList.toggle('active');
        });
        
        // Cerrar al hacer click fuera
        document.addEventListener('click', function(e) {
            if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
                profileDropdown.classList.remove('active');
            }
        });
        
        // Cerrar sesión
        const logoutBtn = profileDropdown.querySelector('.logout-btn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', async function() {
                try {
                    await logoutUser();
                } catch (error) {
                    console.error('Error al cerrar sesión:', error);
                } finally {
                    removeUserDataFromLocalStorage();
                    window.location.href = 'login.html';
                }
            });
        }
    }
});

