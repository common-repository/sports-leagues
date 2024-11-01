<?php
/**
 * Template Loader
 * Sports Leagues :: Template.
 *
 * @since   0.1.0
 * @package Sports_Leagues
 */

class Sports_Leagues_Template extends Gamajo_Template_Loader {

	/**
	 * Parent plugin class.
	 *
	 * @var Sports_Leagues
	 * @since  0.1.0
	 */
	protected $plugin = null;

	/**
	 * Reference to the root directory path of this plugin.
	 * Can either be a defined constant, or a relative reference from where the subclass lives.
	 *
	 * @since 0.1.0
	 * @var string
	 */
	protected $plugin_directory = null;

	/**
	 * Prefix for filter names.
	 *
	 * @since 0.1.0
	 * @var string
	 */
	protected $filter_prefix = 'sl';

	/**
	 * Directory name where custom templates for this plugin should be found in the theme.
	 *
	 * @since 0.1.0
	 * @var string
	 */
	protected $theme_template_directory = 'sports-leagues';


	/**
	 * Constructor.
	 *
	 * @since  0.1.0
	 *
	 * @param  Sports_Leagues $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {

		$this->plugin           = $plugin;
		$this->plugin_directory = $this->plugin->path;

		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.1.0
	 */
	public function hooks() {

		/**
		 * Template loader
		 *
		 * @since 0.1.0
		 */
		add_filter( 'the_content', [ $this, 'content_loader' ] );

		/**
		 * Override single layout file.
		 * more info - /wp-includes/template.php:62
		 *
		 * @since 0.5.3
		 */
		add_filter( 'single_template', [ $this, 'template_loader' ] );
	}

	/**
	 * Load CPT content
	 *
	 * @param string $content Content of the current post.
	 *
	 * @global       $post    WP_Post
	 *
	 * @return string
	 * @since 0.1.0
	 */
	public function content_loader( $content ) {

		global $post;

		if ( $post && in_array( $post->post_type, $this->plugin->get_post_types(), true ) && is_singular() && is_main_query() ) {

			if ( apply_filters( 'sports-leagues/template/load_default_template', true, $post->post_type, $post ) ) {
				// Prepare layout
				$layout = get_post_meta( $post->ID, '_sl_tmpl_layout', true );
				$layout = empty( $layout ) ? '' : ( '-' . sanitize_key( $layout ) );

				if ( empty( $layout ) && 'sl_tournament' === $post->post_type && apply_filters( 'sports-leagues/template/load_tournament_v1', false ) ) {
					$layout = '-v1';
				}

				$this->get_template_part( 'content-' . str_replace( 'sl_', '', $post->post_type ), $layout );
			} else {
				do_action( 'sports-leagues/template/load_alt_template', $post->post_type, $post );
			}

			// Disable default content
			return '';
		}

		return $content;
	}

	/**
	 * Load shortcodes
	 *
	 * @param string $shortcode
	 * @param mixed  $atts
	 *
	 *
	 * @return string
	 * @since 0.1.0
	 */
	public function shortcode_loader( $shortcode, $atts ) {

		// Start an output buffer.
		ob_start();

		// Convert options to object
		$atts = (object) $atts;

		// Check layout (add extra dash)
		$layout = empty( $atts->layout ) ? '' : ( '-' . sanitize_key( $atts->layout ) );

		$this->set_template_data( $atts )->get_template_part( 'shortcode-' . sanitize_key( $shortcode ), $layout );

		// Return the output buffer.
		return ob_get_clean();
	}

	/**
	 * Load widget content
	 *
	 * @param string $widget
	 * @param array  $atts
	 *
	 *
	 * @return string
	 * @since 0.1.0
	 */
	public function widget_loader( $widget, $atts ) {

		// Start an output buffer.
		ob_start();

		// Start widget markup.
		echo $atts['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		// Maybe display widget title.
		echo ( $atts['title'] ) ? $atts['before_title'] . esc_html( $atts['title'] ) . $atts['after_title'] : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		// Check widget layout (add extra dash for layout)
		$layout = empty( $atts['layout'] ) ? '' : ( '-' . sanitize_key( $atts['layout'] ) );

		$this->set_template_data( $atts )->get_template_part( 'widget-' . sanitize_key( $widget ), $layout );

		// End the widget markup.
		echo $atts['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		// Return the output buffer.
		return ob_get_clean();
	}

	/**
	 * Get template file to load.
	 *
	 * @param string $template The path of the template to include.
	 *
	 * @since 0.5.3
	 * @return string
	 */
	public function template_loader( $template ) {

		$current_theme = get_template();
		$new_template  = '';

		$available_post_types = sports_leagues()->get_post_types();

		$alternative_layout = Sports_Leagues_Customizer::get_value( 'general', 'load_alternative_page_layout' );

		if ( $alternative_layout ) {
			if ( in_array( get_post_type(), $available_post_types, true ) ) {
				$new_template = sports_leagues()->path . 'theme-support/layout-' . sanitize_key( $alternative_layout ) . '.php';
			}
		}

		if ( 'twentysixteen' === $current_theme ) {
			if ( in_array( get_post_type(), $available_post_types, true ) ) {
				$new_template = sports_leagues()->path . 'theme-support/twentysixteen/single.php';
			}
		}

		return ( $new_template && file_exists( $new_template ) ) ? $new_template : $template;
	}

	/**
	 * Magic getter for our object.
	 *
	 * @since  0.1.0
	 *
	 * @param  string $field Field to get.
	 *
	 * @throws Exception     Throws an exception if the field is invalid.
	 * @return mixed         Value of the field.
	 */
	public function __get( $field ) {

		if ( property_exists( $this, $field ) ) {
			return $this->$field;
		}

		throw new Exception( 'Invalid ' . __CLASS__ . ' property: ' . $field );
	}

}
