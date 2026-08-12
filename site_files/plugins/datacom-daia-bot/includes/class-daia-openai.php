<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * DAIA Bot - Gemini API handler
 * Migrated from OpenAI to Google Gemini (gemini-2.0-flash)
 */
class DAIA_OpenAI {

    private $gemini_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent';

    public function init() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    public function register_routes() {
        register_rest_route( 'daia/v1', '/chat', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'handle_chat' ),
            'permission_callback' => '__return_true',
        ) );
    }

    private function get_api_key() {
        return get_option( 'daia_openai_api_key', '' ); // same option key, just stores Gemini key now
    }

    private function get_system_prompt() {
        return "Eres DAIA, el asistente virtual oficial de DataCom. Tu tono es profesional, entusiasta, cálido y altamente comercial. 
Tu objetivo es ayudar a los clientes a conocer los servicios de DataCom, asesorarlos de forma amigable y captar sus datos (nombre, correo electrónico y número de WhatsApp) para que el equipo de ventas los contacte y cierre el negocio.

INSTRUCCIONES DE COMUNICACIÓN:
1. PRIVACIDAD DE EMPRESA: BAJO NINGUNA CIRCUNSTANCIA proporciones información sobre empleados, directivos, correos electrónicos internos, números telefónicos personales o datos del representante legal.
2. NO INVENTES: No inventes servicios, precios, ni datos que no estén en este prompt. Tu objetivo principal y casi exclusivo es captar información de clientes (leads).
3. EXCLUSIVIDAD CORPORATIVA (B2B): DataCom se especializa EXCLUSIVAMENTE en el sector corporativo y empresarial. NO ofrecemos internet residencial ni planes para hogares. Si un usuario pregunta por internet residencial, respóndele de forma muy gentil y amable que nuestro giro de negocio está enfocado 100% en brindar soluciones de alta capacidad para empresas y negocios, por lo que no contamos con planes residenciales.
4. NUNCA respondas con una lista técnica o robótica de servicios en mayúsculas. 
5. Cuando te pregunten qué servicios ofrecemos, agrupa las opciones de manera comercial y atractiva (por ejemplo: 'Soluciones de Internet Empresarial', 'Servicios de Data Center y Cloud', 'Soporte y Outsourcing IT', 'Seguridad y Video Vigilancia', etc.).
6. Usa emojis con moderación para hacer la conversación más cálida (por ejemplo: 🚀, 🌐, 🔒, 💻).
7. Explica brevemente los beneficios de los servicios si el cliente muestra interés en una categoría.

CATÁLOGO DE SERVICIOS (Conoce estos servicios, pero preséntalos de forma comercial y conversacional, no como una lista cruda):
- Soluciones de Internet y Conectividad: Internet Dedicado Pyme, Internet Satelital, Internet para Data Center.
- Data Center y Cloud: Alojamiento en Data Center (Colocation), Administración de Data Center, KVA de Potencia.
- Seguridad y Redes: Firewall Gestionado, WiFi Gestionado, Video Vigilancia.
- Soporte y Proyectos: Servicio y Soporte Técnico, Outsourcing IT, Proyectos Especiales, Instalaciones y Obra Civil.
- Venta y Renta: Venta de Bienes Tecnológicos, Renta de Equipos.

REGLA ESTRICTA DE PRECIOS: BAJO NINGUNA CIRCUNSTANCIA debes dar precios. Si un cliente pregunta por el precio, debes decirle con amabilidad que nuestras soluciones son hechas a la medida de cada empresa, y pedirle sus datos (Nombre, Correo, y WhatsApp) para que un Asesor Comercial experto se contacte y le brinde una cotización exacta.

CUMPLIMIENTO LOPDP (Ley Orgánica de Protección de Datos Personales):
- Privacidad ante todo: Como asistente de DataCom, garantizas que los datos recolectados se tratarán bajo principios de licitud, transparencia y minimización (Art. 32 LOPDP).
- Si te preguntan sobre qué hacemos con los datos: Responde que los utilizamos exclusivamente para fines de contacto comercial y provisión de servicios, conservándolos el tiempo mínimo necesario según la ley.
- Si te preguntan cómo ejercer sus derechos (Acceso, Rectificación, Cancelación, Oposición, Portabilidad): Indícales que pueden comunicarse con nuestro Delegado de Protección de Datos (DPD), Marco Logacho, al correo mlogacho@datacom.net.ec o al teléfono +593 999952644, o ingresar a datacom.ec/lopdp/.

CAPTACIÓN DE LEADS: 
Siempre que un cliente muestre interés en contratar, cotizar un servicio o saber precios, debes pedirle sus datos de contacto de forma natural (Nombre, Correo y WhatsApp). 
Si el cliente te proporciona sus datos, debes incluir EXACTAMENTE al final de tu respuesta una etiqueta secreta con el siguiente formato, reemplazando con los datos reales del cliente:
<LEAD nombre=\"[Su nombre]\" correo=\"[Su correo]\" whatsapp=\"[Su whatsapp]\" servicio=\"[Servicio de interes]\">
Esta etiqueta no se le mostrará al usuario, pero es vital para el sistema. Luego despídete cordialmente indicando que un asesor se comunicará muy pronto y que su información será protegida conforme a nuestra Política de Privacidad.";
    }

    public function handle_chat( $request ) {
        $params   = $request->get_json_params();
        $messages = isset( $params['messages'] ) ? $params['messages'] : array();

        if ( empty( $messages ) ) {
            return new WP_Error( 'no_messages', 'No messages provided', array( 'status' => 400 ) );
        }

        $api_key = $this->get_api_key();
        if ( empty( $api_key ) ) {
            return new WP_Error( 'no_api_key', 'API key not configured', array( 'status' => 500 ) );
        }

        // Build Gemini contents array from OpenAI-style messages
        $system_prompt = $this->get_system_prompt();
        $contents = array();

        foreach ( $messages as $msg ) {
            if ( $msg['role'] === 'system' ) continue;
            $role = ( $msg['role'] === 'assistant' ) ? 'model' : 'user';
            $contents[] = array(
                'role'  => $role,
                'parts' => array( array( 'text' => $msg['content'] ) ),
            );
        }

        // Gemini request body
        $body = array(
            'system_instruction' => array(
                'parts' => array( array( 'text' => $system_prompt ) ),
            ),
            'contents'           => $contents,
            'generationConfig'   => array(
                'temperature'     => 0.7,
                'maxOutputTokens' => 800,
            ),
        );

        $url  = $this->gemini_url . '?key=' . $api_key;
        $args = array(
            'method'      => 'POST',
            'headers'     => array( 'Content-Type' => 'application/json' ),
            'body'        => json_encode( $body ),
            'timeout'     => 30,
            'data_format' => 'body',
        );

        $response = wp_remote_post( $url, $args );

        if ( is_wp_error( $response ) ) {
            error_log( 'DAIA Gemini WP_Error: ' . $response->get_error_message() );
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        $response_body = wp_remote_retrieve_body( $response );
        $data          = json_decode( $response_body, true );

        if ( $response_code !== 200 ) {
            error_log( 'DAIA Gemini error ' . $response_code . ': ' . $response_body );
            return new WP_Error( 'gemini_error', 'Error en la respuesta de Gemini', array( 'status' => $response_code, 'data' => $data ) );
        }

        $reply_text = isset( $data['candidates'][0]['content']['parts'][0]['text'] )
            ? $data['candidates'][0]['content']['parts'][0]['text']
            : '';

        if ( empty( $reply_text ) ) {
            error_log( 'DAIA Gemini empty response: ' . $response_body );
            return new WP_Error( 'gemini_error', 'Respuesta vacía de Gemini' );
        }

        error_log( 'DAIA Gemini RAW REPLY: ' . $reply_text );

        // Process LEAD tag (same as before)
        $reply_text = $this->process_lead_tag( $reply_text, $messages );

        return rest_ensure_response( array(
            'reply' => $reply_text
        ) );
    }

    private function process_lead_tag( $text, $messages ) {
        $pattern = '/<LEAD.*?nombre="([^"]*)".*?correo="([^"]*)".*?whatsapp="([^"]*)".*?>/is';

        if ( preg_match( $pattern, $text, $matches ) ) {
            $nombre   = $matches[1];
            $correo   = $matches[2];
            $whatsapp = $matches[3];

            $servicio = 'No especificado';
            if ( preg_match( '/servicio="([^"]*)"/is', $matches[0], $srv ) ) {
                $servicio = $srv[1];
            }

            error_log( "DAIA LEAD FOUND: Nombre=$nombre, Correo=$correo, WhatsApp=$whatsapp, Servicio=$servicio" );
            $this->send_lead_email( $nombre, $correo, $whatsapp, $servicio );

            $cleaned_text = preg_replace( '/<LEAD.*?>/is', '', $text );
            $this->send_customer_email( $nombre, $correo, $messages, trim( $cleaned_text ) );
            $text = $cleaned_text;
        } else {
            error_log( 'DAIA NO LEAD TAG FOUND IN TEXT.' );
        }

        return trim( $text );
    }

    private function send_lead_email( $nombre, $correo, $whatsapp, $servicio ) {
        $to      = 'info@datacom.ec';
        $subject = 'Nuevo Lead captado por DAIA (Bot)';
        $message = "Hola Equipo de Ventas,\n\n";
        $message .= "El asistente virtual DAIA ha captado un nuevo cliente interesado.\n\n";
        $message .= "DATOS DEL CLIENTE:\n";
        $message .= "Nombre: " . sanitize_text_field( $nombre ) . "\n";
        $message .= "Correo: " . sanitize_email( $correo ) . "\n";
        $message .= "WhatsApp: " . sanitize_text_field( $whatsapp ) . "\n";
        $message .= "Servicio de Interés: " . sanitize_text_field( $servicio ) . "\n\n";
        $message .= "Por favor, contactar a la brevedad.\n";

        $headers = array( 'Content-Type: text/plain; charset=UTF-8', 'From: DAIA Bot <daia@datacom.ec>' );
        add_action( 'phpmailer_init', array( $this, 'configure_smtp' ) );
        $result = wp_mail( $to, $subject, $message, $headers );
        error_log( 'DAIA WP_MAIL RESULT: ' . ( $result ? 'SUCCESS' : 'FAILED' ) );
        remove_action( 'phpmailer_init', array( $this, 'configure_smtp' ) );
    }

    private function send_customer_email( $nombre, $correo, $messages, $final_reply ) {
        $subject  = 'Tu conversación con DAIA - DataCom';
        $message  = "Hola " . sanitize_text_field( $nombre ) . ",\n\n";
        $message .= "Gracias por contactarte con DataCom. Adjuntamos una copia de tu conversación con DAIA.\n\n";
        $message .= "--- INICIO ---\n\n";
        foreach ( $messages as $msg ) {
            if ( $msg['role'] === 'system' ) continue;
            $role     = ( $msg['role'] === 'user' ) ? 'Tú' : 'DAIA';
            $message .= $role . ":\n" . sanitize_text_field( $msg['content'] ) . "\n\n";
        }
        $message .= "DAIA:\n" . sanitize_text_field( $final_reply ) . "\n\n--- FIN ---\n\n";
        $message .= "Un asesor se pondrá en contacto pronto.\n\nEquipo DataCom\n";

        $headers = array( 'Content-Type: text/plain; charset=UTF-8', 'From: DAIA Bot <daia@datacom.ec>' );
        add_action( 'phpmailer_init', array( $this, 'configure_smtp' ) );
        $result = wp_mail( $correo, $subject, $message, $headers );
        error_log( 'DAIA CUSTOMER MAIL TO ' . $correo . ': ' . ( $result ? 'SUCCESS' : 'FAILED' ) );
        remove_action( 'phpmailer_init', array( $this, 'configure_smtp' ) );
    }

    public function configure_smtp( $phpmailer ) {
        $phpmailer->isSMTP();
        $phpmailer->Host       = 'mail.datacom.ec';
        $phpmailer->SMTPAuth   = true;
        $phpmailer->Port       = 465;
        $phpmailer->Username   = 'daia@datacom.ec';
        $phpmailer->Password   = 'I2Mh)c*)+dGcLoWa';
        $phpmailer->SMTPSecure = 'ssl';
        $phpmailer->setFrom( 'daia@datacom.ec', 'DAIA Bot' );
    }
}
