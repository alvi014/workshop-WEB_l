<?php
/**
 * Script de Consola: notificar_pendientes.php
 * Ubicación: script_consola/notificar_pendientes.php
 * Propósito: Identificar reservas Pendientes antiguas y notificar a los choferes.
 * * Este script DEBE ser ejecutado desde la terminal (CLI), no desde el navegador.
 * Ejemplo de ejecución: php notificar_pendientes.php
 */


require '../includes/db_conexion.php'; 


$LIMITE_MINUTOS = 30; 
$tiempo_limite = date('Y-m-d H:i:s', strtotime("-$LIMITE_MINUTOS minutes"));

echo "--- Iniciando proceso de Notificación de Reservas Pendientes ---\n";
echo "Buscando reservas Pendientes creadas antes de: $tiempo_limite\n";

try {
  
   
    $sql = "SELECT 
                U.id_usuario AS id_chofer,
                U.nombre AS nombre_chofer,
                U.correo_electronico AS correo_chofer,
                COUNT(RES.id_reserva) AS total_pendientes
            FROM Usuarios U
            JOIN Rides R ON U.id_usuario = R.id_chofer
            JOIN Reservas RES ON R.id_ride = RES.id_ride
            WHERE RES.estado = 'Pendiente'
            AND RES.fecha_solicitud < ?
            GROUP BY U.id_usuario";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$tiempo_limite]);
    $choferes_a_notificar = $stmt->fetchAll();

} catch (\PDOException $e) {
    die("ERROR de BD al buscar reservas: " . $e->getMessage() . "\n");
}


if (empty($choferes_a_notificar)) {
    echo "No se encontraron choferes con solicitudes pendientes antiguas.\n";
} else {
    echo "Choferes encontrados con solicitudes pendientes: " . count($choferes_a_notificar) . "\n";
    
    foreach ($choferes_a_notificar as $chofer) {
        $destinatario = $chofer['correo_chofer'];
        $asunto = "Recordatorio: Tienes ({$chofer['total_pendientes']}) solicitudes de reserva pendientes de aprobar.";
        
        $cuerpo_mensaje = "Hola {$chofer['nombre_chofer']},\n\n";
        $cuerpo_mensaje .= "El sistema ha detectado que tienes **{$chofer['total_pendientes']}** solicitudes de reserva de Rides que llevan más de {$LIMITE_MINUTOS} minutos sin ser gestionadas (aceptadas o rechazadas).\n\n";
        $cuerpo_mensaje .= "Por favor, ingresa a tu panel de control para revisarlas:\n";
        $cuerpo_mensaje .= "URL_DE_TU_SISTEMA/views/chofer/gestionar_reservas.php\n\n";
        $cuerpo_mensaje .= "Agradecemos tu pronta gestión.\n";
        $cuerpo_mensaje .= "Atentamente,\nEl Equipo de Aventones.\n";
        
        // Cabeceras para simular un correo HTML/Texto plano adecuado
        $headers = "From: no-reply@aventones.com\r\n";
        $headers .= "Reply-To: no-reply@aventones.com\r\n";
        $headers .= "Content-type: text/plain; charset=utf-8\r\n";
        
        // --- Intento de Envío de Correo (Simulación) ---
        // La función mail() debe estar correctamente configurada en el servidor (ej. sendmail en XAMPP)
        $envio_exitoso = mail($destinatario, $asunto, $cuerpo_mensaje, $headers);
        
        if ($envio_exitoso) {
            echo "-> Notificación enviada a {$chofer['nombre_chofer']} ({$destinatario}) por {$chofer['total_pendientes']} reservas.\n";
        } else {
            echo "-> ERROR: No se pudo enviar la notificación por correo a {$destinatario}. (Verificar configuración de mail() en php.ini)\n";
        }
    }
}

echo "--- Proceso de Notificación Finalizado ---\n";
?>