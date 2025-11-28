<?php
/**
 * API de Administración de Usuarios (Solo Admin)
 * SMASH UFC
 */

require_once '../config.php';

// Iniciar sesión
startSecureSession();

// Verificar que el usuario esté logueado y sea admin
requireAdmin();

$pdo = getDBConnection();
if (!$pdo) {
    jsonResponse(false, 'Error de conexión a la base de datos', null, 500);
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // Obtener todos los usuarios o uno específico
            $usuarioId = isset($_GET['id']) ? (int)$_GET['id'] : null;
            
            if ($usuarioId) {
                // Obtener un usuario específico
                $stmt = $pdo->prepare("
                    SELECT id, nombre, email, rol, fecha_registro, fecha_actualizacion, activo
                    FROM usuarios 
                    WHERE id = ?
                ");
                $stmt->execute([$usuarioId]);
                $usuario = $stmt->fetch();
                
                if (!$usuario) {
                    jsonResponse(false, 'Usuario no encontrado', null, 404);
                }
                
                $usuario['fecha_registro_formatted'] = date('d/m/Y H:i', strtotime($usuario['fecha_registro']));
                $usuario['fecha_actualizacion_formatted'] = date('d/m/Y H:i', strtotime($usuario['fecha_actualizacion']));
                
                jsonResponse(true, 'Usuario obtenido', $usuario, 200);
            } else {
                // Obtener todos los usuarios
                $stmt = $pdo->prepare("
                    SELECT id, nombre, email, rol, fecha_registro, fecha_actualizacion, activo
                    FROM usuarios
                    ORDER BY fecha_registro DESC
                ");
                $stmt->execute();
                $usuarios = $stmt->fetchAll();
                
                // Formatear fechas
                foreach ($usuarios as &$usuario) {
                    $usuario['fecha_registro_formatted'] = date('d/m/Y H:i', strtotime($usuario['fecha_registro']));
                    $usuario['fecha_actualizacion_formatted'] = date('d/m/Y H:i', strtotime($usuario['fecha_actualizacion']));
                    $usuario['userId'] = 'UFC-' . str_pad($usuario['id'], 3, '0', STR_PAD_LEFT);
                }
                
                jsonResponse(true, 'Usuarios obtenidos', $usuarios, 200);
            }
            break;
            
        case 'PUT':
            // Actualizar usuario
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['id'])) {
                jsonResponse(false, 'ID de usuario requerido', null, 400);
            }
            
            $usuarioId = (int)$input['id'];
            
            // Verificar que el usuario existe
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE id = ?");
            $stmt->execute([$usuarioId]);
            if (!$stmt->fetch()) {
                jsonResponse(false, 'Usuario no encontrado', null, 404);
            }
            
            $nombre = isset($input['nombre']) ? sanitizeInput($input['nombre']) : null;
            $email = isset($input['email']) ? sanitizeInput($input['email']) : null;
            $rol = isset($input['rol']) ? sanitizeInput($input['rol']) : null;
            $activo = isset($input['activo']) ? (int)$input['activo'] : null;
            
            // Construir query dinámicamente
            $updates = [];
            $params = [];
            
            if ($nombre !== null) {
                if (strlen($nombre) < 2) {
                    jsonResponse(false, 'El nombre debe tener al menos 2 caracteres', null, 400);
                }
                $updates[] = "nombre = ?";
                $params[] = $nombre;
            }
            
            if ($email !== null) {
                if (!validateEmail($email)) {
                    jsonResponse(false, 'Email inválido', null, 400);
                }
                // Verificar que el email no esté en uso por otro usuario
                $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
                $stmt->execute([$email, $usuarioId]);
                if ($stmt->fetch()) {
                    jsonResponse(false, 'Este email ya está en uso', null, 409);
                }
                $updates[] = "email = ?";
                $params[] = $email;
            }
            
            if ($rol !== null) {
                if (!in_array($rol, ['usuario', 'admin'])) {
                    jsonResponse(false, 'Rol inválido', null, 400);
                }
                $updates[] = "rol = ?";
                $params[] = $rol;
            }
            
            if ($activo !== null) {
                $updates[] = "activo = ?";
                $params[] = $activo;
            }
            
            if (empty($updates)) {
                jsonResponse(false, 'No hay campos para actualizar', null, 400);
            }
            
            $params[] = $usuarioId;
            
            $stmt = $pdo->prepare("
                UPDATE usuarios 
                SET " . implode(', ', $updates) . ", fecha_actualizacion = NOW()
                WHERE id = ?
            ");
            $stmt->execute($params);
            
            // Obtener el usuario actualizado
            $stmt = $pdo->prepare("
                SELECT id, nombre, email, rol, fecha_registro, fecha_actualizacion, activo
                FROM usuarios 
                WHERE id = ?
            ");
            $stmt->execute([$usuarioId]);
            $usuario = $stmt->fetch();
            
            $usuario['fecha_registro_formatted'] = date('d/m/Y H:i', strtotime($usuario['fecha_registro']));
            $usuario['fecha_actualizacion_formatted'] = date('d/m/Y H:i', strtotime($usuario['fecha_actualizacion']));
            
            jsonResponse(true, 'Usuario actualizado exitosamente', $usuario, 200);
            break;
            
        case 'DELETE':
            // Eliminar usuario (marcar como inactivo o eliminar físicamente)
            $usuarioId = isset($_GET['id']) ? (int)$_GET['id'] : null;
            
            if (!$usuarioId) {
                jsonResponse(false, 'ID de usuario requerido', null, 400);
            }
            
            // Verificar que el usuario existe
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE id = ?");
            $stmt->execute([$usuarioId]);
            if (!$stmt->fetch()) {
                jsonResponse(false, 'Usuario no encontrado', null, 404);
            }
            
            // No permitir eliminar al propio admin
            if ($usuarioId == $_SESSION['user_id']) {
                jsonResponse(false, 'No puedes eliminar tu propia cuenta', null, 400);
            }
            
            // Eliminar usuario (eliminar también comentarios y favoritos por CASCADE)
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->execute([$usuarioId]);
            
            jsonResponse(true, 'Usuario eliminado exitosamente', null, 200);
            break;
            
        default:
            jsonResponse(false, 'Método no permitido', null, 405);
    }
    
} catch (PDOException $e) {
    error_log("Error en usuarios-admin: " . $e->getMessage());
    jsonResponse(false, 'Error al procesar la solicitud', null, 500);
}

