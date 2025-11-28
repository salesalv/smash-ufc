# Página Web UFC - Proyecto

Proyecto web básico de UFC desarrollado con HTML y CSS puro.

## 📁 Estructura del Proyecto

```
Pagina artaza nacho/
│
├── index.html              # Página principal (UFC 229: Khabib vs McGregor)
├── styles.css              # Estilos de la página principal
│
├── eventos.html            # Página de Blog ("La Tendencia Ahora")
├── eventos.css             # Estilos de la página de blog
│
├── peleadores.html         # Página de categorías de peso
├── peleadores.css          # Estilos de la página de peleadores
│
├── contenido.html          # Página de Curso UFC (antes Contenido Adicional)
├── contenido.css           # Estilos de la página de Curso UFC
│
├── perfil-peleador.css     # Estilos compartidos para perfiles de peleadores
│
├── peso-mosca.html         # Perfil campeón Peso Mosca (Brandon Moreno)
├── peso-gallo.html         # Perfil campeón Peso Gallo (Sean O'Malley)
├── peso-pluma.html         # Perfil campeón Peso Pluma (Ilia Topuria)
├── peso-ligero.html        # Perfil campeón Peso Ligero (Islam Makhachev)
├── peso-welter.html        # Perfil campeón Peso Welter (Belal Muhammad)
├── peso-medio.html         # Perfil campeón Peso Medio (Dricus Du Plessis)
├── peso-semipesado.html    # Perfil campeón Peso Semipesado (Alex Pereira)
├── peso-pesado.html        # Perfil campeón Peso Pesado (Jon Jones)
│
├── login.html              # Página de inicio de sesión
├── registro.html           # Página de registro
├── auth.css                # Estilos compartidos para autenticación
│
├── cv.html                 # Página de Currículum Vitae
├── cv.css                  # Estilos de la página de CV
│
├── rankings.html           # Página de rankings oficiales UFC
├── rankings.css            # Estilos de la página de rankings
│
└── README.md               # Este archivo
```

## 🎨 Páginas Implementadas

### 1. **Página Principal** (`index.html`)
- Logo UFC grande y destacado
- Tarjeta de pelea UFC 229
- Khabib "The Eagle" Nurmagomedov vs Connor "The Notorious" McGregor
- Navegación completa

### 2. **Página de Blog** (`eventos.html`)
- Banner rojo "LA TENDENCIA AHORA"
- Grid de contenido destacado:
  - "ESPECTÁCULO GARANTIZADO"
  - "ROSAS JR. DETRACADO"
  - "TOPURIA VS OLIVEIRA" (contenido destacado)
- Layout responsive de 2 columnas

### 3. **Página de Rankings** (`rankings.html`)
- Banner "RANKINGS OFICIALES UFC"
- Selector de categorías de peso (8 botones)
- Sistema de tabs interactivo con JavaScript
- Para cada categoría:
  - Tarjeta destacada del campeón con cinturón dorado
  - Lista de top 5 contendientes
  - Récord de cada peleador
  - Animaciones y efectos hover
- Diseño responsive con transiciones suaves

### 4. **Página de Peleadores** (`peleadores.html`)
- Grid de 8 categorías de peso:
  - Peso Mosca, Peso Gallo, Peso Pluma, Peso Ligero
  - Peso Welter, Peso Medio, Peso Semipesado, Peso Pesado
- Tarjetas con placeholders para imágenes de campeones
- Diseño responsive (4-3-2-1 columnas según pantalla)

### 5. **Página de Curso UFC** (`contenido.html`)
- Banner "¿QUÉ ES SMASH UFC?"
- Tres secciones informativas:
  - **¿Para qué sirve esta página?**
    - Visualización de videos y clips de peleas
    - Conocer atletas por división
  - **¿Para quién está pensada?**
    - Fans que quieren acceso rápido a info clave
    - Principiantes que desean entender categorías
  - **Objetivo**
    - Descripción del propósito del sitio SMASH UFC
- Diseño limpio con tarjetas en fondo claro

### 6. **Páginas de Perfiles de Peleadores** (8 páginas)
Cada categoría de peso tiene su propia página con:
- **Peso Mosca** - Brandon Moreno (21-7-2)
- **Peso Gallo** - Sean O'Malley (18-1-0)
- **Peso Pluma** - Ilia Topuria (15-0-0)
- **Peso Ligero** - Islam Makhachev (26-1-0)
- **Peso Welter** - Belal Muhammad (24-3-1)
- **Peso Medio** - Dricus Du Plessis (22-2-0)
- **Peso Semipesado** - Alex Pereira (11-2-0)
- **Peso Pesado** - Jon Jones (28-1-0)

**Características de cada perfil:**
- Foto del peleador (placeholder)
- Cinturón de campeón dorado
- Badge de categoría de peso
- Nombre completo del peleador
- Récord completo (V-D-E) con código de colores
- Botón para volver a categorías

### 7. **Sistema de Autenticación** (2 páginas)

**A. Página de Inicio de Sesión (`login.html`)**

- Logo UFC clickeable
- Formulario de login con:
  - Campo de correo electrónico
  - Campo de contraseña
  - Checkbox "Recordar sesión"
  - Enlace "¿Olvidaste tu contraseña?"
