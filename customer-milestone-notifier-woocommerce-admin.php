<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @since             1.0.0
 * @package           Wc_Admin_Customer_Milestone_Notifier
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce Admin Customer Milestone Notifier
 * Description:       WooCommerce Admin Customer Milestone Notifier will Notify you when your store will have 1,10,100,250,500,1000,10000 customers registration.
 * Version:           1.0.0
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       customer-milestone-notifier-woocommerce-admin
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce_Activity_Panel_Customer_Milestone_Notification
 */
class WooCommerce_Activity_Panel_Customer_Milestone_Notification {
	/**
	 * Name of the "first customer" note.
	 */
	const FIRST_CUSTOMER_NOTE_NAME = 'woocommerce-admin-first-customer';

	/**
	 * Name of the "ten customers" note.
	 */
	const TEN_CUSTOMER_NOTE_NAME = 'woocommerce-admin-ten-customer';

	/**
	 * Name of the "other customer milestones" note.
	 */
	const CUSTOMERS_MILESTONE_NOTE_NAME = 'wc-admin-customers-milestone';

	/**
	 * Customers count cache.
	 *
	 * @var int
	 */
	protected $customers_count = null;

	/**
	 * Further customer milestone thresholds.
	 *
	 * @var array
	 */
	protected $customer_milestones = array(
		100,
		250,
		500,
		1000,
		5000,
		10000,
	);

	/**
	 *
	 * add_action of customer register.
	 */
	public function __construct() {
		add_action( 'user_register', array( $this, 'customer_milestone_notification' ) );
	}

	/**
	 * Get the total count of customers.
	 *
	 * @return int Total customers count.
	 */
	public function get_customers_count() {
		if ( is_null( $this->customers_count ) ) {
			$this->customers_count = count( get_users( array( 'role' => 'Customer' ) ) );
		}

		return $this->customers_count;
	}

	/**
	 * Add a milestone notes for the store's first customer, first 10 customers and other milestones.
	 *
	 */
	public function customer_milestone_notification() {
		if ( ! class_exists( 'WC_Admin_Notes' ) ) {
			return;
		}

		if ( ! class_exists( 'WC_Data_Store' ) ) {
			return;
		}
		$customers_count = $this->get_customers_count();
		$data_store      = WC_Data_Store::load( 'admin-note' );
		if ( 1 === $customers_count ) {
			// Add the first customer note.
			// First, see if we've already created this kind of note so we don't do it again.
			$note_ids = $data_store->get_notes_with_name( self::FIRST_CUSTOMER_NOTE_NAME );
			foreach ( (array) $note_ids as $note_id ) {
				$note         = WC_Admin_Notes::get_note( $note_id );
				$content_data = $note->get_content_data();
				if ( property_exists( $content_data, 'first_customer' ) ) {
					return;
				}
			}

			// Otherwise, add the note
			$activated_time           = current_time( 'timestamp', 0 );
			$activated_time_formatted = date( 'F jS', $activated_time );
			$note                     = new WC_Admin_Note();
			$note->set_title( __( 'First Customer', 'customer-milestone-notifier-woocommerce-admin' ) );
			$note->set_content(
				__( 'Congratulations on getting your first customer on your store..!', 'customer-milestone-notifier-woocommerce-admin' )
			);
			$note->set_content_data( (object) array(
				'first_customer'      => true,
				'activated'           => $activated_time,
				'activated_formatted' => $activated_time_formatted,
			) );
			$note->set_type( WC_Admin_Note::E_WC_ADMIN_NOTE_INFORMATIONAL );
			$note->set_icon( 'trophy' );
			$note->set_name( self::FIRST_CUSTOMER_NOTE_NAME );
			$note->set_source( 'customer-milestone-notifier-woocommerce-admin' );
			$note->add_action(
				'customer_analytics',
				__( 'Track Customer orders', 'customer-milestone-notifier-woocommerce-admin' ),
				'?page=wc-admin#/analytics/customers'
			);
			$note->save();
		} else if ( 10 === $customers_count ) {
			// Add the ten customers note.
			// First, see if we've already created this kind of note so we don't do it again.
			$note_ids = $data_store->get_notes_with_name( self::TEN_CUSTOMER_NOTE_NAME );
			foreach ( (array) $note_ids as $note_id ) {
				$note         = WC_Admin_Notes::get_note( $note_id );
				$content_data = $note->get_content_data();
				if ( property_exists( $content_data, 'tenth_customer' ) ) {
					return;
				}
			}

			// Otherwise, add the note
			$activated_time           = current_time( 'timestamp', 0 );
			$activated_time_formatted = date( 'F jS', $activated_time );
			$note                     = new WC_Admin_Note();
			$note->set_title(
				sprintf(
					__( 'Congratulations on getting 10 customers on your store.!', 'customer-milestone-notifier-woocommerce-admin' )
				)
			);
			$note->set_content(
				__( "You've hit the 10 customers milestone! Look at you go. Browse some WooCommerce success stories for inspiration.", 'customer-milestone-notifier-woocommerce-admin' )
			);
			$note->set_content_data( (object) array(
				'tenth_customer'      => true,
				'activated'           => $activated_time,
				'activated_formatted' => $activated_time_formatted,
			) );
			$note->set_type( WC_Admin_Note::E_WC_ADMIN_NOTE_INFORMATIONAL );
			$note->set_icon( 'trophy' );
			$note->set_name( self::TEN_CUSTOMER_NOTE_NAME );
			$note->set_source( 'customer-milestone-notifier-woocommerce-admin' );
			$note->add_action( 'browse', __( 'Browse', 'customer-milestone-notifier-woocommerce-admin' ), 'https://woocommerce.com/success-stories/' );
			$note->save();
		} else {
			$this->other_customer_milestones();
		}
	}

