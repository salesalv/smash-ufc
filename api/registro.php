<?php
/**
 * API de Registro de Usuarios
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
if (!isset($input['nombre']) || !isset($input['email']) || !isset($input['password'])) {
    jsonResponse(false, 'Faltan datos requeridos', null, 400);
}

// Limpiar y validar datos
$nombre = sanitizeInput($input['nombre']);
$email = sanitizeInput($input['email']);
$password = $input['password'];
$passwordConfirm = isset($input['password_confirm']) ? $input['password_confirm'] : '';

// Validaciones
if (empty($nombre) || strlen($nombre) < 2) {
    jsonResponse(false, 'El nombre debe tener al menos 2 caracteres', null, 400);
}

if (!validateEmail($email)) {
    jsonResponse(false, 'Email inválido', null, 400);
}

if (strlen($password) < 6) {
    jsonResponse(false, 'La contraseña debe tener al menos 6 caracteres', null, 400);
}

if ($password !== $passwordConfirm) {
    jsonResponse(false, 'Las contraseñas no coinciden', null, 400);
}

// Conectar a la base de datos
$pdo = getDBConnection();
if (!$pdo) {
    jsonResponse(false, 'Error de conexión a la base de datos', null, 500);
}

try {
    // Verificar si el email ya existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        jsonResponse(false, 'Este email ya está registrado', null, 409);
    }
    
    // Hash de la contraseña
    $passwordHash = hashPassword($password);
    
    // Insertar nuevo usuario
    $stmt = $pdo->prepare("
        INSERT INTO usuarios (nombre, email, password, fecha_registro) 
        VALUES (?, ?, ?, NOW())
    ");
    
    $stmt->execute([$nombre, $email, $passwordHash]);
    
    $userId = $pdo->lastInsertId();
    
    // Obtener datos del usuario creado
    $stmt = $pdo->prepare("
        SELECT id, nombre, email, fecha_registro 
        FROM usuarios 
        WHERE id = ?
    ");
    $stmt->execute([$userId]);
    $usuario = $stmt->fetch();
    
    // NO iniciar sesión automáticamente - el usuario debe iniciar sesión manualmente
    // Formatear fecha
    $fechaRegistro = date('d/m/Y', strtotime($usuario['fecha_registro']));
    
    // Preparar respuesta (sin datos sensibles, solo confirmación)
    $responseData = [
        'id' => $usuario['id'],
        'nombre' => $usuario['nombre'],
        'email' => $usuario['email']
    ];
    
    jsonResponse(true, 'Usuario registrado exitosamente. Por favor, inicia sesión.', $responseData, 201);
    
} catch (PDOException $e) {
    error_log("Error en registro: " . $e->getMessage());
    jsonResponse(false, 'Error al registrar usuario', null, 500);
}

