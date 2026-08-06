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
        </style>
    </div>
    <?php
}
add_action( 'wp_footer', 'datacom_lopdp_global_footer', 100 );
