<?php
/**
 * API de Noticias (ABM para Admin)
 * SMASH UFC
 */

// Limpiar cualquier output anterior
if (ob_get_level()) {
    ob_clean();
}

// Habilitar reporte de errores para debugging (quitar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Registrar inicio de la petición
error_log("=== Inicio petición noticias.php ===");
error_log("Método: " . ($_SERVER['REQUEST_METHOD'] ?? 'NO DEFINIDO'));

// Verificar que config.php existe
if (!file_exists('../config.php')) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error: archivo config.php no encontrado']);
    exit;
}

require_once '../config.php';

// Verificar que las funciones necesarias existen
if (!function_exists('startSecureSession') || !function_exists('jsonResponse') || !function_exists('requireAdmin') || !function_exists('getDBConnection')) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error: funciones requeridas no encontradas en config.php']);
    exit;
}

// Iniciar sesión
try {
    startSecureSession();
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error al iniciar sesión: ' . $e->getMessage()]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// Para GET de noticias activas, permitir acceso público
// Para POST, PUT, DELETE, requerir admin
if ($method === 'GET') {
    $activas = isset($_GET['activas']) ? (int)$_GET['activas'] : null;
    // Si solo se piden noticias activas, permitir acceso público
    if ($activas === 1) {
        // No requiere autenticación para ver noticias activas
    } else {
        // Para ver todas las noticias (activas e inactivas), requiere admin
        if (!isset($_SESSION['user_id'])) {
            jsonResponse(false, 'No autorizado', null, 401);
        }
        requireAdmin();
    }
} else {
    // Para POST, PUT, DELETE siempre requiere admin
    if (!isset($_SESSION['user_id'])) {
        jsonResponse(false, 'No autorizado', null, 401);
    }
    requireAdmin();
}

$pdo = getDBConnection();
if (!$pdo) {
    error_log("Error: No se pudo conectar a la base de datos. Verifica config.php y que MySQL esté corriendo.");
    jsonResponse(false, 'Error de conexión a la base de datos. Verifica la configuración.', null, 500);
}

$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

try {
    // Verificar que la tabla noticias existe (solo una vez al inicio del try)
    $stmt = $pdo->query("SHOW TABLES LIKE 'noticias'");
    if ($stmt->rowCount() === 0) {
        error_log("Error: La tabla 'noticias' no existe en la base de datos.");
        jsonResponse(false, 'Error: La tabla de noticias no existe. Ejecuta database_updates.sql', null, 500);
    }
    switch ($method) {
        case 'GET':
            // Obtener todas las noticias o una específica
            $noticiaId = isset($_GET['id']) ? (int)$_GET['id'] : null;
            
            if ($noticiaId) {
                // Obtener una noticia específica
                $stmt = $pdo->prepare("
                    SELECT n.*, u.nombre as autor_nombre 
                    FROM noticias n
                    LEFT JOIN usuarios u ON n.autor_id = u.id
                    WHERE n.id = ?
                ");
                    $stmt->execute([$noticiaId]);
                    $noticia = $stmt->fetch();
                    
                    if (!$noticia) {
                        jsonResponse(false, 'Noticia no encontrada', null, 404);
                    }
                    
                    // Mapear 'imagen' o 'imagen_url' a 'imagen_url' para compatibilidad con el frontend
                    if (isset($noticia['imagen_url']) && !empty($noticia['imagen_url'])) {
                        // Ya tiene imagen_url, mantenerlo
                    } elseif (isset($noticia['imagen']) && !empty($noticia['imagen'])) {
                        $noticia['imagen_url'] = $noticia['imagen'];
                    }
                    
                    // Formatear fecha
                    $noticia['fecha_creacion_formatted'] = date('d/m/Y H:i', strtotime($noticia['fecha_creacion']));
                    
                    jsonResponse(true, 'Noticia obtenida', $noticia, 200);
            } else {
                // Obtener todas las noticias
                $activas = isset($_GET['activas']) ? (int)$_GET['activas'] : null;
                
                if ($activas === 1) {
                    $stmt = $pdo->prepare("
                        SELECT n.*, u.nombre as autor_nombre 
                        FROM noticias n
                        LEFT JOIN usuarios u ON n.autor_id = u.id
                        WHERE n.activa = 1
                        ORDER BY n.fecha_creacion DESC
                    ");
                    $stmt->execute();
                } else {
                    $stmt = $pdo->prepare("
                        SELECT n.*, u.nombre as autor_nombre 
                        FROM noticias n
                        LEFT JOIN usuarios u ON n.autor_id = u.id
                        ORDER BY n.fecha_creacion DESC
                    ");
                    $stmt->execute();
                }
                
                $noticias = $stmt->fetchAll();
                
                // Formatear fechas y mapear campos
                foreach ($noticias as &$noticia) {
                    // Log para debugging
                    error_log("Noticia ID {$noticia['id']} - imagen: " . (isset($noticia['imagen']) ? substr($noticia['imagen'], 0, 50) : 'NO EXISTE') . ", imagen_url: " . (isset($noticia['imagen_url']) ? substr($noticia['imagen_url'], 0, 50) : 'NO EXISTE'));
                    
                    // Mapear 'imagen' o 'imagen_url' a 'imagen_url' para compatibilidad con el frontend
                    if (isset($noticia['imagen_url']) && !empty($noticia['imagen_url'])) {
                        // Ya tiene imagen_url, mantenerlo
                        error_log("Noticia ID {$noticia['id']} - Usando imagen_url existente");
                    } elseif (isset($noticia['imagen']) && !empty($noticia['imagen'])) {
                        $noticia['imagen_url'] = $noticia['imagen'];
                        error_log("Noticia ID {$noticia['id']} - Mapeando imagen a imagen_url");
                    } else {
                        error_log("Noticia ID {$noticia['id']} - No tiene imagen");
                    }
                    $noticia['fecha_creacion_formatted'] = date('d/m/Y H:i', strtotime($noticia['fecha_creacion']));
                    $noticia['fecha_actualizacion_formatted'] = date('d/m/Y H:i', strtotime($noticia['fecha_actualizacion']));
                }
                
                jsonResponse(true, 'Noticias obtenidas', $noticias, 200);
            }
            break;
            
        case 'POST':
            // Crear nueva noticia
            error_log("POST recibido - Iniciando creación de noticia");
            
            $rawInput = file_get_contents('php://input');
            error_log("Raw input recibido: " . substr($rawInput, 0, 200));
            
            if (empty($rawInput)) {
                jsonResponse(false, 'No se recibieron datos', null, 400);
            }
            
            $input = json_decode($rawInput, true);
            
            // Verificar si el JSON es válido
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("Error JSON: " . json_last_error_msg());
                jsonResponse(false, 'JSON inválido: ' . json_last_error_msg(), null, 400);
            }
            
            if (!is_array($input)) {
                error_log("Input no es un array. Tipo: " . gettype($input));
                jsonResponse(false, 'Datos inválidos', null, 400);
            }
            
            if (!isset($input['titulo']) || !isset($input['contenido'])) {
                error_log("Faltan campos requeridos. Input: " . print_r($input, true));
                jsonResponse(false, 'Título y contenido son requeridos', null, 400);
            }
            
            $titulo = sanitizeInput($input['titulo']);
            $contenido = sanitizeInput($input['contenido']);
            $imagen = isset($input['imagen_url']) && !empty($input['imagen_url']) ? sanitizeUrl($input['imagen_url']) : null;
            
            error_log("Datos sanitizados - Título: " . substr($titulo, 0, 50) . ", Contenido: " . substr($contenido, 0, 50));
            error_log("URL de imagen recibida: " . ($imagen ? substr($imagen, 0, 200) : 'null'));
            
            if ($titulo === null || $titulo === '' || strlen($titulo) < 3) {
                jsonResponse(false, 'El título debe tener al menos 3 caracteres', null, 400);
            }
            
            if ($contenido === null || $contenido === '' || strlen($contenido) < 10) {
                jsonResponse(false, 'El contenido debe tener al menos 10 caracteres', null, 400);
            }
            
            // Verificar que el usuario existe en la sesión
            if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
                error_log("Error: user_id no está en la sesión");
                jsonResponse(false, 'Error de sesión. Por favor, inicia sesión nuevamente.', null, 401);
            }
            
            $autorId = $_SESSION['user_id'];
            error_log("Insertando noticia con autor_id: " . $autorId);
            
            // Insertar noticia - intentar con 'imagen' primero (campo real en BD)
            // Si falla, intentar con 'imagen_url'
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO noticias (titulo, contenido, imagen, autor_id) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$titulo, $contenido, $imagen, $autorId]);
                error_log("Noticia insertada con campo 'imagen' - URL: " . ($imagen ? substr($imagen, 0, 100) : 'null'));
            } catch (PDOException $e) {
                // Si falla, intentar con 'imagen_url'
                error_log("Error con campo 'imagen', intentando con 'imagen_url': " . $e->getMessage());
                $stmt = $pdo->prepare("
                    INSERT INTO noticias (titulo, contenido, imagen_url, autor_id) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$titulo, $contenido, $imagen, $autorId]);
                error_log("Noticia insertada con campo 'imagen_url' - URL: " . ($imagen ? substr($imagen, 0, 100) : 'null'));
            }
            
            $noticiaId = $pdo->lastInsertId();
            error_log("Noticia insertada con ID: " . $noticiaId);
            
            if (!$noticiaId) {
                error_log("Error: No se pudo obtener el ID de la noticia insertada");
                jsonResponse(false, 'Error al crear la noticia', null, 500);
            }
            
            // Obtener la noticia creada
            $stmt = $pdo->prepare("
                SELECT n.*, u.nombre as autor_nombre 
                FROM noticias n
                LEFT JOIN usuarios u ON n.autor_id = u.id
                WHERE n.id = ?
            ");
            $stmt->execute([$noticiaId]);
            $noticia = $stmt->fetch();
            
            if (!$noticia) {
                error_log("Error: No se pudo obtener la noticia con ID: " . $noticiaId);
                jsonResponse(false, 'Error al obtener la noticia creada', null, 500);
            }
            
            // Mapear 'imagen' o 'imagen_url' a 'imagen_url' para compatibilidad con el frontend
            if (isset($noticia['imagen_url']) && !empty($noticia['imagen_url'])) {
                // Ya tiene imagen_url, mantenerlo
            } elseif (isset($noticia['imagen']) && !empty($noticia['imagen'])) {
                $noticia['imagen_url'] = $noticia['imagen'];
            }
            
            $noticia['fecha_creacion_formatted'] = date('d/m/Y H:i', strtotime($noticia['fecha_creacion']));
            
            error_log("Noticia creada exitosamente con ID: " . $noticiaId);
            jsonResponse(true, 'Noticia creada exitosamente', $noticia, 201);
            break;
            
        case 'PUT':
            // Actualizar noticia
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);
            
            // Verificar si el JSON es válido
            if (json_last_error() !== JSON_ERROR_NONE) {
                jsonResponse(false, 'JSON inválido: ' . json_last_error_msg(), null, 400);
            }
            
            if (!is_array($input) || !isset($input['id'])) {
                jsonResponse(false, 'ID de noticia requerido', null, 400);
            }
            
            $noticiaId = (int)$input['id'];
            
            // Verificar que la noticia existe
            $stmt = $pdo->prepare("SELECT id FROM noticias WHERE id = ?");
            $stmt->execute([$noticiaId]);
            if (!$stmt->fetch()) {
                jsonResponse(false, 'Noticia no encontrada', null, 404);
            }
            
            $titulo = isset($input['titulo']) ? sanitizeInput($input['titulo']) : null;
            $contenido = isset($input['contenido']) ? sanitizeInput($input['contenido']) : null;
            $imagen = isset($input['imagen_url']) && !empty($input['imagen_url']) ? sanitizeUrl($input['imagen_url']) : null;
            $activa = isset($input['activa']) ? (int)$input['activa'] : null;
            
            // Construir query dinámicamente
            $updates = [];
            $params = [];
            
            if ($titulo !== null && $titulo !== '') {
                if (strlen($titulo) < 3) {
                    jsonResponse(false, 'El título debe tener al menos 3 caracteres', null, 400);
                }
                $updates[] = "titulo = ?";
                $params[] = $titulo;
            }
            
            if ($contenido !== null && $contenido !== '') {
                if (strlen($contenido) < 10) {
                    jsonResponse(false, 'El contenido debe tener al menos 10 caracteres', null, 400);
                }
                $updates[] = "contenido = ?";
                $params[] = $contenido;
            }
            
            if ($imagen !== null && $imagen !== '') {
                // Intentar con 'imagen' primero (campo real en BD)
                $updates[] = "imagen = ?";
                $params[] = $imagen;
            }
            
            if ($activa !== null) {
                $updates[] = "activa = ?";
                $params[] = $activa;
            }
            
            if (empty($updates)) {
                jsonResponse(false, 'No hay campos para actualizar', null, 400);
            }
            
            $params[] = $noticiaId;
            
            $stmt = $pdo->prepare("
                UPDATE noticias 
                SET " . implode(', ', $updates) . ", fecha_actualizacion = NOW()
                WHERE id = ?
            ");
            $stmt->execute($params);
            
            // Obtener la noticia actualizada
            $stmt = $pdo->prepare("
                SELECT n.*, u.nombre as autor_nombre 
                FROM noticias n
                LEFT JOIN usuarios u ON n.autor_id = u.id
                WHERE n.id = ?
            ");
            $stmt->execute([$noticiaId]);
            $noticia = $stmt->fetch();
            
            if (!$noticia) {
                jsonResponse(false, 'Error al obtener la noticia actualizada', null, 500);
            }
            
            // Mapear 'imagen' o 'imagen_url' a 'imagen_url' para compatibilidad con el frontend
            if (isset($noticia['imagen_url']) && !empty($noticia['imagen_url'])) {
                // Ya tiene imagen_url, mantenerlo
            } elseif (isset($noticia['imagen']) && !empty($noticia['imagen'])) {
                $noticia['imagen_url'] = $noticia['imagen'];
            }
            
            $noticia['fecha_actualizacion_formatted'] = date('d/m/Y H:i', strtotime($noticia['fecha_actualizacion']));
            
            jsonResponse(true, 'Noticia actualizada exitosamente', $noticia, 200);
            break;
            
        case 'DELETE':
            // Eliminar noticia
            $noticiaId = isset($_GET['id']) ? (int)$_GET['id'] : null;
            
            if (!$noticiaId) {
                jsonResponse(false, 'ID de noticia requerido', null, 400);
            }
            
            // Verificar que la noticia existe
            $stmt = $pdo->prepare("SELECT id FROM noticias WHERE id = ?");
            $stmt->execute([$noticiaId]);
            if (!$stmt->fetch()) {
                jsonResponse(false, 'Noticia no encontrada', null, 404);
            }
            
            // Eliminar noticia (eliminar también comentarios por CASCADE)
            $stmt = $pdo->prepare("DELETE FROM noticias WHERE id = ?");
            $stmt->execute([$noticiaId]);
            
            jsonResponse(true, 'Noticia eliminada exitosamente', null, 200);
            break;
            
        default:
            jsonResponse(false, 'Método no permitido', null, 405);
    }
    
} catch (PDOException $e) {
    error_log("Error PDO en noticias: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    // En desarrollo, mostrar más detalles (quitar en producción)
    $errorMsg = 'Error al procesar la solicitud';
    if (ini_get('display_errors')) {
        $errorMsg .= ': ' . $e->getMessage();
    }
    jsonResponse(false, $errorMsg, null, 500);
} catch (Exception $e) {
    error_log("Error general en noticias: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    // En desarrollo, mostrar más detalles (quitar en producción)
    $errorMsg = 'Error al procesar la solicitud';
    if (ini_get('display_errors')) {
        $errorMsg .= ': ' . $e->getMessage();
    }
    jsonResponse(false, $errorMsg, null, 500);
}

