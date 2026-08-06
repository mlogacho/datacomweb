<?php
/**
 * Plugin Name: LOPDP Global Footer
 * Description: Inyecta de forma permanente un enlace a la Política de Privacidad en el pie de página de todo el sitio web para cumplir con la LOPDP.
 * Version: 1.0
 * Author: DataCom
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function datacom_lopdp_global_footer() {
    ?>
    <div id="lopdp-global-footer" style="
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background-color: #f8fafc;
        border-top: 1px solid #e2e8f0;
        text-align: center;
        padding: 8px 15px;
        font-family: 'Inter', sans-serif, Arial;
        font-size: 12px;
        z-index: 999998; /* Just below the chat widget */
        box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
    ">
        <a href="/lopdp/" target="_blank" style="color: #475569; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; transition: color 0.2s;">
            <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
            Política de Privacidad y Tratamiento de Datos Personales
        </a>
        <style>
            #lopdp-global-footer a:hover { color: #0ea5e9 !important; }
            /* Push body up slightly so footer doesn't hide content */
            body { padding-bottom: 35px !important; }
            /* Unify font for site description (tagline) in legacy header */
            .description { font-family: 'Inter', Arial, sans-serif !important; font-size: 14px !important; color: #475569 !important; font-style: normal !important; margin-top: 5px; }
            #header { border-bottom: 1px solid #e2e8f0; padding-bottom: 15px; margin-bottom: 25px; background: #fff; }
            #headerimg { max-width: 800px; margin: 0 auto; padding-top: 15px; padding-left: 20px; padding-right: 20px; }
            hr { display: none !important; } /* Hide the ghost line from theme-compat */
            /* Justify paragraphs in LOPDP container */
            .lopdp-container p, .lopdp-container ul { text-align: justify; }
            /* Replace site title text with logo */
            #headerimg h1 a {
                display: block;
                background: url('/wp-content/recurso-1.png') no-repeat left center;
                background-size: contain;
                width: 220px;
                height: 80px;
                text-indent: -9999px; /* Hide the text */
                overflow: hidden;
            }
            #headerimg h1 { margin: 0; padding: 0; line-height: 1; }
        </style>
    </div>
    <?php
}
add_action( 'wp_footer', 'datacom_lopdp_global_footer', 100 );

// Auto-provision the LOPDP page if it doesn't exist (useful for production deployments)
function datacom_auto_provision_lopdp_page() {
    if ( ! get_option( 'datacom_lopdp_page_created' ) ) {
        $page_slug = 'lopdp';
        $page = get_page_by_path( $page_slug );
        
        if ( ! $page ) {
            $html_content = <<<EOD
<div class="lopdp-container" style="max-width: 800px; margin: 0 auto; line-height: 1.6; font-family: Arial, sans-serif;">
    <h1 style="color: #004e92; text-align: center; margin-bottom: 20px;">Política de Protección de Datos Personales</h1>
    <p>En <strong>DataCom S.A.</strong> respetamos su privacidad y cumplimos estrictamente con la <strong>Ley Orgánica de Protección de Datos Personales (LOPDP)</strong> de Ecuador. Esta política describe cómo recolectamos, utilizamos, protegemos y compartimos sus datos personales.</p>
    <h2 style="color: #004e92; margin-top: 30px;">1. Principios del Tratamiento</h2>
    <p>Tratamos sus datos basándonos en los principios de <strong>licitud, transparencia, proporcionalidad y minimización</strong>. Solo recolectamos los datos estrictamente necesarios para la prestación de nuestros servicios y la atención al cliente.</p>
    <h2 style="color: #004e92; margin-top: 30px;">2. Derechos de los Titulares (Derechos ARCO)</h2>
    <p>Como titular de sus datos personales, usted tiene derecho a:</p>
    <ul>
        <li><strong>Acceso:</strong> Conocer qué datos suyos tratamos y con qué fin.</li>
        <li><strong>Rectificación:</strong> Actualizar o corregir datos inexactos.</li>
        <li><strong>Cancelación / Supresión:</strong> Solicitar la eliminación de sus datos cuando ya no sean necesarios para los fines recolectados.</li>
        <li><strong>Oposición y Limitación:</strong> Oponerse al tratamiento por motivos específicos y restringir su uso temporalmente.</li>
        <li><strong>Portabilidad:</strong> Recibir sus datos en un formato estructurado y transferirlos.</li>
    </ul>
    <h2 style="color: #004e92; margin-top: 30px;">3. Tiempos de Conservación</h2>
    <p>Los datos serán conservados únicamente durante el tiempo necesario para los fines del tratamiento o según los plazos que establezcan las leyes.</p>
    <h2 style="color: #004e92; margin-top: 30px;">4. Medidas de Seguridad</h2>
    <p>DataCom S.A. ha implementado medidas de seguridad técnicas y organizativas para garantizar la integridad y privacidad de su información.</p>
    <h2 style="color: #004e92; margin-top: 30px;">5. Contacto del Delegado de Protección de Datos (DPD)</h2>
    <p>Si tiene alguna duda sobre esta política o desea ejercer sus derechos, puede contactar a nuestro DPD:</p>
    <div style="background: #f9f9f9; padding: 15px; border-left: 4px solid #004e92;">
        <p><strong>DPD:</strong> Marco Logacho<br>
        <strong>Correo Electrónico:</strong> <a href="mailto:mlogacho@datacom.net.ec">mlogacho@datacom.net.ec</a><br>
        <strong>Teléfono:</strong> +593 999952644</p>
    </div>
</div>
EOD;
            $page_id = wp_insert_post( array(
                'post_title'    => 'Política de Protección de Datos (LOPDP)',
                'post_name'     => $page_slug,
                'post_content'  => $html_content,
                'post_status'   => 'publish',
                'post_author'   => 1,
                'post_type'     => 'page',
            ) );
            if ( ! is_wp_error( $page_id ) ) {
                update_post_meta( $page_id, '_wp_page_template', 'elementor_header_footer' );
            }
        }
        update_option( 'datacom_lopdp_page_created', true );
    }
}
add_action( 'init', 'datacom_auto_provision_lopdp_page' );
