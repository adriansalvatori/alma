<?php

// create custom plugin settings menu
add_action('admin_menu', 'my_cool_plugin_create_menu');

function my_cool_plugin_create_menu() {

	//create new top-level menu
	add_menu_page('Theme options Plugin Settings', 'Theme options', 'administrator', __FILE__, 'alma_theme_options_page' , plugins_url('/images/icon.png', __FILE__) );
	//call register settings function
	add_action( 'admin_init', 'alma_theme_options' );
}


function alma_theme_options() {
	//register our settings
	register_setting( 'alma_theme_options_group', 'alma_smtp_host' );
	register_setting( 'alma_theme_options_group', 'alma_smtp_port' );
	register_setting( 'alma_theme_options_group', 'alma_smpt_security' );
    register_setting( 'alma_theme_options_group', 'alma_user_name' );
	register_setting( 'alma_theme_options_group', 'alma_password' );
	register_setting( 'alma_theme_options_group', 'alma_from_smtp' );
    register_setting( 'alma_theme_options_group', 'alma_from_name_smtp' );
    register_setting( 'alma_theme_options_group', 'alma_smpt_active' );
    register_setting( 'alma_theme_options_group', 'alma_logo' );
}

function alma_theme_options_page() {
?>
<div class="wrap">
<h1>Configuraciones del tema</h1>

<form method="post" action="options.php">
    <?php settings_fields( 'alma_theme_options_group' ); ?>
    <?php do_settings_sections( 'alma_theme_options_group' ); ?>
    <style>
        .column {
            display: flex;
            flex-direction: column;
        }

        .column label {
            margin-bottom: 10px;
        }

        .column input {
            margin-bottom: 20px;
        }

        .form-table {
            width: 40%;
        }
    </style>
    <div class="form-table">
        <h2>Logo</h2>
        <div class="column">
            <label for=""><i>Puedes cargar una imagen desde los medios o colocar la url de la imagen</i></label>
            <img id="image-preview" width="200" src="<?php echo esc_attr( get_option('alma_logo') ); ?>" alt="">
            <input id="image-url" type="text" name="alma_logo" value="<?php echo esc_attr( get_option('alma_logo') ); ?>"/>
		    <input id="upload-button" type="button" class="button" value="Upload Image" />
        </div>
        <h2>Configuracion SMTP</h2>
        <div class="column">
            <label for="">¿Usar esta configuracion SMTP?</label>
            <input type="checkbox" name="alma_smpt_active" <?php echo esc_attr( get_option('alma_smpt_active') ? 'checked' : '' ); ?> id="">
        </div>
        <div class="column">
            <label for=""><strong>SMTP Host</strong> - <i>La dirección del HOST del servidor de correo SMTP p.e. smtp.midominio.com</i></label>
            <input type="text" name="alma_smtp_host" value="<?php echo esc_attr( get_option('alma_smtp_host') ); ?>" />
        </div>
        <div class="column">
            <label for=""><strong>SMTP Port</strong> - <i>Puerto SMTP - Suele ser el 25, 465 o 587</i></label>
            <input type="text" name="alma_smtp_port" value="<?php echo esc_attr( get_option('alma_smtp_port') ); ?>" />
        </div>
        <div class="column">
            <label for=""><strong>SMTP security</strong> - <i>El tipo de encriptación que usamos al conectar - ssl (deprecated) o tls</i></label>
            <input type="text" name="alma_smpt_security" value="<?php echo esc_attr( get_option('alma_smpt_security') ); ?>" />
        </div>
        
        <div class="column">
            <label for=""><strong>User</strong> - <i>Usuario de la cuenta de correo</i></label>
            <input type="text" name="alma_user_name" value="<?php echo esc_attr( get_option('alma_user_name') ); ?>" />
        </div>
        <div class="column">
            <label for=""><strong>Password</strong> - <i>Contraseña para la autenticación SMTP</i></label>
            <input type="text" name="alma_password" value="<?php echo esc_attr( get_option('alma_password') ); ?>" />
        </div>
        <div class="column">
            <label for=""><strong>From:</strong> - <i>Desde donde se envia p.e "info@dominio.com", "Alma Andrea"</i></label>
            <input type="text" name="alma_from_smtp" value="<?php echo esc_attr( get_option('alma_from_smtp') ); ?>" />
        </div>
        <div class="column">
            <label for=""><strong>FromName:</strong> - <i>Nombre de quien envia p.e "Alma Andrea"</i></label>
            <input type="text" name="alma_from_name_smtp" value="<?php echo esc_attr( get_option('alma_from_name_smtp') ); ?>" />
        </div>
        
        <script>
            jQuery(document).ready(function($){
	            var mediaUploader;

                $('#upload-button').click(function(e) {
                    e.preventDefault();

                // If the uploader object has already been created, reopen the dialog
                    if (mediaUploader) {
                        mediaUploader.open();
                        return;
                    }

                // Extend the wp.media object
                mediaUploader = wp.media.frames.file_frame = wp.media({
                            title: 'Choose Image',
                            button: {
                            text: 'Choose Image'
                        }, multiple: false });

                // When a file is selected, grab the URL and set it as the text field's value
                mediaUploader.on('select', function() {
                        attachment = mediaUploader.state().get('selection').first().toJSON();
                        $('#image-url').val(attachment.url);
                        $('#image-preview').attr('src', attachment.url);
                    });

                // Open the uploader dialog
                mediaUploader.open();
                });

            });
        </script>
    </div>
    
    <?php submit_button(); ?>

</form>
</div>
<?php }