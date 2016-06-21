<?php
/*
 * Admin Settings
 */
if( !class_exists( 'Settings_API_Extended_Widget_Options' ) ){
	class Settings_API_Extended_Widget_Options {
		
		/*
		 * For easier overriding we declared the keys
		 * here as well as our tabs array which is populated
		 * when registering settings
		 */
		private $class_settings_key = 'extwopts_class_settings';
		private $upgrade_settings_key = 'extwopts_upgrade_settings';
		private $plugin_options_key = 'extwopts_plugin_options';
		private $plugin_settings_tabs = array();

		
		/*
		 * Fired during plugins_loaded (very very early),
		 * so don't miss-use this, only actions and filters,
		 * current ones speak for themselves.
		 */
		function __construct() {
			add_action( 'init', array( &$this, 'load_settings' ) );
			add_action( 'admin_init', array( &$this, 'register_class_settings' ) );
			add_action( 'admin_init', array( &$this, 'register_upgrade_tab' ) );
			add_action( 'admin_menu', array( &$this, 'add_admin_menus' ) );
		}

		function on_load_page(){
		}
		
		/*
		 * Loads both the general and advanced settings from
		 * the database into their respective arrays. Uses
		 * array_merge to merge with default values if they're
		 * missing.
		 */
		function load_settings() {
			$this->classes_settings = (array) get_option( $this->class_settings_key );
		}
		
		/*
		 * Registers the general settings via the Settings API,
		 * appends the setting to the tabs array of the object.
		 */
		function register_class_settings() {
			$this->plugin_settings_tabs[$this->class_settings_key] = __( 'Widget Classes', 'widget-options' );
			
			register_setting( $this->class_settings_key, $this->class_settings_key );
			add_settings_section( 'general_section', __( 'Widget Classes', 'widget-options' ), array( &$this, 'classes_options_section' ), $this->class_settings_key );
		}

		function classes_options_section(){ 
			if( !isset( $this->classes_settings['id_field'] ) ){
				$this->classes_settings['id_field'] = 'yes';
			}
			if( !isset( $this->classes_settings['class_field'] ) ){
				$this->classes_settings['class_field'] = 'both';
			}
			$classlists = array();
			if( isset( $this->classes_settings['classlists'] ) ){
				$classlists = $this->classes_settings['classlists'];
			}
			?>
			<div id="poststuff" class="fortyfourwp-metabox-holder metabox-holder has-right-sidebar">
				<div id="side-info-column" class="inner-sidebar">
					<div class="fortyfourwp-banners">
						<p><a href="https://phpbits.net/plugin/extended-widget-options/" target="_blank"><img src="<?php echo plugins_url('/assets/images/banner-widget-options.jpg', dirname(__FILE__) )?>" /></a></p>
						<p><a href="https://wordpress.org/plugins/forty-four/" target="_blank"><img src="<?php echo plugins_url('/assets/images/banner-forty-four.jpg', dirname(__FILE__) )?>" /></a></p>
					</div>
				</div>
				<div id="post-body" class="has-sidebar">
					<div id="post-body-content" class="has-sidebar-content">
						<table class="form-table opts-classes-setting-table">
			                <tbody>
			                    <tr valign="top">
			                        <td scope="row">
			                            <strong><?php _e( 'Show ID Fields', 'widget-options' );?></strong> 
			                            <label for="opts-classes-id-yes">
			                            	<input type="radio" value="yes" id="opts-classes-id-yes" name="<?php echo $this->class_settings_key; ?>[id_field]" <?php if( 'yes' == $this->classes_settings['id_field'] ){ echo 'checked="checked"'; }?> /><?php _e( 'Yes', 'widget-options' );?>
			                            </label>&nbsp;&nbsp;
			                            <label for="opts-classes-id-no">
			                            	<input type="radio" value="no" id="opts-classes-id-no" name="<?php echo $this->class_settings_key; ?>[id_field]" <?php if( 'no' == $this->classes_settings['id_field'] ){ echo 'checked="checked"'; }?> /><?php _e( 'No', 'widget-options' );?>
			                            </label>&nbsp;&nbsp;
			                        </td>
			                    </tr>

			                    <tr valign="top">
			                        <td scope="row">
			                            <strong><?php _e( 'Classes Field Type', 'widget-options' );?></strong> 

			                            <label for="opts-classes-class-text">
			                            	<input type="radio" value="text" id="opts-classes-class-text" name="<?php echo $this->class_settings_key; ?>[class_field]" <?php if( 'text' == $this->classes_settings['class_field'] ){ echo 'checked="checked"'; }?> /><?php _e( 'Text Field', 'widget-options' );?>
			                            </label>&nbsp;&nbsp;

			                            <label for="opts-classes-class-predefined">
			                            	<input type="radio" value="predefined" id="opts-classes-class-predefined" name="<?php echo $this->class_settings_key; ?>[class_field]" <?php if( 'predefined' == $this->classes_settings['class_field'] ){ echo 'checked="checked"'; }?> /><?php _e( 'Predefined Class Checkboxes', 'widget-options' );?>
			                            </label>&nbsp;&nbsp;

			                            <label for="opts-classes-class-both">
			                            	<input type="radio" value="both" id="opts-classes-class-both" name="<?php echo $this->class_settings_key; ?>[class_field]" <?php if( 'both' == $this->classes_settings['class_field'] ){ echo 'checked="checked"'; }?> /><?php _e( 'Both', 'widget-options' );?>
			                            </label>&nbsp;&nbsp;

			                            <label for="opts-classes-class-hide">
			                            	<input type="radio" value="hide" id="opts-classes-class-hide" name="<?php echo $this->class_settings_key; ?>[class_field]" <?php if( 'hide' == $this->classes_settings['class_field'] ){ echo 'checked="checked"'; }?> /><?php _e( 'Hide', 'widget-options' );?>
			                            </label>&nbsp;&nbsp;
			                        </td>
			                    </tr>
			                </tbody>
			            </table>
			            <h3><?php _e( 'Predefined Classes', 'widget-options' );?></h3>
			            <p><?php _e( 'Set a class lists that you want to be available as pre-choices on the Class/ID Widget Options tab.', 'widget-options' );?></p>
			            <div id="opts-predefined-classes">
			            	<ul>
			            		<li class="opts-hidden-placeholder"></li>
			            		<?php
			            			if( !empty( $classlists ) && is_array( $classlists ) ){
			            				$classlists = array_unique( $classlists );
			            				foreach ($classlists as $key => $value) {
			            					echo '<li><input type="hidden" name="extwopts_class_settings[classlists][]" value="'. $value .'" /><span class"opts-li-value">'. $value .'</span> <a href="#" class="opts-remove-class-btn"><span class="dashicons dashicons-dismiss"></span></a></li>';
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
			            				<a href="#" class="opts-add-class-btn"><span class="dashicons dashicons-plus-alt"></span></a><br />
			            				<small><em><?php _e( 'Note: Click the Plus icon to add the class.', 'widget-options' );?></em></small>
			            			</td>
			            		</tr>
			            	</tbody>
			            </table>
						
						<?php 
						if(function_exists('submit_button')) { submit_button(); } else { ?>
						<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
						<?php }?>

					</div>
				</div>
				<br class="clear"/>
			</div>
			
			
		<?php }

		/*
		 * Registers the pro tabs via the Settings API,
		 * appends the setting to the tabs array of the object.
		 */
		function register_upgrade_tab() {
			$this->plugin_settings_tabs[$this->upgrade_settings_key] = __( 'Extended Features', 'widget-options' );
			
			register_setting( $this->upgrade_settings_key, $this->upgrade_settings_key );
			add_settings_section( 'general_section', __( 'Upgrade to Extended Widget Options', 'widget-options' ), array( &$this, 'upgrade_options_section' ), $this->upgrade_settings_key );
		}

		function upgrade_options_section(){ 
			?>
			<div class="widget-opts-upgrade">
				<p><strong><?php _e( 'Get a Fully-Packed Widget Options and maximize your widget control!', 'widget-options' );?></strong></p>
				<p><?php _e( 'Aside from the free features already available, you will get the following features. ', 'widget-options' );?></p>
	            <ul>
	                <li><span class="dashicons dashicons-yes"></span> <?php _e( 'Display Widget Columns', 'widget-options' );?></li>
	                <li><span class="dashicons dashicons-yes"></span> <?php _e( 'More Alignment Options', 'widget-options' );?></li>
	                <li><span class="dashicons dashicons-yes"></span> <?php _e( 'User Roles Visibility Options', 'widget-options' );?></li>
	                <li><span class="dashicons dashicons-yes"></span> <?php _e( 'Show or Hide widgets for specific day', 'widget-options' );?></li>
	                <li><span class="dashicons dashicons-yes"></span> <?php _e( 'Show or Hide widgets for date range', 'widget-options' );?></li>
	                <li><span class="dashicons dashicons-yes"></span> <?php _e( 'Show or Hide widgets for specific days on the given date range', 'widget-options' );?></li>
	            </ul>
	            <p><strong><?php _e( 'Brand New Features added on version 2.0', 'widget-options' );?></strong></p>
	            <ul>
	                <li><span class="dashicons dashicons-yes"></span> <?php _e( 'Widget Styling', 'widget-options' );?></li>
	                <li><span class="dashicons dashicons-yes"></span> <?php _e( 'Post & Post Types Extended Terms Support', 'widget-options' );?></li>
	                <li><span class="dashicons dashicons-yes"></span> <?php _e( 'Display Widget Logic', 'widget-options' );?></li>
	                <li><span class="dashicons dashicons-yes"></span> <?php _e( 'and other improvements...', 'widget-options' );?></li>
	            </ul>

	            <p><span class="dashicons dashicons-plus"></span> <strong><?php _e( 'PLUGIN LIFETIME UPDATES', 'widget-options' );?></strong></p>
	            
	            <p><strong><a href="https://phpbits.net/plugin/extended-widget-options/" class="widget-opts-learnmore" target="_blank"><?php _e( 'Learn More', 'widget-options' );?> <span class="dashicons dashicons-arrow-right-alt"></span></a></strong></p>
			</div>
			<?php
		}
		
		/*
		 * Called during admin_menu, adds an options
		 * page under Settings called My Settings, rendered
		 * using the wplftr_plugin_options_page method.
		 */
		function add_admin_menus() {
			add_options_page( __( 'Widget Options', 'widget-options' ), __( 'Widget Options', 'widget-options' ), 'manage_options', $this->plugin_options_key, array( &$this, 'plugin_options_page' ) );
		}
		
		/*
		 * Plugin Options page rendering goes here, checks
		 * for active tab and replaces key with the related
		 * settings key. Uses the wplftr_plugin_options_tabs method
		 * to render the tabs.
		 */
		function plugin_options_page() {
			$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->class_settings_key;
			?>
			<div class="wrap">
				<?php $this->plugin_options_tabs(); ?>
				<form method="post" action="options.php">
					<?php wp_nonce_field( 'update-options' ); ?>
					<?php settings_fields( $tab ); ?>
					<?php do_settings_sections( $tab ); ?>
				</form>
			</div>
			<?php
		}
		
		/*
		 * Renders our tabs in the plugin options page,
		 * walks through the object's tabs array and prints
		 * them one by one. Provides the heading for the
		 * wplftr_plugin_options_page method.
		 */
		function plugin_options_tabs() {
			$current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->class_settings_key;

			screen_icon();
			echo '<h2 class="nav-tab-wrapper">';
			foreach ( $this->plugin_settings_tabs as $tab_key => $tab_caption ) {
				$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
				echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->plugin_options_key . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';	
			}
			echo '</h2>';
		}
	};

	// Initialize the plugin
	add_action( 'plugins_loaded', create_function( '', '$settings_api_extended_widget_options = new Settings_API_Extended_Widget_Options;' ) );
}

?>