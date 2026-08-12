<?php
/*
Plugin Name: LOPDP Global Footer and Cookie Banner
Description: Custom cookie banner, LOPDP footer, and logo fix for LOPDP/Cookies pages.
Version: 5.0
*/

// Fix the site logo size on pages that use the default theme header (LOPDP, Cookies)
add_action('wp_head', function() {
    // Only apply on pages with default theme header (not Elementor full pages)
    if (!is_page(['lopdp', 'cookies'])) return;
    ?>
    <style>
        /* --- Header / Logo fix --- */
        body.page-id-4775 .site-header,
        body.page-id-4771 .site-header {
            border-bottom: 1px solid #e0e0e0;
            overflow: visible !important;
            min-height: 80px;
        }
        body.page-id-4775 .site-branding,
        body.page-id-4771 .site-branding {
            display: flex !important;
            align-items: center !important;
            overflow: visible !important;
            padding: 10px 0;
        }
        body.page-id-4775 .site-logo,
        body.page-id-4771 .site-logo {
            display: block !important;
            overflow: visible !important;
            line-height: 0;
        }
        body.page-id-4775 .site-logo a,
        body.page-id-4771 .site-logo a { display: block; overflow: visible; }
        body.page-id-4775 .site-logo img.custom-logo,
        body.page-id-4771 .site-logo img.custom-logo {
            width: auto !important;
            height: 55px !important;
            max-width: none !important;
            display: block !important;
        }
        body.page-id-4775 .site-branding .site-title,
        body.page-id-4775 .site-branding .site-description,
        body.page-id-4771 .site-branding .site-title,
        body.page-id-4771 .site-branding .site-description { display: none !important; }

        /* --- Hide sidebar / widget area entirely --- */
        body.page-id-4775 #secondary,
        body.page-id-4771 #secondary,
        body.page-id-4775 .widget-area,
        body.page-id-4771 .widget-area { display: none !important; }

        /* Full-width content when sidebar is gone */
        body.page-id-4775 #primary,
        body.page-id-4771 #primary {
            width: 100% !important;
            max-width: 100% !important;
            float: none !important;
        }

        /* --- Fix font sizes (too large in TwentyNineteen) --- */
        body.page-id-4775 .entry-content h1,
        body.page-id-4771 .entry-content h1 {
            font-size: 1.75rem !important;
            line-height: 1.3 !important;
            text-align: center;
        }
        body.page-id-4775 .entry-content h2,
        body.page-id-4771 .entry-content h2 {
            font-size: 1.2rem !important;
            line-height: 1.4 !important;
        }
        body.page-id-4775 .entry-content h3,
        body.page-id-4771 .entry-content h3 { font-size: 1.05rem !important; }
        body.page-id-4775 .entry-content p,
        body.page-id-4775 .entry-content li,
        body.page-id-4771 .entry-content p,
        body.page-id-4771 .entry-content li {
            font-size: 0.95rem !important;
            line-height: 1.7 !important;
        }

        /* Hide the WordPress-generated page title (content has its own h1) */
        body.page-id-4775 .entry-header .entry-title,
        body.page-id-4771 .entry-header .entry-title { display: none !important; }

        /* --- Content container --- */
        body.page-id-4775 .entry-content,
        body.page-id-4771 .entry-content {
            max-width: 860px;
            margin: 0 auto;
            padding: 20px 30px;
        }
    </style>
    <?php
}, 5);

// Cookie banner and LOPDP footer on ALL pages
add_action('wp_footer', function() {
    if (is_admin()) return;
    ?>
    <style>
        /* Wrapper fixed at the bottom of the viewport */
        #ccb-fixed-wrapper {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 99999;
            width: 100%;
        }
        #custom-cookie-banner {
            display: none;
            width: 100%;
            background-color: #000000;
            color: #ffffff;
            padding: 14px 50px 14px 30px;
            font-family: Arial, sans-serif;
            font-size: 13px;
            position: relative;
            box-sizing: border-box;
        }
        #custom-cookie-banner.ccb-visible {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 15px;
        }
        #custom-cookie-banner p {
            margin: 0;
            flex: 1;
            min-width: 200px;
            line-height: 1.5;
        }
        #custom-cookie-banner a.ccb-link {
            color: #ffffff;
            text-decoration: underline;
        }
        .ccb-buttons {
            display: flex;
            gap: 10px;
            flex-shrink: 0;
        }
        .ccb-btn {
            background-color: #4dbfb0;
            color: #ffffff;
            border: none;
            padding: 8px 18px;
            cursor: pointer;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
        }
        .ccb-btn:hover { background-color: #3ca294; }
        .ccb-close {
            background: none;
            border: none;
            color: #aaaaaa;
            font-size: 22px;
            cursor: pointer;
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            line-height: 1;
            padding: 5px;
        }
        .ccb-close:hover { color: #ffffff; }
        .lopdp-bar {
            width: 100%;
            background-color: #f8f9fa;
            border-top: 1px solid #e0e0e0;
            padding: 10px 0;
            text-align: center;
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #555555;
            box-sizing: border-box;
            display: block;
        }
        .lopdp-bar a {
            color: #555555;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .lopdp-bar a:hover { text-decoration: underline; }
    </style>

    <div id="ccb-fixed-wrapper">
        <div id="custom-cookie-banner">
            <p>Utilizamos cookies para ofrecerte la mejor experiencia en nuestra web. Al continuar navegando, aceptas nuestra <a href="/lopdp/" class="ccb-link">Política de Cookies</a>.</p>
            <div class="ccb-buttons">
                <button class="ccb-btn" id="ccb-accept">Aceptar todas</button>
                <button class="ccb-btn" id="ccb-refuse">Rechazar no esenciales</button>
            </div>
            <button class="ccb-close" id="ccb-close" aria-label="Cerrar">&#215;</button>
        </div>

        <div class="lopdp-bar">
            <a href="/lopdp/">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 4l6 2.67V11c0 3.88-2.64 7.48-6 8.93C8.64 18.48 6 14.88 6 11V7.67L12 5zm-1 6v2h2v-2h-2zm0-4v3h2V7h-2z"/></svg>
                Política de Privacidad y Tratamiento de Datos Personales
            </a>
        </div>
    </div>

    <script>
    (function() {
        var COOKIE_NAME = "datacom_cookies_v2";
        var banner = document.getElementById("custom-cookie-banner");
        if (!banner) return;

        function getCookie(name) {
            var match = document.cookie.match(new RegExp("(^| )" + name + "=([^;]+)"));
            return match ? match[2] : null;
        }

        function setCookie(name, value, days) {
            var d = new Date();
            d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
            document.cookie = name + "=" + value + "; expires=" + d.toUTCString() + "; path=/; SameSite=Lax";
        }

        function dismiss() {
            banner.classList.remove("ccb-visible");
            banner.style.display = "none";
            setCookie(COOKIE_NAME, "1", 30);
        }

        if (!getCookie(COOKIE_NAME)) {
            banner.classList.add("ccb-visible");
        }

        document.getElementById("ccb-accept").addEventListener("click", dismiss);
        document.getElementById("ccb-refuse").addEventListener("click", dismiss);
        document.getElementById("ccb-close").addEventListener("click", dismiss);
    })();
    </script>
    <?php
}, 100);
