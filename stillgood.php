<?php
/**
 * Plugin Name:       StillGood
 * Description:       Displays a customizable badge before older evergreen posts that are still relevant, with per-post exclusion.
 * Version:           1.2.1
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Cristiano Ferrari
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       stillgood
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'STILLGOOD_VERSION', '1.2.1' );
define( 'STILLGOOD_OPTION_KEY', 'stillgood_options' );
define( 'STILLGOOD_META_KEY', '_stillgood_exclude' );

/**
 * Loads local translation files from the /languages directory.
 */
add_action(
	'init',
	function () {
		load_plugin_textdomain(
			'stillgood',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);
	}
);

/**
 * Returns plugin options merged with defaults.
 *
 * @return array
 */
function stillgood_get_options() {
	$defaults = array(
		'min_years'    => 5,
		'badge_text'   => __( 'This article is a few years old, but it is still relevant.', 'stillgood' ),
		'bg_color'     => '#f6f7f9',
		'border_color' => '#2563eb',
		'text_color'   => '#1f2937',
	);

	$saved = get_option( STILLGOOD_OPTION_KEY, array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}

	return wp_parse_args( $saved, $defaults );
}

/**
 * Adds a Settings link on the Plugins screen.
 */
add_filter(
	'plugin_action_links_' . plugin_basename( __FILE__ ),
	function ( $links ) {
		$url  = admin_url( 'options-general.php?page=stillgood' );
		$link = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'stillgood' ) . '</a>';
		array_unshift( $links, $link );
		return $links;
	}
);

/**
 * Registers the Settings page under Settings -> StillGood.
 */
add_action(
	'admin_menu',
	function () {
		add_options_page(
			'StillGood',
			'StillGood',
			'manage_options',
			'stillgood',
			'stillgood_render_settings_page'
		);
	}
);

/**
 * Registers settings, section, and fields via the Settings API.
 */
add_action(
	'admin_init',
	function () {
		register_setting(
			'stillgood_settings_group',
			STILLGOOD_OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => 'stillgood_sanitize_options',
			)
		);

		add_settings_section(
			'stillgood_main_section',
			__( 'Badge appearance and rules', 'stillgood' ),
			function () {
				echo '<p>' . esc_html__( 'Choose after how many years the badge appears and customize its text and colors.', 'stillgood' ) . '</p>';
			},
			'stillgood'
		);

		add_settings_field(
			'stillgood_min_years',
			__( 'Show after (years)', 'stillgood' ),
			'stillgood_field_min_years',
			'stillgood',
			'stillgood_main_section'
		);

		add_settings_field(
			'stillgood_badge_text',
			__( 'Badge text', 'stillgood' ),
			'stillgood_field_badge_text',
			'stillgood',
			'stillgood_main_section'
		);

		add_settings_field(
			'stillgood_colors',
			__( 'Colors', 'stillgood' ),
			'stillgood_field_colors',
			'stillgood',
			'stillgood_main_section'
		);
	}
);

/**
 * Sanitizes saved options.
 *
 * @param mixed $input Submitted form values.
 * @return array
 */
function stillgood_sanitize_options( $input ) {
	$defaults = stillgood_get_options();
	$input    = is_array( $input ) ? $input : array();

	$min_years = isset( $input['min_years'] ) ? absint( $input['min_years'] ) : $defaults['min_years'];
	if ( $min_years < 1 ) {
		$min_years = 1;
	}

	$badge_text = isset( $input['badge_text'] ) ? sanitize_text_field( $input['badge_text'] ) : $defaults['badge_text'];
	if ( '' === trim( $badge_text ) ) {
		$badge_text = $defaults['badge_text'];
	}

	$bg_color     = isset( $input['bg_color'] ) ? sanitize_hex_color( $input['bg_color'] ) : '';
	$border_color = isset( $input['border_color'] ) ? sanitize_hex_color( $input['border_color'] ) : '';
	$text_color   = isset( $input['text_color'] ) ? sanitize_hex_color( $input['text_color'] ) : '';

	return array(
		'min_years'    => $min_years,
		'badge_text'   => $badge_text,
		'bg_color'     => $bg_color ? $bg_color : '#f6f7f9',
		'border_color' => $border_color ? $border_color : '#2563eb',
		'text_color'   => $text_color ? $text_color : '#1f2937',
	);
}

/**
 * Field: minimum years threshold.
 */
function stillgood_field_min_years() {
	$options = stillgood_get_options();
	?>
	<input
		type="number"
		min="1"
		max="50"
		name="<?php echo esc_attr( STILLGOOD_OPTION_KEY ); ?>[min_years]"
		value="<?php echo esc_attr( $options['min_years'] ); ?>"
		class="small-text"
	/>
	<p class="description">
		<?php esc_html_e( 'The badge only appears on posts published at least this many years ago.', 'stillgood' ); ?>
	</p>
	<?php
}

/**
 * Field: badge text.
 */
function stillgood_field_badge_text() {
	$options = stillgood_get_options();
	?>
	<input
		type="text"
		name="<?php echo esc_attr( STILLGOOD_OPTION_KEY ); ?>[badge_text]"
		value="<?php echo esc_attr( $options['badge_text'] ); ?>"
		class="large-text"
	/>
	<p class="description">
		<?php esc_html_e( 'Optional: use {years} in the text to automatically insert the actual years since publication (e.g. "This article is over {years} years old, but it is still relevant.").', 'stillgood' ); ?>
	</p>
	<?php
}

/**
 * Field: badge colors.
 */
