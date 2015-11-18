<?php
/**
 * Register Settings
 *
 * @package     QUADS
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2015, René Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


/**
 * Get an option
 *
 * Looks to see if the specified setting exists, returns default if not
 *
 * @since 1.0.0
 * @return mixed
 */
function quads_get_option( $key = '', $default = false ) {
	global $quads_options;
	$value = ! empty( $quads_options[ $key ] ) ? $quads_options[ $key ] : $default;
	$value = apply_filters( 'quads_get_option', $value, $key, $default );
	return apply_filters( 'quads_get_option_' . $key, $value, $key, $default );
}

/**
 * Get Settings
 *
 * Retrieves all plugin settings
 *
 * @since 1.0
 * @return array QUADS settings
 */
function quads_get_settings() {
	$settings = get_option( 'quads_settings' );
               
        
	if( empty( $settings ) ) {
		// Update old settings with new single option

		$general_settings = is_array( get_option( 'quads_settings_general' ) )    ? get_option( 'quads_settings_general' )  	: array();
                $visual_settings = is_array( get_option( 'quads_settings_visual' ) )   ? get_option( 'quads_settings_visual' )   : array();
                $networks = is_array( get_option( 'quads_settings_networks' ) )   ? get_option( 'quads_settings_networks' )   : array();
		$ext_settings     = is_array( get_option( 'quads_settings_extensions' ) ) ? get_option( 'quads_settings_extensions' )	: array();
		$license_settings = is_array( get_option( 'quads_settings_licenses' ) )   ? get_option( 'quads_settings_licenses' )   : array();
                $addons_settings = is_array( get_option( 'quads_settings_addons' ) )   ? get_option( 'quads_settings_addons' )   : array();
                
		$settings = array_merge( $general_settings, $visual_settings, $networks, $ext_settings, $license_settings, $addons_settings);

		update_option( 'quads_settings', $settings);
	}
	return apply_filters( 'quads_get_settings', $settings );
}

/**
 * Add all settings sections and fields
 *
 * @since 1.0
 * @return void
*/
function quads_register_settings() {

	if ( false == get_option( 'quads_settings' ) ) {
		add_option( 'quads_settings' );
	}

	foreach( quads_get_registered_settings() as $tab => $settings ) {

		add_settings_section(
			'quads_settings_' . $tab,
			__return_null(),
			'__return_false',
			'quads_settings_' . $tab
		);

		foreach ( $settings as $option ) {

			$name = isset( $option['name'] ) ? $option['name'] : '';

			add_settings_field(
				'quads_settings[' . $option['id'] . ']',
				$name,
				function_exists( 'quads_' . $option['type'] . '_callback' ) ? 'quads_' . $option['type'] . '_callback' : 'quads_missing_callback',
				'quads_settings_' . $tab,
				'quads_settings_' . $tab,
				array(
					'id'      => isset( $option['id'] ) ? $option['id'] : null,
					'desc'    => ! empty( $option['desc'] ) ? $option['desc'] : '',
					'name'    => isset( $option['name'] ) ? $option['name'] : null,
					'section' => $tab,
					'size'    => isset( $option['size'] ) ? $option['size'] : null,
					'options' => isset( $option['options'] ) ? $option['options'] : '',
					'std'     => isset( $option['std'] ) ? $option['std'] : '',
                                        'textarea_rows' => isset( $option['textarea_rows']) ? $option['textarea_rows'] : ''
				)
			);
		}

	}

	// Creates our settings in the options table
	register_setting( 'quads_settings', 'quads_settings', 'quads_settings_sanitize' );

}
add_action('admin_init', 'quads_register_settings');

