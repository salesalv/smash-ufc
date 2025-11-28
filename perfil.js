// Cargar datos del usuario
document.addEventListener('DOMContentLoaded', async function() {
    // Intentar cargar desde la API primero
    let userData = null;
    
    try {
        const result = await getUserProfile();
        if (result.success) {
            userData = result.data;
            saveUserDataToLocalStorage(userData);
        }
    } catch (error) {
        // Si falla, intentar desde localStorage
        userData = getUserDataFromLocalStorage();
        
        if (!userData) {
            // Si no hay datos, redirigir al login
            alert('Por favor, inicia sesión para acceder a tu perfil');
            window.location.href = 'login.html';
            return;
        }
    }
    
    if (!userData) {
        alert('No se pudieron cargar los datos del usuario');
        window.location.href = 'login.html';
        return;
    }
    
    // Mostrar datos en el perfil
    document.getElementById('profile-name').textContent = userData.nombre;
    document.getElementById('profile-email').textContent = userData.email;
    document.getElementById('avatar-letter').textContent = userData.nombre.charAt(0).toUpperCase();
    document.getElementById('fecha-registro').textContent = userData.fechaRegistro;
    document.getElementById('user-id').textContent = userData.userId;
    
    // Llenar formulario
    document.getElementById('nombre').value = userData.nombre;
    document.getElementById('email').value = userData.email;
    
    // Manejar botón de editar perfil
    const btnEditProfile = document.getElementById('btn-edit-profile');
    const editButtonContainer = document.getElementById('edit-button-container');
    const profileForm = document.getElementById('profile-form');
    const btnCancelEdit = document.getElementById('btn-cancel-edit');
    const nombreInput = document.getElementById('nombre');
    const emailInput = document.getElementById('email');
    
    btnEditProfile.addEventListener('click', function() {
        // Ocultar botón de editar
        editButtonContainer.style.display = 'none';
        // Mostrar formulario
        profileForm.style.display = 'block';
        // Habilitar campos
        nombreInput.disabled = false;
        emailInput.disabled = false;
        // Enfocar en el primer campo
        nombreInput.focus();
    });
    
    // Manejar botón de cancelar
    btnCancelEdit.addEventListener('click', function() {
        // Restaurar valores originales
        nombreInput.value = userData.nombre;
        emailInput.value = userData.email;
        // Deshabilitar campos
        nombreInput.disabled = true;
        emailInput.disabled = true;
        // Ocultar formulario
        profileForm.style.display = 'none';
        // Mostrar botón de editar
        editButtonContainer.style.display = 'block';
    });
    
    // Manejar envío del formulario
    profileForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const nuevoNombre = document.getElementById('nombre').value.trim();
        const nuevoEmail = document.getElementById('email').value.trim();
        
        // Validar email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(nuevoEmail)) {
            alert('Por favor, ingresa un correo electrónico válido');
            return;
        }
        
        // Deshabilitar botón mientras se procesa
        const submitBtn = document.querySelector('.btn-save');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'GUARDANDO...';
        
        try {
            // Actualizar en la base de datos
            const result = await updateUserProfile(nuevoNombre, nuevoEmail);
            
            if (result.success) {
                // Actualizar datos locales
                userData = result.data;
                saveUserDataToLocalStorage(userData);
                
                // Actualizar interfaz
                document.getElementById('profile-name').textContent = nuevoNombre;
                document.getElementById('profile-email').textContent = nuevoEmail;
                document.getElementById('avatar-letter').textContent = nuevoNombre.charAt(0).toUpperCase();
                
                // Deshabilitar campos
                nombreInput.disabled = true;
                emailInput.disabled = true;
                // Ocultar formulario
                profileForm.style.display = 'none';
                // Mostrar botón de editar
                editButtonContainer.style.display = 'block';
                
                // Mostrar mensaje de éxito
                alert('¡Perfil actualizado exitosamente!');
                
                // Actualizar también el dropdown de perfil si existe
                updateProfileDropdown();
            }
        } catch (error) {
            alert('Error: ' + error.message);
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    });
});

// Función para actualizar el dropdown de perfil
function updateProfileDropdown() {
    const userData = getUserDataFromLocalStorage();
    if (userData) {
        const profileDropdown = document.querySelector('.profile-dropdown');
        if (profileDropdown) {
            const profileInfo = profileDropdown.querySelector('.profile-info');
            if (profileInfo) {
                profileInfo.querySelector('h3').textContent = userData.nombre;
                profileInfo.querySelector('p').textContent = userData.email;
            }
            
            const profileAvatar = profileDropdown.querySelector('.profile-avatar');
            if (profileAvatar) {
                profileAvatar.textContent = userData.nombre.charAt(0).toUpperCase();
            }
        }
    }
}