- Botón principal "INICIAR SESIÓN"
- Enlace a página de registro
- Validación básica con JavaScript
- Footer con copyright

**B. Página de Registro (`registro.html`)**
- Logo UFC clickeable
- Formulario de registro con:
  - Campo nombre completo
  - Campo correo electrónico
  - Campo contraseña (mínimo 6 caracteres)
  - Campo confirmar contraseña
  - Checkbox términos y condiciones
- Botón principal "CREAR CUENTA"
- Enlace a página de login
- Validación de contraseñas coincidentes
- Footer con copyright

**Características del sistema de autenticación:**
- Diseño moderno con fondo degradado oscuro
- Logo UFC animado con efecto de resplandor
- Campos de entrada con efectos hover y focus
- Checkbox personalizado
- Animaciones suaves (fadeIn)
- Validación de formularios con JavaScript
- Diseño 100% responsive
- Tema consistente con colores UFC (rojo #d20a0a)

### 8. **Página de Currículum Vitae** (`cv.html`)
- Header con degradado rojo UFC
- Nombre destacado en grande
- Layout de dos columnas:
  - **Columna Izquierda:**
    - Experiencia profesional con descripción detallada
    - Formación académica
  - **Columna Derecha:**
    - Habilidades con bullets
    - Idiomas
    - Referencias
- Botón de contacto "¡Charlemos!" con número de teléfono
- Botón "VOLVER AL INICIO"
- Diseño moderno con paleta de colores UFC
- Totalmente responsive

## 🚀 Características

✅ **Diseño Responsive** - Adaptable a móviles, tablets y desktop
✅ **Navegación Integrada** - Todos los botones conectan las páginas
✅ **Efectos Visuales** - Hover effects, sombras y transiciones
✅ **Logo Clickeable** - El logo UFC regresa al inicio
✅ **HTML y CSS Puro** - Sin dependencias externas
✅ **Perfiles Individuales** - 8 páginas de campeones con récords detallados
✅ **Cinturón Animado** - Efecto dorado con resplandor para campeones
✅ **Sistema de Récords** - Código de colores para victorias (verde), derrotas (rojo) y empates (amarillo)
✅ **Sistema de Autenticación** - Login y registro con validación JavaScript
✅ **Rankings Interactivos** - Sistema de tabs dinámico con JavaScript para 8 categorías
✅ **Botón de Sesión** - Presente en todas las páginas de navegación principal

## 🎯 Navegación

- **Logo UFC** → Regresa al inicio (`index.html`)
- **Botón BLOG** → `eventos.html` (La Tendencia Ahora)
- **Botón RANKINGS** → `rankings.html`
  - Selector de categorías (Mosca, Gallo, Pluma, Ligero, Welter, Medio, Semipesado, Pesado)
  - Muestra campeón y top 5 contendientes por categoría
- **Botón PELEADORES** → `peleadores.html`
  - **Tarjeta Peso Mosca** → `peso-mosca.html`
  - **Tarjeta Peso Gallo** → `peso-gallo.html`
  - **Tarjeta Peso Pluma** → `peso-pluma.html`
  - **Tarjeta Peso Ligero** → `peso-ligero.html`
  - **Tarjeta Peso Welter** → `peso-welter.html`
  - **Tarjeta Peso Medio** → `peso-medio.html`
  - **Tarjeta Peso Semipesado** → `peso-semipesado.html`
  - **Tarjeta Peso Pesado** → `peso-pesado.html`
- **Botón CURSO UFC** → `contenido.html`
  - Información sobre SMASH UFC
  - ¿Para qué sirve? ¿Para quién está pensada? Objetivo
- **Botón INICIAR SESIÓN** → `login.html`
  - Desde login → `registro.html` (enlace "Regístrate aquí")
  - Desde registro → `login.html` (enlace "Inicia sesión aquí")

## 💻 Cómo Usar

1. Abre `index.html` en tu navegador para ver la página principal
2. Navega entre secciones usando los botones del menú
3. Haz clic en el logo UFC para volver al inicio

## 🎨 Paleta de Colores

- **Rojo UFC**: `#d20a0a` / `#c00`
- **Negro**: `#000` / `#1a1a1a`
- **Blanco**: `#fff`
- **Gris**: `#d4d4d4` / `#333`

## 📱 Breakpoints Responsive

- **Desktop**: > 1200px (4 columnas en peleadores)
- **Tablet**: 768px - 1200px (3 columnas)
- **Móvil Grande**: 480px - 768px (2 columnas)
- **Móvil Pequeño**: < 480px (1 columna)

## 🔄 Próximas Mejoras

- [ ] Agregar imágenes reales de peleadores y eventos
- [ ] Integrar backend para autenticación real
- [ ] Agregar base de datos para usuarios y rankings
- [ ] Implementar sesiones y cookies
- [ ] Agregar funcionalidad de búsqueda de peleadores
- [ ] Implementar galería de imágenes interactiva
- [ ] Agregar página de perfil de usuario
- [ ] Integrar fuentes personalizadas
- [ ] Expandir rankings a top 15 por categoría
- [ ] Agregar estadísticas detalladas de peleadores

---

**Desarrollado con HTML y CSS** 🥊

