<?php
/**
 * API de Comentarios (ABM para Usuarios)
 * SMASH UFC
 */

require_once '../config.php';

// Iniciar sesión
startSecureSession();

$pdo = getDBConnection();
if (!$pdo) {
    jsonResponse(false, 'Error de conexión a la base de datos', null, 500);
}

$method = $_SERVER['REQUEST_METHOD'];

// Para GET (leer comentarios), permitir acceso público
// Para POST, PUT, DELETE, requerir autenticación
if ($method !== 'GET') {
    // Verificar que el usuario esté logueado para crear/editar/eliminar
    if (!isset($_SESSION['user_id'])) {
        jsonResponse(false, 'No autorizado', null, 401);
    }
}

$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

try {
    switch ($method) {
        case 'GET':
            // Obtener comentarios de una noticia o uno específico
            $noticiaId = isset($_GET['noticia_id']) ? (int)$_GET['noticia_id'] : null;
            $comentarioId = isset($_GET['id']) ? (int)$_GET['id'] : null;
            
            if ($comentarioId) {
                // Obtener un comentario específico
                $stmt = $pdo->prepare("
                    SELECT c.*, u.nombre as usuario_nombre, u.email as usuario_email
                    FROM comentarios c
                    LEFT JOIN usuarios u ON c.usuario_id = u.id
                    WHERE c.id = ? AND c.activo = 1
                ");
                $stmt->execute([$comentarioId]);
                $comentario = $stmt->fetch();
                
                if (!$comentario) {
                    jsonResponse(false, 'Comentario no encontrado', null, 404);
                }
                
                $comentario['fecha_creacion_formatted'] = date('d/m/Y H:i', strtotime($comentario['fecha_creacion']));
                $comentario['puede_editar'] = ($userId && $comentario['usuario_id'] == $userId);
                
                jsonResponse(true, 'Comentario obtenido', $comentario, 200);
            } else if ($noticiaId) {
                // Obtener todos los comentarios de una noticia (solo activos)
                // Usar CAST para asegurar que activo se compare correctamente
                $stmt = $pdo->prepare("
                    SELECT c.*, u.nombre as usuario_nombre, u.email as usuario_email
                    FROM comentarios c
                    LEFT JOIN usuarios u ON c.usuario_id = u.id
                    WHERE c.noticia_id = ? AND CAST(c.activo AS UNSIGNED) = 1
                    ORDER BY c.fecha_creacion DESC
                ");
                $stmt->execute([$noticiaId]);
                $comentarios = $stmt->fetchAll();
                
                // Filtrar adicionalmente por si acaso (doble verificación)
                $comentarios = array_filter($comentarios, function($comentario) {
                    $activo = $comentario['activo'];
                    // Verificar que activo sea 1, '1', o true
                    return ($activo == 1 || $activo === '1' || $activo === true || $activo === 1);
                });
                
                // Reindexar el array después del filtro
                $comentarios = array_values($comentarios);
                
                // Formatear fechas y agregar flag de edición
                foreach ($comentarios as &$comentario) {
                    $comentario['fecha_creacion_formatted'] = date('d/m/Y H:i', strtotime($comentario['fecha_creacion']));
                    $comentario['puede_editar'] = ($userId && $comentario['usuario_id'] == $userId);
                }
                
                jsonResponse(true, 'Comentarios obtenidos', $comentarios, 200);
            } else {
                jsonResponse(false, 'Se requiere noticia_id o id', null, 400);
            }
            break;
            
        case 'POST':
            // Crear nuevo comentario
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['noticia_id']) || !isset($input['contenido'])) {
                jsonResponse(false, 'ID de noticia y contenido son requeridos', null, 400);
            }
            
            $noticiaId = (int)$input['noticia_id'];
            $contenido = sanitizeInput($input['contenido']);
            
            if (empty($contenido) || strlen($contenido) < 3) {
                jsonResponse(false, 'El comentario debe tener al menos 3 caracteres', null, 400);
            }
            
            // Verificar que la noticia existe
            $stmt = $pdo->prepare("SELECT id FROM noticias WHERE id = ? AND activa = 1");
            $stmt->execute([$noticiaId]);
            if (!$stmt->fetch()) {
                jsonResponse(false, 'Noticia no encontrada o inactiva', null, 404);
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO comentarios (noticia_id, usuario_id, contenido) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$noticiaId, $userId, $contenido]);
            
            $comentarioId = $pdo->lastInsertId();
            
            // Obtener el comentario creado
            $stmt = $pdo->prepare("
                SELECT c.*, u.nombre as usuario_nombre, u.email as usuario_email
                FROM comentarios c
                LEFT JOIN usuarios u ON c.usuario_id = u.id
                WHERE c.id = ?
            ");
            $stmt->execute([$comentarioId]);
            $comentario = $stmt->fetch();
            
            $comentario['fecha_creacion_formatted'] = date('d/m/Y H:i', strtotime($comentario['fecha_creacion']));
            $comentario['puede_editar'] = true;
            
            jsonResponse(true, 'Comentario creado exitosamente', $comentario, 201);
            break;
            
        case 'PUT':
            // Actualizar comentario
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['id']) || !isset($input['contenido'])) {
                jsonResponse(false, 'ID y contenido son requeridos', null, 400);
            }
            
            $comentarioId = (int)$input['id'];
            $contenido = sanitizeInput($input['contenido']);
            
            if (empty($contenido) || strlen($contenido) < 3) {
                jsonResponse(false, 'El comentario debe tener al menos 3 caracteres', null, 400);
            }
            
            // Verificar que el comentario existe y pertenece al usuario
            $stmt = $pdo->prepare("SELECT id, usuario_id FROM comentarios WHERE id = ?");
            $stmt->execute([$comentarioId]);
            $comentario = $stmt->fetch();
            
            if (!$comentario) {
                jsonResponse(false, 'Comentario no encontrado', null, 404);
            }
            
            if ($comentario['usuario_id'] != $userId) {
                jsonResponse(false, 'No tienes permiso para editar este comentario', null, 403);
            }
            
            $stmt = $pdo->prepare("
                UPDATE comentarios 
                SET contenido = ?, fecha_actualizacion = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$contenido, $comentarioId]);
            
            // Obtener el comentario actualizado
            $stmt = $pdo->prepare("
                SELECT c.*, u.nombre as usuario_nombre, u.email as usuario_email
                FROM comentarios c
                LEFT JOIN usuarios u ON c.usuario_id = u.id
                WHERE c.id = ?
            ");
            $stmt->execute([$comentarioId]);
            $comentario = $stmt->fetch();
            
            $comentario['fecha_creacion_formatted'] = date('d/m/Y H:i', strtotime($comentario['fecha_creacion']));
            $comentario['puede_editar'] = true;
            
            jsonResponse(true, 'Comentario actualizado exitosamente', $comentario, 200);
            break;
            
        case 'DELETE':
            // Eliminar comentario (marcar como inactivo)
            error_log("=== INICIO DELETE COMENTARIO ===");
            error_log("SESSION user_id: " . var_export($_SESSION['user_id'] ?? 'NO DEFINIDO', true));
            error_log("userId variable: " . var_export($userId, true));
            
            $comentarioId = isset($_GET['id']) ? (int)$_GET['id'] : null;
            error_log("Comentario ID recibido: " . var_export($comentarioId, true));
            
            if (!$comentarioId) {
                jsonResponse(false, 'ID de comentario requerido', null, 400);
            }
            
            // Verificar que userId está definido (debe estar porque se verificó antes)
            if (!$userId) {
                error_log("Error DELETE comentario: userId no está definido");
                jsonResponse(false, 'Error de sesión', null, 401);
            }
            
            // Verificar que el comentario existe y pertenece al usuario
            $stmt = $pdo->prepare("SELECT id, usuario_id, activo FROM comentarios WHERE id = ?");
            $stmt->execute([$comentarioId]);
            $comentario = $stmt->fetch();
            
            if (!$comentario) {
                jsonResponse(false, 'Comentario no encontrado', null, 404);
            }
            
            // Convertir ambos a int para comparación correcta
            $comentarioUsuarioId = (int)$comentario['usuario_id'];
            $sessionUserId = (int)$userId;
            
            error_log("Verificación de permisos - Comentario usuario_id: $comentarioUsuarioId (tipo: " . gettype($comentario['usuario_id']) . "), Sesión user_id: $sessionUserId (tipo: " . gettype($userId) . ")");
            
            if ($comentarioUsuarioId !== $sessionUserId) {
                error_log("Permiso denegado - IDs no coinciden");
                jsonResponse(false, 'No tienes permiso para eliminar este comentario', null, 403);
            }
            
            // Marcar como inactivo en lugar de eliminar físicamente
            // Usar UPDATE directo sin transacciones (similar a cómo se eliminan noticias)
            $stmt = $pdo->prepare("UPDATE comentarios SET activo = 0 WHERE id = ?");
            $stmt->execute([$comentarioId]);
            
            // Verificar que se actualizó correctamente
            $stmt = $pdo->prepare("SELECT activo FROM comentarios WHERE id = ?");
            $stmt->execute([$comentarioId]);
            $verificacion = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($verificacion && ($verificacion['activo'] == 0 || $verificacion['activo'] === '0' || $verificacion['activo'] === 0)) {
                jsonResponse(true, 'Comentario eliminado exitosamente', null, 200);
            } else {
                jsonResponse(false, 'Error al eliminar el comentario', null, 500);
            }
            break;
            
        default:
            jsonResponse(false, 'Método no permitido', null, 405);
    }
    
} catch (PDOException $e) {
    error_log("Error en comentarios: " . $e->getMessage());
    jsonResponse(false, 'Error al procesar la solicitud', null, 500);
}