	/**
	 * Add milestone notes for other significant thresholds.
	 */
	public function other_customer_milestones() {
		$customers_count = $this->get_customers_count();
		if ( in_array( $customers_count, $this->customer_milestones ) ) {
			// We only want one milestone note at any time.
			WC_Admin_Notes::delete_notes_with_name( self::CUSTOMERS_MILESTONE_NOTE_NAME );


			// Add the milestone note.
			$activated_time           = current_time( 'timestamp', 0 );
			$activated_time_formatted = date( 'F jS', $activated_time );
			$note                     = new WC_Admin_Note();
			$note->set_title(
				sprintf(
				/* translators: Number of customer registered. */
					__( 'Congratulations on processing %s customers..!', 'customer-milestone-notifier-woocommerce-admin' ),
					wc_format_decimal( $customers_count )
				)
			);
			$note->set_content(
				__( 'Another order milestone! Take a look at your Customer Report to review your customer details.', 'customer-milestone-notifier-woocommerce-admin' )
			);
			$note->set_content_data( (object) array(
				'other_customer'      => true,
				'activated'           => $activated_time,
				'activated_formatted' => $activated_time_formatted,
			) );
			$note->set_type( WC_Admin_Note::E_WC_ADMIN_NOTE_INFORMATIONAL );
			$note->set_icon( 'trophy' );
			$note->set_name( self::CUSTOMERS_MILESTONE_NOTE_NAME );
			$note->set_source( 'customer-milestone-notifier-woocommerce-admin' );
			$note->add_action( 'review-customers', __( 'Review your custmers', 'customer-milestone-notifier-woocommerce-admin' ), '?page=wc-admin#/analytics/customers' );
			$note->save();
		}
	}

	/*
	 * It will check that WooCommerce Admin plugin is installed activated or not before ativation of this plugin.
	 */
	public static function self_deactivate_notice() {
		if ( ! class_exists( 'WC_Admin_Notes' ) ) {
			wp_die( "It requires WooCommerce Admin to be installed and active." );
		}
	}

	/**
	 * Removes any notes this plugin created.
	 */
	public static function remove_customer_milestone_notifier_notes() {
		if ( ! class_exists( 'WC_Admin_Notes' ) ) {
			return;
		}

		WC_Admin_Notes::delete_notes_with_name( self::FIRST_CUSTOMER_NOTE_NAME );
		WC_Admin_Notes::delete_notes_with_name( self::TEN_CUSTOMER_NOTE_NAME );
		WC_Admin_Notes::delete_notes_with_name( self::CUSTOMERS_MILESTONE_NOTE_NAME );
	}
}

function customer_milestone_notifier_activate() {
	WooCommerce_Activity_Panel_Customer_Milestone_Notification::self_deactivate_notice();
}

register_activation_hook( __FILE__, 'customer_milestone_notifier_activate' );

function customer_milestone_notifier_deactivate() {
	WooCommerce_Activity_Panel_Customer_Milestone_Notification::remove_customer_milestone_notifier_notes();
}

register_deactivation_hook( __FILE__, 'customer_milestone_notifier_deactivate' );

new WooCommerce_Activity_Panel_Customer_Milestone_Notification();
