<?php
/**
 * API para verificar sesión activa
 * SMASH UFC
 */

require_once '../config.php';

// Iniciar sesión
startSecureSession();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    jsonResponse(false, 'No hay sesión activa', null, 401);
}

$pdo = getDBConnection();
if (!$pdo) {
    jsonResponse(false, 'Error de conexión a la base de datos', null, 500);
}

$userId = $_SESSION['user_id'];

try {
    // Obtener datos del usuario
    $stmt = $pdo->prepare("
        SELECT id, nombre, email, rol, fecha_registro 
        FROM usuarios 
        WHERE id = ? AND activo = 1
    ");
    $stmt->execute([$userId]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        // Si el usuario no existe, destruir sesión
        session_unset();
        session_destroy();
        jsonResponse(false, 'Usuario no encontrado', null, 404);
    }
    
    // Actualizar rol en sesión
    $_SESSION['user_rol'] = $usuario['rol'] ?? 'usuario';
    
    // Formatear fecha
    $fechaRegistro = date('d/m/Y', strtotime($usuario['fecha_registro']));
    
    // Preparar respuesta
    $userData = [
        'id' => $usuario['id'],
        'nombre' => $usuario['nombre'],
        'email' => $usuario['email'],
        'rol' => $usuario['rol'] ?? 'usuario',
        'fechaRegistro' => $fechaRegistro,
        'userId' => 'UFC-' . str_pad($usuario['id'], 3, '0', STR_PAD_LEFT)
    ];
    
    jsonResponse(true, 'Sesión activa', $userData, 200);
    
} catch (PDOException $e) {
    error_log("Error en check-session: " . $e->getMessage());
    jsonResponse(false, 'Error al verificar sesión', null, 500);
}

