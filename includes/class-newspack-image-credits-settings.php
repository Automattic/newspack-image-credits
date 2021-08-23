<?php
/**
 * Settings Page.
 *
 * @package Newspack_Image_Credits
 */
class Newspack_Image_Credits_Settings {
	/**
	 * Set up hooks.
	 */
	public static function init() {
		add_action( 'admin_init', [ __CLASS__, 'page_init' ] );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_scripts' ] );
	}

	/**
	 * Default values for site-wide settings.
	 *
	 * @return array Array of default settings.
	 */
	public static function get_default_settings() {
		return [
			[
				'description' => __( 'A CSS class name to be applied to all image credit elements. Leave blank to display no class name.', 'newspack-image-credits' ),
				'key'         => 'newspack_image_credits_class_name',
				'label'       => __( 'Image Credit Class Name', 'newspack-image-credits' ),
				'type'        => 'input',
				'value'       => __( 'image-credit', 'newspack-image-credits' ),
			],
			[
				'description' => __( 'A label to prefix all image credits. Leave blank to display no prefix.', 'newspack-image-credits' ),
				'key'         => 'newspack_image_credits_prefix_label',
				'label'       => __( 'Image Credit Label', 'newpack-listings' ),
				'type'        => 'input',
				'value'       => __( 'Credit:', 'newspack-image-credits' ),
			],
			[
				'description' => __( 'A placeholder image to be displayed in place of images without credits. If none is chosen, the image will be displayed normally whether or not it has a credit.', 'newspack-image-credits' ),
				'key'         => 'newspack_image_credits_placeholder',
				'label'       => __( 'Placeholder Image', 'newpack-listings' ),
				'type'        => 'image',
				'value'       => null,
			],
		];
	}

	/**
	 * Get current site-wide settings, or defaults if not set.
	 *
	 * @param string|null $option (Optional) Key name of a single setting to get. If not given, will return all settings.
	 * @param boolean     $get_default (Optional) If true, return the default value.
	 *
	 * @return array|boolean Array of current site-wide settings, or false if returning a single option with no value.
	 */
	public static function get_settings( $option = null, $get_default = false ) {
		$defaults = self::get_default_settings();

		$settings = array_reduce(
			$defaults,
			function( $acc, $setting ) use ( $get_default ) {
				$key         = $setting['key'];
				$value       = $get_default ? $setting['value'] : get_option( $key, $setting['value'] );
				$acc[ $key ] = $value;
				return $acc;
			},
			[]
		);

		// If passed an option key name, just give that option.
		if ( ! empty( $option ) ) {
			return $settings[ $option ];
		}

		// Otherwise, return all settings.
		return $settings;
	}

	/**
	 * Options page callback
	 */
	public static function create_admin_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Newspack Image Credits: Settings', 'newspack-image-credits' ); ?></h1>
			<form method="post" action="options.php">
			<?php
				settings_fields( 'newspack_image_credits_options_group' );
				do_settings_sections( 'newspack-image-credits-settings-admin' );
				submit_button();
			?>
			</form>
		</div>
		<?php
	}

	/**
	 * Enqueue scripts for settings page.
	 */
	public static function enqueue_scripts( $hook_suffix ) {
		if ( 'options-media.php' === $hook_suffix ) {
			wp_enqueue_media();
			wp_enqueue_script(
				'newspack-image-credits-admin',
				NEWSPACK_IMAGE_CREDITS_URL . 'dist/admin.js',
				[ 'jquery' ],
				filemtime( NEWSPACK_IMAGE_CREDITS_PLUGIN_FILE . 'dist/admin.js' ),
				true
			);
		}
	}

	/**
	 * Register and add settings
	 */
	public static function page_init() {
		add_settings_section(
			'newspack_image_credits_options_group',
			__( 'Image Credits', 'newspack-image-credits' ),
			null,
			'media'
		);
		foreach ( self::get_default_settings() as $setting ) {
			register_setting(
				'newspack_image_credits_options_group',
				$setting['key']
			);
			add_settings_field(
				$setting['key'],
				$setting['label'],
				[ __CLASS__, 'newspack_image_credits_settings_callback' ],
				'media',
				'newspack_image_credits_options_group',
				$setting
			);
		};
	}

	/**
	 * Render settings fields.
	 *
	 * @param array $setting Settings array.
	 */
	public static function newspack_image_credits_settings_callback( $setting ) {
		$key   = $setting['key'];
		$type  = $setting['type'];
		$value = get_option( $key, $setting['value'] );

		if ( 'checkbox' === $type ) {
			printf(
				'<input type="checkbox" id="%s" name="%s" %s /><p class="description" for="%s">%s</p>',
				esc_attr( $key ),
				esc_attr( $key ),
				! empty( $value ) ? 'checked' : '',
				esc_attr( $key ),
				esc_html( $setting['description'] )
			);
		} elseif ( 'image' === $type ) {
			printf(
				'<div class="image-setting-wrapper"><input type="hidden" id="%s" name="%s" value="%s" />%s <p>%s %s</p><p class="description" for="%s">%s</p></div>',
				esc_attr( $key ),
				esc_attr( $key ),
				! empty( $value ) ? esc_attr( $value ) : '',
				! empty( $value ) ?
					wp_get_attachment_image(
						$value,
						'medium',
						false,
						[
							'class'   => 'newspack-image-credits-placeholder-preview',
							'data-id' => $value,
						]
					) :
					'',
				'<input name="choose-image" type="button" class="button button-primary" style="margin-right: 4px" value="' . __( 'Choose Image', 'newspack-image-credits' ) . '" />',
				! empty( $value ) ? '<input name="clear-image" type="button" class="button" value="' . __( 'Clear Image', 'newspack-image-credits' ) . '" />' : '',
				esc_attr( $key ),
				esc_html( $setting['description'] )
			);
		} else {
			printf(
				'<input type="text" id="%s" name="%s" value="%s" class="regular-text" /><p class="description" for="%s">%s</p>',
				esc_attr( $key ),
				esc_attr( $key ),
				esc_attr( $value ),
				esc_attr( $key ),
				esc_html( $setting['description'] )
			);
		}
	}
}

if ( is_admin() ) {
	Newspack_Image_Credits_Settings::init();
}