/**
 * Retrieve the array of plugin settings
 *
 * @since 1.8
 * @return array
*/
function quads_get_registered_settings() {

	/**
	 * 'Whitelisted' QUADS settings, filters are provided for each settings
	 * section to allow extensions and other plugins to add their own settings
	 */
	$quads_settings = array(
		/** General Settings */
		'general' => apply_filters( 'quads_settings_general',
			array(
                                'general_header' => array(
					'id' => 'general_header',
					'name' => '<strong>' . __( 'General settings', 'quads' ) . '</strong>',
					'desc' => __( ' ', 'quads' ),
					'type' => 'header'
				),
                                'quads_sharemethod' => array(
					'id' => 'quads_sharemethod',
					'name' =>  __( 'Share counts', 'quads' ),
					'desc' => __('<i>quadsEngine</i> collects shares by calling directly social networks from your server. All shares are cached and stored in your database. <p> If you notice performance issues choose the classical <i>Sharedcount.com</i>. This needs an API key and is limited to 10.000 free requests daily but it is a little bit faster on requesting. After caching there is no performance advantage to quadsEngine! <p> <strong>quadsEngine collects: </strong> Facebook, Twitter, LinkedIn, Google+, Pinterest, Stumbleupon, Buffer, VK. <strong>Default:</strong> quadsEngine', 'quads'),
					'type' => 'select',
					'options' => array(
                                            'quadsengine' => 'quadsEngine',
                                            'sharedcount' => 'Sharedcount.com'
                                        )
     
				),
				
				'quickads_apikey' => array(
					'id' => 'quickads_apikey',
					'name' => __( 'Sharedcount.com API Key', 'quads' ),
					'desc' => __( 'Get it at <a href="https://www.sharedcount.com" target="_blank">SharedCount.com</a> for 10.000 free daily requests.', 'quads' ),
					'type' => 'text',
					'size' => 'medium'
				),
				'quickads_sharecount_domain' => array(
					'id' => 'quickads_sharecount_domain',
					'name' => __( 'Sharedcount.com endpint', 'quads' ),
					'desc' => __( 'The SharedCount Domain your API key is configured to query. For example, free.sharedcount.com. This may update automatically if configured incorrectly.', 'quads' ),
					'type' => 'text',
					'size' => 'medium',
					'std'  => 'free.sharedcount.com'
				),
                                'quickads_cache' => array(
					'id' => 'quickads_cache',
					'name' =>  __( 'Cache expiration', 'quads' ),
					'desc' => __('Shares are counted for every post after this time. Notice that Sharedcount.com uses his own cache (30 - 60min) so share count does not update immediately. Make sure to increase this value especially when you use quadsEngine! Otherwise it could happen that some networks block your requests due to hammering their rate limits. <p><strong>Default: </strong>5 min. <strong>Recommended: </strong>30min and more', 'quads'),
					'type' => 'select',
					'options' => quads_get_expiretimes()
				),
                                'disable_sharecount' => array(
					'id' => 'disable_sharecount',
					'name' => __( 'Disable Sharecount', 'quads' ),
					'desc' => __( 'Use this when curl() is not supported on your server or share counts should not counted. This mode does not call the database and no SQL queries are generated. (Only less performance advantage. All db requests are cached) Default: false', 'quads' ),
					'type' => 'checkbox'
				),
                                'hide_sharecount' => array(
					'id' => 'hide_sharecount',
					'name' => __( 'Hide Sharecount', 'quads' ),
					'desc' => __( '<strong>Optional:</strong> If you fill in any number here, the shares for a specific post are not shown until the share count of this number is reached.', 'quads' ),
					'type' => 'text',
                                        'size' => 'small'
				),
                                'excluded_from' => array(
					'id' => 'excluded_from',
					'name' => __( 'Exclude from', 'quads' ),
					'desc' => __( 'Exclude share buttons from a list of specific posts and pages. Put in the page id separated by a comma, e.g. 23, 63, 114 ', 'quads' ),
					'type' => 'text',
                                        'size' => 'medium'
				),
                                'execution_order' => array(
					'id' => 'execution_order',
					'name' => __( 'Execution Order', 'quads' ),
					'desc' => __( 'If you use other content plugins you can define here the execution order. Lower numbers mean earlier execution. E.g. Say "0" and Quick AdSense Reloaded is executed before any other plugin (When the other plugin is not overwriting our execution order). Default is "1000"', 'quads' ),
					'type' => 'text',
					'size' => 'small',
                                        'std'  => 1000
				),
                                'fake_count' => array(
					'id' => 'fake_count',
					'name' => __( 'Fake Share counts', 'quads' ),
					'desc' => __( 'This number will be aggregated to all your share counts and is multiplied with a post specific factor. (Number of post title words divided with 10).', 'quads' ),
					'type' => 'text',
                                        'size' => 'medium'
				),
                                'load_scripts_footer' => array(
					'id' => 'load_scripts_footer',
					'name' => __( 'JS Load Order', 'quads' ),
					'desc' => __( 'Enable this to load all *.js files into footer. Make sure your theme uses the wp_footer() template tag in the appropriate place. Default: Disabled', 'quads' ),
					'type' => 'checkbox'
				),
                                'facebook_count' => array(
					'id' => 'facebook_count_mode',
					'name' => __( 'Facebook Count', 'quads' ),
					'desc' => __( 'Get the Facebook total count including "likes" and "shares" or get only the pure share count', 'quads' ),
					'type' => 'select',
                                        'options' => array(
                                            'shares' => 'Shares',
                                            'likes' => 'Likes',
                                            'total' => 'Total: likes + shares + comments'
                                            
                                        )
				),
                                'uninstall_on_delete' => array(
					'id' => 'uninstall_on_delete',
					'name' => __( 'Remove Data on Uninstall?', 'quads' ),
					'desc' => __( 'Check this box if you would like Quick AdSense Reloaded to completely remove all of its data when the plugin is deleted.', 'quads' ),
					'type' => 'checkbox'
				),
                                'debug_header' => array(
					'id' => 'debug_header',
					'name' => '<strong>' . __( 'Debug', 'quads' ) . '</strong>',
					'desc' => __( ' ', 'quads' ),
					'type' => 'header'
				),
                                array(
					'id' => 'disable_cache',
					'name' => __( 'Disable Cache', 'quads' ),
					'desc' => __( '<strong>Note: </strong>Use this only for testing to see if shares are counted! Your page loading performance will drop. Works only when sharecount is enabled.<br>' . quads_cache_status(), 'quads' ),
					'type' => 'checkbox'
				),
                                'delete_cache_objects' => array(
					'id' => 'delete_cache_objects',
					'name' => __( 'Purge DB Cache', 'quads' ),
					'desc' => __( '<strong>Note: </strong>Use this with caution when you think your share counts are wrong. Checking this and using the save button will delete all stored quadsshare post_meta objects.<br>' . quads_delete_cache_objects(), 'quads' ),
					'type' => 'checkbox'
				),
                                
                                'debug_mode' => array(
					'id' => 'debug_mode',
					'name' => __( 'Debug mode', 'quads' ),
					'desc' => __( '<strong>Note: </strong> Check this box before you get in contact with our support team. This allows us to check publically hidden debug messages on your website. Do not forget to disable it thereafter! Enable this also to write daily sorted log files of requested share counts to folder <strong>/wp-content/plugins/quadssharer/logs</strong>. Please send us this files when you notice a wrong share count.' . quads_log_permissions(), 'quads' ),
					'type' => 'checkbox'
				)
                                
			)
		),
                'visual' => apply_filters('quads_settings_visual',
			array(
                            'style_header' => array(
					'id' => 'style_header',
					'name' => '<strong>' . __( 'Customize', 'quads' ) . '</strong>',
					'desc' => __( ' ', 'quads' ),
					'type' => 'header'
                                ),
				'quickads_round' => array(
					'id' => 'quickads_round',
					'name' => __( 'Round Shares', 'quads' ),
					'desc' => __( 'Share counts greater than 1.000 will be shown as 1k. Greater than 1 Million as 1M', 'quads' ),
					'type' => 'checkbox'
				),
                                'animate_shares' => array(
					'id' => 'animate_shares',
					'name' => __( 'Animate Shares', 'quads' ),
					'desc' => __( 'Count up the shares on page loading with a nice looking animation effect. This only works on singular pages and not with shortcodes generated buttons.', 'quads' ),
					'type' => 'checkbox'
				),
                                'sharecount_title' => array(
					'id' => 'sharecount_title',
					'name' => __( 'Share count title', 'quads' ),
					'desc' => __( 'Change the text of the Share count title. <strong>Default:</strong> SHARES', 'quads' ),
					'type' => 'text',
					'size' => 'medium',
                                        'std' => 'SHARES'
				),
				'quickads_hashtag' => array(
					'id' => 'quickads_hashtag',
					'name' => __( 'Twitter handle', 'quads' ),
					'desc' => __( '<strong>Optional:</strong> Using your twitter username, e.g. \'Quick AdSense Reloaded\' results in via @Quick AdSense Reloaded', 'quads' ),
					'type' => 'text',
					'size' => 'medium'
				),
                                /*'share_color' => array(
					'id' => 'share_color',
					'name' => __( 'Share count color', 'quads' ),
					'desc' => __( 'Choose color of the share number in hex format, e.g. #7FC04C: ', 'quads' ),
					'type' => 'text',
					'size' => 'medium',
                                        'std' => '#cccccc'
				),*/
                                'share_color' => array(
					'id' => 'share_color',
					'name' => __( 'Share count color', 'quads' ),
					'desc' => __( 'Choose color of the share number in hex format, e.g. #7FC04C: ', 'quads' ),
					'type' => 'text',
					'size' => 'medium',
                                        'std' => '#cccccc'
				),
                                'border_radius' => array(
					'id' => 'border_radius',
					'name' => __( 'Border Radius', 'quads' ),
					'desc' => __( 'Specify the border radius of all buttons in pixel. A border radius of 20px results in circle buttons. Default value is zero.', 'quads' ),
					'type' => 'select',
                                        'options' => array(
                                                0 => 0,
						1 => 1,
						2 => 2,
                                                3 => 3,
						4 => 4,
                                                5 => 5,
						6 => 6,
                                                7 => 7,
                                                8 => 8,
						9 => 9,
                                                10 => 10,
						11 => 11,
                                                12 => 12,
						13 => 13,
                                                14 => 14,
                                                15 => 15,
						16 => 16,
                                                17 => 17,
						18 => 18,
                                                19 => 19,
						20 => 20,
                                                'default' => 'default'
					),
                                        'std' => 'default'
					
				),
                                array(
                                        'id' => 'button_width',
                                        'name' => __( 'Button width', 'quadspv' ),
                                        'desc' => __( 'Minimum with of the large share buttons in pixels', 'quadspv' ),
                                        'type' => 'number',
                                        'size' => 'normal',
                                        'std' => '177'
                                ), 
                                'quads_style' => array(
					'id' => 'quads_style',
					'name' => __( 'Share button style', 'quads' ),
					'desc' => __( 'Change visual appearance of the share buttons.', 'quads' ),
					'type' => 'select',
                                        'options' => array(
						'shadow' => 'Shadowed buttons',
                                                'gradiant' => 'Gradient colored buttons',
                                                'default' => 'Clean buttons - no effects'
					),
                                        'std' => 'default'
					
				),
                                'small_buttons' => array(
					'id' => 'small_buttons',
					'name' => __( 'Use small buttons', 'quads' ),
					'desc' => __( 'All buttons will be shown as pure small icons without any text on desktop and mobile devices all the time.<br><strong>Note:</strong> Disable this when you use the <a href="https://www.quadsshare.net/downloads/quadsshare-responsive/" target="_blank">responsive Add-On</a>', 'quads' ),
					'type' => 'checkbox'
				),
                                'subscribe_behavior' => array(
					'id' => 'subscribe_behavior',
					'name' => __( 'Subscribe button', 'quads' ),
					'desc' => __( 'Specify if the subscribe button is opening a content box below the button or if the button is linked to the "subscribe url" below.', 'quads' ),
					'type' => 'select',
                                        'options' => array(
						'content' => 'Open content box',
                                                'link' => 'Open Subscribe Link'
					),
                                        'std' => 'content'
					
				),
                                'subscribe_link' => array(
					'id' => 'subscribe_link',
					'name' => __( 'Subscribe URL', 'quads' ),
					'desc' => __( 'Link the Subscribe button to this URL. This can be the url to your subscribe page, facebook fanpage, RSS feed etc. e.g. http://yoursite.com/subscribe', 'quads' ),
					'type' => 'text',
					'size' => 'regular',
                                        'std' => ''
				),
                                /*'subscribe_content' => array(
					'id' => 'subscribe_content',
					'name' => __( 'Subscribe content', 'quads' ),
					'desc' => __( '<br>Define the content of the opening toggle subscribe window here. Use formulars, like button, links or any other text. Shortcodes are supported, e.g.: [contact-form-7]', 'quads' ),
					'type' => 'textarea',
					'textarea_rows' => '3',
                                        'size' => 15
				),*/
                                'additional_content' => array(
					'id' => 'additional_content',
					'name' => __( 'Additional Content', 'quads' ),
					'desc' => __( '', 'quads' ),
					'type' => 'add_content',
                                        'options' => array(
                                            'box1' => array(
                                                'id' => 'content_above',
                                                'name' => __( 'Content Above', 'quads' ),
                                                'desc' => __( 'Content appearing above share buttons. Use HTML, formulars, like button, links or any other text. Shortcodes are supported, e.g.: [contact-form-7]', 'quads' ),
                                                'type' => 'textarea',
                                                'textarea_rows' => '3',
                                                'size' => 15
                                                ),
                                            'box2' => array(
                                                'id' => 'content_below',
                                                'name' => __( 'Content Below', 'quads' ),
                                                'desc' => __( 'Content appearing below share buttons.  Use HTML, formulars, like button, links or any other text. Shortcodes are supported, e.g.: [contact-form-7]', 'quads' ),
                                                'type' => 'textarea',
                                                'textarea_rows' => '3',
                                                'size' => 15
                                                ),
                                            'box3' => array(
                                                'id' => 'subscribe_content',
                                                'name' => __( 'Subscribe content', 'quads' ),
                                                'desc' => __( 'Define the content of the opening toggle subscribe window here. Use formulars, like button, links or any other text. Shortcodes are supported, e.g.: [contact-form-7]', 'quads' ),
                                                'type' => 'textarea',
                                                'textarea_rows' => '3',
                                                'size' => 15
                                                )
                                        )
				),                   
                                'custom_css' => array(
					'id' => 'custom_css',
					'name' => __( 'Custom CSS', 'quads' ),
					'desc' => __( '<br>Use Quick AdSense Reloaded custom styles here', 'quads' ),
					'type' => 'textarea',
					'size' => 15
                                        
				),
                                'location_header' => array(
					'id' => 'location_header',
					'name' => '<strong>' . __( 'Location & Position', 'quads' ) . '</strong>',
					'desc' => __( ' ', 'quads' ),
					'type' => 'header'
                                ),
                                'quickads_position' => array(
					'id' => 'quickads_position',
					'name' => __( 'Position', 'quads' ),
					'desc' => __( 'Position of Share Buttons. If this is set to <i>manual</i> use the shortcode function [quadsshare] or use php code <br><strong>&lt;?php echo do_shortcode("[quadsshare]"); ?&gt;</strong> in template files. Be aware that you have to enable the above mentioned option "Load JS and CSS all over" if you experience issues with do_shortcode and the buttons are not shown, because we are not able to automatically detect the use of do_shortcode. See all <a href="https://www.quadsshare.net/faq/#Is_there_a_shortcode_for_pages_and_posts" target="_blank">available shortcodes</a> here.', 'quads' ),
					'type' => 'select',
                                        'options' => array(
						'before' => __( 'Top', 'quads' ),
						'after' => __( 'Bottom', 'quads' ),
                                                'both' => __( 'Top and Bottom', 'quads' ),
						'manual' => __( 'Manual', 'quads' )
					)
					
				),
                                'post_types' => array(
					'id' => 'post_types',
					'name' => __( 'Post Types', 'quads' ),
					'desc' => __( 'Select on which post_types the share buttons appear. This values will be ignored when position is specified "manual".', 'quads' ),
					'type' => 'posttypes'
				),
                                'loadall' => array(
					'id' => 'loadall',
					'name' => __( 'Load JS and CSS all over', 'quads' ),
					'desc' => __( 'This loads JS and CSS files on all site content pages. Select this only if you are using  <strong>&lt;?php echo do_shortcode("[quadsshare]"); ?&gt;</strong> and buttons are not shown in the expected content. <br>If you disable this option all styles and scripts are loaded conditionally only where they are needed.', 'quads' ),
					'type' => 'checkbox',
                                        'std' => 'false'
				),
                                'singular' => array(
					'id' => 'singular',
					'name' => __( 'Categories', 'quads' ),
					'desc' => __('Enable this checkbox to enable Quick AdSense Reloaded on categories with multiple blogposts. <br><strong>Note: </strong> Post_types: "Post" must be enabled.','quads'),
					'type' => 'checkbox',
                                        'std' => '0'
				),
				'frontpage' => array(
					'id' => 'frontpage',
					'name' => __( 'Frontpage', 'quads' ),
					'desc' => __('Enable share buttons on frontpage','quads'),
					'type' => 'checkbox'
				),
                                /*'current_url' => array(
					'id' => 'current_url',
					'name' => __( 'Current Page URL', 'quads' ),
					'desc' => __('Force sharing the current page on non singular pages like categories with multiple blogposts','quads'),
					'type' => 'checkbox'
				),*/
                                'twitter_popup' => array(
					'id' => 'twitter_popup',
					'name' => __( 'Twitter Popup disable', 'quads' ),
					'desc' => __('Check this box if your twitter popup is openening twice. This happens sometimes when you are using any third party twitter plugin or the twitter SDK on your website.','quads'),
					'type' => 'checkbox',
                                        'std' => '0'
                                    
				),
                                /*'quads_shortcode_info' => array(
					'id' => 'quads_shortcode_info',
					'name' => __( 'Note:', 'quads' ),
					'desc' => __('Using the shortcode <strong>[quadsshare]</strong> forces loading of dependacy scripts and styles on specific pages. It is overwriting any other location setting.','quads'),
					'type' => 'note',
                                        'label_for' => 'test'
                                    
				),*/
                                
                        )
		),
                 'networks' => apply_filters( 'quads_settings_networks',
                         array(
                                'services_header' => array(
					'id' => 'services_header',
					'name' => __( 'Select available networks', 'quads' ),
					'desc' => '',
					'type' => 'header'
				),
                                'visible_services' => array(
					'id' => 'visible_services',
					'name' => __( 'Large Buttons', 'quads' ),
					'desc' => __( 'Specify how many services and social networks are visible before the "Plus" Button is shown. This buttons turn into large prominent buttons.', 'quads' ),
					'type' => 'select',
                                        'options' => numberServices()
				),
                                'networks' => array(
					'id' => 'networks',
					'name' => '<strong>' . __( 'Services', 'quads' ) . '</strong>',
					'desc' => __('Drag and drop the Share Buttons to sort them and specify which ones should be enabled. If you enable more services than the specified value "Large Buttons", the plus sign is automatically added to the last visible big share button.<br><strong>No Share Services visible after update?</strong> Disable and enable the Quick AdSense Reloaded Plugin solves this. ','quads'),
					'type' => 'networks',
                                        'options' => quads_get_networks_list()
                                 )
                         )
                ),
		'licenses' => apply_filters('quads_settings_licenses',
			array('licenses_header' => array(
					'id' => 'licenses_header',
					'name' => __( 'Activate your Add-Ons', 'quads' ),
					'desc' => '',
					'type' => 'header'
				),)
		),
                'extensions' => apply_filters('quads_settings_extension',
			array()
		),
                'addons' => apply_filters('quads_settings_addons',
			array(
                                'addons' => array(
					'id' => 'addons',
					'name' => __( '', 'quads' ),
					'desc' => __( '', 'quads' ),
					'type' => 'addons'
				)
                            //quads_addons_callback()
                        )
		)
	);

	return $quads_settings;
}

