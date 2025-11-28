# 🚀 Guía de Instalación - SMASH UFC con PHP y MySQL

Esta guía te ayudará a configurar el proyecto SMASH UFC con XAMPP, PHP y MySQL.

## 📋 Requisitos Previos

- **XAMPP** (versión 7.4 o superior)
- **Navegador web** (Chrome, Firefox, Edge, etc.)
- **Editor de código** (opcional, para editar archivos)

---

## 🔧 Paso 1: Instalar XAMPP

1. Descarga XAMPP desde: https://www.apachefriends.org/
2. Instala XAMPP en tu sistema (por defecto en `C:\xampp` en Windows)
3. Asegúrate de instalar:
   - ✅ Apache
   - ✅ MySQL
   - ✅ PHP

---

## 📁 Paso 2: Configurar el Proyecto

1. **Copia el proyecto** a la carpeta de XAMPP:
   ```
   C:\xampp\htdocs\smash-ufc\
   ```
   O si prefieres otro nombre:
   ```
   C:\xampp\htdocs\tu-proyecto\
   ```

2. **Estructura de carpetas** (debe quedar así):
   ```
   C:\xampp\htdocs\smash-ufc\
   ├── api/
   │   ├── registro.php
   │   ├── login.php
   │   ├── perfil.php
   │   ├── logout.php
   │   └── check-session.php
   ├── index.html
   ├── config.php
   ├── api.js
   ├── database.sql
   └── ... (todos los demás archivos)
   ```

---

## 🗄️ Paso 3: Crear la Base de Datos

### Opción A: Usando phpMyAdmin (Recomendado)

1. **Inicia XAMPP** y activa:
   - ✅ Apache
   - ✅ MySQL

2. Abre tu navegador y ve a:
   ```
   http://localhost/phpmyadmin
   ```

3. **Crea la base de datos:**
   - Click en "Nueva" (New) en el menú lateral
   - Nombre de la base de datos: `smash_ufc`
   - Intercalación: `utf8mb4_unicode_ci`
   - Click en "Crear" (Create)

4. **Importa las tablas:**
   - Selecciona la base de datos `smash_ufc`
   - Click en la pestaña "Importar" (Import)
   - Click en "Elegir archivo" y selecciona `database.sql`
   - Click en "Continuar" (Go)

### Opción B: Usando la línea de comandos

1. Abre la terminal/consola
2. Navega a la carpeta de MySQL:
   ```bash
   cd C:\xampp\mysql\bin
   ```
3. Ejecuta MySQL:
   ```bash
   mysql -u root
   ```
4. Ejecuta el script SQL:
   ```sql
   source C:\xampp\htdocs\smash-ufc\database.sql
   ```
   O copia y pega el contenido de `database.sql` directamente

---

## ⚙️ Paso 4: Configurar la Conexión

1. Abre el archivo `config.php` en tu editor
2. Verifica que la configuración sea correcta:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'smash_ufc');
   define('DB_USER', 'root');
   define('DB_PASS', '');  // Por defecto XAMPP no tiene contraseña
   ```

3. **Si cambiaste la contraseña de MySQL**, actualiza `DB_PASS`:
   ```php
   define('DB_PASS', 'tu_contraseña');
   ```

---

## 🌐 Paso 5: Acceder al Proyecto

1. **Asegúrate de que XAMPP esté corriendo:**
   - Apache: ✅ Verde
   - MySQL: ✅ Verde

2. **Abre tu navegador** y ve a:
   ```
   http://localhost/smash-ufc/
   ```
   O el nombre que le hayas dado a tu carpeta

3. **¡Listo!** Deberías ver la página principal de SMASH UFC

---

## 🧪 Paso 6: Probar el Sistema

### Crear una cuenta de prueba:

1. Ve a: `http://localhost/smash-ufc/registro.html`
2. Completa el formulario:
   - Nombre: Tu nombre
   - Email: tu@email.com
   - Contraseña: 123456 (mínimo 6 caracteres)
3. Click en "CREAR CUENTA"
4. Deberías ser redirigido al inicio

### Iniciar sesión:

1. Ve a: `http://localhost/smash-ufc/login.html`
2. Ingresa el email y contraseña que registraste
3. Click en "INICIAR SESIÓN"
4. Deberías ver el botón de perfil (👤) en la esquina superior derecha

### Verificar en la base de datos:

1. Ve a: `http://localhost/phpmyadmin`
2. Selecciona la base de datos `smash_ufc`
3. Click en la tabla `usuarios`
4. Deberías ver tu usuario registrado

---

## 🔍 Solución de Problemas

### Error: "Error de conexión a la base de datos"

**Solución:**
- Verifica que MySQL esté corriendo en XAMPP
- Verifica que el nombre de la base de datos sea `smash_ufc`
- Verifica que el usuario sea `root` y la contraseña esté vacía (o sea la correcta)

### Error: "No se puede acceder a la página"

**Solución:**
- Verifica que Apache esté corriendo en XAMPP
- Verifica que la URL sea correcta: `http://localhost/smash-ufc/`
- Verifica que los archivos estén en `C:\xampp\htdocs\smash-ufc\`

### Error: "Método no permitido" o "405"

**Solución:**
- Asegúrate de que los archivos PHP estén en la carpeta `api/`
- Verifica que `api.js` esté incluido en las páginas HTML

### Error: "No autorizado" o "401"

**Solución:**
- Asegúrate de haber iniciado sesión primero
- Verifica que las sesiones de PHP estén habilitadas
- Limpia las cookies del navegador y vuelve a intentar

---

## 📝 Notas Importantes

1. **Sesiones PHP:** Las sesiones se guardan en `C:\xampp\tmp\` por defecto
2. **Permisos:** En Windows generalmente no hay problemas de permisos
3. **Puertos:** 
   - Apache usa el puerto 80
   - MySQL usa el puerto 3306
   - Si hay conflictos, cámbialos en la configuración de XAMPP

---

## 🎯 Próximos Pasos

Una vez que todo funcione:

1. ✅ Puedes personalizar los estilos en los archivos CSS
2. ✅ Puedes agregar más funcionalidades en los archivos PHP
3. ✅ Puedes agregar más tablas a la base de datos según necesites

---

## 📞 Soporte

Si tienes problemas:
1. Revisa los logs de Apache: `C:\xampp\apache\logs\error.log`
2. Revisa los logs de PHP: `C:\xampp\php\logs\php_error_log`
3. Verifica la consola del navegador (F12) para errores JavaScript

---

**¡Listo para usar! 🥊✨**

