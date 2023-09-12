<?php


namespace BlazeCommerce\Features;

class LoadCartFromSession
{
	private static $instance = null;

	public static function get_instance()
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct()
	{
		add_action('woocommerce_load_cart_from_session', array($this, 'woocommerce_load_cart_from_session'));
		add_action('init', array($this, 'load_user_from_session'));
		add_action('wp_footer', array($this, 'remove_session_id_from_url_script'));
	}

	public function woocommerce_load_cart_from_session()
	{
		// Bail if there isn't any data
		if (!isset($_GET['session_id'])) {
			return;
		}

		$session_id = sanitize_text_field($_GET['session_id']);

		try {
			$handler = new \WC_Session_Handler();
			$session_data = $handler->get_session($session_id);


			// We were passed a session ID, yet no session was found. Let's log this and bail.
			if (empty($session_data)) {
				throw new \Exception('Could not locate WooCommerce session on checkout');
			}

			// Go get the session instance (WC_Session) from the Main WC Class
			$session = WC()->session;

			// Set the session variable
			foreach ($session_data as $key => $value) {
				$session_value = unserialize($value);
				$session->set($key, $session_value);
			}
		} catch (\Exception $exception) {
			// ErrorHandling::capture( $exception );
		}
	}

	public function load_user_from_session()
    {
        // Bail if there isn't any data
		if (!isset($_GET['session_id']) || is_user_logged_in()) {
			return;
		}

		$session_id = sanitize_text_field($_GET['session_id']);

		try {
			$handler = new \WC_Session_Handler();
			$session_data = $handler->get_session($session_id);


			// We were passed a session ID, yet no session was found. Let's log this and bail.
			if (empty($session_data)) {
				throw new \Exception('Could not locate WooCommerce session on checkout');
			}

            if ($customer = $session_data['customer']) {
                $customer_data = unserialize($customer);
                $customer_id = $customer_data['id'];
                echo "<pre>"; print_r($customer_id); echo "</pre>";

                if ($customer_id) {
                    // Authenticate the user and set the authentication cookies
                    wp_set_auth_cookie($customer_id);
                }
            }
		} catch (\Exception $exception) {
			// ErrorHandling::capture( $exception );
		}
    }

	public function remove_session_id_from_url_script()
	{
        $restricted_pages = apply_filters('blaze_commerce_restricted_pages', is_cart());
        if ( $restricted_pages ) {
            wp_redirect(home_url());
            exit;
        }

        $pages_should_redirect_to_frontend = apply_filters('blaze_commerce_pages_should_redirect_to_frontend', is_shop() || is_product_category() || is_product());
        if ( $pages_should_redirect_to_frontend ) {
            wp_redirect(home_url( $_SERVER['REQUEST_URI'] ));
            exit;
        }

        if (!class_exists('WooCommerce') || (!isset($_GET['session_id']) && !isset($_GET['from_commerce']))) {
            return;
        }

		$url = remove_query_arg(['session_id', 'from_commerce'], $_SERVER['REQUEST_URI']);
		wp_redirect(apply_filters('blaze_commerce_destination_url_from_frontend', $url));
		exit;
	}
}
