<?php
if ( ! defined( 'ABSPATH' ) ) {
    require_once('/var/www/html/wp-load.php');
}

$page_title = 'Política de Protección de Datos (LOPDP)';
$page_slug = 'lopdp';

$page = get_page_by_path($page_slug);
$page_id = $page ? $page->ID : 0;

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
    <p>Los datos serán conservados únicamente durante el tiempo necesario para los fines del tratamiento o según los plazos que establezcan las leyes, como el Código Tributario y normativas de telecomunicaciones (ARCOTEL).</p>

    <h2 style="color: #004e92; margin-top: 30px;">4. Medidas de Seguridad</h2>
    <p>DataCom S.A. ha implementado medidas de seguridad técnicas (cifrado, control de accesos MFA) y organizativas (auditorías, confidencialidad) para garantizar la integridad y privacidad de su información.</p>

    <h2 style="color: #004e92; margin-top: 30px;">5. Contacto del Delegado de Protección de Datos (DPD)</h2>
    <p>Si tiene alguna duda sobre esta política o desea ejercer sus derechos, puede contactar a nuestro DPD:</p>
    <div style="background: #f9f9f9; padding: 15px; border-left: 4px solid #004e92;">
        <p><strong>DPD:</strong> Marco Logacho<br>
        <strong>Correo Electrónico:</strong> <a href="mailto:mlogacho@datacom.net.ec">mlogacho@datacom.net.ec</a><br>
        <strong>Teléfono:</strong> +593 999952644</p>
    </div>
</div>
EOD;

$page_data = array(
    'post_title'    => $page_title,
    'post_name'     => $page_slug,
    'post_content'  => $html_content,
    'post_status'   => 'publish',
    'post_author'   => 1,
    'post_type'     => 'page',
);

if ( $page_id == 0 ) {
    $page_id = wp_insert_post( $page_data );
    echo "Page created successfully. ID: " . $page_id . "\\n";
} else {
    $page_data['ID'] = $page_id;
    wp_update_post( $page_data );
    echo "Page updated successfully. ID: " . $page_id . "\\n";
}
