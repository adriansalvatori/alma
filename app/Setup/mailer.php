<?php 
if(get_option( 'alma_smpt_active' )){
    add_action('phpmailer_init','send_smtp_email');

    function send_smtp_email( $phpmailer )
    {
        // Define que estamos enviando por SMTP
        $phpmailer->isSMTP();
    
        // La dirección del HOST del servidor de correo SMTP p.e. smtp.midominio.com
        $phpmailer->Host = get_option( 'alma_smpt_active' ) || '';
    
        // Uso autenticación por SMTP (true|false)
        $phpmailer->SMTPAuth = true;
    
        // Puerto SMTP - Suele ser el 25, 465 o 587
        $phpmailer->Port = get_option( 'alma_smtp_port' ) || 465;
    
        // Usuario de la cuenta de correo
        $phpmailer->Username = get_option( 'alma_user_name' ) || '';
    
        // Contraseña para la autenticación SMTP
        $phpmailer->Password = get_option( 'alma_password' ) || '';
    
        // El tipo de encriptación que usamos al conectar - ssl (deprecated) o tls
        $phpmailer->SMTPSecure = get_option( 'alma_smpt_security' ) || 'tls';
    
        $phpmailer->From = get_option( 'alma_from_smtp' ) || '';
        $phpmailer->FromName = get_option( 'alma_from_name_smtp' ) || '';
    }
}

add_action( 'wp_ajax_nopriv_send_mailsinador', function(){
    $data = $_POST;
    $folderPath = dirname(__DIR__) . '/mailtemp' . '/';
    $to = json_decode(stripslashes($data['to']))->mails;
    $subject = $data['subject'] ? $data['subject'] : 'Contacto desde '. home_url();
    $body = ''.$data['mailBody'].'';
    $success = $data['success'] ? $data['success'] : 'Mail send';
    $error = $data['error'] ? $data['error'] : 'Mail fail';
    $files = $_FILES['fileToUpload'];

    $count = count($files['name']);
    $attachment = [];
    for ($i = 0; $i < $count; $i++) {
        move_uploaded_file($files['tmp_name'][$i], $folderPath . $files['name'][$i]);
        $attachment[]= $folderPath . $files['name'][$i];
    }

    foreach ($head as $key) {
        $headers[]= $key;
    }
    $headers[]= "Content-Type: text/html; charset=UTF-8";
    $headers[]= "From: Fonclaro <no-response@fonclaro.comercialcolombiana.com.co>";

    if(wp_mail( $to, $subject , $body, $headers )) {
        for ($i = 0; $i < $count; $i++) {
            unlink($folderPath . $files['name'][$i]);
        }
        wp_send_json_success( __( $success ) );
    }else {
        for ($i = 0; $i < $count; $i++) {
            unlink($folderPath . $files['name'][$i]);
        }
        wp_send_json_error( __( $error ) );
    }
});