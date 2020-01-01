<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * A custom Email class to send Brix waiver email
 *
 * @since 0.1
 * @extends \WC_Email
 */
class WC_Brix_Waiver_Email extends WC_Email {


	/**
	 * Set email defaults
	 *
	 * @since 0.1
	 */
	public function __construct() {

		// set ID, this simply needs to be a unique name
		$this->id = 'wc_brix_waiver';

		// this is the title in WooCommerce Email settings
		$this->title = 'Waiver Customer Notification';
		$this->customer_email = true;
		// this is the description in WooCommerce email settings
		$this->description = 'Waiver Customer Notification emails are sent when a customer places an order with category "training-programs"';

		// these are the default heading and subject lines that can be overridden using the settings
		$this->heading = 'Mandatory Waiver';
		$this->subject = 'Waiver for Online Coaching';

		// these define the locations of the templates that this email should use, we'll just use the new order template since this email is similar
		$this->template_html  = 'emails/brix-waiver-notification.php';
		$this->template_plain = 'emails/plain/brix-waiver-notification.php';

		// Trigger on new paid orders
		add_action( 'woocommerce_order_status_pending_to_processing_notification', array( $this, 'trigger' ) );
		add_action( 'woocommerce_order_status_failed_to_processing_notification',  array( $this, 'trigger' ) );
        add_action( 'woocommerce_order_status_completed_notification', array( $this, 'trigger' ) );

		// Call parent constructor to load any other defaults not explicity defined here
		parent::__construct();

	

	}

    /**
     * Determine if the email should actually be sent and setup email merge variables
     *
     * @since 0.1
     * @param int $order_id
     */
    public function trigger( $order_id ) {

        // bail if no order ID is present
        if ( ! $order_id )
            return;

        // setup order object
        $this->object = new WC_Order( $order_id );
        // set our flag to be false until we find a product in that category
        $cat_check = false;

// check each cart item for our category
        foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

            $product = $cart_item['data'];

            // replace 'training-programs' with your category's slug
            if ( has_term( 'training-programs', 'product_cat', $product->get_id() ) ) {
                $cat_check = true;
                // break because we only need one "true" to matter here
                break;
            }
            
        }

// if a product in the cart is in our category, do something
        if ( ! $cat_check ) {
            return;
        }

	
        // replace variables in the subject/headings
        $this->recipient                      = $this->object->get_billing_email();
        $this->placeholders[] = '{order_date}';
        $this->placeholders[] = date_i18n( wc_date_format(), strtotime( $this->object->order_date ) );

        $this->placeholders[] = '{order_number}';
        $this->placeholders[] = $this->object->get_order_number();

        if ( ! $this->is_enabled())
            return;

        // woohoo, send the email!
        $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
    }







    /**
	 * get_content_html function.
	 *
	 * @since 0.1
	 * @return string
	 */
	public function get_content_html() {
		ob_start();
        wc_get_template( $this->template_html, array(
			'order'         => $this->object,
			'email_heading' => $this->get_heading()
		) );
		return ob_get_clean();
	}


	/**
	 * get_content_plain function.
	 *
	 * @since 0.1
	 * @return string
	 */
	public function get_content_plain() {
		ob_start();
        wc_get_template( $this->template_plain, array(
			'order'         => $this->object,
			'email_heading' => $this->get_heading()
		) );
		return ob_get_clean();
	}


	/**
	 * Initialize Settings Form Fields
	 *
	 * @since 2.0
	 */
	public function init_form_fields() {

		$this->form_fields = array(
			'enabled'    => array(
				'title'   => 'Enable/Disable',
				'type'    => 'checkbox',
				'label'   => 'Enable this email notification',
				'default' => 'yes'
			),
			'subject'    => array(
				'title'       => 'Subject',
				'type'        => 'text',
				'description' => sprintf( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', $this->subject ),
				'placeholder' => '',
				'default'     => ''
			),
			'heading'    => array(
				'title'       => 'Email Heading',
				'type'        => 'text',
				'description' => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.' ), $this->heading ),
				'placeholder' => '',
				'default'     => ''
			),
			'email_type' => array(
				'title'       => 'Email type',
				'type'        => 'select',
				'description' => 'Choose which format of email to send.',
				'default'     => 'html',
				'class'       => 'email_type',
				'options'     => array(
					'html' 	    => __( 'HTML', 'woocommerce' ),
				)
			)
		);
	}


} // end \WC_Expedited_Order_Email class