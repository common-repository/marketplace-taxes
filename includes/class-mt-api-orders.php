<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( version_compare( WC()->version, '3.7.0', '>=' ) ) {
    require_once WC()->plugin_path() . '/includes/legacy/api/v2/class-wc-api-orders.php';
} else {
    require_once WC()->plugin_path() . '/includes/api/legacy/v2/class-wc-api-orders.php';
}

/**
 * WC REST API Orders Controller.
 *
 * Custom implementation of the WC_API_Orders class that returns shop orders
 * or vendor shop orders based on the authenticated user's role.
 */
class MT_API_Orders extends WC_API_Orders {

    /**
     * @var bool Is the authenticated user a vendor?
     */
    protected $is_user_vendor = false;

    /**
     * Setup class
     *
     * @param WC_API_Server $server
     *
     * @since 2.1
     */
    public function __construct( $server ) {
        add_filter( 'woocommerce_api_check_authentication', array( $this, 'check_user_role' ) );

        parent::__construct( $server );
    }

    /**
     * Checks the user role of the authenticated user.
     *
     * @param WP_User $user
     *
     * @return WP_User
     */
    public function check_user_role( $user ) {
        if ( is_wp_error( $user ) ) {
            return $user;
        }

        $this->is_user_vendor = MT()->integration->is_vendor( $user->ID );

        // Operate on vendor sub orders if the current user is a vendor
        if ( $this->is_user_vendor ) {
            $this->post_type = apply_filters( 'mt_vendor_order_post_type', 'shop_order' );

            add_filter( 'posts_clauses', array( $this, 'filter_query_clauses' ), 10, 2 );
        }

        add_action( 'pre_get_posts', array( $this, 'filter_order_query' ) );

        return $user;
    }

    /**
     * Gets the consumer key provided in the request.
     *
     * @return string
     */
    protected function get_consumer_key() {
        $params = WC()->api->server->params['GET'];

        if ( ! empty( $_SERVER['PHP_AUTH_USER'] ) ) {
            return $_SERVER['PHP_AUTH_USER'];
        } elseif ( ! empty( $params['consumer_key'] ) ) {
            return $params['consumer_key'];
        } elseif ( ! empty( $params['oauth_consumer_key'] ) ) {
            return $params['oauth_consumer_key'];
        }

        return '';
    }

    /**
     * Checks whether the current API request is coming from TaxJar.
     *
     * @return bool
     */
    protected function is_taxjar_request() {
        global $wpdb;

        $description = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT description FROM {$wpdb->prefix}woocommerce_api_keys WHERE consumer_key = %s",
                wc_api_hash( $this->get_consumer_key() )
            )
        );

        return 'TaxJar' === $description;
    }

    /**
     * Filters the order query to ensure that vendors only see their own orders.
     *
     * @param WP_Query $query
     */
    public function filter_order_query( &$query ) {
        if ( $this->is_user_vendor ) {
            $meta_query = $query->get( 'meta_query' );

            if ( ! is_array( $meta_query ) ) {
                $meta_query = [];
            }

            $meta_query[] = [
                'key'   => apply_filters( 'mt_vendor_order_vendor_key', '_vendor_id' ),
                'value' => get_current_user_id(),
            ];

            $query->set( 'meta_query', $meta_query );
        } else {
            $query->set( 'post_parent', 0 );
        }

        if ( $this->is_user_vendor ) {
            do_action( 'mt_pre_get_vendor_orders', $query );
        }
    }

    /**
     * Allows integrations to filter the vendor order query clauses.
     *
     * @param array    $clauses
     * @param WP_Query $query
     *
     * @return array
     */
    public function filter_query_clauses( $clauses, $query ) {
        return apply_filters( 'mt_vendor_order_clauses', $clauses, $query );
    }

    /**
     * Get all orders
     *
     * @param string $fields
     * @param array  $filter
     * @param string $status
     * @param int    $page
     *
     * @return array
     * @since 2.1
     *
     */
    public function get_orders( $fields = null, $filter = array(), $status = null, $page = 1 ) {
        if ( $this->is_taxjar_request() ) {
            $merchant_of_record = MT()->settings->get( 'merchant_of_record', 'vendor' );

            // Force empty response if the current user is not the Merchant of Record
            if ( $this->is_user_vendor && 'vendor' !== $merchant_of_record ) {
                add_filter( 'woocommerce_api_query_args', array( $this, 'force_empty_response' ) );
            } elseif ( ! $this->is_user_vendor && 'vendor' === $merchant_of_record ) {
                add_filter( 'woocommerce_api_query_args', array( $this, 'force_empty_response' ) );
            }

            // Block TaxJar from importing refunds - we handle that ourselves
            if ( isset( $filter['status'] ) && 'refunded' === $filter['status'] ) {
                add_filter( 'woocommerce_api_query_args', array( $this, 'force_empty_response' ) );
            }

            // Record the earliest requested transaction date so we know which
            // refunds to upload
            if ( isset( $filter['updated_at_min'] ) ) {
                $this->update_reports_start_date( $filter['updated_at_min'] );
            }
        }

        return parent::get_orders( $fields, $filter, $status, $page );
    }

    /**
     * Updates the TaxJar reports start date based on the value of the
     * `update_at_min` filter.
     *
     * @param string $updated_at_min
     */
    protected function update_reports_start_date( $updated_at_min ) {
        if ( empty( $updated_at_min ) ) {
            return;
        }

        if ( $this->is_user_vendor ) {
            $current_min = get_user_meta( get_current_user_id(), 'mt_reports_start_date', true );
        } else {
            $current_min = get_option( 'mt_reports_start_date', '' );
        }

        $requested_time = strtotime( $updated_at_min );

        if ( ! $current_min || $requested_time < strtotime( $current_min ) ) {
            $current_min = date( 'Y-m-d 00:00:00', $requested_time );
        }

        if ( $this->is_user_vendor ) {
            update_user_meta( get_current_user_id(), 'mt_reports_start_date', $current_min );
        } else {
            update_option( 'mt_reports_start_date', $current_min );
        }
    }

    /**
     * Forces an empty response by setting post__in.
     *
     * @param array $args
     *
     * @return array $args
     */
    public function force_empty_response( $args ) {
        $args['post__in'] = [ 0 ];

        return $args;
    }

}
