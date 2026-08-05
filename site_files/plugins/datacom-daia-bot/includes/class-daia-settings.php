<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class DAIA_Settings {

    public function init() {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    public function add_plugin_page() {
        add_menu_page(
            'Ajustes DAIA Bot', 
            'DAIA Bot', 
            'manage_options', 
            'daia-bot-settings', 
            array( $this, 'create_admin_page' ), 
            'dashicons-format-chat', 
            100 
        );
    }

    public function create_admin_page() {
        ?>
        <div class="wrap">
            <h1>Configuración de DAIA Bot</h1>
            <form method="post" action="options.php">
            <?php
                settings_fields( 'daia_bot_option_group' );
                do_settings_sections( 'daia-bot-settings-admin' );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    public function page_init() {
        register_setting(
            'daia_bot_option_group',
            'daia_openai_api_key',
            array( $this, 'sanitize' )
        );

        add_settings_section(
            'daia_bot_setting_section',
            'Configuración de la API',
            array( $this, 'section_info' ),
            'daia-bot-settings-admin'
        );

        add_settings_field(
            'daia_openai_api_key',
            'OpenAI API Key',
            array( $this, 'api_key_callback' ),
            'daia-bot-settings-admin',
            'daia_bot_setting_section'
        );
    }

    public function sanitize( $input ) {
        return sanitize_text_field( $input );
    }

    public function section_info() {
        echo 'Ingresa tu clave de API de OpenAI para que el chatbot pueda procesar respuestas.';
    }

    public function api_key_callback() {
        $val = get_option( 'daia_openai_api_key' );
        echo '<input type="password" id="daia_openai_api_key" name="daia_openai_api_key" value="' . esc_attr( $val ) . '" style="width:400px;" />';
    }
}
