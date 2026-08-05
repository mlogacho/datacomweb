<?php
/*
Plugin Name: Cookie Notice Force Show
Description: Forces the Cookie Notice to show if JS fails.
*/

add_action('wp_footer', function() {
    ?>
    <script>
    setTimeout(function() {
        var cn = document.getElementById('cookie-notice');
        if (cn && cn.classList.contains('cookie-notice-hidden')) {
            // Check if cookie exists natively just in case
            if (document.cookie.indexOf('cookie_notice_accepted') === -1) {
                cn.classList.remove('cookie-notice-hidden');
                cn.classList.add('cookie-notice-visible');
            }
        }
    }, 1500);
    </script>
    <?php
}, 9999);
