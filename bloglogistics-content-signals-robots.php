<?php
/**
 * Plugin Name:       BlogLogistics Content Signals for Robots.txt
 * Plugin URI:        https://github.com/bloglogisticsdev/bloglogistics-content-signals-robots
 * Description:       Safely manages website-use preference signals in a physical robots.txt file.
 * Version:           1.0.0
 * Requires at least: 7.0
 * Requires PHP:      8.3
 * Author:            BlogLogistics
 * Author URI:        https://www.bloglogistics.com/
 * License:           GPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Update URI:        https://github.com/bloglogisticsdev/bloglogistics-content-signals-robots
 * Text Domain:       bloglogistics-content-signals-robots
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BLOGLOGISTICS_CSR_VERSION', '1.0.0' );
define( 'BLOGLOGISTICS_CSR_SLUG', 'bloglogistics-content-signals-robots' );
define( 'BLOGLOGISTICS_CSR_FILE', __FILE__ );
define( 'BLOGLOGISTICS_CSR_DIR', plugin_dir_path( __FILE__ ) );
define( 'BLOGLOGISTICS_CSR_REPO_URL', 'https://github.com/bloglogisticsdev/bloglogistics-content-signals-robots/' );
define( 'BLOGLOGISTICS_CSR_UPDATE_MANIFEST_URL', 'https://updates.bloglogistics.com/plugins/bloglogistics-content-signals-robots.json' );

$bloglogistics_csr_puc = BLOGLOGISTICS_CSR_DIR . 'vendor/plugin-update-checker/plugin-update-checker.php';

if ( file_exists( $bloglogistics_csr_puc ) ) {
	if ( ! class_exists( '\\YahnisElsts\\PluginUpdateChecker\\v5\\PucFactory', false ) ) {
		require_once $bloglogistics_csr_puc;
	}

	require_once BLOGLOGISTICS_CSR_DIR . 'includes/class-bloglogistics-content-signals-robots-updater.php';

	if ( class_exists( 'BlogLogistics_Content_Signals_Robots_Updater', false ) ) {
		BlogLogistics_Content_Signals_Robots_Updater::init(
			array(
				'repo_url'    => BLOGLOGISTICS_CSR_UPDATE_MANIFEST_URL,
				'plugin_file' => BLOGLOGISTICS_CSR_FILE,
				'slug'        => BLOGLOGISTICS_CSR_SLUG,
			)
		);
	}
}

if ( ! class_exists( 'BlogLogistics_Content_Signals_Robots', false ) ) {

	/**
	 * Manage the Content-Signal line in a physical robots.txt file.
	 */
	final class BlogLogistics_Content_Signals_Robots {

		private const OPTION_NAME = 'bloglogistics_csr_options';
		private const MAX_BACKUPS = 5;

		/**
		 * Register hooks.
		 */
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
			add_action( 'admin_post_bloglogistics_csr_save', array( $this, 'handle_save' ) );
			add_action( 'admin_post_bloglogistics_csr_restore_defaults', array( $this, 'handle_restore_defaults' ) );
		}

		/**
		 * Plugin defaults.
		 *
		 * @return array<string, mixed>
		 */
		public static function defaults(): array {
			return array(
				'enabled'                => true,
				'allow_search'           => true,
				'allow_ai_answers'       => true,
				'allow_ai_training'      => false,
				'original_captured'      => false,
				'original_signal_line'   => '',
				'original_signal_exists' => false,
			);
		}

		/**
		 * Plugin activation.
		 */
		public static function activate(): void {
			$options = get_option( self::OPTION_NAME, array() );

			if ( ! is_array( $options ) ) {
				$options = array();
			}

			$options = wp_parse_args( $options, self::defaults() );
			update_option( self::OPTION_NAME, $options );

			$instance = new self();
			$instance->apply_preferences( $options );
		}

		/**
		 * Get the physical robots.txt path.
		 */
		private function get_robots_path(): string {
			return trailingslashit( ABSPATH ) . 'robots.txt';
		}

		/**
		 * Get merged options.
		 *
		 * @return array<string, mixed>
		 */
		private function get_options(): array {
			$options = get_option( self::OPTION_NAME, array() );

			if ( ! is_array( $options ) ) {
				$options = array();
			}

			return wp_parse_args( $options, self::defaults() );
		}

		/**
		 * Add the BlogLogistics admin menu and this plugin's settings page.
		 */
		public function add_admin_menu(): void {
			$this->register_bloglogistics_parent_menu();

			add_submenu_page(
				'bloglogistics',
				esc_html__( 'Robots.txt Content Preferences', 'bloglogistics-content-signals-robots' ),
				esc_html__( 'Robots.txt Content Preferences', 'bloglogistics-content-signals-robots' ),
				'manage_options',
				'bloglogistics-content-signals-robots',
				array( $this, 'render_settings_page' )
			);
		}

		/**
		 * Register the shared BlogLogistics parent menu if another BlogLogistics plugin has not already done so.
		 */
		private function register_bloglogistics_parent_menu(): void {
			if ( $this->bloglogistics_parent_menu_exists() ) {
				return;
			}

			add_menu_page(
				esc_html__( 'BlogLogistics', 'bloglogistics-content-signals-robots' ),
				esc_html__( 'BlogLogistics', 'bloglogistics-content-signals-robots' ),
				'manage_options',
				'bloglogistics',
				array( $this, 'render_bloglogistics_parent_page' ),
				'dashicons-rss',
				58
			);
		}

		/**
		 * Check whether the shared BlogLogistics parent menu already exists.
		 */
		private function bloglogistics_parent_menu_exists(): bool {
			global $menu;

			if ( ! is_array( $menu ) ) {
				return false;
			}

			foreach ( $menu as $item ) {
				if ( isset( $item[2] ) && 'bloglogistics' === $item[2] ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Shared parent menu page.
		 */
		public function render_bloglogistics_parent_page(): void {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			?>
			<div class="wrap">
				<h1><?php esc_html_e( 'BlogLogistics', 'bloglogistics-content-signals-robots' ); ?></h1>
				<p><?php esc_html_e( 'Use the submenu links to configure installed BlogLogistics plugins.', 'bloglogistics-content-signals-robots' ); ?></p>
			</div>
			<?php
		}

		/**
		 * Render settings page.
		 */
		public function render_settings_page(): void {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$options        = $this->get_options();
			$robots_path    = $this->get_robots_path();
			$file_exists    = file_exists( $robots_path );
			$file_readable  = $file_exists && is_readable( $robots_path );
			$file_writable  = $file_exists && is_writable( $robots_path );
			$line_preview   = $this->build_signal_line( $options );
			$message_code   = isset( $_GET['bloglogistics_csr_message'] ) ? sanitize_key( wp_unslash( $_GET['bloglogistics_csr_message'] ) ) : '';
			?>
			<div class="wrap bloglogistics-csr-wrap">
				<h1><?php esc_html_e( 'Robots.txt Content Preferences', 'bloglogistics-content-signals-robots' ); ?></h1>

				<?php $this->render_message( $message_code ); ?>

				<p><?php esc_html_e( 'This plugin updates one line in your physical robots.txt file. It does not rewrite the whole file. It only manages the preference line placed directly under User-agent: *.', 'bloglogistics-content-signals-robots' ); ?></p>

				<div class="notice notice-warning inline">
					<p><?php esc_html_e( 'These settings publish your website’s preferences in robots.txt. They do not guarantee that every crawler, search engine, or AI system will follow them.', 'bloglogistics-content-signals-robots' ); ?></p>
				</div>

				<h2><?php esc_html_e( 'robots.txt file status', 'bloglogistics-content-signals-robots' ); ?></h2>
				<table class="widefat striped" style="max-width: 900px;">
					<tbody>
						<tr>
							<th scope="row"><?php esc_html_e( 'File path', 'bloglogistics-content-signals-robots' ); ?></th>
							<td><code><?php echo esc_html( $robots_path ); ?></code></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Status', 'bloglogistics-content-signals-robots' ); ?></th>
							<td><?php echo esc_html( $this->get_file_status_text( $file_exists, $file_readable, $file_writable ) ); ?></td>
						</tr>
					</tbody>
				</table>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="max-width: 900px; margin-top: 20px;">
					<input type="hidden" name="action" value="bloglogistics_csr_save" />
					<?php wp_nonce_field( 'bloglogistics_csr_save', 'bloglogistics_csr_nonce' ); ?>

					<h2><?php esc_html_e( 'Settings', 'bloglogistics-content-signals-robots' ); ?></h2>

					<table class="form-table" role="presentation">
						<tbody>
							<tr>
								<th scope="row"><?php esc_html_e( 'Manage robots.txt preferences', 'bloglogistics-content-signals-robots' ); ?></th>
								<td>
									<label>
										<input type="checkbox" name="bloglogistics_csr_enabled" value="1" <?php checked( ! empty( $options['enabled'] ) ); ?> />
										<?php esc_html_e( 'Let this plugin manage website-use preferences in robots.txt.', 'bloglogistics-content-signals-robots' ); ?>
									</label>
									<p class="description"><?php esc_html_e( 'Turn this on to let this plugin add or update the preference line in your physical robots.txt file. Turn it off to restore the original preference line, or remove it if there was not one before.', 'bloglogistics-content-signals-robots' ); ?></p>
								</td>
							</tr>

							<tr>
								<th scope="row"><?php esc_html_e( 'Search results', 'bloglogistics-content-signals-robots' ); ?></th>
								<td>
									<label>
										<input type="checkbox" name="bloglogistics_csr_allow_search" value="1" <?php checked( ! empty( $options['allow_search'] ) ); ?> />
										<?php esc_html_e( 'Allow search engines to show this site in search results.', 'bloglogistics-content-signals-robots' ); ?>
									</label>
									<p class="description"><?php esc_html_e( 'Recommended: On. This allows normal search engines to include the site in search results.', 'bloglogistics-content-signals-robots' ); ?></p>
								</td>
							</tr>

							<tr>
								<th scope="row"><?php esc_html_e( 'AI answers', 'bloglogistics-content-signals-robots' ); ?></th>
								<td>
									<label>
										<input type="checkbox" name="bloglogistics_csr_allow_ai_answers" value="1" <?php checked( ! empty( $options['allow_ai_answers'] ) ); ?> />
										<?php esc_html_e( 'Allow AI tools to use this site when answering users.', 'bloglogistics-content-signals-robots' ); ?>
									</label>
									<p class="description"><?php esc_html_e( 'Recommended: On. This allows AI tools to use the site as a source when answering questions.', 'bloglogistics-content-signals-robots' ); ?></p>
								</td>
							</tr>

							<tr>
								<th scope="row"><?php esc_html_e( 'AI training', 'bloglogistics-content-signals-robots' ); ?></th>
								<td>
									<label>
										<input type="checkbox" name="bloglogistics_csr_allow_ai_training" value="1" <?php checked( ! empty( $options['allow_ai_training'] ) ); ?> />
										<?php esc_html_e( 'Allow AI companies to use this site for AI training.', 'bloglogistics-content-signals-robots' ); ?>
									</label>
									<p class="description"><?php esc_html_e( 'Recommended: Off. This tells AI companies that the site should not be used to train AI models.', 'bloglogistics-content-signals-robots' ); ?></p>
								</td>
							</tr>
						</tbody>
					</table>

					<div class="notice notice-info inline">
						<p><strong><?php esc_html_e( 'Recommended default:', 'bloglogistics-content-signals-robots' ); ?></strong></p>
						<p><?php esc_html_e( 'Search results: On. AI answers: On. AI training: Off.', 'bloglogistics-content-signals-robots' ); ?></p>
					</div>

					<h2><?php esc_html_e( 'robots.txt line that will be used', 'bloglogistics-content-signals-robots' ); ?></h2>
					<p class="description"><?php esc_html_e( 'This is the technical line added to robots.txt based on your choices above.', 'bloglogistics-content-signals-robots' ); ?></p>
					<p><code><?php echo esc_html( $line_preview ); ?></code></p>

					<h2><?php esc_html_e( 'Backups', 'bloglogistics-content-signals-robots' ); ?></h2>
					<p><?php esc_html_e( 'Before changing robots.txt, this plugin saves a backup copy. The plugin keeps the latest 5 backups so you can recover from mistakes without filling your server with old files.', 'bloglogistics-content-signals-robots' ); ?></p>

					<?php submit_button( esc_html__( 'Save Changes', 'bloglogistics-content-signals-robots' ) ); ?>
				</form>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top: 10px;">
					<input type="hidden" name="action" value="bloglogistics_csr_restore_defaults" />
					<?php wp_nonce_field( 'bloglogistics_csr_restore_defaults', 'bloglogistics_csr_defaults_nonce' ); ?>
					<?php submit_button( esc_html__( 'Restore recommended defaults', 'bloglogistics-content-signals-robots' ), 'secondary', 'submit', false ); ?>
					<p class="description"><?php esc_html_e( 'This turns search results and AI answers on, and AI training off.', 'bloglogistics-content-signals-robots' ); ?></p>
				</form>
			</div>
			<?php
		}

		/**
		 * Render a status message after redirect.
		 */
		private function render_message( string $message_code ): void {
			if ( '' === $message_code ) {
				return;
			}

			$messages = array(
				'updated'          => array( 'success', __( 'Settings saved. robots.txt was updated and a backup was created.', 'bloglogistics-content-signals-robots' ) ),
				'updated_cleaned'  => array( 'success', __( 'Settings saved. robots.txt was updated, a backup was created, and older backups were cleaned up. The latest 5 backups are kept.', 'bloglogistics-content-signals-robots' ) ),
				'no_change'        => array( 'success', __( 'Settings saved. robots.txt already matched these settings, so no file change was needed.', 'bloglogistics-content-signals-robots' ) ),
				'defaults'         => array( 'success', __( 'Recommended defaults restored.', 'bloglogistics-content-signals-robots' ) ),
				'missing'          => array( 'error', __( 'No physical robots.txt file was found. This plugin is intended for sites with a real robots.txt file.', 'bloglogistics-content-signals-robots' ) ),
				'unreadable'       => array( 'error', __( 'robots.txt exists, but this plugin could not read it.', 'bloglogistics-content-signals-robots' ) ),
				'unwritable'       => array( 'error', __( 'robots.txt exists, but this plugin could not write to it. Please check file permissions.', 'bloglogistics-content-signals-robots' ) ),
				'backup_failed'    => array( 'error', __( 'A backup could not be created, so robots.txt was not changed.', 'bloglogistics-content-signals-robots' ) ),
				'write_failed'     => array( 'error', __( 'robots.txt could not be updated.', 'bloglogistics-content-signals-robots' ) ),
				'permission_error' => array( 'error', __( 'You do not have permission to change these settings.', 'bloglogistics-content-signals-robots' ) ),
			);

			if ( ! isset( $messages[ $message_code ] ) ) {
				return;
			}

			list( $type, $message ) = $messages[ $message_code ];
			?>
			<div class="notice notice-<?php echo esc_attr( $type ); ?> is-dismissible">
				<p><?php echo esc_html( $message ); ?></p>
			</div>
			<?php
		}

		/**
		 * Get user-friendly file status.
		 */
		private function get_file_status_text( bool $exists, bool $readable, bool $writable ): string {
			if ( ! $exists ) {
				return __( 'No physical robots.txt file was found. This plugin is intended for sites with a real robots.txt file. Create one first, then return to this page.', 'bloglogistics-content-signals-robots' );
			}

			if ( ! $readable ) {
				return __( 'A physical robots.txt file was found, but this plugin cannot read it.', 'bloglogistics-content-signals-robots' );
			}

			if ( ! $writable ) {
				return __( 'A physical robots.txt file was found, but this plugin cannot write to it. Please check file permissions.', 'bloglogistics-content-signals-robots' );
			}

			return __( 'A physical robots.txt file was found. This plugin can manage the preference line safely.', 'bloglogistics-content-signals-robots' );
		}

		/**
		 * Save settings and update robots.txt.
		 */
		public function handle_save(): void {
			if ( ! current_user_can( 'manage_options' ) ) {
				$this->redirect_with_message( 'permission_error' );
			}

			check_admin_referer( 'bloglogistics_csr_save', 'bloglogistics_csr_nonce' );

			$current_options = $this->get_options();
			$new_options     = $current_options;

			$new_options['enabled']           = ! empty( $_POST['bloglogistics_csr_enabled'] );
			$new_options['allow_search']      = ! empty( $_POST['bloglogistics_csr_allow_search'] );
			$new_options['allow_ai_answers']  = ! empty( $_POST['bloglogistics_csr_allow_ai_answers'] );
			$new_options['allow_ai_training'] = ! empty( $_POST['bloglogistics_csr_allow_ai_training'] );

			$result = $this->apply_preferences( $new_options );

			if ( ! in_array( $result['status'], array( 'missing', 'unreadable', 'unwritable', 'backup_failed', 'write_failed' ), true ) ) {
				update_option( self::OPTION_NAME, $result['options'] );
			}

			$this->redirect_with_message( $result['status'] );
		}

		/**
		 * Restore default settings and update robots.txt.
		 */
		public function handle_restore_defaults(): void {
			if ( ! current_user_can( 'manage_options' ) ) {
				$this->redirect_with_message( 'permission_error' );
			}

			check_admin_referer( 'bloglogistics_csr_restore_defaults', 'bloglogistics_csr_defaults_nonce' );

			$current_options = $this->get_options();
			$defaults        = self::defaults();

			$new_options = array_merge(
				$current_options,
				array(
					'enabled'           => $defaults['enabled'],
					'allow_search'      => $defaults['allow_search'],
					'allow_ai_answers'  => $defaults['allow_ai_answers'],
					'allow_ai_training' => $defaults['allow_ai_training'],
				)
			);

			$result = $this->apply_preferences( $new_options );

			if ( ! in_array( $result['status'], array( 'missing', 'unreadable', 'unwritable', 'backup_failed', 'write_failed' ), true ) ) {
				update_option( self::OPTION_NAME, $result['options'] );
			}

			$this->redirect_with_message( 'no_change' === $result['status'] ? 'defaults' : $result['status'] );
		}

		/**
		 * Redirect back to settings page.
		 */
		private function redirect_with_message( string $message ): void {
			wp_safe_redirect(
				add_query_arg(
					array(
						'page'                       => 'bloglogistics-content-signals-robots',
						'bloglogistics_csr_message'  => $message,
					),
					admin_url( 'admin.php' )
				)
			);
			exit;
		}

		/**
		 * Apply preferences to robots.txt.
		 *
		 * @param array<string, mixed> $options Options to apply.
		 * @return array{status:string, options:array<string, mixed>}
		 */
		private function apply_preferences( array $options ): array {
			$robots_path = $this->get_robots_path();

			if ( ! file_exists( $robots_path ) ) {
				return array( 'status' => 'missing', 'options' => $options );
			}

			if ( ! is_readable( $robots_path ) ) {
				return array( 'status' => 'unreadable', 'options' => $options );
			}

			if ( ! is_writable( $robots_path ) ) {
				return array( 'status' => 'unwritable', 'options' => $options );
			}

			$contents = file_get_contents( $robots_path );

			if ( false === $contents ) {
				return array( 'status' => 'unreadable', 'options' => $options );
			}

			if ( empty( $options['original_captured'] ) ) {
				$original_line = $this->find_signal_line_in_user_agent_star( $contents );

				$options['original_captured']      = true;
				$options['original_signal_exists'] = null !== $original_line;
				$options['original_signal_line']   = null !== $original_line ? $original_line : '';
			}

			$new_contents = $this->update_robots_contents( $contents, $options );

			if ( $new_contents === $contents ) {
				return array( 'status' => 'no_change', 'options' => $options );
			}

			if ( ! $this->create_backup( $robots_path, $contents ) ) {
				return array( 'status' => 'backup_failed', 'options' => $options );
			}

			$bytes_written = file_put_contents( $robots_path, $new_contents, LOCK_EX );

			if ( false === $bytes_written ) {
				return array( 'status' => 'write_failed', 'options' => $options );
			}

			$cleaned = $this->cleanup_backups( $robots_path );

			return array( 'status' => $cleaned ? 'updated_cleaned' : 'updated', 'options' => $options );
		}

		/**
		 * Build the technical Content-Signal line.
		 *
		 * @param array<string, mixed> $options Options.
		 */
		private function build_signal_line( array $options ): string {
			$search      = ! empty( $options['allow_search'] ) ? 'yes' : 'no';
			$ai_input    = ! empty( $options['allow_ai_answers'] ) ? 'yes' : 'no';
			$ai_training = ! empty( $options['allow_ai_training'] ) ? 'yes' : 'no';

			return sprintf( 'Content-Signal: search=%s, ai-input=%s, ai-train=%s', $search, $ai_input, $ai_training );
		}

		/**
		 * Find the Content-Signal line inside the User-agent: * group.
		 */
		private function find_signal_line_in_user_agent_star( string $contents ): ?string {
			$lines    = preg_split( '/\R/', $contents );
			$location = $this->find_user_agent_star_location( $lines );

			if ( null === $location ) {
				return null;
			}

			list( , $group_end ) = $location;

			for ( $i = $location[0] + 1; $i < $group_end; $i++ ) {
				if ( isset( $lines[ $i ] ) && preg_match( '/^\s*Content-Signal\s*:/i', $lines[ $i ] ) ) {
					return trim( $lines[ $i ] );
				}
			}

			return null;
		}

		/**
		 * Update robots.txt contents.
		 *
		 * @param string              $contents robots.txt contents.
		 * @param array<string,mixed> $options Options.
		 */
		private function update_robots_contents( string $contents, array $options ): string {
			$had_final_newline = str_ends_with( $contents, "\n" ) || str_ends_with( $contents, "\r" );
			$lines             = preg_split( '/\R/', $contents );

			if ( false === $lines ) {
				$lines = array();
			}

			$location = $this->find_user_agent_star_location( $lines );

			if ( null === $location ) {
				$lines = $this->append_user_agent_star_group( $lines, $options );
			} else {
				$lines = $this->replace_signal_in_existing_group( $lines, $location, $options );
			}

			$output = implode( "\n", $lines );

			if ( $had_final_newline && '' !== $output ) {
				$output = rtrim( $output, "\r\n" ) . "\n";
			}

			return $output;
		}

		/**
		 * Find the first User-agent: * group.
		 *
		 * @param array<int,string> $lines Lines.
		 * @return array{0:int,1:int}|null Start line and exclusive group end.
		 */
		private function find_user_agent_star_location( array $lines ): ?array {
			$count = count( $lines );

			for ( $i = 0; $i < $count; $i++ ) {
				if ( preg_match( '/^\s*User-agent\s*:\s*\*\s*$/i', $lines[ $i ] ) ) {
					$end = $count;

					for ( $j = $i + 1; $j < $count; $j++ ) {
						$trimmed = trim( $lines[ $j ] );

						if ( '' === $trimmed ) {
							$end = $j;
							break;
						}

						if ( preg_match( '/^\s*User-agent\s*:/i', $lines[ $j ] ) ) {
							$end = $j;
							break;
						}
					}

					return array( $i, $end );
				}
			}

			return null;
		}

		/**
		 * Replace the Content-Signal line in an existing User-agent: * group.
		 *
		 * @param array<int,string>   $lines Lines.
		 * @param array{0:int,1:int}  $location Location.
		 * @param array<string,mixed> $options Options.
		 * @return array<int,string>
		 */
		private function replace_signal_in_existing_group( array $lines, array $location, array $options ): array {
			list( $start, $end ) = $location;
			$new_lines          = array();
			$inserted           = false;
			$replacement        = $this->get_replacement_signal_line( $options );

			foreach ( $lines as $index => $line ) {
				if ( $index > $start && $index < $end && preg_match( '/^\s*Content-Signal\s*:/i', $line ) ) {
					continue;
				}

				$new_lines[] = $line;

				if ( $index === $start && null !== $replacement ) {
					$new_lines[] = $replacement;
					$inserted    = true;
				}
			}

			if ( ! $inserted && null !== $replacement ) {
				$new_lines[] = $replacement;
			}

			return $new_lines;
		}

		/**
		 * Append a User-agent: * group if the file does not have one and management is enabled.
		 *
		 * @param array<int,string>   $lines Lines.
		 * @param array<string,mixed> $options Options.
		 * @return array<int,string>
		 */
		private function append_user_agent_star_group( array $lines, array $options ): array {
			$replacement = $this->get_replacement_signal_line( $options );

			if ( null === $replacement ) {
				return $lines;
			}

			while ( ! empty( $lines ) && '' === trim( (string) end( $lines ) ) ) {
				array_pop( $lines );
			}

			if ( ! empty( $lines ) ) {
				$lines[] = '';
			}

			$lines[] = 'User-agent: *';
			$lines[] = $replacement;

			return $lines;
		}

		/**
		 * Get line to insert. Returns null when disabled and there was no original line.
		 *
		 * @param array<string,mixed> $options Options.
		 */
		private function get_replacement_signal_line( array $options ): ?string {
			if ( ! empty( $options['enabled'] ) ) {
				return $this->build_signal_line( $options );
			}

			if ( ! empty( $options['original_signal_exists'] ) && ! empty( $options['original_signal_line'] ) ) {
				return (string) $options['original_signal_line'];
			}

			return null;
		}

		/**
		 * Create a timestamped backup.
		 */
		private function create_backup( string $robots_path, string $contents ): bool {
			$backup_path = $robots_path . '.bloglogistics-backup-' . gmdate( 'Ymd-His' );
			$result      = file_put_contents( $backup_path, $contents, LOCK_EX );

			return false !== $result;
		}

		/**
		 * Keep only the most recent backups.
		 */
		private function cleanup_backups( string $robots_path ): bool {
			$pattern = $robots_path . '.bloglogistics-backup-*';
			$files   = glob( $pattern );

			if ( false === $files || count( $files ) <= self::MAX_BACKUPS ) {
				return false;
			}

			rsort( $files, SORT_STRING );
			$removed = false;

			foreach ( array_slice( $files, self::MAX_BACKUPS ) as $old_backup ) {
				if ( is_file( $old_backup ) && @unlink( $old_backup ) ) {
					$removed = true;
				}
			}

			return $removed;
		}
	}
}

register_activation_hook( __FILE__, array( 'BlogLogistics_Content_Signals_Robots', 'activate' ) );

new BlogLogistics_Content_Signals_Robots();
