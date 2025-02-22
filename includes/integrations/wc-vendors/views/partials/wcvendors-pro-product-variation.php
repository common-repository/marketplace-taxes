<?php

/**
 * A single product variation
 *
 * This file is used to load a single product variation for a product.
 *
 * Modified version of the core WC Vendors template file where the variation
 * ID is passed to all action hooks.
 *
 * @link       http://www.wcvendors.com
 * @since      1.3.0
 * @version    1.5.4
 *
 * @package    WCVendors_Pro
 * @subpackage WCVendors_Pro/public/partials
 */

extract( $variation_data );

// Fix false data added to db
$_download_expiry = ( $_download_expiry == -1 ) ? '' : $_download_expiry;
$_download_limit  = ( $_download_limit == -1 ) ? '' : $_download_limit;

$variations_options = (array) get_option( 'wcvendors_hide_product_variations' );

?>

<div class="wcv_variation wcv-metabox closed all-100" rel="<?php echo esc_attr( $variation_id ); ?>"
     data-loop="<?php echo $loop; ?>">
    <div class="wcv-cols-group wcv_variation_inner">
        <div class="all-100">
            <h5 class="variation_title">
                <span class="wcv-sort"><i class="wcv-icon wcv-icon-sort"></i></span>
                <strong>#<?php echo esc_html( $variation_data['id'] ); ?> : </strong>
                <span class="variations_wrapper">
				<?php

                $attributes = WCVendors_Pro_Utils::array_sort( $attributes, 'position' );

                foreach ( $attributes as $key => $attribute ) {
                    // Get current value for variation (if set)
                    $variation_selected_value = isset(
                        $variation_data[ 'attribute_' . sanitize_title(
                            $attribute['name']
                        ) ]
                    ) ? $variation_data[ 'attribute_' . sanitize_title( $attribute['name'] ) ] : '';

                    if ( array_key_exists( 'is_variation', $attribute ) && $attribute['is_variation'] == 0 ) {
                        continue;
                    }

                    // Name will be something like attribute_pa_color
                    printf(
                        '<select data-taxonomy="%1$s" class="variation_attribute %2$s" name="attribute_%2$s[%3$d]">',
                        sanitize_title( $attribute['name'] ),
                        sanitize_title( $key ),
                        $loop
                    );

                    printf(
                        '<option value="">%s %s&hellip;</option>',
                        __( 'Any', 'wcvendors-pro' ),
                        esc_html( wc_attribute_label( $key ) )
                    );

                    if ( array_key_exists( 'values', $attribute ) ) {

                        foreach ( $attribute['values'] as $key => $value ) {
                            printf(
                                '<option value="%s" %s>%s</option>',
                                esc_attr( $key ),
                                selected( $variation_selected_value, $key, false ),
                                esc_html( $value )
                            );
                        }

                    } else {

                        $post_terms = wp_get_post_terms( $parent_data['id'], $attribute['name'] );

                        foreach ( $post_terms as $term ) {
                            printf(
                                '<option value="%s" %s>%s</option>',
                                esc_attr( $term->slug ),
                                selected( $variation_selected_value, $term->slug, false ),
                                esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name ) )
                            );
                        }

                    }

                    echo '</select>';
                }
                ?>
				</span>
                <span><i class="wcv-icon wcv-icon-caret-down" aria-hidden="true"></i></span>
                <a href="#" class="remove_variation delete" rel="<?php echo esc_attr( $variation_id ); ?>"
                   data-loop="<?php echo $loop; ?>"><?php _e( 'Remove', 'wcvendors-pro' ); ?></a>
                <input type="hidden" name="variable_post_id[<?php echo $loop; ?>]"
                       value="<?php echo esc_attr( $variation_id ); ?>">
                <input type="hidden" class="variation_menu_order" name="variation_menu_order[<?php echo $loop; ?>]"
                       value="<?php echo isset( $menu_order ) ? absint( $menu_order ) : 0; ?>">
            </h5>
        </div>
    </div>
    <div class="wcv_variable_attributes wcv-metabox-content" style="display: none;">

        <?php do_action( 'wcv_product_variation_before_general', $variation_id, $loop ); ?>

        <div class="wcv-cols-group wcv-horizontal-gutters">
            <div class="all-50 upload_image">
                <?php if ( 'yes' != get_option( 'wcvendors_hide_product_variations_featured' ) )  : ?>
                    <a href="#" class="upload_image_button <?php if ( $_thumbnail_id > 0 ) {
                        echo 'wcv_remove';
                    } ?>" rel="<?php echo esc_attr( $variation_id ); ?>">
                        <img class="wc_placeholder_img" src="<?php if ( ! empty( $image ) ) {
                            echo esc_attr( $image );
                        } else {
                            echo esc_attr( wc_placeholder_img_src() );
                        } ?>">
                        <input type="hidden" name="upload_image_id[<?php echo $loop; ?>]" class="upload_image_id"
                               value="<?php echo esc_attr( $_thumbnail_id ); ?>">
                    </a>
                <?php endif; ?>
            </div>
            <div class="all-50 sku">
                <?php if ( 'yes' != get_option( 'wcvendors_hide_product_variations_sku' ) )  : ?>
                    <?php if ( wc_product_sku_enabled() )  : ?>
                        <div class="control-group">
                        <label><?php _e( 'SKU', 'wcvendors-pro' ); ?>: </label>
                        <div class="control">
                            <input type="text" name="variable_sku[<?php echo $loop; ?>]"
                                   value="<?php if ( isset( $_sku ) ) {
                                       echo esc_attr( $_sku );
                                   } ?>" placeholder="<?php echo esc_attr( $parent_data['sku'] ); ?>">
                        </div>
                    <?php else : ?>
                        <input type="hidden" name="variable_sku[<?php echo $loop; ?>]"
                               value="<?php if ( isset( $_sku ) ) {
                                   echo esc_attr( $_sku );
                               } ?>">
                    <?php endif; ?>
                    </div>
                <?php else : ?>
                    <input type="hidden" name="variable_sku[<?php echo $loop; ?>]" value="<?php if ( isset( $_sku ) ) {
                        echo esc_attr( $_sku );
                    } ?>">
                <?php endif; ?>
            </div>
        </div>

        <?php do_action( 'wcv_product_variation_after_general', $variation_id, $loop ); ?>

        <hr style="clear: both;">

        <?php do_action( 'wcv_product_variation_before_options', $variation_id, $loop ); ?>

        <!-- Variable options  -->
        <div class="wcv-cols-group wcv-horizontal-gutters">
            <div class="all-100">

                <div class="wcv-column-group">
                    <div class="all-25">
                        <?php if ( 'yes' != get_option( 'wcvendors_hide_product_variations_enabled' ) )  : ?>
                            <input type="checkbox" class="checkbox variable_enabled"
                                   name="variable_enabled[<?php echo $loop; ?>]" <?php checked( $_enabled ); ?> >
                            <label><?php _e( 'Enabled', 'wcvendors-pro' ); ?></label>
                        <?php endif; ?>
                    </div>
                    <div class="all-25">
                        <?php if ( 'yes' != get_option( 'wcvendors_hide_product_variations_downloadable' ) )  : ?>
                            <input type="checkbox" class="checkbox variable_is_downloadable"
                                   name="variable_is_downloadable[<?php echo $loop; ?>]" <?php checked(
                                isset( $_downloadable ) ? $_downloadable : '',
                                'yes'
                            ); ?> >
                            <label><?php _e( 'Downloadable', 'wcvendors-pro' ); ?> </label>
                        <?php endif; ?>
                    </div>
                    <div class="all-25">
                        <?php if ( 'yes' != get_option( 'wcvendors_hide_product_variations_virtual' ) )  : ?>
                            <input type="checkbox" class="checkbox variable_is_virtual"
                                   name="variable_is_virtual[<?php echo $loop; ?>]" <?php checked(
                                isset( $_virtual ) ? $_virtual : '',
                                'yes'
                            ); ?> >
                            <label><?php _e( 'Virtual', 'wcvendors-pro' ); ?></label>
                        <?php endif; ?>
                    </div>
                    <?php if ( 'yes' != get_option( 'wcvendors_hide_product_variations_manage_stock' ) )  : ?>
                        <?php if ( get_option( 'woocommerce_manage_stock' ) == 'yes' ) : ?>
                            <div class="all-25">
                                <input type="checkbox" class="checkbox variable_manage_stock"
                                       name="variable_manage_stock[<?php echo $loop; ?>]" <?php checked(
                                    isset( $_manage_stock ) ? $_manage_stock : '',
                                    'yes'
                                ); ?> >
                                <label><?php _e( 'Manage stock?', 'wcvendors-pro' ); ?></label>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <?php do_action( 'wcv_product_variation_after_options', $variation_id, $loop ); ?>

        <hr style="clear: both;">

        <?php do_action( 'wcv_product_variation_before_pricing', $variation_id, $loop ); ?>

        <!-- Variable pricing  -->
        <div class="wcv-cols-group wcv-horizontal-gutters variable_pricing">
            <?php if ( 'yes' != get_option( 'wcvendors_hide_product_variations_price' ) )  : ?>
                <div class="all-50">
                    <div class="control-group">
                        <label><?php echo __(
                                              'Regular Price:',
                                              'wcvendors-pro'
                                          ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?></label>
                        <div class="control">
                            <input type="text" size="5" name="variable_regular_price[<?php echo $loop; ?>]"
                                   value="<?php if ( isset( $_regular_price ) ) {
                                       echo esc_attr( $_regular_price );
                                   } ?>" class="wc_input_price variable_regular_price"
                                   placeholder="<?php esc_attr_e( 'Variation price (required)', 'wcvendors-pro' ); ?>">
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <div class="all-50">
                <?php if ( 'yes' != get_option( 'wcvendors_hide_product_variations_sale_price' ) )  : ?>
                    <div class="control-group">
                        <label><?php echo __( 'Sale Price:', 'wcvendors-pro' ) . ' (' . get_woocommerce_currency_symbol(
                                ) . ')'; ?></label>
                        <div class="control">
                            <input type="text" size="5" name="variable_sale_price[<?php echo $loop; ?>]"
                                   class="variable_sale_price" value="<?php if ( isset( $_sale_price ) ) {
                                echo esc_attr( $_sale_price );
                            } ?>" class="wc_input_price">
                        </div>
                        <p clas="tip"><a href="#" class="sale_schedule"><?php _e( 'Schedule', 'wcvendors-pro' ); ?></a>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="wcv-cols-group wcv-horizontal-gutters sale_price_dates_fields" style="display:none;">
            <div class="all-50">
                <div class="control-group">
                    <label><?php _e( 'Sale start date:', 'wcvendors-pro' ); ?></label>
                    <div class="control">
                        <input type="text" class="sale_price_dates_from wcv-datepicker"
                               name="variable_sale_price_dates_from[<?php echo $loop; ?>]"
                               value="<?php echo ! empty( $_sale_price_dates_from ) ? date_i18n(
                                   'Y-m-d',
                                   $_sale_price_dates_from
                               ) : ''; ?>" placeholder="<?php echo esc_attr_x(
                            'From&hellip;',
                            'placeholder',
                            'wcvendors-pro'
                        ) ?> YYYY-MM-DD" maxlength="10"
                               pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])">
                    </div>
                </div>
            </div>

            <div class="all-50">
                <div class="control-group">
                    <label><?php _e( 'Sale end date:', 'wcvendors-pro' ); ?></label>
                    <div class="control">
                        <input type="text" class="sale_price_dates_to wcv-datepicker"
                               name="variable_sale_price_dates_to[<?php echo $loop; ?>]"
                               value="<?php echo ! empty( $_sale_price_dates_to ) ? date_i18n(
                                   'Y-m-d',
                                   $_sale_price_dates_to
                               ) : ''; ?>" placeholder="<?php echo esc_attr_x(
                            'To&hellip;',
                            'placeholder',
                            'wcvendors-pro'
                        ) ?> YYYY-MM-DD" maxlength="10"
                               pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])">
                    </div>
                    <p class="tip">
                        <a href="#" class="cancel_sale_schedule" style="display:none">
                            <?php _e( 'Cancel schedule', 'wcvendors-pro' ); ?>
                        </a>
                    </p>
                </div>
            </div>
        </div>

        <?php do_action( 'wcv_product_variation_after_pricing', $variation_id, $loop ); ?>

        <?php do_action( 'wcv_product_variation_before_stock', $variation_id, $loop ); ?>

        <?php if ( 'yes' == get_option( 'woocommerce_manage_stock' ) ) : ?>
            <div class="wcv-cols-group wcv-horizontal-gutters show_if_variation_manage_stock" style="display: none;">
                <div class="all-50">
                    <?php if ( 'yes' != get_option( 'wcvendors_hide_product_variations_stock_qty' ) )  : ?>
                        <div class="control-group">
                            <label><?php _e( 'Stock Qty:', 'wcvendors-pro' ); ?></label>
                            <div class="control">
                                <input type="number" size="5" name="variable_stock[<?php echo $loop; ?>]"
                                       class="variable_stock" value="<?php if ( isset( $_stock ) ) {
                                    echo esc_attr( wc_stock_amount( $_stock ) );
                                } ?>" step="any">
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="all-50">
                    <?php if ( 'yes' != get_option( 'wcvendors_hide_product_variations_allow_backorders' ) )  : ?>
                        <div class="control-group">
                            <label><?php _e( 'Allow Backorders?', 'wcvendors-pro' ); ?></label>
                            <div class="control">
                                <select name="variable_backorders[<?php echo $loop; ?>]">
                                    <?php
                                    foreach ( $parent_data['backorder_options'] as $key => $value ) {
                                        printf(
                                            '<option value="%s" %s>%s</option>',
                                            esc_attr( $key ),
                                            selected( $key === $_backorders, true, false ),
                                            esc_html( $value )
                                        );
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="wcv-cols-group wcv-horizontal-gutters show_if_variation_manage_stock" style="display: none;">
            <div class="all-100">
                <?php if ( 'yes' != get_option( 'wcvendors_hide_product_variations_stock_status' ) )  : ?>
                    <div class="control-group">
                        <label><?php _e( 'Stock status', 'wcvendors-pro' ); ?></label>
                        <div class="control">
                            <select name="variable_stock_status[<?php echo $loop; ?>]" style="width:100%">
                                <?php
                                foreach ( $parent_data['stock_status_options'] as $key => $value ) {
                                    printf(
                                        '<option value="%s" %s>%s</option>',
                                        esc_attr( $key === $_stock_status ? '' : $key ),
                                        selected( $key === $_stock_status, true, false ),
                                        esc_html( $value )
                                    );
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php do_action( 'wcv_product_variation_after_stock', $variation_id, $loop ); ?>

        <?php do_action( 'wcv_product_variation_before_weight_dimensions', $variation_id, $loop ); ?>

        <?php if ( wc_product_weight_enabled() || wc_product_dimensions_enabled() ) : ?>
            <div class="wcv-cols-group wcv-horizontal-gutters">
                <?php if ( 'yes' != get_option( 'wcvendors_hide_product_variations_weight' ) )  : ?>
                    <?php if ( wc_product_weight_enabled() ) : ?>
                        <div class="all-50 hide_if_variation_virtual">
                            <div class="control-group">
                                <label>
                                    <?php printf(
                                        '%s (%s)',
                                        __( 'Weight', 'wcvendors-pro' ),
                                        esc_html( get_option( 'woocommerce_weight_unit' ) )
                                    ); ?>
                                </label>
                                <div class="control">
                                    <input type="text" size="5" name="variable_weight[<?php echo $loop; ?>]"
                                           class="variable_weight wc_input_decimal"
                                           value="<?php if ( isset( $_weight ) ) {
                                               echo esc_attr( $_weight );
                                           } ?>" placeholder="<?php echo esc_attr( $parent_data['weight'] ); ?>">
                                </div>
                            </div>
                        </div>
                    <?php else : ?>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ( 'yes' != get_option( 'wcvendors_hide_product_variations_dimensions' ) )  : ?>
                    <?php if ( wc_product_dimensions_enabled() ) : ?>
                        <div class="all-50 dimensions_field hide_if_variation_virtual">
                            <div class="control-group">
                                <label for="product_length">
                                    <?php printf(
                                        '%s (%s)',
                                        __( 'Dimensions (L&times;W&times;H)', 'wcvendors-pro' ),
                                        esc_html( get_option( 'woocommerce_dimension_unit' ) )
                                    ); ?>
                                </label>
                                <div class="wcv-cols-group wcv-horizontal-gutters">
                                    <div class="all-33">
                                        <div class="control">
                                            <input id="product_length" class="variable_length" type="text"
                                                   name="variable_length[<?php echo $loop; ?>]"
                                                   value="<?php if ( isset( $_length ) ) {
                                                       echo esc_attr( $_length );
                                                   } ?>"
                                                   placeholder="<?php echo esc_attr( $parent_data['length'] ); ?>">
                                        </div>
                                    </div>
                                    <div class="all-33">
                                        <div class="control">
                                            <input class="variable_width" type="text"
                                                   name="variable_width[<?php echo $loop; ?>]"
                                                   value="<?php if ( isset( $_width ) ) {
                                                       echo esc_attr( $_width );
                                                   } ?>"
                                                   placeholder="<?php echo esc_attr( $parent_data['width'] ); ?>">
                                        </div>
                                    </div>
                                    <div class="all-33">
                                        <div class="control">
                                            <input class="variable_height" type="text"
                                                   name="variable_height[<?php echo $loop; ?>]"
                                                   value="<?php if ( isset( $_height ) ) {
                                                       echo esc_attr( $_height );
                                                   } ?>"
                                                   placeholder="<?php echo esc_attr( $parent_data['height'] ); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else : ?>
                        <!-- <p>&nbsp;</p> -->
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php do_action( 'wcv_product_variation_after_weight_dimensions', $variation_id, $loop ); ?>

        <?php do_action( 'wcv_product_variation_before_shipping_class', $variation_id, $loop ); ?>

        <div class="wcv-cols-group wcv-horizontal-gutters hide_if_variation_virtual">
            <div class="all-100">
                <?php if ( 'yes' != get_option( 'wcvendors_hide_product_variations_shipping_class' ) )  : ?>
                    <div class="control-group">
                        <label><?php _e( 'Shipping class:', 'wcvendors-pro' ); ?></label>
                        <div class="control">
                            <?php
                            $args = array(
                                'taxonomy'         => 'product_shipping_class',
                                'hide_empty'       => 0,
                                'show_option_none' => __( 'Same as parent', 'wcvendors-pro' ),
                                'name'             => 'variable_shipping_class[' . $loop . ']',
                                'id'               => '',
                                'selected'         => isset( $shipping_class ) ? esc_attr( $shipping_class ) : '',
                                'echo'             => 0
                            );

                            echo wp_dropdown_categories( $args ); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <?php do_action( 'wcv_product_variation_after_shipping_class', $variation_id, $loop ); ?>

        <?php do_action( 'wcv_product_variation_before_tax_class', $variation_id, $loop ); ?>

        <?php if ( wc_tax_enabled() ) : ?>
            <div class="wcv-cols-group wcv-horizontal-gutters">
                <div class="all-100">
                    <?php if ( 'yes' != get_option( 'wcvendors_hide_product_variations_tax_class' ) )  : ?>
                        <div class="control-group">
                            <label><?php _e( 'Tax class:', 'wcvendors-pro' ); ?></label>
                            <div class="control">
                                <select name="variable_tax_class[<?php echo $loop; ?>]">
                                    <option value="parent" <?php selected( is_null( $_tax_class ), true ); ?>>
                                        <?php _e( 'Same as parent', 'wcvendors-pro' ); ?>
                                    </option>
                                    <?php
                                    foreach ( $parent_data['tax_class_options'] as $key => $value ) {
                                        printf(
                                            '<option value="%s" %s>%s</option>',
                                            esc_attr( $key ),
                                            selected( $key === $_tax_class, true, false ),
                                            esc_html( $value )
                                        );
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php do_action( 'wcv_product_variation_after_tax_class', $variation_id, $loop ); ?>

        <?php do_action( 'wcv_product_variation_before_shipping_class', $variation_id, $loop ); ?>

        <div class="wcv-cols-group wcv-horizontal-gutters">
            <div class="all-100">
                <?php if ( 'yes' != get_option( 'wcvendors_hide_product_variations_tax_class' ) )  : ?>
                    <div class="control-group">
                        <label><?php _e( 'Variation Description:', 'wcvendors-pro' ); ?></label>
                        <div class="control">
                            <?php
                            $description = '';

                            if ( isset( $variation_data['_variation_description'] ) ) {
                                $description = esc_textarea( $variation_data['_variation_description'] );
                            }

                            printf(
                                '<textarea name="variable_description[%d]" rows="3" style="width: 100%;">%s</textarea>',
                                $loop,
                                $description
                            );
                            ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php do_action( 'wcv_product_variation_after_shipping_class', $variation_id, $loop ); ?>

        <?php do_action( 'wcv_product_variation_before_download_files', $variation_id, $loop ); ?>

        <div class="wcv-cols-group wcv-horizontal-gutters show_if_variation_downloadable" style="display: none;">
            <div class="all-100">
                <?php if ( 'yes' != get_option( 'wcvendors_hide_product_variations_download_files' ) )  : ?>
                    <div class="control-group downloadable_files">
                        <label><?php _e( 'Downloadable Files', 'wcvendors-pro' ); ?>:</label>
                        <div class="control">
                            <table>
                                <thead>
                                <div>
                                    <th>
                                        <?php _e( 'Name', 'wcvendors-pro' ); ?>
                                    </th>
                                    <th colspan="2">
                                        <?php _e( 'File URL', 'wcvendors-pro' ); ?>
                                    </th>
                                    <th>&nbsp;</th>
                                </div>
                                </thead>
                                <tbody>
                                <?php
                                if ( $_downloadable_files ) {

                                    $file_display_type = get_option( 'wcvendors_file_display' );

                                    foreach ( $_downloadable_files as $key => $file ) {

                                        $file_id      = WCVendors_Pro::get_attachment_id( $key );
                                        $file_display = ( $file_display_type == 'file_url' ) ? $file['file'] : basename(
                                            $file['file']
                                        );


                                        if ( ! is_array( $file ) ) {
                                            $file = array(
                                                'file' => $file,
                                                'name' => '',
                                            );
                                        }

                                        include( 'wcvendors-pro-product-variation-download.php' );
                                    }
                                }
                                ?>
                                </tbody>
                                <tfoot>
                                <div>
                                    <th colspan="4">
                                        <a href="#" class="button insert" data-row="<?php
                                        $file         = array(
                                            'file' => '',
                                            'name' => ''
                                        );
                                        $file_id      = '';
                                        $file_display = '';
                                        ob_start();
                                        include( 'wcvendors-pro-product-variation-download.php' );
                                        echo esc_attr( ob_get_clean() );
                                        ?>"><?php _e( 'Add File', 'wcvendors-pro' ); ?></a>
                                    </th>
                                </div>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="wcv-cols-group wcv-horizontal-gutters show_if_variation_downloadable" style="display: none;">
            <?php if ( 'yes' != get_option( 'wcvendors_hide_product_variations_download_files' ) )  : ?>
                <div class="all-50">
                    <?php if ( 'yes' != get_option( 'wcvendors_hide_product_variations_download_limit' ) )  : ?>
                        <div class="control-group">
                            <label><?php _e( 'Download Limit:', 'wcvendors-pro' ); ?></label>
                            <div class="control">
                                <input type="number" size="5" name="variable_download_limit[<?php echo $loop; ?>]"
                                       class="variable_download_limit" value="<?php if ( isset( $_download_limit ) ) {
                                    echo esc_attr( $_download_limit );
                                } ?>" placeholder="<?php esc_attr_e( 'Unlimited', 'wcvendors-pro' ); ?>" step="1"
                                       min="0">
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="all-50">
                    <?php if ( 'yes' != get_option( 'wcvendors_hide_product_variations_download_expiry' ) )  : ?>
                        <div class="control-group">
                            <label><?php _e( 'Download Expiry:', 'wcvendors-pro' ); ?></label>
                            <div class="control">
                                <input type="number" size="5" name="variable_download_expiry[<?php echo $loop; ?>]"
                                       class="variable_download_expiry" value="<?php if ( isset( $_download_expiry ) ) {
                                    echo esc_attr( $_download_expiry );
                                } ?>" placeholder="<?php esc_attr_e( 'Unlimited', 'wcvendors-pro' ); ?>" step="1"
                                       min="0">
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php do_action( 'wcv_product_variation_after_download_files', $variation_id, $loop ); ?>
    </div>
</div>
