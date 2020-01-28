<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Assume everything is false.
$sources   = false;
$galleries = false;

$migrate = isset($_GET['migration']) ? $_GET['migration'] : false;
$delete  = isset($_GET['delete']) ? $_GET['delete'] : false;

$modula_importer = Modula_Importer::get_instance();
$sources         = $modula_importer->get_cources();

$sources = apply_filters('modula_importable_galleries', $sources);
?>

    <div class="row">
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row" valign="top">
                    <?php esc_html_e('Gallery source', 'modula-importer'); ?>
                    <div class="tab-header-tooltip-container modula-tooltip"><span>[?]</span>
                        <div class="tab-header-description modula-tooltip-content">
                            <?php esc_html_e('Select from which source would you like to migrate the gallery.', 'modula-importer') ?>
                            <?php esc_html_e('Migrating galleries will also replace the shortcode of the gallery with the new Modula shortcode in pages and posts.', 'modula-importer') ?>
                        </div>
                    </div>
                </th>
                <td>
                    <select name="modula_select_gallery_source" id="modula_select_gallery_source">
                        <option value="none"><?php echo (count($sources) > 0) ? esc_html('Select gallery source', 'modula-importer') : esc_html('No galleries detected', 'modula-importer'); ?></option>
                        <?php
                        foreach ($sources as $source => $label) {
                            echo '<option value="' . $source . '"> ' . $label . '</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <!-- Select all checkbox-->
    <div class="row select-all-wrapper hide">
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row" valign="top">
                    <?php echo esc_html__('Gallery database entries.', 'modula-importer'); ?>
                    <div class="tab-header-tooltip-container modula-tooltip"><span>[?]</span>
                        <div class="tab-header-description modula-tooltip-content">
                            <?php esc_html_e('Check this if you want to delete remnants or data entries in the database from the migrated galleries.', 'modula-importer') ?>
                        </div>
                    </div>
                </th>
                <td>
                    <div>
                        <label for="delete-old-entries"
                               data-id="delete-old-entries">
                            <input type="checkbox" name="delete-old-entries"
                                   id="delete-old-entries"
                                   value=""/>
                            <?php echo esc_html__('Delete old gallery entries.', 'modula-importer'); ?>
                        </label>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="update-complete">
        <?php
        if ($migrate && !$delete) {
            echo '<h3>' . esc_html__('All done, good job! All galleries have been migrated.', 'modula-importer') . '</h3>';
        }

        if ($migrate && $delete) {
            echo '<h3>' . esc_html__('All done, good job! All galleries have been migrated and old entries have been deleted.', 'modula-importer') . '</h3>';
        }

        ?>
    </div>
<?php
foreach ($sources as $source=>$label) {
    ?>

    <div id="modula-<?php echo esc_attr($source); ?>-importer" class="row modula-importer-row hide">
        <form id="modula_importer_<?php echo esc_attr($source); ?>" method="post">
            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row" valign="top">
                        <?php echo esc_html($label) . esc_html__(' galleries', 'modula-importer'); ?>
                    </th>
                    <td>
                        <div class="modula-importer-checkbox-wrapper">
                            <label for="select-galleries-<?php echo esc_attr($source); ?>"
                                   data-id="select-all-<?php echo esc_attr($source); ?>">
                                <input type="checkbox" name="select-all-<?php echo esc_attr($source); ?>"
                                       id="select-all-<?php echo esc_attr($source); ?>"
                                       value="" class="select-all-checkbox"/>
                                <?php printf(esc_html__('Select all %s galleries.', 'modula-importer'), $label); ?>
                            </label>
                        </div>
                        <div class="modula-found-galleries"></div>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" valign="top">
                    </th>
                    <td>
                        <div>
                            <?php submit_button(__('Migrate', 'modula-importer'), 'primary', 'modula-importer-submit-' . $source, false); ?>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </form>
    </div>
    <?php
}
?>