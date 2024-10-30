<div class="wrap">
    <h1><?php esc_html_e('Cashbene Gateway Settings', 'cashbene_gateway_plugin') ?></h1>
    <form method="post" action="options.php">
        <?php
        // This prints out all hidden setting fields
        settings_fields('cashbene_gateway_plugin_settings_group');
        do_settings_sections('cashbene-gateway-admin');
        submit_button();
        ?>
    </form>
</div>