/**
 * Settings Sanitization
 *
 * Adds a settings error (for the updated message)
 * At some point this will validate input
 *
 * @since 1.0.0
 *
 * @param array $input The value input in the field
 *
 * @return string $input Sanitized value
 */
function quads_settings_sanitize( $input = array() ) {

	global $quads_options;

	if ( empty( $_POST['_wp_http_referer'] ) ) {
		return $input;
	}

	parse_str( $_POST['_wp_http_referer'], $referrer );

	$settings = quads_get_registered_settings();
	$tab      = isset( $referrer['tab'] ) ? $referrer['tab'] : 'general';

	$input = $input ? $input : array();
	$input = apply_filters( 'quads_settings_' . $tab . '_sanitize', $input );

	// Loop through each setting being saved and pass it through a sanitization filter
	foreach ( $input as $key => $value ) {

		// Get the setting type (checkbox, select, etc)
		$type = isset( $settings[$tab][$key]['type'] ) ? $settings[$tab][$key]['type'] : false;

		if ( $type ) {
			// Field type specific filter
			$input[$key] = apply_filters( 'quads_settings_sanitize_' . $type, $value, $key );
		}

		// General filter
		$input[$key] = apply_filters( 'quads_settings_sanitize', $value, $key );
	}

	// Loop through the whitelist and unset any that are empty for the tab being saved
	if ( ! empty( $settings[$tab] ) ) {
		foreach ( $settings[$tab] as $key => $value ) {

			// settings used to have numeric keys, now they have keys that match the option ID. This ensures both methods work
			if ( is_numeric( $key ) ) {
				$key = $value['id'];
			}

			if ( empty( $input[$key] ) ) {
				unset( $quads_options[$key] );
			}

		}
	}

	// Merge our new settings with the existing
	$output = array_merge( $quads_options, $input );

	add_settings_error( 'quads-notices', '', __( 'Settings updated.', 'quads' ), 'updated' );

	return $output;
}

