<?php

namespace BlazeWooless\Settings;

use BlazeWooless\TypesenseClient;

class FooterThreeSettings extends BaseSettings {
	private static $instance = null;
	public $tab_key = 'footer_3';
	public $page_label = 'Footer 3';

	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new self( 'wooless_footer_3_settings_options' );
		}

		return self::$instance;
	}

	public function settings_callback( $options ) {
		$footer_content_3 = array();
		if ( isset( $_POST['footer_content_3'] ) ) {
			$footer_content_3 = json_decode( stripslashes( $_POST['footer_content_3'] ), true );
		}

		if ( is_array( $footer_content_3 ) ) {
			update_option( 'blaze_wooless_footer_3_content', $footer_content_3 );

			TypesenseClient::get_instance()->site_info()->upsert( [ 
				'id' => '1000007',
				'name' => 'footer_content_3',
				'value' => json_encode( $footer_content_3 ),
				'updated_at' => time(),
			] );
		}

		return $options;
	}

	public function settings() {
		return array();
	}

	public function section_callback() {
		echo '<p>Select which areas of content you wish to display.</p>';
	}

	public function register_hooks() {
		add_action( 'blaze_wooless_render_settings_tab_footer', array( $this, 'default_draggable_data' ), 10 );
		add_action( 'blaze_wooless_after_site_info_sync', array( $this, 'add_footer_three_data' ), 10, 2 );
	}

	public function footer_callback() {
		require_once BLAZE_WOOLESS_PLUGIN_DIR . 'views/draggable-content-simple.php';
	}

	public function default_draggable_data() {
		if ( empty( $_GET['tab'] ) || $this->tab_key !== $_GET['tab'] )
			return;

		$footer_content = get_option( 'blaze_wooless_footer_3_content', '' );
		?>
		<input type="hidden" id="draggable_result" name="footer_content_3"
			value='<?php echo json_encode( $footer_content ) ?>' />
		<?php
	}

	public function add_footer_three_data() {
		$footer_content_3 = get_option( 'blaze_wooless_footer_3_content', '' );
		if ( empty( $footer_content_3 ) )
			return;

		TypesenseClient::get_instance()->site_info()->upsert( [ 
			'id' => '1000007',
			'name' => 'footer_content_3',
			'value' => json_encode( $footer_content_3 ),
			'updated_at' => time(),
		] );
	}
}

FooterThreeSettings::get_instance();
