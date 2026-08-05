<?php
/**
 * Plugin Name: DAIA - Asistente Virtual DataCom
 * Description: Chatbot impulsado por OpenAI para la atención al cliente de DataCom.
 * Version: 1.0.0
 * Author: Tu Experto de Desarrollo
 * Text Domain: datacom-daia-bot
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Define plugin constants
define( 'DAIA_BOT_PATH', plugin_dir_path( __FILE__ ) );
define( 'DAIA_BOT_URL', plugin_dir_url( __FILE__ ) );

// Include necessary files
require_once DAIA_BOT_PATH . 'includes/class-daia-openai.php';
require_once DAIA_BOT_PATH . 'includes/class-daia-settings.php';

// Initialize the plugin
function daia_bot_init() {
    $daia_openai = new DAIA_OpenAI();
    $daia_openai->init();
    
    if ( is_admin() ) {
        $daia_settings = new DAIA_Settings();
        $daia_settings->init();
    }
}
add_action( 'plugins_loaded', 'daia_bot_init' );

// Enqueue scripts and styles
function daia_bot_enqueue_scripts() {
    wp_enqueue_style( 'daia-bot-style', DAIA_BOT_URL . 'assets/css/daia-chat.css', array(), '1.0.0' );
    wp_enqueue_script( 'daia-bot-script', DAIA_BOT_URL . 'assets/js/daia-chat.js', array(), '1.0.0', true );
    
    // Pass AJAX URL and REST URL to JS
    wp_localize_script( 'daia-bot-script', 'daiaBotData', array(
        'restUrl'  => esc_url_raw( rest_url( 'daia/v1/chat' ) ),
        'nonce'    => wp_create_nonce( 'wp_rest' ),
        'avatar'   => DAIA_BOT_URL . 'assets/img/daia-avatar.png'
    ) );
}
add_action( 'wp_enqueue_scripts', 'daia_bot_enqueue_scripts' );

// Output Chat UI HTML in footer
function daia_bot_output_chat_html() {
    ?>
    <div id="daia-chat-widget" class="daia-chat-closed">
        <div id="daia-chat-launcher">
            <img src="<?php echo esc_url( DAIA_BOT_URL . 'assets/img/daia-avatar.png' ); ?>" alt="DAIA" class="daia-avatar-launcher">
            <span class="daia-launcher-text">Chatea con DAIA</span>
        </div>
        <div id="daia-chat-window">
            <div class="daia-chat-header">
                <div class="daia-header-info">
                    <img src="<?php echo esc_url( DAIA_BOT_URL . 'assets/img/daia-avatar.png' ); ?>" alt="DAIA" class="daia-header-avatar">
                    <div>
                        <h4>DAIA</h4>
                        <span class="daia-status">Asistente Virtual DataCom</span>
                    </div>
                </div>
                <button id="daia-close-btn">&times;</button>
            </div>
            <div class="daia-chat-messages" id="daia-chat-messages">
                <!-- Messages will be injected here via JS -->
            </div>
            
            <div id="daia-consent-overlay" class="daia-consent-overlay">
                <div class="daia-consent-content">
                    <p>Para brindarle un mejor servicio, por favor acepte nuestra política de privacidad.</p>
                    <label>
                        <input type="checkbox" id="daia-consent-checkbox">
                        He leído y acepto la <a href="/lopdp/" target="_blank">Política de Protección de Datos Personales</a>.
                    </label>
                    <button id="daia-consent-btn" disabled>Empezar a Chatear</button>
                </div>
            </div>

            <div class="daia-chat-input-area" id="daia-chat-input-area" style="display: none;">
                <input type="text" id="daia-chat-input" placeholder="Escribe tu mensaje aquí..." autocomplete="off">
                <button id="daia-send-btn">
                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                </button>
            </div>
        </div>
    </div>
    <?php
}
add_action( 'wp_footer', 'daia_bot_output_chat_html' );