/**
 * DEPRECATED Misc Settings Sanitization
 *
 * @since 1.0
 * @param array $input The value inputted in the field
 * @return string $input Sanitizied value
 */
/*function quads_settings_sanitize_misc( $input ) {

	global $quads_options;*/

	/*if( quads_get_file_download_method() != $input['download_method'] || ! quads_htaccess_exists() ) {
		// Force the .htaccess files to be updated if the Download method was changed.
		quads_create_protection_files( true, $input['download_method'] );
	}*/

	/*if( ! empty( $input['enable_sequential'] ) && ! quads_get_option( 'enable_sequential' ) ) {

		// Shows an admin notice about upgrading previous order numbers
		QUADS()->session->set( 'upgrade_sequential', '1' );

	}*/

	/*return $input;
}
add_filter( 'quads_settings_misc_sanitize', 'quads_settings_sanitize_misc' );
         * */

/**
 * Sanitize text fields
 *
 * @since 1.8
 * @param array $input The field value
 * @return string $input Sanitizied value
 */
function quads_sanitize_text_field( $input ) {
	return trim( $input );
}
add_filter( 'quads_settings_sanitize_text', 'quads_sanitize_text_field' );

/**
 * Retrieve settings tabs
 *
 * @since 1.8
 * @param array $input The field value
 * @return string $input Sanitizied value
 */