function stillgood_field_colors() {
	$options = stillgood_get_options();
	?>
	<div style="display:flex;flex-wrap:wrap;gap:20px;align-items:center;">
		<label style="display:flex;align-items:center;gap:8px;">
			<input type="color" name="<?php echo esc_attr( STILLGOOD_OPTION_KEY ); ?>[bg_color]" value="<?php echo esc_attr( $options['bg_color'] ); ?>" />
			<span><?php esc_html_e( 'Background', 'stillgood' ); ?></span>
		</label>
		<label style="display:flex;align-items:center;gap:8px;">
			<input type="color" name="<?php echo esc_attr( STILLGOOD_OPTION_KEY ); ?>[border_color]" value="<?php echo esc_attr( $options['border_color'] ); ?>" />
			<span><?php esc_html_e( 'Accent border', 'stillgood' ); ?></span>
		</label>
		<label style="display:flex;align-items:center;gap:8px;">
			<input type="color" name="<?php echo esc_attr( STILLGOOD_OPTION_KEY ); ?>[text_color]" value="<?php echo esc_attr( $options['text_color'] ); ?>" />
			<span><?php esc_html_e( 'Text', 'stillgood' ); ?></span>
		</label>
	</div>
	<?php
}

/**
 * Renders the Settings page.
 */
function stillgood_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1>StillGood</h1>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'stillgood_settings_group' );
			do_settings_sections( 'stillgood' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

/**
 * Adds the exclusion meta box to the post editor sidebar.
 */
add_action(
	'add_meta_boxes',
	function () {
		add_meta_box(
			'stillgood_exclude_box',
			'StillGood',
			'stillgood_render_meta_box',
			'post',
			'side',
			'default'
		);
	}
);

/**
 * Renders the exclusion meta box.
 *
 * @param WP_Post $post Current post object.
 */
function stillgood_render_meta_box( $post ) {
	wp_nonce_field( 'stillgood_save_exclude', 'stillgood_nonce' );
	$excluded = get_post_meta( $post->ID, STILLGOOD_META_KEY, true );
	?>
	<label for="stillgood_exclude" style="display:flex;align-items:flex-start;gap:8px;cursor:pointer;">
		<input
			type="checkbox"
			id="stillgood_exclude"
			name="stillgood_exclude"
			value="1"
			<?php checked( $excluded, '1' ); ?>
		/>
		<span><?php esc_html_e( 'Hide the badge on this post', 'stillgood' ); ?></span>
	</label>
	<?php
}

/**
 * Saves the per-post exclusion checkbox.
 */
add_action(
	'save_post_post',
	function ( $post_id ) {
		if ( ! isset( $_POST['stillgood_nonce'] ) ) {
			return;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST['stillgood_nonce'] ) );
		if ( ! wp_verify_nonce( $nonce, 'stillgood_save_exclude' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['stillgood_exclude'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['stillgood_exclude'] ) ) ) {
			update_post_meta( $post_id, STILLGOOD_META_KEY, '1' );
		} else {
			delete_post_meta( $post_id, STILLGOOD_META_KEY );
		}
	}
);

/**
 * Checks whether a post qualifies for the badge and returns its age in full years.
 *
 * @param int $post_id Post ID.
 * @return int|false Number of full years if eligible, false otherwise.
 */
function stillgood_get_post_years_if_eligible( $post_id ) {
	if ( ! $post_id || '1' === get_post_meta( $post_id, STILLGOOD_META_KEY, true ) ) {
		return false;
	}

	$published = get_post_datetime( $post_id, 'date' );
	if ( ! $published ) {
		return false;
	}

	$now     = current_datetime();
	$diff    = $published->diff( $now );
	$options = stillgood_get_options();

	if ( $diff->invert || $diff->y < (int) $options['min_years'] ) {
		return false;
	}

	return (int) $diff->y;
}

/**
 * Prepends the badge to eligible single posts.
 */
add_filter(
	'the_content',
	function ( $content ) {
		if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		$post_id = get_the_ID();
		$years   = stillgood_get_post_years_if_eligible( $post_id );

		if ( false === $years ) {
			return $content;
		}

		$options = stillgood_get_options();
		$text    = str_replace( '{years}', (string) $years, $options['badge_text'] );

		$badge = sprintf(
			'<aside class="stillgood-badge" role="note" aria-label="%s"><p>%s</p></aside>',
			esc_attr__( 'Still relevant content notice', 'stillgood' ),
			esc_html( $text )
		);

		return $badge . $content;
	},
	5
);

/**
 * Enqueues inline styles via official WordPress APIs, only on eligible posts.
 */
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( ! is_singular( 'post' ) ) {
			return;
		}

		$post_id = get_queried_object_id();
		if ( false === stillgood_get_post_years_if_eligible( $post_id ) ) {
			return;
		}

		$options = stillgood_get_options();
		$bg      = sanitize_hex_color( $options['bg_color'] ) ?: '#f6f7f9';
		$border  = sanitize_hex_color( $options['border_color'] ) ?: '#2563eb';
		$color   = sanitize_hex_color( $options['text_color'] ) ?: '#1f2937';

		$css = sprintf(
			'.stillgood-badge{margin:0 0 1.75rem;padding:0.9rem 1.2rem;background-color:%1$s;border-left:4px solid %2$s;border-radius:4px;color:%3$s;font-size:0.95rem;line-height:1.5;}.stillgood-badge p{margin:0;}',
			$bg,
			$border,
			$color
		);

		wp_register_style( 'stillgood-style', false, array(), STILLGOOD_VERSION );
		wp_enqueue_style( 'stillgood-style' );
		wp_add_inline_style( 'stillgood-style', $css );
	}
);
