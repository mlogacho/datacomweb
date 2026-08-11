<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class DAIA_OpenAI {
    
    private function get_api_key() {
        return get_option('daia_openai_api_key', '');
    }

    public function init() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    public function register_routes() {
        register_rest_route( 'daia/v1', '/chat', array(
            'methods'  => 'POST',
            'callback' => array( $this, 'handle_chat_request' ),
            'permission_callback' => '__return_true'
        ) );
    }

    public function handle_chat_request( WP_REST_Request $request ) {
        $messages = $request->get_param( 'messages' );
        
        if ( empty( $messages ) || ! is_array( $messages ) ) {
            return new WP_Error( 'invalid_data', 'No messages provided', array( 'status' => 400 ) );
        }

        // Prepare messages for OpenAI
        $system_prompt = $this->get_system_prompt();
        
        $openai_messages = array(
            array(
                'role'    => 'system',
                'content' => $system_prompt
            )
        );

        foreach ( $messages as $msg ) {
            // Solo aceptamos roles validos
            if ( in_array( $msg['role'], array( 'user', 'assistant' ) ) ) {
                $openai_messages[] = array(
                    'role'    => $msg['role'],
                    'content' => sanitize_text_field( $msg['content'] )
                );
            }
        }

        $response = $this->call_openai( $openai_messages );
        
        if ( is_wp_error( $response ) ) {
            return $response;
        }

        return rest_ensure_response( array(
            'reply' => $response
        ) );
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
Siempre que un cliente muestre interés en contratar, cotizar un servicio o saber precios, debes pedirle sus datos de contacto de forma natural. 
Si el cliente te proporciona sus datos, debes incluir EXACTAMENTE al final de tu respuesta una etiqueta secreta con el siguiente formato, reemplazando con los datos reales del cliente:
<LEAD nombre=\"[Su nombre]\" correo=\"[Su correo]\" whatsapp=\"[Su whatsapp]\" servicio=\"[Servicio de interes]\">
Esta etiqueta no se le mostrará al usuario, pero es vital para el sistema. Luego despídete cordialmente indicando que un asesor se comunicará muy pronto y que su información será protegida conforme a nuestra Política de Privacidad.";
    }

    private function call_openai( $messages ) {
        $url = 'https://api.openai.com/v1/chat/completions';
        
        $body = array(
            'model'       => 'gpt-4o-mini',
            'messages'    => $messages,
            'temperature' => 0.7,
            'max_tokens'  => 500
        );

        $args = array(
            'body'        => wp_json_encode( $body ),
            'headers'     => array(
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $this->get_api_key()
            ),
            'timeout'     => 30,
            'data_format' => 'body'
        );

        $response = wp_remote_post( $url, $args );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        $response_body = wp_remote_retrieve_body( $response );
        $data = json_decode( $response_body, true );

        if ( $response_code !== 200 ) {
            return new WP_Error( 'openai_error', 'Error en la respuesta de OpenAI', array( 'status' => $response_code, 'data' => $data ) );
        }

        if ( isset( $data['choices'][0]['message']['content'] ) ) {
            $reply_text = $data['choices'][0]['message']['content'];
            
            error_log("DAIA RAW REPLY: " . $reply_text);
            
            // Check for LEAD tag and process it
            $reply_text = $this->process_lead_tag( $reply_text, $messages );
            
            return $reply_text;
        }

        return new WP_Error( 'openai_error', 'Respuesta vacía de OpenAI' );
    }

    private function process_lead_tag( $text, $messages ) {
        // Buscar la etiqueta <LEAD ... > (más permisiva)
        $pattern = '/<LEAD.*?nombre="([^"]*)".*?correo="([^"]*)".*?whatsapp="([^"]*)".*?>/is';
        
        if ( preg_match( $pattern, $text, $matches ) ) {
            $nombre   = $matches[1];
            $correo   = $matches[2];
            $whatsapp = $matches[3];
            
            // Extraer servicio si existe
            $servicio = 'No especificado';
            if ( preg_match( '/servicio="([^"]*)"/is', $matches[0], $srv_match ) ) {
                $servicio = $srv_match[1];
            }
            
            error_log("DAIA LEAD FOUND: Nombre=$nombre, Correo=$correo, WhatsApp=$whatsapp, Servicio=$servicio");
            $this->send_lead_email( $nombre, $correo, $whatsapp, $servicio );
            
            // Remover la etiqueta del texto que verá el usuario (remover cualquier formato de etiqueta LEAD)
            $cleaned_text = preg_replace( '/<LEAD.*?>/is', '', $text );
            
            // Enviar copia al cliente
            $this->send_customer_email( $nombre, $correo, $messages, trim( $cleaned_text ) );
            
            $text = $cleaned_text;
        } else {
            error_log("DAIA NO LEAD TAG FOUND IN TEXT.");
        }
        
        return trim( $text );
    }

    private function send_lead_email( $nombre, $correo, $whatsapp, $servicio ) {
        $to = 'info@datacom.ec';
        $subject = 'Nuevo Lead captado por DAIA (Bot)';
        
        $message = "Hola Equipo de Ventas,\n\n";
        $message .= "El asistente virtual DAIA ha captado un nuevo cliente interesado en un servicio.\n\n";
        $message .= "DATOS DEL CLIENTE:\n";
        $message .= "Nombre: " . sanitize_text_field( $nombre ) . "\n";
        $message .= "Correo: " . sanitize_email( $correo ) . "\n";
        $message .= "WhatsApp: " . sanitize_text_field( $whatsapp ) . "\n";
        $message .= "Servicio de Interés: " . sanitize_text_field( $servicio ) . "\n\n";
        $message .= "Por favor, contactar a la brevedad.\n";
        
        $headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            'From: DAIA Bot <daia@datacom.ec>'
        );
        
        // Configurar SMTP específicamente para este envío
        add_action( 'phpmailer_init', array( $this, 'configure_smtp' ) );
        
        add_action( 'wp_mail_failed', function( $wp_error ) {
            error_log("DAIA WP_MAIL_FAILED: " . print_r($wp_error, true));
        } );

        // Enviar el correo usando wp_mail
        $result = wp_mail( $to, $subject, $message, $headers );
        error_log("DAIA WP_MAIL RESULT: " . ($result ? 'SUCCESS' : 'FAILED'));
        
        // Remover configuración SMTP para no afectar otros correos del sitio
        remove_action( 'phpmailer_init', array( $this, 'configure_smtp' ) );
    }

    private function send_customer_email( $nombre, $correo, $messages, $final_reply ) {
        $subject = 'Tu conversación con DAIA - DataCom';
        
        $message = "Hola " . sanitize_text_field( $nombre ) . ",\n\n";
        $message .= "Gracias por contactarte con DataCom. Adjuntamos una copia de tu conversación con nuestro asistente virtual DAIA.\n\n";
        $message .= "--- INICIO DE LA CONVERSACIÓN ---\n\n";
        
        foreach ( $messages as $msg ) {
            $role = ( $msg['role'] === 'user' ) ? 'Tú' : 'DAIA';
            if ( $msg['role'] === 'system' ) continue;
            
            $message .= $role . ":\n" . sanitize_text_field( $msg['content'] ) . "\n\n";
        }
        
        $message .= "DAIA:\n" . sanitize_text_field( $final_reply ) . "\n\n";
        $message .= "--- FIN DE LA CONVERSACIÓN ---\n\n";
        $message .= "Un asesor comercial se pondrá en contacto contigo pronto.\n\n";
        $message .= "Saludos,\nEl equipo de DataCom\n";
        
        $headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            'From: DAIA Bot <daia@datacom.ec>'
        );
        
        add_action( 'phpmailer_init', array( $this, 'configure_smtp' ) );
        $result = wp_mail( $correo, $subject, $message, $headers );
        error_log("DAIA CUSTOMER WP_MAIL RESULT TO $correo: " . ($result ? 'SUCCESS' : 'FAILED'));
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
