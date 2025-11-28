<?php
/**
 * API de Login de Usuarios
 * SMASH UFC
 */

require_once '../config.php';

// Solo permitir método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Método no permitido', null, 405);
}

// Obtener datos del POST
$input = json_decode(file_get_contents('php://input'), true);

// Validar que se recibieron los datos necesarios
if (!isset($input['email']) || !isset($input['password'])) {
    jsonResponse(false, 'Email y contraseña son requeridos', null, 400);
}

// Limpiar y validar datos
$email = sanitizeInput($input['email']);
$password = $input['password'];

// Validaciones
if (!validateEmail($email)) {
    jsonResponse(false, 'Email inválido', null, 400);
}

if (empty($password)) {
    jsonResponse(false, 'La contraseña es requerida', null, 400);
}

// Conectar a la base de datos
$pdo = getDBConnection();
if (!$pdo) {
    jsonResponse(false, 'Error de conexión a la base de datos', null, 500);
}

try {
    // Buscar usuario por email
    $stmt = $pdo->prepare("
        SELECT id, nombre, email, password, rol, fecha_registro 
        FROM usuarios 
        WHERE email = ? AND activo = 1
    ");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();
    
    // Verificar si el usuario existe y la contraseña es correcta
    if (!$usuario || !verifyPassword($password, $usuario['password'])) {
        jsonResponse(false, 'Email o contraseña incorrectos', null, 401);
    }
    
    // Iniciar sesión
    startSecureSession();
    $_SESSION['user_id'] = $usuario['id'];
    $_SESSION['user_email'] = $usuario['email'];
    $_SESSION['user_nombre'] = $usuario['nombre'];
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
    
    jsonResponse(true, 'Sesión iniciada correctamente', $userData, 200);
    
} catch (PDOException $e) {
    error_log("Error en login: " . $e->getMessage());
    jsonResponse(false, 'Error al iniciar sesión', null, 500);
}