function quads_get_settings_tabs() {

	$settings = quads_get_registered_settings();

	$tabs             = array();
	$tabs['general']  = __( 'General', 'quads' );

        if( ! empty( $settings['visual'] ) ) {
		$tabs['visual'] = __( 'Visual', 'quads' );
	} 
        
        if( ! empty( $settings['networks'] ) ) {
		$tabs['networks'] = __( 'Social Networks', 'quads' );
	}  
        
	if( ! empty( $settings['extensions'] ) ) {
		$tabs['extensions'] = __( 'Extensions', 'quads' );
	}
	
	if( ! empty( $settings['licenses'] ) ) {
		$tabs['licenses'] = __( 'Licenses', 'quads' );
	}
        $tabs['addons'] = __( 'Add-Ons', 'quads' );

	//$tabs['misc']      = __( 'Misc', 'quads' );

	return apply_filters( 'quads_settings_tabs', $tabs );
}

       /*
	* Retrieve a list of possible expire cache times
	*
	* @since  2.0.0
	* @change 
	*
	* @param  array  $methods  Array mit verfügbaren Arten
	*/

        function quads_get_expiretimes()
	{
		/* Defaults */
        $times = array(
        '300' => 'in 5 minutes',
        '600' => 'in 10 minutes',
        '1800' => 'in 30 minutes',
        '3600' => 'in 1 hour',
        '21600' => 'in 6 hours',
        '43200' => 'in 12 hours',
        '86400' => 'in 24 hours'
        );
            return $times;
	}
   

/**
 * Retrieve array of  social networks Facebook / Twitter / Subscribe
 *
 * @since 2.0.0
 * 
 * @return array Defined social networks
 */
function quads_get_networks_list() {

        $networks = get_option('quads_networks');
	return apply_filters( 'quads_get_networks_list', $networks );
}

/**
 * Header Callback
 *
 * Renders the header.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @return void
 */
function quads_header_callback( $args ) {
	//echo '<hr/>';
        echo '&nbsp';
}

/**
 * Checkbox Callback
 *
 * Renders checkboxes.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $quads_options Array of all the QUADS Options
 * @return void
 */
