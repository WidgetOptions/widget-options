<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/*
 * Admin Settings
 */
if( !class_exists( 'Modular_Settings_API_Widget_Options' ) ){
	class Modular_Settings_API_Widget_Options {

		private $translation 	= array();
		private $settings 		= array();

		function __construct(){
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'widgetopts_module_cards', array( $this, 'visibility' ) );
			add_action( 'widgetopts_module_cards', array( $this, 'devices' ) );
			add_action( 'widgetopts_module_cards', array( $this, 'alignment' ) );
			add_action( 'widgetopts_module_cards', array( $this, 'hide_title' ) );
			add_action( 'widgetopts_module_cards', array( $this, 'classes' ) );
			add_action( 'widgetopts_module_cards', array( $this, 'logic' ) );
			add_action( 'widgetopts_module_cards', array( $this, 'links' ) );
			add_action( 'widgetopts_module_cards', array( $this, 'fixed' ) );
			add_action( 'widgetopts_module_cards', array( $this, 'columns' ) );
			add_action( 'widgetopts_module_cards', array( $this, 'roles' ) );
			add_action( 'widgetopts_module_cards', array( $this, 'dates' ) );
			add_action( 'widgetopts_module_cards', array( $this, 'styling' ) );
			add_action( 'widgetopts_module_cards', array( $this, 'animation' ) );
			add_action( 'widgetopts_module_cards', array( $this, 'taxonomies' ) );
			add_action( 'widgetopts_module_cards', array( $this, 'shortcodes' ) );
			add_action( 'widgetopts_module_cards', array( $this, 'disable_widgets' ) );
			add_action( 'widgetopts_module_cards', array( $this, 'permission' ) );
			add_action( 'widgetopts_module_cards', array( $this, 'siteorigin' ) );
			// add_action( 'widgetopts_module_cards', array( $this, 'suggest' ) );

			add_action( 'widgetopts_module_sidebar', array( $this, 'upgrade_pro' ) );
			add_action( 'widgetopts_module_sidebar', array( $this, 'optin' ) );
			add_action( 'widgetopts_module_sidebar', array( $this, 'support_box' ) );
			add_action( 'widgetopts_module_sidebar', array( $this, 'more_plugins' ) );
			// add_action( 'widgetopts_module_cards', array( $this, 'upsell_pro' ) );
			add_action( 'wp_ajax_widgetopts_ajax_settings',  array( $this, 'ajax_request' ) );
			// add_action( 'admin_init' , array(&$this,'on_load_page' ));

			$this->settings 	= unserialize( get_option( 'widgetopts_tabmodule-settings' ) );
		}

		function admin_menu() {
			$this->pagehook = add_options_page(
				__( 'Widget Options', 'extended-widget-options' ),
				__( 'Widget Options', 'extended-widget-options' ),
				'manage_options',
				'widgetopts_plugin_settings',
				array(
					$this,
					'settings_page'
				)
			);

			add_action( 'load-' . $this->pagehook , array( &$this,'on_load_page' ) );
		}

		function on_load_page(){
			global $pagenow;

			wp_register_script(
				'jquery-widgetopts-module',
				plugins_url( 'assets/js/settings.js' , dirname(__FILE__) ),
				array( 'jquery' ),
				'',
				true
			);

			$this->translation = array(
				'save_settings'     => __( 'Save Settings', 'extended-widget-options' ),
				'close_settings'    => __( 'Close', 'extended-widget-options' ),
				'show_settings'     => __( 'Configure Settings', 'extended-widget-options' ),
				'hide_settings'     => __( 'Hide Settings', 'extended-widget-options' ),
				'show_description'  => __( 'Learn More', 'extended-widget-options' ),
				'hide_description'  => __( 'Hide Details', 'extended-widget-options' ),
				'show_information'  => __( 'Show Details', 'extended-widget-options' ),
				'activate'          => __( 'Enable', 'extended-widget-options' ),
				'deactivate'        => __( 'Disable', 'extended-widget-options' ),
				'successful_save'   => __( 'Settings saved successfully for %1$s.', 'extended-widget-options' ),
				'deactivate_btn' 	=> __( 'Deactivate License', 'extended-widget-options' ),
				'activate_btn' 		=> __( 'Activate License', 'extended-widget-options' ),
				'status_valid' 		=> __( 'Valid', 'extended-widget-options' ),
				'status_invalid' 	=> __( 'Invalid', 'extended-widget-options' ),
			);

			wp_enqueue_script( 'jquery-widgetopts-module' );
			wp_localize_script( 'jquery-widgetopts-module', 'widgetopts', array( 'translation' => $this->translation, 'ajax_action' => 'widgetopts_ajax_settings', 'ajax_nonce' => wp_create_nonce( 'widgetopts-settings-nonce' ), ) );
		}

		function ajax_request(){
			// $response = (object) array( 'source' => 'WIDGETOPTS_Response', 'errors' => array() , 'method' => $_POST['method'], 'module' => $_POST['module'] );
			$response = array( 'errors' => array() );

			if( !isset( $_POST['method'] ) ) return;
			if( !isset( $_POST['nonce'] ) ) return;

			if ( ! wp_verify_nonce( $_POST['nonce'], 'widgetopts-settings-nonce' ) ) {
				return;
			}

			switch ( $_POST['method'] ) {
				case 'activate':
				case 'deactivate':
						if( !isset( $_POST['module'] ) ) return;
						update_option( 'widgetopts_tabmodule-' . sanitize_text_field( $_POST['module'] ), sanitize_text_field( $_POST['method'] ) );
					break;

				case 'save':
						$response['messages'] = array( __( 'Settings saved successfully.', 'extended-widget-options' ) );
						if( !isset( $_POST['data'] ) ) return;
						parse_str( $_POST['data']['--widgetopts-form-serialized-data'], $params );
						$sanitized = $this->sanitize_array( $params );
						update_option( 'widgetopts_tabmodule-settings', maybe_serialize( $sanitized ) );
					break;

				default:
					# code...
					break;
			}
			$response['source'] = 'WIDGETOPTS_Response';
			$response['response'] 	= 'success';
			$response['closeModal'] = true;
			$response = (object) $response;
			delete_transient( 'widgetopts_tabs_transient' );
			echo json_encode( $response );
			die();
		}

		function settings_page() {
			$view = 'grid';
			?>
			<div class="wrap">
				<h1>
					<?php _e( 'Widget Options', 'extended-widget-options' ); ?>
					<a href="<?php echo esc_url( apply_filters( 'widget_options_support_url', 'https://wordpress.org/support/plugin/widget-options/' ) ); ?>" target="_blank" class="page-title-action"><?php _e( 'Support', 'extended-widget-options' ); ?></a>
				</h1>

				<div id="widgetopts-settings-messages-container"></div>

				<div class="widgetopts-settings-desc">
					<?php _e( 'Enable or disable any widget options tabs using this option. Some features has settings configuration that you can take advantage of to get the most out of Widget Options on fully managing your widgets.', 'extended-widget-options' );?>
				</div>
				<div class="widgetopts-badge widgetopts-badge-settings">
					<span class="widgetopts-mascot"></span>
				</div>

				<div id="poststuff" class="widgetopts-poststuff">
					<div id="post-body" class="metabox-holder columns-2 hide-if-no-js">
						<div id="postbox-container-2" class="postbox-container">

							<div class="widgetopts-module-cards-container <?php echo $view; ?> hide-if-no-js">
								<form enctype="multipart/form-data" method="post" action="/wp-admin/admin.php?page=widgetopts_plugin_settings" id="widgetopts-module-settings-form">
									<ul class="widgetopts-module-cards">
										<?php echo do_action( 'widgetopts_module_cards' );?>
									</ul>
								</form>
							</div>
							<div class="widgetopts-modal-background"></div>
						</div>

						<div id="postbox-container-1" class="postbox-container">
							<?php echo do_action( 'widgetopts_module_sidebar' );?>
						</div>

					</div>
				</div>
			</div>
		<?php }

		function start_modal( $option = null ){ ?>
			<div class="widgetopts-module-settings-container">
				<div class="widgetopts-modal-navigation">
					<button class="dashicons widgetopts-close-modal"></button>
				</div>
				<div class="widgetopts-module-settings-content-container">
					<div class="widgetopts-module-settings-content">
		<?php }

		function end_modal( $option = null ){ ?>
					</div>
				</div>
				<div class="widgetopts-list-content-footer hide-if-no-js">
					<button class="button button-primary align-left widgetopts-module-settings-save"><?php echo $this->translation['save_settings'];?></button>
					<button class="button button-secondary align-left widgetopts-module-settings-cancel"><?php _e( 'Cancel', 'extended-widget-options' );?></button>
				</div>
				<div class="widgetopts-modal-content-footer">
					<?php if( $option == 'activate' ){ ?>
						<button class="button button-secondary align-right widgetopts-toggle-activation"><?php echo $this->translation['deactivate'];?></button>
					<?php }else{ ?>
						<button class="button button-primary align-right widgetopts-toggle-activation"><?php echo $this->translation['activate'];?></button>
					<?php } ?>
					<button class="button button-primary align-left widgetopts-module-settings-save"><?php _e( 'Save Settings', 'extended-widget-options' );?></button>
				</div>
			</div>
		<?php }

		function visibility(){
			$option 	= get_option( 'widgetopts_tabmodule-visibility' );
			$visibility = ( isset( $this->settings['visibility'] ) ) ? $this->settings['visibility'] : array();
			?>
			<li class="widgetopts-module-card <?php echo ( $option == 'activate' ) ? 'widgetopts-module-type-enabled' : 'widgetopts-module-type-disabled'; ?>" id="widgetopts-module-card-visibility" data-module-id="visibility">
				<div class="widgetopts-module-card-content">
					<h2><?php _e( 'Pages Visibility', 'extended-widget-options' );?></h2>
					<p class="widgetopts-module-desc">
						<?php _e( 'Easily restrict any widgets visibility on specific WordPress pages.', 'extended-widget-options' );?>
					</p>
					<?php //print_r( $visibility );?>
					<div class="widgetopts-module-actions hide-if-no-js">
						<?php if( $option == 'activate' ){ ?>
							<button class="button button-secondary widgetopts-toggle-settings"><?php echo $this->translation['show_settings'];?></button>
							<button class="button button-secondary widgetopts-toggle-activation"><?php echo $this->translation['deactivate'];?></button>
						<?php }else{ ?>
							<button class="button button-secondary widgetopts-toggle-settings"><?php echo $this->translation['show_description'];?></button>
							<button class="button button-primary widgetopts-toggle-activation"><?php echo $this->translation['activate'];?></button>
						<?php } ?>

					</div>
				</div>

				<?php $this->start_modal( $option ); ?>
					<span class="dashicons widgetopts-dashicons dashicons-visibility"></span>
					<h3 class="widgetopts-modal-header"><?php _e( 'Pages Visibility', 'extended-widget-options' );?></h3>
					<p>
						<?php _e( 'Visibility tab allows you to completely control each widgets visibility and restrict them on any WordPress pages. You can turn on/off the underlying tabs for post types, taxonomies and miscellanous options using the options below when this feature is enabled.', 'extended-widget-options' );?>
					</p>
					<table class="form-table widgetopts-settings-section">
						<tr>
							<th scope="row">
								<label for="widgetopts-visibility-post_types"><?php _e( 'Post Types Tab', 'extended-widget-options' );?></label>
							</th>
							<td>
								<input type="checkbox" id="widgetopts-visibility-post_types" name="visibility[post_type]" <?php echo $this->is_checked( $visibility, 'post_type' ) ?> value="1" />
								<label for="widgetopts-visibility-post_types"><?php _e( 'Enable Post Types Restriction', 'extended-widget-options' );?></label>
								<p class="description">
									<?php _e( 'This feature will allow visibility restriction of every widgets per post types and per pages.', 'extended-widget-options' );?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="widgetopts-visibility-taxonomies"><?php _e( 'Taxonomies Tab', 'extended-widget-options' );?></label>
							</th>
							<td>
								<input type="checkbox" id="widgetopts-visibility-taxonomies" name="visibility[taxonomies]" <?php echo $this->is_checked( $visibility, 'taxonomies' ) ?> value="1" />
								<label for="widgetopts-visibility-taxonomies"><?php _e( 'Enable Taxonomies Restriction', 'extended-widget-options' );?></label>
								<p class="description">
									<?php _e( 'This tab option will allow you to control visibility via taxonomy and terms archive pages.', 'extended-widget-options' );?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="widgetopts-visibility-misc"><?php _e( 'Miscellaneous Tab', 'extended-widget-options' );?></label>
							</th>
							<td>
								<input type="checkbox" id="widgetopts-visibility-misc" name="visibility[misc]" <?php echo $this->is_checked( $visibility, 'misc' ) ?> value="1" />
								<label for="widgetopts-visibility-misc"><?php _e( 'Enable Miscellaneous Options', 'extended-widget-options' );?></label>
								<p class="description">
									<?php _e( 'Restrict widgets visibility on WordPress miscellanous pages such as home page, blog page, 404, search, etc.', 'extended-widget-options' );?>
								</p>
							</td>
						</tr>
					</table>
				<?php $this->end_modal( $option ); ?>
			</li>
		<?php }

		function devices(){
			$option  = get_option( 'widgetopts_tabmodule-devices' );?>

			<li class="widgetopts-module-card widgetopts-module-card-no-settings <?php echo ( $option == 'activate' ) ? 'widgetopts-module-type-enabled' : 'widgetopts-module-type-disabled'; ?>" id="widgetopts-module-card-devices" data-module-id="devices">
				<div class="widgetopts-module-card-content">
					<h2><?php _e( 'Devices Restriction', 'extended-widget-options' );?></h2>
					<p class="widgetopts-module-desc">
						<?php _e( 'Show or hide specific WordPress widgets on desktop, tablet and/or mobile screen sizes.', 'extended-widget-options' );?>
					</p>
					<?php //print_r( $visibility );?>
					<div class="widgetopts-module-actions hide-if-no-js">
						<?php if( $option == 'activate' ){ ?>
							<button class="button button-secondary widgetopts-toggle-settings"><?php echo $this->translation['show_description'];?></button>
							<button class="button button-secondary widgetopts-toggle-activation"><?php echo $this->translation['deactivate'];?></button>
						<?php }else{ ?>
							<button class="button button-secondary widgetopts-toggle-settings"><?php echo $this->translation['show_description'];?></button>
							<button class="button button-primary widgetopts-toggle-activation"><?php echo $this->translation['activate'];?></button>
						<?php } ?>

					</div>
				</div>

				<?php $this->start_modal( $option ); ?>
					<span class="dashicons widgetopts-dashicons widgetopts-no-top dashicons-smartphone"></span>
					<h3 class="widgetopts-modal-header"><?php _e( 'Devices Restriction', 'extended-widget-options' );?></h3>
					<p>
						<?php _e( 'This feature will allow you to display different sidebar widgets for each devices. You can restrict visibility on desktop, tablet and/or mobile device screen sizes at ease via checkboxes.', 'extended-widget-options' );?>
					</p>
					<p class="widgetopts-settings-section">
						<?php _e( 'No additional settings available.', 'extended-widget-options' );?>
					</p>
				<?php $this->end_modal( $option ); ?>

			</li>
		<?php }

		function alignment(){
			$option  = get_option( 'widgetopts_tabmodule-alignment' ); ?>

			<li class="widgetopts-module-card widgetopts-module-card-no-settings <?php echo ( $option == 'activate' ) ? 'widgetopts-module-type-enabled' : 'widgetopts-module-type-disabled'; ?>" id="widgetopts-module-card-alignment" data-module-id="alignment">
				<div class="widgetopts-module-card-content">
					<h2><?php _e( 'Custom Alignment', 'extended-widget-options' );?></h2>
					<p class="widgetopts-module-desc">
						<?php _e( 'Assign custom alignments on each widgets for desktop, table and/or mobile devices.', 'extended-widget-options' );?>
					</p>
					<?php //print_r( $visibility );?>
					<div class="widgetopts-module-actions hide-if-no-js">
						<?php if( $option == 'activate' ){ ?>
							<button class="button button-secondary widgetopts-toggle-settings"><?php echo $this->translation['show_description'];?></button>
							<button class="button button-secondary widgetopts-toggle-activation"><?php echo $this->translation['deactivate'];?></button>
						<?php }else{ ?>
							<button class="button button-secondary widgetopts-toggle-settings"><?php echo $this->translation['show_description'];?></button>
							<button class="button button-primary widgetopts-toggle-activation"><?php echo $this->translation['activate'];?></button>
						<?php } ?>

					</div>
				</div>

				<?php $this->start_modal( $option ); ?>
					<span class="dashicons widgetopts-dashicons widgetopts-no-top dashicons-editor-aligncenter"></span>
					<h3 class="widgetopts-modal-header"><?php _e( 'Custom Alignment', 'extended-widget-options' );?></h3>
					<p>
						<?php _e( 'Custom alignment widget options will allow you to assign different content alignments for each widgets on specific devices. You can choose whether you want them to be left, right, justify or centered aligned on desktop, tablet or mobile devices.', 'extended-widget-options' );?>
					</p>
					<p class="widgetopts-settings-section">
						<?php _e( 'No additional settings available.', 'extended-widget-options' );?>
					</p>
				<?php $this->end_modal( $option ); ?>

			</li>
		<?php }

		function hide_title(){
			$option  = get_option( 'widgetopts_tabmodule-hide_title' ); ?>

			<li class="widgetopts-module-card widgetopts-module-card-no-settings <?php echo ( $option == 'activate' ) ? 'widgetopts-module-type-enabled' : 'widgetopts-module-type-disabled'; ?>" id="widgetopts-module-card-hide_title" data-module-id="hide_title">
				<div class="widgetopts-module-card-content">
					<h2><?php _e( 'Hide Title', 'extended-widget-options' );?></h2>
					<p class="widgetopts-module-desc">
						<?php _e( 'Allows you to hide widget title on the front-end but keep them on the backend.', 'extended-widget-options' );?>
					</p>
					<?php //print_r( $visibility );?>
					<div class="widgetopts-module-actions hide-if-no-js">
						<?php if( $option == 'activate' ){ ?>
							<button class="button button-secondary widgetopts-toggle-settings"><?php echo $this->translation['show_description'];?></button>
							<button class="button button-secondary widgetopts-toggle-activation"><?php echo $this->translation['deactivate'];?></button>
						<?php }else{ ?>
							<button class="button button-secondary widgetopts-toggle-settings"><?php echo $this->translation['show_description'];?></button>
							<button class="button button-primary widgetopts-toggle-activation"><?php echo $this->translation['activate'];?></button>
						<?php } ?>

					</div>
				</div>

				<?php $this->start_modal( $option ); ?>
					<span class="dashicons widgetopts-dashicons widgetopts-no-top dashicons-admin-generic"></span>
					<h3 class="widgetopts-modal-header"><?php _e( 'Hide Title', 'extended-widget-options' );?></h3>
					<p>
						<?php _e( 'Easily hide each widget title via checkbox. No need for PHP snippet to hide them per widgets.', 'extended-widget-options' );?>
					</p>
					<p class="widgetopts-settings-section">
						<?php _e( 'No additional settings available.', 'extended-widget-options' );?>
					</p>
				<?php $this->end_modal( $option ); ?>

			</li>
		<?php }

		function classes(){
			$option 	= get_option( 'widgetopts_tabmodule-classes' );
			$classes	= ( isset( $this->settings['classes'] ) ) ? $this->settings['classes'] : array();
			$classlists = ( isset( $classes['classlists'] ) && is_array( $classes['classlists'] ) ) ? $classes['classlists'] : array(); ?>

			<li class="widgetopts-module-card <?php echo ( $option == 'activate' ) ? 'widgetopts-module-type-enabled' : 'widgetopts-module-type-disabled'; ?>" id="widgetopts-module-card-classes" data-module-id="classes">
				<div class="widgetopts-module-card-content">
					<h2><?php _e( 'Classes & ID', 'extended-widget-options' );?></h2>
					<p class="widgetopts-module-desc">
						<?php _e( 'Assign custom css classes and ID on each widgets for element targeting.', 'extended-widget-options' );?>
					</p>

					<div class="widgetopts-module-actions hide-if-no-js">
						<?php if( $option == 'activate' ){ ?>
							<button class="button button-secondary widgetopts-toggle-settings"><?php echo $this->translation['show_settings'];?></button>
							<button class="button button-secondary widgetopts-toggle-activation"><?php echo $this->translation['deactivate'];?></button>
						<?php }else{ ?>
							<button class="button button-secondary widgetopts-toggle-settings"><?php echo $this->translation['show_description'];?></button>
							<button class="button button-primary widgetopts-toggle-activation"><?php echo $this->translation['activate'];?></button>
						<?php } ?>

					</div>

				</div>

				<?php $this->start_modal( $option ); ?>
					<span class="dashicons widgetopts-dashicons widgetopts-no-top dashicons-admin-generic"></span>
					<h3 class="widgetopts-modal-header"><?php _e( 'Classes & ID', 'extended-widget-options' );?></h3>
					<p>
						<?php _e( 'Custom alignment widget options will allow you to assign different content alignments for each widgets on specific devices. You can choose whether you want them to be left, right, justify or centered aligned on desktop, tablet or mobile devices.', 'extended-widget-options' );?>
					</p>
					<table class="form-table widgetopts-settings-section">
						<tr>
							<th scope="row">
								<label for="widgetopts-classes-id"><?php _e( 'Show ID Field', 'extended-widget-options' );?></label>
							</th>
							<td>
								<input type="checkbox" id="widgetopts-classes-id" name="classes[id]" <?php echo $this->is_checked( $classes, 'id' ) ?> value="1" />
								<label for="widgetopts-classes-id"><?php _e( 'Enable ID Field', 'extended-widget-options' );?></label>
								<p class="description">
									<?php _e( 'Allow user to add custom ID on each widgets. ', 'extended-widget-options' );?>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label><?php _e( 'Classes Field Type', 'extended-widget-options' );?></label>
							</th>
							<td>
								<label for="widgetopts-classes-class-text">
									<input type="radio" value="text" id="widgetopts-classes-class-text" name="classes[type]" <?php if( isset( $classes['type'] ) && 'text' == $classes['type'] ){ echo 'checked="checked"'; }?> /><?php _e( 'Text Field', 'extended-widget-options' );?>
								</label>&nbsp;&nbsp;

								<label for="widgetopts-classes-class-predefined">
									<input type="radio" value="predefined" id="widgetopts-classes-class-predefined" name="classes[type]" <?php if( isset( $classes['type'] ) && 'predefined' == $classes['type'] ){ echo 'checked="checked"'; }?> /><?php _e( 'Predefined Class Checkboxes', 'extended-widget-options' );?>
								</label>&nbsp;&nbsp;

								<label for="widgetopts-classes-class-both">
									<input type="radio" value="both" id="widgetopts-classes-class-both" name="classes[type]" <?php if( isset( $classes['type'] ) && 'both' == $classes['type'] ){ echo 'checked="checked"'; }?> /><?php _e( 'Both', 'extended-widget-options' );?>
								</label>&nbsp;&nbsp;

								<label for="widgetopts-classes-class-hide">
									<input type="radio" value="hide" id="widgetopts-classes-class-hide" name="classes[type]" <?php if( isset( $classes['type'] ) && 'hide' == $classes['type'] ){ echo 'checked="checked"'; }?> /><?php _e( 'Hide', 'extended-widget-options' );?>
								</label>
								<p class="description">
									<?php _e( 'Select which field type you want to manage each widget classes option.', 'extended-widget-options' );?>
								</p>
							</td>
						</tr>
					</table>
					<div class="widgetopts-settings-section">
						<h4><?php _e( 'Predefined Classes', 'extended-widget-options' );?></h4>
						<p><?php _e( 'Set a class lists that you want to be available as pre-choices on the Class/ID Widget Options tab.', 'extended-widget-options' );?></p>

						<div id="opts-predefined-classes">
							<ul>
								<li class="opts-hidden-placeholder"></li>
								<?php
									if( !empty( $classlists ) && is_array( $classlists ) ){
										$classlists = array_unique( $classlists );
										foreach ($classlists as $key => $value) {
											echo '<li><input type="hidden" name="classes[classlists][]" value="'. $value .'" /><span class"opts-li-value">'. $value .'</span> <a href="#" class="opts-remove-class-btn"><span class="dashicons dashicons-dismiss"></span></a></li>';
										}
									}
								?>
							</ul>
						</div>

						<table class="form-table">
							<tbody>
								<tr valign="top">
									<td scope="row" valign="middle">
										<input type="text" class="regular-text code opts-add-class-txtfld" />
										<a href="#" class="opts-add-class-btn widgetopts-add-class-btn"><span class="dashicons dashicons-plus-alt"></span></a><br />
										<small><em><?php _e( 'Note: Click the Plus icon to add the class.', 'extended-widget-options' );?></em></small>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				<?php $this->end_modal( $option ); ?>

			</li>
		<?php }

		function logic(){
			$option  	= get_option( 'widgetopts_tabmodule-logic' );
			$logic 		= ( isset( $this->settings['logic'] ) ) ? $this->settings['logic'] : array(); ?>

			<li class="widgetopts-module-card <?php echo ( $option == 'activate' ) ? 'widgetopts-module-type-enabled' : 'widgetopts-module-type-disabled'; ?>" id="widgetopts-module-card-logic" data-module-id="logic">
				<div class="widgetopts-module-card-content">
					<h2><?php _e( 'Display Logic', 'extended-widget-options' );?></h2>
					<p class="widgetopts-module-desc">
						<?php _e( 'Use WordPress PHP conditional tags to assign each widgets visibility.', 'extended-widget-options' );?>
					</p>
					<?php //print_r( $visibility );?>
					<div class="widgetopts-module-actions hide-if-no-js">
						<?php if( $option == 'activate' ){ ?>
							<button class="button button-secondary widgetopts-toggle-settings"><?php echo $this->translation['show_settings'];?></button>
							<button class="button button-secondary widgetopts-toggle-activation"><?php echo $this->translation['deactivate'];?></button>
						<?php }else{ ?>
							<button class="button button-secondary widgetopts-toggle-settings"><?php echo $this->translation['show_description'];?></button>
							<button class="button button-primary widgetopts-toggle-activation"><?php echo $this->translation['activate'];?></button>
						<?php } ?>

					</div>

				</div>

				<?php $this->start_modal( $option ); ?>
					<span class="dashicons widgetopts-dashicons widgetopts-no-top dashicons-admin-generic"></span>
					<h3 class="widgetopts-modal-header"><?php _e( 'Display Logic', 'extended-widget-options' );?></h3>
					<p>
						<?php _e( 'Display Widget Logic will let you control where you want the widgets to appear using WordPress conditional tags.', 'extended-widget-options' );?>
					</p>
					<p>
						<?php _e( '<strong>Please note</strong> that the display logic you introduce is EVAL\'d directly. Anyone who has access to edit widget appearance will have the right to add any code, including malicious and possibly destructive functions. There is an optional filter <code>widget_options_logic_override</code> which you can use to bypass the EVAL with your own code if needed.', 'extended-widget-options' );?>
					</p>
					<table class="form-table widgetopts-settings-section">
						<tr>
							<th scope="row">
								<label for="widgetopts-logic-notice"><?php _e( 'Hide Notice', 'extended-widget-options' );?></label>
							</th>
							<td>
								<input type="checkbox" id="widgetopts-logic-notice" name="logic[notice]" <?php echo $this->is_checked( $logic, 'notice' ) ?> value="1" />
								<label for="widgetopts-logic-notice"><?php _e( 'Disable Notice Toggler', 'extended-widget-options' );?></label>
								<p class="description">
									<?php _e( 'Hide similar filter notice above on each widget display logic feature.', 'extended-widget-options' );?>
								</p>
							</td>
						</tr>
					</table>
				<?php $this->end_modal( $option ); ?>

			</li>
		<?php }

		function links(){ ?>
			<li class="widgetopts-module-card widgetopts-module-type-pro" data-module-id="links">
				<div class="widgetopts-module-card-content">
					<a href="http://widget-options.com/" target="_blank" class="widgetopts-pro-upsell"></a>
					<h2><?php _e( 'Link Widget', 'extended-widget-options' );?></h2>
					<div class="widgetopts-pro-label"><span class="dashicons dashicons-lock"></span></div>
					<p class="widgetopts-module-desc">
						<?php _e( 'Add custom links to any widgets to redirect users on click action.', 'extended-widget-options' );?>
					</p>
				</div>
			</li>
		<?php }

		function fixed(){?>
			<li class="widgetopts-module-card widgetopts-module-type-pro" data-module-id="fixed">
				<div class="widgetopts-module-card-content">
					<a href="http://widget-options.com/" target="_blank" class="widgetopts-pro-upsell"></a>
					<h2><?php _e( 'Fixed Widget', 'extended-widget-options' );?></h2>
					<div class="widgetopts-pro-label"><span class="dashicons dashicons-lock"></span></div>
					<p class="widgetopts-module-desc">
						<?php _e( 'Add fixed positioning to each widget when the page is scrolled.', 'extended-widget-options' );?>
					</p>
				</div>
			</li>
		<?php }

		function columns(){ ?>
			<li class="widgetopts-module-card widgetopts-module-type-pro" data-module-id="columns">
				<div class="widgetopts-module-card-content">
					<a href="http://widget-options.com/features/responsive-wordpress-widget-columns/" target="_blank" class="widgetopts-pro-upsell"></a>
					<h2><?php _e( 'Column Display', 'extended-widget-options' );?></h2>
					<div class="widgetopts-pro-label"><span class="dashicons dashicons-lock"></span></div>
					<p class="widgetopts-module-desc">
						<?php _e( 'Manage your widgets display as columns, set different columns for specific devices.', 'extended-widget-options' );?>
					</p>
				</div>
			</li>
		<?php }

		function roles(){ ?>
			<li class="widgetopts-module-card widgetopts-module-type-pro" data-module-id="roles">
				<div class="widgetopts-module-card-content">
					<a href="http://widget-options.com/features/restrict-wordpress-widgets-per-user-roles/" target="_blank" class="widgetopts-pro-upsell"></a>
					<h2><?php _e( 'User Roles Restriction', 'extended-widget-options' );?></h2>
					<div class="widgetopts-pro-label"><span class="dashicons dashicons-lock"></span></div>
					<p class="widgetopts-module-desc">
						<?php _e( 'Restrict each widgets visibility for each user roles at ease via checkboxes.', 'extended-widget-options' );?>
					</p>
				</div>
			</li>
		<?php }

		function dates(){ ?>
			<li class="widgetopts-module-card widgetopts-module-type-pro" data-module-id="dates">
				<div class="widgetopts-module-card-content">
					<a href="http://widget-options.com/features/restrict-wordpress-widgets-per-days-date-range/" target="_blank" class="widgetopts-pro-upsell"></a>
					<h2><?php _e( 'Days & Date Range', 'extended-widget-options' );?></h2>
					<div class="widgetopts-pro-label"><span class="dashicons dashicons-lock"></span></div>
					<p class="widgetopts-module-desc">
						<?php _e( 'Restrict widget visibility in any day of the week and/or specific date range.', 'extended-widget-options' );?>
					</p>
				</div>
			</li>
		<?php }

		function styling(){ ?>
			<li class="widgetopts-module-card widgetopts-module-type-pro" data-module-id="styling">
				<div class="widgetopts-module-card-content">
					<a href="http://widget-options.com/features/wordpress-widgets-styling/" target="_blank" class="widgetopts-pro-upsell"></a>
					<h2><?php _e( 'Custom Styling', 'extended-widget-options' );?></h2>
					<div class="widgetopts-pro-label"><span class="dashicons dashicons-lock"></span></div>
					<p class="widgetopts-module-desc">
						<?php _e( 'Set custom widget colors and styling to make your widget stand-out more.', 'extended-widget-options' );?>
					</p>
				</div>
			</li>
		<?php }

		function animation(){ ?>
			<li class="widgetopts-module-card widgetopts-module-type-pro" data-module-id="animation">
				<div class="widgetopts-module-card-content">
					<a href="http://widget-options.com/" target="_blank" class="widgetopts-pro-upsell"></a>
					<h2><?php _e( 'Animation', 'extended-widget-options' );?></h2>
					<div class="widgetopts-pro-label"><span class="dashicons dashicons-lock"></span></div>
					<p class="widgetopts-module-desc">
						<?php _e( 'Add CSS animation effect to your widgets on page load or page scroll.', 'extended-widget-options' );?>
					</p>
				</div>
			</li>
		<?php }

		function taxonomies(){ ?>
			<li class="widgetopts-module-card widgetopts-module-type-pro" data-module-id="taxonomies">
				<div class="widgetopts-module-card-content">
					<a href="http://widget-options.com/features/post-types-taxonomies-widget-visibility/" target="_blank" class="widgetopts-pro-upsell"></a>
					<h2><?php _e( 'Extended Taxonomy Terms', 'extended-widget-options' );?></h2>
					<div class="widgetopts-pro-label"><span class="dashicons dashicons-lock"></span></div>
					<p class="widgetopts-module-desc">
						<?php _e( 'Extend each widget visibility for custom post types taxonomies and terms.', 'extended-widget-options' );?>
					</p>
				</div>
			</li>
		<?php }

		function disable_widgets(){?>
			<li class="widgetopts-module-card widgetopts-module-type-pro" data-module-id="disable_widgets">
				<div class="widgetopts-module-card-content">
					<a href="http://widget-options.com/" target="_blank" class="widgetopts-pro-upsell"></a>
					<h2><?php _e( 'Disable Widgets', 'extended-widget-options' );?></h2>
					<div class="widgetopts-pro-label"><span class="dashicons dashicons-lock"></span></div>
					<p class="widgetopts-module-desc">
						<?php _e( 'Disable several widgets that you won\'t be using to lessen widget dashboard space.', 'extended-widget-options' );?>
					</p>
				</div>
			</li>
		<?php }

		function permission(){ ?>
			<li class="widgetopts-module-card widgetopts-module-type-pro" data-module-id="permission">
				<div class="widgetopts-module-card-content">
					<a href="http://widget-options.com/" target="_blank" class="widgetopts-pro-upsell"></a>
					<h2><?php _e( 'Permission', 'extended-widget-options' );?></h2>
					<div class="widgetopts-pro-label"><span class="dashicons dashicons-lock"></span></div>
					<p class="widgetopts-module-desc">
						<?php _e( 'Hide widget options tabs below each widgets to selected user roles.', 'extended-widget-options' );?>
					</p>
				</div>
			</li>
		<?php }

		function shortcodes(){ ?>
			<li class="widgetopts-module-card widgetopts-module-type-pro" data-module-id="shortcodes">
				<div class="widgetopts-module-card-content">
					<a href="http://widget-options.com/" target="_blank" class="widgetopts-pro-upsell"></a>
					<h2><?php _e( 'Shortcodes', 'extended-widget-options' );?></h2>
					<div class="widgetopts-pro-label"><span class="dashicons dashicons-lock"></span></div>
					<p class="widgetopts-module-desc">
						<?php _e( 'Display any sidebars and widgets anywhere using shortcodes.', 'extended-widget-options' );?>
					</p>
				</div>
			</li>
		<?php }

		function siteorigin(){ ?>
			<li class="widgetopts-module-card widgetopts-module-type-pro" data-module-id="siteorigin">
				<div class="widgetopts-module-card-content">
					<a href="http://widget-options.com/" target="_blank" class="widgetopts-pro-upsell"></a>
					<h2><?php _e( 'SiteOrigin Pagebuilder Support', 'extended-widget-options' );?></h2>
					<div class="widgetopts-pro-label"><span class="dashicons dashicons-lock"></span></div>
					<p class="widgetopts-module-desc">
						<?php _e( 'Extends widget options functionality to SiteOrigin Pagebuilder Plugin.', 'extended-widget-options' );?>
					</p>
				</div>
			</li>
		<?php }

		function suggest(){ ?>
			<li class="widgetopts-module-card widgetopts-module-type-pro" data-module-id="suggest">
				<div class="widgetopts-module-card-content">
					<a href="http://widget-options.com/contact/" target="_blank" class="widgetopts-pro-upsell"></a>
					<h2><?php _e( 'Coming Soon', 'extended-widget-options' );?></h2>
					<p class="widgetopts-module-desc">
						<?php _e( 'More features coming soon! Any suggestions and recommendations are greatly appreciated.', 'extended-widget-options' );?>
					</p>

				</div>
			</li>
		<?php }

		function upsell_pro(){ ?>
			<li class="widgetopts-module-card widgetopts-module-type-pro" data-module-id="visibility">
				<div class="widgetopts-module-card-content">
					<a href="#" target="_blank" class="widgetopts-pro-upsell"></a>
					<h2><?php _e( 'Visibility', 'extended-widget-options' );?></h2>
					<div class="widgetopts-pro-label">Pro</div>
					<p class="widgetopts-module-desc">
						<?php _e( 'Ensure that your site is using the recommended features and settings.', 'extended-widget-options' );?>
					</p>
				</div>
			</li>
		<?php }

		function upgrade_pro(){ ?>
			<div id="widgetopts-sidebar-widget-support" class="postbox widgetopts-sidebar-widget" style="border-color: #ffb310; border-width: 3px;">
				<h3 class="hndle ui-sortable-handle"><span><?php _e( 'Get Extended Widget Options', 'extended-widget-options' );?></span></h3>
				<div class="inside">
					<p>
						<?php _e( '<strong>Unlock all features!</strong> Get the world\'s most complete widget management and get best out of your widgets with <a href="http://widget-options.com/" target="_blank">Extended Widget Options</a> including: ', 'extended-widget-options' );?>
					</p>
					<ul style="list-style: outside; padding-left: 15px;">
						<li>
							<?php _e( 'Custom Styling', 'extended-widget-options' );?>
						</li>
						<li>
							<?php _e( 'Widget Animations', 'extended-widget-options' );?>
						</li>
						<li>
							<?php _e( 'Custom Columns Display', 'extended-widget-options' );?>
						</li>
						<li>
							<?php _e( 'Taxonomies and Custom Post Types Support', 'extended-widget-options' );?>
						</li>
						<li>
							<?php _e( 'Fixed Sticky Widgets', 'extended-widget-options' );?>
						</li>
						<li>
							<?php _e( 'and more pro-only features', 'extended-widget-options' );?>
						</li>
					</ul>
					<p>
						<a class="button-primary" href="http://widget-options.com/?utm_source=wordpressadmin&amp;utm_medium=widget&amp;utm_campaign=widgetoptsprocta" target="_blank"><?php _e( 'Get Extended Widget Options', 'extended-widget-options' );?></a>
					</p>
				</div>
			</div>
		<?php }

		function optin(){ ?>
			<div id="widgetopts-sidebar-widget-optin" class="postbox widgetopts-sidebar-widget">
				<h3 class="hndle ui-sortable-handle"><span><?php _e( 'Get Widget Management Tips', 'extended-widget-options' );?></span></h3>
				<div class="inside">
					<form action="https://phpbits.us12.list-manage.com/subscribe/post?u=5597485458f20da5305e44c55&amp;id=65b77b0af4" method="post" class="validate" target="_blank" novalidate>
						<p>
							<?php _e( 'Get additional tips and resources on how to manage your widgets better + latest news and release updates of Widget Options plugin.', 'extended-widget-options' );?>
						</p>
						<p>
							<?php _e( 'Email Address:', 'extended-widget-options' );?><br  />
							<input name="EMAIL" class="required email widefat" placeholder="<?php _e( 'email@domain.com', 'extended-widget-options' );?>" />
						</p>
						<p>
							<button class="button-secondary"><?php _e( 'Subscribe', 'extended-widget-options' );?></button>
						</p>
					</form>
				</div>
			</div>
		<?php }

		function support_box(){ ?>
			<div id="widgetopts-sidebar-widget-support" class="postbox widgetopts-sidebar-widget">
				<h3 class="hndle ui-sortable-handle"><span><?php _e( 'Need Help on Managing Widgets?', 'extended-widget-options' );?></span></h3>
				<div class="inside">
					<p>
						<?php _e( 'Since you are using the free version, you can get the free support on WordPress.org community forums. Thanks!', 'extended-widget-options' );?>
					</p>
					<p>
						<a class="button-secondary" href="https://wordpress.org/support/plugin/widget-options/" target="_blank"><?php _e( 'Get Free Support', 'extended-widget-options' );?></a>
					</p>
					<p>
						<?php _e( 'Manage your widgets better and get fast professional support by upgrading to Extended Widget Options.', 'extended-widget-options' );?>
					</p>
					<p>
						<a class="button-secondary" href="http://widget-options.com/?utm_source=wordpressadmin&amp;utm_medium=widget&amp;utm_campaign=widgetoptsprocta" target="_blank"><?php _e( 'Get Extended Widget Options', 'extended-widget-options' );?></a>
					</p>
				</div>
			</div>
		<?php }

		function more_plugins(){ ?>
			<div id="widgetopts-sidebar-widget-more_plugins" class="postbox widgetopts-sidebar-widget">
				<h3 class="hndle ui-sortable-handle"><span><?php _e( 'More Free Plugins', 'extended-widget-options' );?></span></h3>
				<div class="inside">
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<td scope="row">
									<a href="https://wordpress.org/plugins/forty-four/" target="_blank">
										<span class="dashicons dashicons-editor-unlink"></span><br />
										<?php _e( 'Forty Four - 404 Page and 301 SEO Redirection Plugin', 'extended-widget-options' );?>
									</a>
								</td>
								<td scope="row">
									<a href="https://wordpress.org/plugins/easy-profile-widget/" target="_blank">
										<span class="dashicons dashicons-admin-users"></span><br />
										<?php _e( 'Easy Profile Info Widget Plugin', 'extended-widget-options' );?>
									</a>
								</td>
							</tr>
							<tr valign="top">
								<td scope="row">
									<a href="https://wordpress.org/plugins/wp-image-hover-lite/" target="_blank">
										<span class="dashicons dashicons-share"></span><br />
										<?php _e( 'Social Icons Image Hover', 'extended-widget-options' );?>
									</a>
								</td>
								<td scope="row">
									<a href="https://wordpress.org/plugins/wp-author-box-lite/" target="_blank">
										<span class="dashicons dashicons-nametag"></span><br />
										<?php _e( 'Author Box Plugin', 'extended-widget-options' );?>
									</a>
								</td>
							</tr>
						</tbody>
					</table>
					<p>
						<a class="button-secondary" href="https://profiles.wordpress.org/phpbits/#content-plugins" target="_blank"><?php _e( 'View all Free Plugins', 'extended-widget-options' );?></a>
					</p>
				</div>
			</div>
		<?php }

		function sanitize_array( &$array ) {
		    foreach ($array as &$value) {
		        if( !is_array($value) ) {
					// sanitize if value is not an array
		            $value = sanitize_text_field( $value );
				}else{
					// go inside this function again
		            $this->sanitize_array($value);
				}
		    }

		    return $array;
		}

		function is_checked( $array, $key ){
			return ( isset( $array[$key] ) && '1' == $array[$key] ) ? 'checked="checked"' : '';
		}

    }

	new Modular_Settings_API_Widget_Options();
}
?>
