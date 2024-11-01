<div>
    <h2><?= __('Word Loader Options', 'd-word-loader')?></h2>
    <?= __('In this page you can configure this plugin', 'd-word-loader')?>
    
    <form action="options.php" method="post">
        <?php settings_fields('word-loader-settings'); ?>
        <?php do_settings_sections('word-loader'); ?>

        <input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
    </form>
</div>