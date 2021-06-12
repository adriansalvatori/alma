<?php
// on development
add_action( 'wp_ajax_nopriv_modelos_3d', function(){
    $folderPath = dirname(__DIR__) . '/models3d' . '/';
    $json_data = $_POST['config'];
    mkdir ( $folderPath . $_POST['folder_name']);
    move_uploaded_file($_FILES["file"]["tmp_name"], $folderPath . $_POST['folder_name'] .'/'.$_FILES['file']['name']);
    file_put_contents($folderPath . $_POST['folder_name'] . "/config.json", stripslashes($json_data));

    wp_send_json_success( __( 'toobien' ));
});

function get_models($ruta){
    $ruta = $ruta ? $ruta : dirname(__DIR__) . '/models3d/';
    // Se comprueba que realmente sea la ruta de un directorio
    if (is_dir($ruta)){
        // Abre un gestor de directorios para la ruta indicada
        $gestor = opendir($ruta);
        echo "<ul>";

        // Recorre todos los elementos del directorio
        while (($archivo = readdir($gestor)) !== false)  {
                
            $ruta_completa = $ruta . "/" . $archivo;

            // Se muestran todos los archivos y carpetas excepto "." y ".."
            if ($archivo != "." && $archivo != "..") {
                // Si es un directorio se recorre recursivamente
                if (is_dir($ruta_completa)) {
                    echo "<li>" . $archivo . "</li>";
                    get_models($ruta_completa);
                } else {
                    echo "<li>" . $archivo . "</li>";
                }
            }
        }
        
        // Cierra el gestor de directorios
        closedir($gestor);
        echo "</ul>";
    } else {
        echo "No es una ruta de directorio valida<br/>";
    }
};