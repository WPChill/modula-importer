<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$nextgen           = Modula_Nextgen_Importer::get_instance();
$nextgen_galleries = $nextgen->get_galleries();
?>
<div class="row">
    <form id="modula_importer_nextgen" method="post">

        <table class="form-table">
            <tbody>
            <?php if ('inactive' != $nextgen_galleries && false != $nextgen_galleries) {
                $import_settings = get_option('modula_importer');
                ?>
                <!-- If NextGen gallery plugin is installed and active and there are galleries created -->
                <tr valign="top">
                    <th scope="row" valign="top">
                        <?php esc_html_e('NextGEN galleries', 'modula-importer'); ?>
                    </th>
                    <td>

                        <?php foreach ($nextgen_galleries as $ng_gallery) {
                            $imported = ((isset($import_settings['galleries']) && isset($import_settings['galleries'][$ng_gallery->gid])) ? true : false);
                            ?>

                            <div>
                                <label for="galleries-<?php echo esc_attr($ng_gallery->gid); ?>"
                                       data-id="<?php echo esc_attr($ng_gallery->gid); ?>"<?php echo($imported ? ' class="imported"' : ''); ?>>
                                    <input type="checkbox" name="gallery"
                                           id="galleries-<?php echo esc_attr($ng_gallery->gid); ?>"
                                           value="<?php echo esc_attr($ng_gallery->gid); ?>"/>
                                    <?php echo esc_html($ng_gallery->title); ?>
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
                        <?php esc_html_e('Import NextGEN galleries', 'modula-importer'); ?>
                    </th>
                    <td>
                        <div>
                            <?php submit_button(__('Import Galleries', 'modula-importer'), 'primary', 'modula-importer-submit', false); ?>
                        </div>
                    </td>
                </tr>
            <?php } else if (false == $nextgen_galleries) { ?>
                <!-- If NextGEN gallery plugin is installed and active but there are no galleries created -->
                <tr valign="top">
                    <th scope="row" valign="top">
                        <?php esc_html_e('There are no NextGEN galleries', 'modula-importer'); ?>
                    </th>
                </tr>
            <?php } else { ?>
                <!-- If NextGEN gallery plugin is not installed or is inactive -->
                <tr valign="top">
                    <th scope="row" valign="top">
                        <?php esc_html_e('NextGEN plugin is not active', 'modula-importer'); ?>
                    </th>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </form>
</div>