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
            
            // Check for LEAD tag and process it
            $reply_text = $this->process_lead_tag( $reply_text );
            
            return $reply_text;
        }

        return new WP_Error( 'openai_error', 'Respuesta vacía de OpenAI' );
    }

    private function process_lead_tag( $text ) {
        // Buscar la etiqueta <LEAD ... >
        $pattern = '/<LEAD\s+nombre="([^"]*)"\s+correo="([^"]*)"\s+whatsapp="([^"]*)"\s+servicio="([^"]*)">/i';
        
        if ( preg_match( $pattern, $text, $matches ) ) {
            $nombre   = $matches[1];
            $correo   = $matches[2];
            $whatsapp = $matches[3];
            $servicio = $matches[4];
            
            $this->send_lead_email( $nombre, $correo, $whatsapp, $servicio );
            
            // Remover la etiqueta del texto que verá el usuario
            $text = preg_replace( $pattern, '', $text );
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
        
        $headers = array('Content-Type: text/plain; charset=UTF-8');
        
        // Enviar el correo usando wp_mail
        wp_mail( $to, $subject, $message, $headers );
    }
}
