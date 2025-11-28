<?php
/**
 * API de Logout
 * SMASH UFC
 */

require_once '../config.php';

// Iniciar sesión
startSecureSession();

// Destruir sesión
session_unset();
session_destroy();

jsonResponse(true, 'Sesión cerrada correctamente', null, 200);

