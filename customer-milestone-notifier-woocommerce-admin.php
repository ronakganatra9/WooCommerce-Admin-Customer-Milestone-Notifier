<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://ronakganatra.blog
 * @since             1.0.0
 * @package           Wc_Admin_Customer_Milestone_Notifier
 *
 * @wordpress-plugin
 * Plugin Name:      WooCommerce Admin Customer Milestone Notifier
 * Plugin URI:        http://ronakganatra.blog
 * Description:       WooCommerce Admin Customer Milestone Notifier will Notify you when your store will have 1,10,100,250,500,1000,10000 customers registration.
 * Version:           1.0.0
 * Author:            Ronak Ganatra
 * Author URI:        http://ronakganatra.blog
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       customer-milestone-notifier-woocommerce-admin
 * Domain Path:       /languages
 */

class WooCommerce_Activity_Panel_Customer_Milestone_Notification {
    const FIRST_CUSTOMER_NOTE_NAME = 'customer-milestone-notifier-woocommerce-admin';

	public function __construct() {
		add_action( 'user_register', array( $this, 'add_activity_panel_customer_milestone_notification' ) );
	}
    /**
     * Adds a note to the merchant' inbox for the first customer registration.
     */
    public static function add_activity_panel_customer_milestone_notification() {
	    $total_customers =  count( get_users( array( 'role' => 'Customer' ) ) );
        if ( ! class_exists( 'WC_Admin_Notes' ) ) {
            return;
        }

        if ( ! class_exists( 'WC_Data_Store' ) ) {
            return;
        }

        $data_store = WC_Data_Store::load( 'admin-note' );
	   if ( 11 === $total_customers ) {
        // First, see if we've already created this kind of note so we don't do it again.
        $note_ids = $data_store->get_notes_with_name( self::FIRST_CUSTOMER_NOTE_NAME );
        foreach( (array) $note_ids as $note_id ) {
            $note         = WC_Admin_Notes::get_note( $note_id );
            $content_data = $note->get_content_data();
            if ( property_exists( $content_data, 'first_customer' ) ) {
                return;
            }
        }

        // Otherwise, add the note
        $activated_time = current_time( 'timestamp', 0 );
        $activated_time_formatted = date( 'F jS', $activated_time );
        $note = new WC_Admin_Note();
        $note->set_title( __( 'First Customer', 'customer-milestone-notifier-woocommerce-admin' ) );
        $note->set_content(
            sprintf(
            /* translators: a date, e.g. November 1st */
                __( 'Congratulations on getting your first customer..!!', 'customer-milestone-notifier-woocommerce-admin' )
            )
        );
        $note->set_content_data( (object) array(
            'first_customer'     => true,
            'activated'           => $activated_time,
            'activated_formatted' => $activated_time_formatted,
        ) );
        $note->set_type( WC_Admin_Note::E_WC_ADMIN_NOTE_INFORMATIONAL );
        // See https://automattic.github.io/gridicons/ for icon names.
        // Don't include the gridicons- part of the name.
        $note->set_icon( 'trophy' );
        $note->set_name( self::FIRST_CUSTOMER_NOTE_NAME );
        $note->set_source( 'customer-milestone-notifier-woocommerce-admin' );
        // This example has two actions. A note can have 0 or 1 as well.
        $note->add_action(
            'customer_analytics',
            __( 'Track Customer orders', 'customer-milestone-notifier-woocommerce-admin' ),
            '?page=wc-admin#/analytics/customers'
        );
//        $note->add_action(
//            'settings',
//            __( 'Learn More', 'customer-milestone-notifier-woocommerce-admin' ),
//            'https://github.com/woocommerce/wc-admin/tree/master/docs'
//        );
        $note->save();
	    }
    }

    /**
     * Removes any notes this plugin created.
     */
    public static function remove_activity_panel_inbox_notes() {
        if ( ! class_exists( 'WC_Admin_Notes' ) ) {
            return;
        }

        WC_Admin_Notes::delete_notes_with_name( self::FIRST_CUSTOMER_NOTE_NAME );
    }
}

function customer_milestone_notifier_activate() {
//    WooCommerce_Activity_Panel_Inbox_Example_Plugin_One::add_activity_panel_inbox_welcome_note();
}
register_activation_hook( __FILE__, 'customer_milestone_notifier_activate' );

function customer_milestone_notifier_deactivate() {
	WooCommerce_Activity_Panel_Customer_Milestone_Notification::remove_activity_panel_inbox_notes();
}
register_deactivation_hook( __FILE__, 'customer_milestone_notifier_deactivate' );

new WooCommerce_Activity_Panel_Customer_Milestone_Notification();