<?php
/**
 * API de Perfil de Usuario
 * SMASH UFC
 */

require_once '../config.php';

// Iniciar sesión
startSecureSession();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    jsonResponse(false, 'No autorizado', null, 401);
}

$pdo = getDBConnection();
if (!$pdo) {
    jsonResponse(false, 'Error de conexión a la base de datos', null, 500);
}

$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // Obtener perfil del usuario
        $stmt = $pdo->prepare("
            SELECT id, nombre, email, fecha_registro, fecha_actualizacion 
            FROM usuarios 
            WHERE id = ? AND activo = 1
        ");
        $stmt->execute([$userId]);
        $usuario = $stmt->fetch();
        
        if (!$usuario) {
            jsonResponse(false, 'Usuario no encontrado', null, 404);
        }
        
        // Formatear fechas
        $fechaRegistro = date('d/m/Y', strtotime($usuario['fecha_registro']));
        $fechaActualizacion = date('d/m/Y H:i', strtotime($usuario['fecha_actualizacion']));
        
        $userData = [
            'id' => $usuario['id'],
            'nombre' => $usuario['nombre'],
            'email' => $usuario['email'],
            'fechaRegistro' => $fechaRegistro,
            'fechaActualizacion' => $fechaActualizacion,
            'userId' => 'UFC-' . str_pad($usuario['id'], 3, '0', STR_PAD_LEFT)
        ];
        
        jsonResponse(true, 'Perfil obtenido correctamente', $userData, 200);
        
    } elseif ($method === 'PUT' || $method === 'POST') {
        // Actualizar perfil del usuario
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['nombre']) || !isset($input['email'])) {
            jsonResponse(false, 'Nombre y email son requeridos', null, 400);
        }
        
        $nombre = sanitizeInput($input['nombre']);
        $email = sanitizeInput($input['email']);
        
        // Validaciones
        if (empty($nombre) || strlen($nombre) < 2) {
            jsonResponse(false, 'El nombre debe tener al menos 2 caracteres', null, 400);
        }
        
        if (!validateEmail($email)) {
            jsonResponse(false, 'Email inválido', null, 400);
        }
        
        // Verificar si el email ya está en uso por otro usuario
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
        $stmt->execute([$email, $userId]);
        
        if ($stmt->fetch()) {
            jsonResponse(false, 'Este email ya está en uso', null, 409);
        }
        
        // Actualizar usuario
        $stmt = $pdo->prepare("
            UPDATE usuarios 
            SET nombre = ?, email = ?, fecha_actualizacion = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$nombre, $email, $userId]);
        
        // Actualizar sesión
        $_SESSION['user_nombre'] = $nombre;
        $_SESSION['user_email'] = $email;
        
        // Obtener datos actualizados
        $stmt = $pdo->prepare("
            SELECT id, nombre, email, fecha_registro, fecha_actualizacion 
            FROM usuarios 
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
        $usuario = $stmt->fetch();
        
        // Formatear fechas
        $fechaRegistro = date('d/m/Y', strtotime($usuario['fecha_registro']));
        $fechaActualizacion = date('d/m/Y H:i', strtotime($usuario['fecha_actualizacion']));
        
        $userData = [
            'id' => $usuario['id'],
            'nombre' => $usuario['nombre'],
            'email' => $usuario['email'],
            'fechaRegistro' => $fechaRegistro,
            'fechaActualizacion' => $fechaActualizacion,
            'userId' => 'UFC-' . str_pad($usuario['id'], 3, '0', STR_PAD_LEFT)
        ];
        
        jsonResponse(true, 'Perfil actualizado exitosamente', $userData, 200);
        
    } else {
        jsonResponse(false, 'Método no permitido', null, 405);
    }
    
} catch (PDOException $e) {
    error_log("Error en perfil: " . $e->getMessage());
    jsonResponse(false, 'Error al procesar la solicitud', null, 500);
}

