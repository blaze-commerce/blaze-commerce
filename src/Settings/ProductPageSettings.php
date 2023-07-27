<?php

namespace BlazeWooless\Settings;

use BlazeWooless\Features\AttributeSettings;
use BlazeWooless\TypesenseClient;

class ProductPageSettings extends BaseSettings 
{
    private static $instance = null;
    public $tab_key = 'product';
    public $page_label = 'Product Page';

    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self( 'wooless_settings_product_page_options' );
        }

        return self::$instance;
    }
    
    public function settings_callback( $options )
    {
        try {
            $this->update_fields( $options );
        } catch (\Throwable $th) {
            
        }
        

        return $options;
    }

    public function settings()
    {
        $product_page_settings = array(
            'wooless_settings_product_page_section' => array(
                'label' => 'Product Page',
                'options' => array(
                    array(
                        'id' => 'privacy_policy',
                        'label' => 'Privacy Policy',
                        'type' => 'textarea',
                        'args' => array(
                            'description' => 'Set the privacy policy content.',
                        ),
                    ),
                    array(
                        'id' => 'returns_policy',
                        'label' => 'Returns Policy',
                        'type' => 'textarea',
                        'args' => array(
                            'description' => 'Set the returns policy content.'
                        ),
                    ),
                )
            ),
        );

        return apply_filters( 'blaze_wooless_product_page_settings', $product_page_settings );
    }

    public function section_callback() {
        echo '<p>Select which areas of content you wish to display.</p>';
    }

    public function update_fields( $options )
    {
        TypesenseClient::get_instance()->site_info()->upsert([
            'id' => '1000001',
            'name' => 'privacy_policy_content',
            'value' => $options['privacy_policy'],
            'updated_at' => time(),
        ]);
        TypesenseClient::get_instance()->site_info()->upsert([
            'id' => '1000002',
            'name' => 'returns_policy_content',
            'value' => $options['returns_policy'],
            'updated_at' => time(),
        ]);

        do_action( 'blaze_wooless_save_product_page_settings', $options );
    }

    public function register_hooks()
    {
        add_action( 'blaze_wooless_after_site_info_sync', array( $this, 'sync_additional_data' ), 10 );
    }

    public function sync_additional_data()
    {
        $options = $this->get_option();
        $this->update_fields( $options );
    }
}

ProductPageSettings::get_instance();