function quads_checkbox_callback( $args ) {
	global $quads_options;

	$checked = isset( $quads_options[ $args[ 'id' ] ] ) ? checked( 1, $quads_options[ $args[ 'id' ] ], false ) : '';
	$html = '<input type="checkbox" id="quads_settings[' . $args['id'] . ']" name="quads_settings[' . $args['id'] . ']" value="1" ' . $checked . '/>';
	$html .= '<label class="quads_hidden" for="quads_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}


/**
 * Multicheck Callback
 *
 * Renders multiple checkboxes.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $quads_options Array of all the QUADS Options
 * @return void
 */
function quads_multicheck_callback( $args ) {
	global $quads_options;

	if ( ! empty( $args['options'] ) ) {
		foreach( $args['options'] as $key => $option ):
			if( isset( $quads_options[$args['id']][$key] ) ) { $enabled = $option; } else { $enabled = NULL; }
			echo '<input name="quads_settings[' . $args['id'] . '][' . $key . ']" id="quads_settings[' . $args['id'] . '][' . $key . ']" type="checkbox" value="' . $option . '" ' . checked($option, $enabled, false) . '/>&nbsp;';
			echo '<label for="quads_settings[' . $args['id'] . '][' . $key . ']">' . $option . '</label><br/>';
		endforeach;
		echo '<p class="description quads_hidden">' . $args['desc'] . '</p>';
	}
}

/**
 * Radio Callback
 *
 * Renders radio boxes.
 *
 * @since 1.3.3
 * @param array $args Arguments passed by the setting
 * @global $quads_options Array of all the QUADS Options
 * @return void
 */
function quads_radio_callback( $args ) {
	global $quads_options;

	foreach ( $args['options'] as $key => $option ) :
		$checked = false;

		if ( isset( $quads_options[ $args['id'] ] ) && $quads_options[ $args['id'] ] == $key )
			$checked = true;
		elseif( isset( $args['std'] ) && $args['std'] == $key && ! isset( $quads_options[ $args['id'] ] ) )
			$checked = true;

		echo '<input name="quads_settings[' . $args['id'] . ']"" id="quads_settings[' . $args['id'] . '][' . $key . ']" type="radio" value="' . $key . '" ' . checked(true, $checked, false) . '/>&nbsp;';
		echo '<label for="quads_settings[' . $args['id'] . '][' . $key . ']">' . $option . '</label><br/>';
	endforeach;

	echo '<p class="description quads_hidden">' . $args['desc'] . '</p>';
}

/**
 * Gateways Callback
 *
 * Renders gateways fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $quads_options Array of all the QUADS Options
 * @return void
 */
function quads_gateways_callback( $args ) {
	global $quads_options;

	foreach ( $args['options'] as $key => $option ) :
		if ( isset( $quads_options['gateways'][ $key ] ) )
			$enabled = '1';
		else
			$enabled = null;

		echo '<input name="quads_settings[' . $args['id'] . '][' . $key . ']"" id="quads_settings[' . $args['id'] . '][' . $key . ']" type="checkbox" value="1" ' . checked('1', $enabled, false) . '/>&nbsp;';
		echo '<label for="quads_settings[' . $args['id'] . '][' . $key . ']">' . $option['admin_label'] . '</label><br/>';
	endforeach;
}

/**
 * Dropdown Callback (drop down)
 *
 * Renders gateways select menu
 *
 * @since 1.5
 * @param array $args Arguments passed by the setting
 * @global $quads_options Array of all the QUADS Options
 * @return void
 */
function quads_gateway_select_callback($args) {
	global $quads_options;

	echo '<select name="quads_settings[' . $args['id'] . ']"" id="quads_settings[' . $args['id'] . ']">';

	foreach ( $args['options'] as $key => $option ) :
		$selected = isset( $quads_options[ $args['id'] ] ) ? selected( $key, $quads_options[$args['id']], false ) : '';
		echo '<option value="' . esc_attr( $key ) . '"' . $selected . '>' . esc_html( $option['admin_label'] ) . '</option>';
	endforeach;

	echo '</select>';
	echo '<label for="quads_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';
}

/**
 * Text Callback
 *
 * Renders text fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $quads_options Array of all the QUADS Options
 * @return void
 */
function quads_text_callback( $args ) {
	global $quads_options;

	if ( isset( $quads_options[ $args['id'] ] ) )
		$value = $quads_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="text" class="' . $size . '-text" id="quads_settings[' . $args['id'] . ']" name="quads_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
	$html .= '<label class="quads_hidden" class="quads_hidden" for="quads_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Number Callback
 *
 * Renders number fields.
 *
 * @since 1.9
 * @param array $args Arguments passed by the setting
 * @global $quads_options Array of all the QUADS Options
 * @return void
 */
function quads_number_callback( $args ) {
	global $quads_options;

	if ( isset( $quads_options[ $args['id'] ] ) )
		$value = $quads_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$max  = isset( $args['max'] ) ? $args['max'] : 999999;
	$min  = isset( $args['min'] ) ? $args['min'] : 0;
	$step = isset( $args['step'] ) ? $args['step'] : 1;

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="number" step="' . esc_attr( $step ) . '" max="' . esc_attr( $max ) . '" min="' . esc_attr( $min ) . '" class="' . $size . '-text" id="quads_settings[' . $args['id'] . ']" name="quads_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
	$html .= '<label class="quads_hidden" for="quads_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Textarea Callback
 *
 * Renders textarea fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $quads_options Array of all the QUADS Options
 * @return void
 */
function quads_textarea_callback( $args ) {
	global $quads_options;

	if ( isset( $quads_options[ $args['id'] ] ) )
		$value = $quads_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : '40';
	$html = '<textarea class="large-text quads-textarea" cols="50" rows="' . $size . '" id="quads_settings[' . $args['id'] . ']" name="quads_settings[' . $args['id'] . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
	$html .= '<label class="quads_hidden" for="quads_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Password Callback
 *
 * Renders password fields.
 *
 * @since 1.3
 * @param array $args Arguments passed by the setting
 * @global $quads_options Array of all the QUADS Options
 * @return void
 */
function quads_password_callback( $args ) {
	global $quads_options;

	if ( isset( $quads_options[ $args['id'] ] ) )
		$value = $quads_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="password" class="' . $size . '-text" id="quads_settings[' . $args['id'] . ']" name="quads_settings[' . $args['id'] . ']" value="' . esc_attr( $value ) . '"/>';
	$html .= '<label for="quads_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Missing Callback
 *
 * If a function is missing for settings callbacks alert the user.
 *
 * @since 1.3.1
 * @param array $args Arguments passed by the setting
 * @return void
 */
function quads_missing_callback($args) {
	printf( __( 'The callback function used for the <strong>%s</strong> setting is missing.', 'quads' ), $args['id'] );
}

/**
 * Select Callback
 *
 * Renders select fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $quads_options Array of all the QUADS Options
 * @return void
 */
function quads_select_callback($args) {
	global $quads_options;

	if ( isset( $quads_options[ $args['id'] ] ) )
		$value = $quads_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$html = '<select id="quads_settings[' . $args['id'] . ']" name="quads_settings[' . $args['id'] . ']"/>';

	foreach ( $args['options'] as $option => $name ) :
		$selected = selected( $option, $value, false );
		$html .= '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>';
	endforeach;

	$html .= '</select>';
	$html .= '<label class="quads_hidden" for="quads_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Color select Callback
 *
 * Renders color select fields.
 *
 * @since 2.1.2
 * @param array $args Arguments passed by the setting
 * @global $quads_options Array of all the QUADS Options
 * @return void
 */
/*function quads_color_select_callback( $args ) {
	global $quads_options;

	if ( isset( $quads_options[ $args['id'] ] ) )
		$value = $quads_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$html = '<select id="quads_settings[' . $args['id'] . ']" name="quads_settings[' . $args['id'] . ']"/>';

	foreach ( $args['options'] as $option => $color ) :
		$selected = selected( $option, $value, false );
		$html .= '<option value="' . $option . '" ' . $selected . '>' . $color['label'] . '</option>';
	endforeach;

	$html .= '</select>';
	$html .= '<label for="quads_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}*/

function quads_color_select_callback( $args ) {
	global $quads_options;
        
        if ( isset( $quads_options[ $args['id'] ] ) )
		$value = $quads_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$html = '<strong>#:</strong><input type="text" style="max-width:80px;border:1px solid #' . esc_attr( stripslashes( $value ) ) . ';border-right:20px solid #' . esc_attr( stripslashes( $value ) ) . ';" id="quads_settings[' . $args['id'] . ']" class="medium-text ' . $args['id'] . '" name="quads_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';

	$html .= '</select>';
	$html .= '<label class="quads_hidden" for="quads_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Rich Editor Callback
 *
 * Renders rich editor fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $quads_options Array of all the QUADS Options
 * @global $wp_version WordPress Version
 */
function quads_rich_editor_callback( $args ) {
	global $quads_options, $wp_version;
	if ( isset( $quads_options[ $args['id'] ] ) )
		$value = $quads_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	if ( $wp_version >= 3.3 && function_exists( 'wp_editor' ) ) {
		ob_start();
		wp_editor( stripslashes( $value ), 'quads_settings_' . $args['id'], array( 'textarea_name' => 'quads_settings[' . $args['id'] . ']', 'textarea_rows' => $args['textarea_rows'] ) );
		$html = ob_get_clean();
	} else {
		$html = '<textarea class="large-text quads-richeditor" rows="10" id="quads_settings[' . $args['id'] . ']" name="quads_settings[' . $args['id'] . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
	}

	$html .= '<br/><label class="quads_hidden" for="quads_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Upload Callback
 *
 * Renders upload fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $quads_options Array of all the QUADS Options
 * @return void
 */
function quads_upload_callback( $args ) {
	global $quads_options;

	if ( isset( $quads_options[ $args['id'] ] ) )
		$value = $quads_options[$args['id']];
	else
		$value = isset($args['std']) ? $args['std'] : '';

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="text" class="' . $size . '-text quads_upload_field" id="quads_settings[' . $args['id'] . ']" name="quads_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
	$html .= '<span>&nbsp;<input type="button" class="quads_settings_upload_button button-secondary" value="' . __( 'Upload File', 'quads' ) . '"/></span>';
	$html .= '<label class="quads_hidden" for="quads_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}


/**
 * Color picker Callback
 *
 * Renders color picker fields.
 *
 * @since 1.6
 * @param array $args Arguments passed by the setting
 * @global $quads_options Array of all the QUADS Options
 * @return void
 */
function quads_color_callback( $args ) {
	global $quads_options;

	if ( isset( $quads_options[ $args['id'] ] ) )
		$value = $quads_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$default = isset( $args['std'] ) ? $args['std'] : '';

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="text" class="quads-color-picker" id="quads_settings[' . $args['id'] . ']" name="quads_settings[' . $args['id'] . ']" value="' . esc_attr( $value ) . '" data-default-color="' . esc_attr( $default ) . '" />';
	$html .= '<label class="quads_hidden" for="quads_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}


/**
 * Registers the license field callback for Software Licensing
 *
 * @since 1.5
 * @param array $args Arguments passed by the setting
 * @global $quads_options Array of all the QUADS Options
 * @return void
 */
if ( ! function_exists( 'quads_license_key_callback' ) ) {
	function quads_license_key_callback( $args ) {
		global $quads_options;

		if ( isset( $quads_options[ $args['id'] ] ) )
			$value = $quads_options[ $args['id'] ];
		else
			$value = isset( $args['std'] ) ? $args['std'] : '';

		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$html = '<input type="text" class="' . $size . '-text" id="quads_settings[' . $args['id'] . ']" name="quads_settings[' . $args['id'] . ']" value="' . esc_attr( $value ) . '"/>';

		if ( 'valid' == get_option( $args['options']['is_valid_license_option'] ) ) {
			$html .= '<input type="submit" class="button-secondary" name="' . $args['id'] . '_deactivate" value="' . __( 'Deactivate License',  'quads' ) . '"/>';
                        $html .= '<span style="font-weight:bold;color:green;"> License key activated! </span> <p style="color:green;font-size:13px;"> You´ll get updates for this Add-On automatically!</p>';
                } else {
                    $html .= '<span style="color:red;"> License key not activated!</span style=""><p style="font-size:13px;font-weight:bold;">You´ll get no important security and feature updates for this Add-On!</p>';
                }
		$html .= '<label for="quads_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

                wp_nonce_field( $args['id'] . '-nonce', $args['id'] . '-nonce' );
                
		echo $html;
	}
}



/**
 * Registers the Add-Ons field callback for Quick AdSense Reloaded Add-Ons
 *
 * @since 2.0.5
 * @param array $args Arguments passed by the setting
 * @return html
 */
function quads_addons_callback( $args ) {
	$html = quads_add_ons_page();
	echo $html;
}

/**
 * Registers the image upload field
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $quads_options Array of all the QUADS Options
 * @return void
 */

	function quads_upload_image_callback( $args ) {
		global $quads_options;

		if ( isset( $quads_options[ $args['id'] ] ) )
			$value = $quads_options[ $args['id'] ];
		else
			$value = isset( $args['std'] ) ? $args['std'] : '';

		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$html = '<input type="text" class="' . $size . '-text ' . $args['id'] . '" id="quads_settings[' . $args['id'] . ']" name="quads_settings[' . $args['id'] . ']" value="' . esc_attr( $value ) . '"/>';
	
		$html .= '<input type="submit" class="button-secondary quads_upload_image" name="' . $args['id'] . '_upload" value="' . __( 'Select Image',  'quads' ) . '"/>';
		
		$html .= '<label class="quads_hidden" for="quads_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

		echo $html;
	}
        
        
/* 
 * Post Types Callback
 * 
 * Adds a multiple choice drop box
 * for selecting where Quick AdSense Reloaded should be enabled
 * 
 * @since 2.0.9
 * @param array $args Arguments passed by the setting
 * @return void
 * 
 */

function quads_posttypes_callback ($args){
  global $quads_options;
  $posttypes = get_post_types();

  //if ( ! empty( $args['options'] ) ) {
  if ( ! empty( $posttypes ) ) {
		//foreach( $args['options'] as $key => $option ):
                foreach( $posttypes as $key => $option ):
			if( isset( $quads_options[$args['id']][$key] ) ) { $enabled = $option; } else { $enabled = NULL; }
			echo '<input name="quads_settings[' . $args['id'] . '][' . $key . ']" id="quads_settings[' . $args['id'] . '][' . $key . ']" type="checkbox" value="' . $option . '" ' . checked($option, $enabled, false) . '/>&nbsp;';
			echo '<label for="quads_settings[' . $args['id'] . '][' . $key . ']">' . $option . '</label><br/>';
		endforeach;
		echo '<p class="description quads_hidden">' . $args['desc'] . '</p>';
	}
}

/* 
 * Note Callback
 * 
 * Show a note
 * 
 * @since 2.2.8
 * @param array $args Arguments passed by the setting
 * @return void
 * 
 */

function quads_note_callback ($args){
  global $quads_options;
  //$html = !empty($args['desc']) ? $args['desc'] : '';
  $html = '';
  echo $html;
}

/**
 * Additional content Callback 
 * Adds several content text boxes selectable via jQuery easytabs() 
 *
 * @param array $args
 * @return string $html
 * @scince 2.3.2
 */

function quads_add_content_callback($args){
    	global $quads_options;

        $html = '<div id="quadstabcontainer" class="tabcontent_container"><ul class="quadstabs" style="width:99%;max-width:500px;">';
            foreach ( $args['options'] as $option => $name ) :
                    $html .= '<li class="quadstab" style="float:left;margin-right:4px;"><a href="#'.$name['id'].'">'.$name['name'].'</a></li>';
            endforeach;
        $html .= '</ul>';
        $html .= '<div class="quadstab-container">';
            foreach ( $args['options'] as $option => $name ) :
                    $value = isset($quads_options[$name['id']]) ? $quads_options[ $name['id']] : '';
                    $textarea = '<textarea class="large-text quads-textarea" cols="50" rows="15" id="quads_settings['. $name['id'] .']" name="quads_settings['.$name['id'].']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
                    $html .= '<div id="'.$name['id'].'" style="max-width:500px;"><span style="padding-top:60px;display:block;">' . $name['desc'] . ':</span><br>' . $textarea . '</div>';
            endforeach;
        $html .= '</div>';
        $html .= '</div>';
	echo $html;
}

        
/**
 * Hook Callback
 *
 * Adds a do_action() hook in place of the field
 *
 * @since 1.0.8.2
 * @param array $args Arguments passed by the setting
 * @return void
 */
function quads_hook_callback( $args ) {
	do_action( 'quads_' . $args['id'] );
}

/**
 * Set manage_options as the cap required to save QUADS settings pages
 *
 * @since 1.9
 * @return string capability required
 */
function quads_set_settings_cap() {
	return 'manage_options';
}
add_filter( 'option_page_capability_quads_settings', 'quads_set_settings_cap' );


/* returns array with amount of available services
 * @since 2.0
 * @return array
 */

function numberServices(){
    $number = 1;
    $array = array();
    while ($number <= count(quads_get_networks_list())){
        $array[] = $number++; 

    }
    $array['all'] = __('All Services');
    return apply_filters('quads_return_services', $array);
}

/* Purge the Quick AdSense Reloaded 
 * database quads_TABLE
 * 
 * @since 2.0.4
 * @return string
 */

function quads_delete_cache_objects(){
    global $quads_options, $wpdb;
    if (isset($quads_options['delete_cache_objects'])){
        //$sql = "TRUNCATE TABLE " . quads_TABLE;
        //require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        //$wpdb->query($sql);
        delete_post_meta_by_key( 'quads_timestamp' );
        delete_post_meta_by_key( 'quads_shares' ); 
        delete_post_meta_by_key( 'quads_jsonshares' );
        return ' <strong style="color:red;">' . __('DB cache deleted! Do not forget to uncheck this box for performance increase after doing the job.', 'quads') . '</strong> ';
    }
}

/* returns Cache Status if enabled or disabled
 *
 * @since 2.0.4
 * @return string
 */

function quads_cache_status(){
    global $quads_options;
    if (isset($quads_options['disable_cache'])){
        return ' <strong style="color:red;">' . __('Transient Cache disabled! Enable it for performance increase.' , 'quads') . '</strong> ';
    }
}

/* Permission check if logfile is writable
 *
 * @since 2.0.6
 * @return string
 */

function quads_log_permissions(){
    global $quads_options;
    if (!QUADS()->logger->checkDir() ){
        return '<br><strong style="color:red;">' . __('Log file directory not writable! Set FTP permission to 755 or 777 for /wp-content/plugins/quadssharer/logs/', 'quads') . '</strong> <br> Read here more about <a href="http://codex.wordpress.org/Changing_File_Permissions" target="_blank">file permissions</a> ';
    }
}
