<div id="cashbene__main" data-page="<?php echo esc_attr($displayPage); ?>" data-plugin-version="<?php echo esc_attr($cashbenePluginVersion) ?>">
    <button id="cashbene__main-button">
        <?php if($displayPage === 'product') : ?>
            Szybki zakup z cashback <span id="cashbene__main-button-span"><?php echo esc_attr($cashbackValue); ?>%</span>
        <?php elseif ($displayPage === 'cart'): ?>
            Idź do kasy <span id="cashbene__main-button-span">(<?php echo esc_attr($cashbackValue); ?>% cashback)</span>
        <?php endif; ?>
    </button>
    <p>Nasz sklep daje Ci <span class="cashbene__main-text-span"><?php echo esc_attr($cashbackValue); ?>%&nbsp;zwrotu&nbsp;na&nbsp;konto</span><br>za zakup powyższą metodą.</p>
    <div id="cashbene__snackbar"></div>
</div>
