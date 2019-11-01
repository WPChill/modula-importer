<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$wp_core           = Modula_WP_Core_Gallery_Importer::get_instance();
$wp_core_galleries = $wp_core->get_galleries();
?>
<div class="row">
    <form id="modula_importer_wp_core_gallery" method="post">

        <table class="form-table">
            <tbody>
            <?php if (false != $wp_core_galleries) {
                $import_settings = get_option('modula_importer');
                ?>
                <!-- If NextGen gallery plugin is installed and active and there are galleries created -->
                <tr valign="top">
                    <th scope="row" valign="top">
                        <?php esc_html_e('Pages/Posts with WP Core Galleries', 'modula-importer'); ?>
                    </th>
                    <td>

                        <?php foreach ($wp_core_galleries as $wp_core_gallery) {
                            $imported = ((isset($import_settings['galleries']['wp-core']) && isset($import_settings['galleries']['wp-core'][$wp_core_gallery->ID])) ? true : false);
                            ?>

                            <div>
                                <label for="galleries-<?php echo esc_attr($wp_core_gallery->ID); ?>"
                                       data-id="<?php echo esc_attr($wp_core_gallery->ID); ?>"<?php echo($imported ? ' class="imported"' : ''); ?>>
                                    <input type="checkbox" name="gallery"
                                           id="galleries-<?php echo esc_attr($wp_core_gallery->ID); ?>"
                                           value="<?php echo esc_attr($wp_core_gallery->ID); ?>"/>
                                    <?php echo esc_html($wp_core_gallery->post_title); ?>
                                    <span style="color:blue;">
                                    <?php if ($imported) {
                                        esc_html_e('Imported', 'modula-importer');
                                    } ?>
                                </span>
                            </div>

                        <?php } ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" valign="top">
                        <?php esc_html_e('Convert and create gallery', 'modula-importer'); ?>
                    </th>
                    <td>
                        <div>
                            <?php submit_button(__('Convert and create gallery', 'modula-importer'), 'primary', 'modula-importer-submit', false); ?>
                        </div>
                    </td>
                </tr>
            <?php } else if (false == $wp_core_galleries) { ?>
                <!-- If NextGEN gallery plugin is installed and active but there are no galleries created -->
                <tr valign="top">
                    <th scope="row" valign="top">
                        <?php esc_html_e('There are no wp core galleries in pages or posts', 'modula-importer'); ?>
                    </th>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </form>
</div>