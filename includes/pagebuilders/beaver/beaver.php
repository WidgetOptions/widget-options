<?php

/**
 * Plugin Name: Widget Options for Beaver Builder
 * Plugin URI: https://widget-options.com/
 * Description: <strong>Requires <a href="https://wordpress.org/plugins/widget-options/" target="_blank">Widget Options Plugin</a></strong>! Extend functionalities to Beaver Builder for more visibility restriction options.
 * Version: 1.0
 * Author: Widget Options Team
 * Author URI: https://widget-options.com/
 * Text Domain: widget-options
 * Domain Path: languages
 *
 * @category Widgets
 * @author Widget Options Team
 * @version 1.0
 */
// Exit if accessed directly.
if (!defined('ABSPATH')) exit;
if (!class_exists('WP_Widget_Options_Beaver')) :

	/**
	 * Main WP_Widget_Options_Beaver Class.
	 *
	 * @since 1.0
	 */
	class WP_Widget_Options_Beaver
	{

		public static function init()
		{
			$class = __CLASS__;
			new $class;
		}

		function __construct()
		{
			global $widget_options;

			add_filter('fl_builder_register_settings_form', array(&$this, 'widgetopts_beaver_settings'), 10, 2);
			add_action('fl_builder_control_widgetopts-beaver-tabnav', array(&$this, 'fl_widgetopts_beaver_tabnav'), 1, 4);
			add_action('wp_enqueue_scripts', array(&$this, 'fl_widgetopts_beaver_scripts'));
			add_action('fl_builder_control_widgetopts-select2', array(&$this, 'fl_widgetopts_beaver_select2'), 1, 4);
			add_action('fl_builder_control_widgetopts-upgrade', array(&$this, 'fl_widgetopts_upgrade'), 1, 4);
			// add_action( 'admin_notices', array( &$this, 'widgetopts_plugin_check' ) );

			add_filter('fl_builder_is_node_visible', array(&$this, 'widgetopts_beaver_is_node_visible'), 10, 2);
			add_action('fl_builder_control_widgetopts-beaver-legacy', array(&$this, 'fl_widgetopts_beaver_legacy_control'), 1, 4);
		}

		function widgetopts_beaver_settings($form, $id)
		{
			if (!isset($form['widgetopts']) && !is_admin()) {
				//fix not registering global values
				if (!function_exists('widgetopts_register_globals')) {
					require_once WIDGETOPTS_PLUGIN_DIR . 'includes/admin/globals.php';
					widgetopts_register_globals();
				}

				global $widget_options, $widgetopts_taxonomies, $widgetopts_types, $widgetopts_categories;
				$widgetopts_pages 		= widgetopts_global_pages();


				$sections 	= array();
				$pages      = (!empty($widgetopts_pages))       ? $widgetopts_pages         : array();
				$taxonomies = (!empty($widgetopts_taxonomies))  ? $widgetopts_taxonomies    : array();
				$types      = (!empty($widgetopts_types))       ? $widgetopts_types         : array();
				$categories = (!empty($widgetopts_categories))  ? $widgetopts_categories    : array();

				$get_terms = array();
				if (!empty($widget_options['settings']['taxonomies']) && is_array($widget_options['settings']['taxonomies'])) {
					foreach ($widget_options['settings']['taxonomies'] as $tax_opt => $vall) {
						$tax_name = 'widgetopts_taxonomy_' . str_replace('-', '__', $tax_opt);
						// global $$tax_name;
						if (isset($GLOBALS[$tax_name])) {
							$get_terms[$tax_opt] = $GLOBALS[$tax_name];
						}
					}
				}

				// print_r( $pages ); die();

				$sections['widgetopts-fields'] = array(
					'fields' 	  => array(
						'widgetopts-tabnav' => array(
							'type'          => 'widgetopts-beaver-tabnav',
						)
					)
				);

				if (isset($widget_options['visibility']) && 'activate' == $widget_options['visibility']) {
					$visibility_fld = array();

					$visibility_fld['widgetopts_visibility_show'] = array(
						'type'          => 'select',
						'label' 		=> __('Show or Hide', 'widget-options'),
						'options'       => array(
							'hide'      => __('Hide on Selected Pages', 'widget-options'),
							'show'      => __('Show on Selected Pages', 'widget-options')
						)
					);

					if (isset($widget_options['settings']['visibility']) && isset($widget_options['settings']['visibility']['post_type']) && '1' == $widget_options['settings']['visibility']['post_type']) {

						if (!empty($pages)) {
							$pages_array 	= array();
							foreach ($pages as $page) {
								$pages_array[$page->ID] = $page->post_title;
							}

							$visibility_fld['widgetopts_visibility_pages'] = array(
								'type'				=> 'widgetopts-select2',
								'label'				=> __('Pages', 'widget-options'),
								'options'			=> $pages_array,
								'multi-select' 		=> true,
								'description' 		=> __('Click to search or select', 'widget-options'),
							);
						}

						if (!empty($types)) {
							$types_array = array();
							foreach ($types as $ptype => $type) {
								$types_array[$ptype] = $type->labels->name;
							}

							$visibility_fld['widgetopts_visibility_types'] = array(
								'type'				=> 'widgetopts-select2',
								'label'				=> __('Post Types', 'widget-options'),
								'options'			=> $types_array,
								'multi-select' 		=> true,
								'description' 		=> __('Click to search or select', 'widget-options')
							);
						}
					}

					if (isset($widget_options['settings']['visibility']) && isset($widget_options['settings']['visibility']['taxonomies']) && '1' == $widget_options['settings']['visibility']['taxonomies']) {
						//          if( !empty( $widget_options['settings']['taxonomies'] ) && is_array( $widget_options['settings']['taxonomies'] ) ){
						//              foreach ( $widget_options['settings']['taxonomies'] as $tax_opt => $vallue ) {
						//                  $term_array = array();
						//                  // if( !empty( $get_terms ) ){
						//                  	foreach ( $get_terms[ $tax_opt ] as $get_term ) {
						//                       $term_array[ $get_term->term_id ] = $get_term->name;
						//                   }

						//                   $visibility_fld['widgetopts_visibility_tax_'. $tax_opt] = array(
						// 	'type'				=> 'widgetopts-select2',
						// 	'label'				=> $taxonomies[ $tax_opt ]->label,
						// 	'options'			=> $term_array,
						// 	'multi-select' 		=> true,
						// 	'description' 		=> __( 'Click to search or select', 'widget-options' )
						// );
						//                  // }
						//              }
						//          }

						if (!empty($categories)) {
							$cat_array = array();
							foreach ($categories as $cat) {
								$cat_array[$cat->cat_ID] = $cat->cat_name;
							}

							$visibility_fld['widgetopts_visibility_tax_category'] = array(
								'type'				=> 'widgetopts-select2',
								'label'				=> __('Categories', 'widget-options'),
								'options'			=> $cat_array,
								'multi-select' 		=> true,
								'description' 		=> __('Click to search or select', 'widget-options')
							);
						}

						if (!empty($taxonomies)) {
							$tax_array = array();
							foreach ($taxonomies as $taxonomy) {
								$tax_array[$taxonomy->name] = $taxonomy->label;
							}

							$visibility_fld['widgetopts_visibility_taxonomies'] = array(
								'type'				=> 'widgetopts-select2',
								'label'				=> __('Taxonomies', 'widget-options'),
								'options'			=> $tax_array,
								'multi-select' 		=> true,
								'description' 		=> __('Click to search or select', 'widget-options')
							);
						}
					}

					if (isset($widget_options['settings']['visibility']) && isset($widget_options['settings']['visibility']['misc']) && '1' == $widget_options['settings']['visibility']['misc']) {
						$visibility_fld['widgetopts_visibility_misc'] = array(
							'type'				=> 'widgetopts-select2',
							'label'				=> __('Miscellaneous', 'widget-options'),
							'options'			=> array(
								'home'      =>  __('Home/Front', 'widget-options'),
								'blog'      =>  __('Blog', 'widget-options'),
								'archives'  =>  __('Archives', 'widget-options'),
								'404'       =>  __('404', 'widget-options'),
								'search'    =>  __('Search', 'widget-options')
							),
							'multi-select' 	=> true,
							'description' 	=> __('Click to search or select', 'widget-options')
						);
					}

					$sections['widgetopts-visibility'] = array(
						'fields' 	  =>  $visibility_fld
					);
				}

				if (
					isset($widget_options['logic']) && 'activate' == $widget_options['logic'] ||
					isset($widget_options['acf']) && 'activate' == $widget_options['acf']
				) {
					$settings_fld = array();

					if (isset($widget_options['logic']) && 'activate' == $widget_options['logic']) {
						// Get available snippets for dropdown
						$snippet_options = array('' => __('— No Logic (Always Show) —', 'widget-options'));
						if (class_exists('WidgetOpts_Snippets_CPT')) {
							$snippets = WidgetOpts_Snippets_CPT::get_all_snippets();
							foreach ($snippets as $snippet) {
								$snippet_options[$snippet['id']] = $snippet['title'];
							}
						}

						$snippet_description = __('Select a logic snippet to control when this widget is displayed.', 'widget-options');
						if (current_user_can('manage_options')) {
							$snippet_description .= '<br><a href="' . admin_url('edit.php?post_type=widgetopts_snippet') . '" target="_blank" style="display:inline-block;margin-top:5px;padding:3px 8px;background:#0073aa;color:#fff;text-decoration:none;border-radius:3px;font-size:11px;">' . __('Manage Snippets', 'widget-options') . ' →</a>';
						}

						// Hidden field to ensure BB includes legacy logic value in settings
						$settings_fld['widgetopts_settings_logic'] = array(
							'type'    => 'hidden',
							'default' => '',
						);

						// Hidden flag: set to '1' when user clicks Clear button
						$settings_fld['widgetopts_logic_cleared'] = array(
							'type'    => 'hidden',
							'default' => '',
						);

						// Widget Options version stamp
						$settings_fld['widgetopts_wopt_version'] = array(
							'type'    => 'hidden',
							'default' => '',
						);

						// Per-node legacy logic display (shown only when this node has legacy code)
						$settings_fld['widgetopts_legacy_logic_display'] = array(
							'type' => 'widgetopts-beaver-legacy',
						);

						$settings_fld['widgetopts_logic_snippet_id'] = array(
							'type'          => 'widgetopts-select2',
							'label' 		=> __('Display Logic Snippet', 'widget-options'),
							'options'       => $snippet_options,
							'description' 	=> $snippet_description
						);
					}

					//ACF
					if (isset($widget_options['acf']) && 'activate' == $widget_options['acf']) {
						$fields = array('' => __('Select Field', 'widget-options'));

						if (function_exists('acf_get_field_groups')) {
							$groups = acf_get_field_groups();
							if (is_array($groups)) {
								foreach ($groups as $group) {
									$fields_group = acf_get_fields($group);
									if (!empty($fields_group)) {
										foreach ($fields_group as $k => $fg) {
											$fields[$fg['key']] = $fg['label'];
										}
									}
								}
							}
						} else {
							$groups = apply_filters('acf/get_field_groups', array());
							if (is_array($groups)) {
								foreach ($groups as $group) {
									$fields_group = apply_filters('acf/field_group/get_fields', array(), $group['id']);
									if (!empty($fields_group)) {
										foreach ($fields_group as $k => $fg) {
											$fields[$fg['key']] = $fg['label'];
										}
									}
								}
							}
						}

						$settings_fld['widgetopts_acf_visibility'] = array(
							'type'          => 'select',
							'label' 		=> __('Show or Hide based on Advanced Custom Field', 'widget-options'),
							'options'       => array(
								'hide'      => __('Hide when Condition\'s Met', 'widget-options'),
								'show'      => __('Show when Condition\'s Met', 'widget-options')
							)
						);

						$settings_fld['widgetopts_acf_field'] = array(
							'type'				=> 'select',
							'label'				=> __('Select ACF Field', 'widget-options'),
							'options'			=> $fields
						);

						$settings_fld['widgetopts_acf_condition'] = array(
							'type'          => 'select',
							'label' 		=> __('Select Condition', 'widget-options'),
							'options'       => array(
								''      	 =>  __('Select Condition', 'widget-options'),
								'equal'      =>  __('Is Equal To', 'widget-options'),
								'not_equal'  =>  __('Is Not Equal To', 'widget-options'),
								'contains'   =>  __('Contains', 'widget-options'),
								'not_contains'   =>  __('Does Not Contain', 'widget-options'),
								'empty'      =>  __('Is Empty', 'widget-options'),
								'not_empty'  =>  __('Is Not Empty', 'widget-options')
							)
						);
						$settings_fld['widgetopts_acf_value'] = array(
							'type'          => 'textarea',
							'label' 		=> __('Conditional Value', 'widget-options'),
							'description' 	=> __('Add your Conditional Value here if you selected Equal to, Not Equal To or Contains on the selection above.', 'widget-options')
						);
					}

					$sections['widgetopts-settings'] = array(
						'fields' 	  =>  $settings_fld
					);
				}

				$upgrade_fld = array();
				$upgrade_fld['widgetopts_upgrade_section'] = array(
					'type' 	=> 'widgetopts-upgrade'
				);

				$sections['widgetopts-upgrade'] = array(
					'fields' 	  =>  $upgrade_fld
				);

				$form['widgetopts'] = array(
					'title' 	=>	__('Widget Options', 'widget-options'),
					'sections' 	=>  $sections
				);
			}

			return $form;
		}

		function fl_widgetopts_beaver_tabnav($name, $value, $field, $settings)
		{
			global $widget_options;
?>
			<div class="fl-builder-widgetopts-tab">
				<?php if (isset($widget_options['visibility']) && 'activate' == $widget_options['visibility']) { ?>
					<a onclick="widgetoptsBeaverModule.navClick(event)" href="#fl-builder-settings-section-widgetopts-visibility" class="widgetopts-s-active"><span class="dashicons dashicons-visibility"></span><?php _e('Visibility', 'widget-options'); ?></a>
				<?php } ?>
				<?php if (isset($widget_options['logic']) && 'activate' == $widget_options['logic']) { ?>
					<a onclick="widgetoptsBeaverModule.navClick(event)" href="#fl-builder-settings-section-widgetopts-settings" class=""><span class="dashicons dashicons-admin-generic"></span><?php _e('Settings', 'widget-options'); ?></a>
				<?php } ?>
				<a onclick="widgetoptsBeaverModule.navClick(event)" href="#fl-builder-settings-section-widgetopts-upgrade"><span class="dashicons dashicons-plus"></span><?php _e('More', 'widget-options'); ?></a>
			</div>
		<?php }

		function fl_widgetopts_beaver_scripts()
		{
			if (class_exists('FLBuilderModel') && FLBuilderModel::is_builder_active()) {
				$js_dir  = WIDGETOPTS_PLUGIN_URL . 'assets/js/';
				$css_dir = WIDGETOPTS_PLUGIN_URL . 'assets/css/';

				// Use minified libraries if SCRIPT_DEBUG is turned off
				$suffix  = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';

				wp_enqueue_style('widgetopts-beaver-css', $css_dir . 'beaver-widgetopts.css', array(), WIDGETOPTS_VERSION);
				wp_enqueue_style('widgetopts-beaver-select2-css', $css_dir . 'select2.min.css', array(), WIDGETOPTS_VERSION);
				wp_enqueue_style('widgetopts-jquery-ui', $css_dir . '/jqueryui/1.11.4/themes/ui-lightness/jquery-ui.css', array(), WIDGETOPTS_VERSION);
				wp_enqueue_style('jquery-ui');

				wp_enqueue_script(
					'beaver-widgetopts',
					$js_dir . 'jquery.widgetopts.beaver' . $suffix . '.js',
					array('jquery'),
					WIDGETOPTS_VERSION,
					true
				);
				wp_enqueue_script(
					'beaver-widgetopts-select2',
					$js_dir . 'select2.min.js',
					array('jquery', 'beaver-widgetopts'),
					WIDGETOPTS_VERSION,
					true
				);
				wp_enqueue_script(
					'beaver-widgetopts-s2',
					$js_dir . 'select2-settings' . $suffix . '.js',
					array('jquery', 'beaver-widgetopts'),
					WIDGETOPTS_VERSION,
					true
				);
			}
		}

		function fl_widgetopts_beaver_get_fld_options($settings, $field, $options = array())
		{
			if (!is_object($settings) && !is_array($settings)) return $options;

			foreach ($settings as $key => $val) {
				if ($key === $field) {
					if (is_array($val)) {
						foreach ($val as $v) {
							$options[$v] = $v;
						}
					} else {
						$options[$val] = $val;
					}
				} else {
					if (is_array($val) || is_object($val)) {
						$options = $this->fl_widgetopts_beaver_get_fld_options($val, $field, $options);
					}
				}
			}

			return array_unique($options);
		}

		function fl_widgetopts_beaver_select2($name, $value, $field, $settings)
		{
			$options = ($field['options']) ? $field['options'] : array();

			if (isset($field['options_from_field'])) {
				$options_field = $field['options_from_field'];
				$post_data = FLBuilderModel::get_post_data();
				$parent_settings = $post_data['node_settings'];

				$options = $this->fl_widgetopts_beaver_get_fld_options($parent_settings, $options_field, $options);
			}

			// Create attributes
			$attributes = '';
			if (isset($field['attributes']) && is_array($field['attributes'])) {
				foreach ($field['attributes'] as $key => $val) {
					$attributes .= $key . '="' . $val . '" ';
				}
			}

			if (!empty($options) && $value && is_array($value)) {
				uksort($options, function ($key1, $key2) use ($value) {
					return array_search($key1, $value) <=> array_search($key2, $value);
				});
			}
			if (!isset($field['class'])) {
				$field['class'] = '';
			}

			// Show the select field
		?>
			<select name="<?php echo $name;
							if (isset($field['multi-select'])) echo '[]'; ?>" class="widgetopts-select2 <?php echo $field['class']; ?>" <?php if (isset($field['multi-select'])) echo 'multiple ';
																																	echo $attributes; ?> placeholder="<?php _e('Click to search or select', 'widget-options'); ?>">
				<option value=""><?php echo isset($options['']) ? esc_html(is_array($options['']) ? $options['']['label'] : $options['']) : ''; ?></option>
				<?php
				foreach ($options as $option_key => $option_val) :

					if ($option_key === '') {
						continue;
					}

					if (is_array($option_val) && isset($option_val['premium']) && $option_val['premium'] && true === FL_BUILDER_LITE) {
						continue;
					}

					$label = is_array($option_val) ? $option_val['label'] : $option_val;

					if (is_array($value) && in_array($option_key, $value)) {
						$selected = ' selected="selected"';
					} else if (!is_array($value) && selected($value, $option_key, true)) {
						$selected = ' selected="selected"';
					} else {
						$selected = '';
					}

				?>
					<option value="<?php echo $option_key; ?>" <?php echo $selected; ?>><?php echo $label; ?></option>
				<?php endforeach; ?>
			</select>
		<?php }

		/**
		 * Custom control: show legacy logic readonly textarea + warning per-node
		 */
		function fl_widgetopts_beaver_legacy_control($name, $value, $field, $settings)
		{
			// Don't show if snippet_id is already set (migration done)
			if (!empty($settings->widgetopts_logic_snippet_id)) {
				return;
			}

			// Only render if this specific node has legacy logic code
			$legacy_code = '';
			if (isset($settings->widgetopts_settings_logic) && !empty($settings->widgetopts_settings_logic)) {
				$legacy_code = $settings->widgetopts_settings_logic;
			} elseif (isset($settings->widgetopts_settings_logic_backup) && !empty($settings->widgetopts_settings_logic_backup)) {
				$legacy_code = $settings->widgetopts_settings_logic_backup;
			}

			if (empty($legacy_code)) {
				return;
			}

			$migration_url = admin_url('options-general.php?page=widgetopts_migration');
			?>
			<div class="widgetopts-legacy-logic-wrap" style="margin-bottom:15px;">
				<p><strong><?php _e('Legacy Display Logic Code', 'widget-options'); ?></strong></p>
				<textarea readonly="readonly" rows="4" style="width:100%;background:#f9f2f4;color:#c7254e;font-family:monospace;font-size:12px;cursor:not-allowed;resize:vertical;"><?php echo esc_textarea($legacy_code); ?></textarea>
				<p style="margin-top:8px;padding:8px 12px;background:#fff3cd;border-left:4px solid #ffc107;color:#856404;font-size:12px;">
					<?php _e('This module uses legacy inline display logic. Please migrate it to the new snippet-based system.', 'widget-options'); ?>
					<?php if (current_user_can(WIDGETOPTS_MIGRATION_PERMISSIONS)) : ?>
						<br><a href="<?php echo esc_url($migration_url); ?>" target="_blank"><?php _e('Go to Migration Page', 'widget-options'); ?> &rarr;</a>
					<?php endif; ?>
				</p>
				<button type="button" class="fl-builder-button fl-builder-button-primary widgetopts-bb-clear-legacy" style="margin-top:8px;padding:5px 15px;background:#dc3545;color:#fff;border:none;border-radius:3px;cursor:pointer;font-size:12px;" onclick="(function(btn){var form=jQuery(btn).closest('form,.fl-builder-settings');if(form.length){form.find('input[name=widgetopts_settings_logic]').val('');form.find('input[name=widgetopts_logic_cleared]').val('1');jQuery(btn).closest('.widgetopts-legacy-logic-wrap').slideUp();form.find('#fl-field-widgetopts_logic_snippet_id').show();}})( this)">
					<?php _e('Clear Legacy Logic', 'widget-options'); ?>
				</button>
			</div>
			<script>
			jQuery(function(){
				var wrap = jQuery('.widgetopts-legacy-logic-wrap');
				if (wrap.length && wrap.is(':visible')) {
					wrap.closest('form,.fl-builder-settings').find('#fl-field-widgetopts_logic_snippet_id').hide();
				}
			});
			</script>
			<?php
		}

		function fl_widgetopts_upgrade($name, $value, $field, $settings)
		{ ?>
			<div class="extended-widget-opts-tabcontent extended-widget-opts-tabcontent-gopro">
				<p class="widgetopts-unlock-features">
					<span class="dashicons dashicons-lock"></span><?php _e('Unlock all Options', 'widget-options'); ?>
				</p>
				<p>
					<?php _e('Get the world\'s most complete widget management now with Beaver Builder integration! Upgrade to extended version to get:', 'widget-options'); ?>
				</p>
				<ul>
					<li>
						<span class="dashicons dashicons-lock"></span> <?php _e('Animation Options', 'widget-options'); ?>
					</li>
					<li>
						<span class="dashicons dashicons-lock"></span> <?php _e('Custom Styling Options', 'widget-options'); ?>
					</li>
					<li>
						<span class="dashicons dashicons-lock"></span> <?php _e('Set Alignment per Devices', 'widget-options'); ?>
					</li>
					<li>
						<span class="dashicons dashicons-lock"></span> <?php _e('User Roles Visibility Restriction', 'widget-options'); ?>
					</li>
					<li>
						<span class="dashicons dashicons-lock"></span> <?php _e('Fixed/Sticky Widget Options', 'widget-options'); ?>
					</li>
					<li>
						<span class="dashicons dashicons-lock"></span> <?php _e('Days and Date Range Restriction', 'widget-options'); ?>
					</li>
					<li>
						<span class="dashicons dashicons-lock"></span> <?php _e('Link Module Block Options', 'widget-options'); ?>
					</li>
					<li>
						<span class="dashicons dashicons-lock"></span> <?php _e('Extended Taxonomy and Post Types Support', 'widget-options'); ?>
					</li>
					<li>
						<span class="dashicons dashicons-lock"></span> <?php _e('Target URLs and Wildcard Restrictions', 'widget-options'); ?>
					</li>
					<li>
						<span class="dashicons dashicons-lock"></span> <?php _e('Beaver Builder Support', 'widget-options'); ?>
					</li>
				</ul>
				<p><strong><a href="<?php echo apply_filters('widget_options_site_url', trailingslashit(WIDGETOPTS_PLUGIN_WEBSITE) . '?utm_source=beaverbuilder&utm_medium=learnmore&utm_campaign=widgetoptsprotab'); ?>" class="button-primary" target="_blank"><?php _e('Learn More', 'widget-options'); ?></a></strong></p>
			</div>
			<?php }

		function widgetopts_beaver_is_node_visible($is_visible, $node)
		{

			//return if editing
			if (class_exists('FLBuilderModel') && FLBuilderModel::is_builder_active()) {
				return $is_visible;
			}

			global $widget_options;

			$settings 	= $node->settings;
			$hidden     = false;
			$visibility_opts    = isset($settings->widgetopts_visibility_show) ? $settings->widgetopts_visibility_show : 'hide';

			$tax_opts   = (isset($widget_options['settings']) && isset($widget_options['settings']['taxonomies_keys'])) ? $widget_options['settings']['taxonomies_keys'] : array();
			$is_misc    = ('activate' == $widget_options['visibility'] && isset($widget_options['settings']['visibility']) && isset($widget_options['settings']['visibility']['misc'])) ? true : false;
			$is_types   = ('activate' == $widget_options['visibility'] && isset($widget_options['settings']['visibility']) && isset($widget_options['settings']['visibility']['post_type'])) ? true : false;
			$is_tax     = ('activate' == $widget_options['visibility'] && isset($widget_options['settings']['visibility']) && isset($widget_options['settings']['visibility']['taxonomies'])) ? true : false;
			$is_inherit = ('activate' == $widget_options['visibility'] && isset($widget_options['settings']['visibility']) && isset($widget_options['settings']['visibility']['inherit'])) ? true : false;

			// echo '<pre>';
			// print_r( $settings );
			// echo '</pre>';

			//pages
			if ($is_misc && ((is_home() && is_front_page()) || is_front_page())) {
				if (isset($settings->widgetopts_visibility_misc) && is_array($settings->widgetopts_visibility_misc) && in_array('home', $settings->widgetopts_visibility_misc) && $visibility_opts == 'hide') {
					$hidden = true; //hide if checked on hidden pages
				} elseif ((!isset($settings->widgetopts_visibility_misc) || (isset($settings->widgetopts_visibility_misc) && is_array($settings->widgetopts_visibility_misc) && !in_array('home', $settings->widgetopts_visibility_misc))) && $visibility_opts == 'show') {
					$hidden = true; //hide if not checked on visible pages
				}

				//do return to bypass other conditions
				$hidden = apply_filters('widgetopts_beaver_visibility_home', $hidden);
				if ($hidden) {
					return false;
				}
			} elseif ($is_misc && is_home()) {
				//NOT CHECKED YET
				if (isset($settings->widgetopts_visibility_misc) && is_array($settings->widgetopts_visibility_misc) && in_array('blog', $settings->widgetopts_visibility_misc) && $visibility_opts == 'hide') {
					$hidden = true; //hide if checked on hidden pages
				} elseif ((!isset($settings->widgetopts_visibility_misc) || (isset($settings->widgetopts_visibility_misc) && is_array($settings->widgetopts_visibility_misc) && !in_array('blog', $settings->widgetopts_visibility_misc))) && $visibility_opts == 'show') {
					$hidden = true; //hide if not checked on visible pages
				}

				//do return to bypass other conditions
				$hidden = apply_filters('widgetopts_beaver_visibility_blog', $hidden);
				if ($hidden) {
					return false;
				}
			} elseif ($is_tax && is_tag()) {

				if (!isset($settings->widgetopts_visibility_taxonomies) || (isset($settings->widgetopts_visibility_taxonomies) && !is_array($settings->widgetopts_visibility_taxonomies))) {
					$settings->widgetopts_visibility_taxonomies = array();
				}

				if (in_array('post_tag', $settings->widgetopts_visibility_taxonomies) && $visibility_opts == 'hide') {
					$hidden = true; //hide to all tags
				} elseif (in_array('post_tag', $settings->widgetopts_visibility_taxonomies) && $visibility_opts == 'show') {
					$hidden = false; //hide to all tags
				}
				//
				// //do return to bypass other conditions
				$hidden = apply_filters('widgetopts_beaver_visibility_tags', $hidden);
				if ($hidden) {
					return false;
				}
			} elseif ($is_tax && is_tax()) {
				$term = get_queried_object();
				//taxonomies page
				if (!isset($settings->widgetopts_visibility_taxonomies) || (isset($settings->widgetopts_visibility_taxonomies) && !is_array($settings->widgetopts_visibility_taxonomies))) {
					$settings->widgetopts_visibility_taxonomies = array();
				}
				// print_r( $term_lists );
				if (in_array($term->taxonomy, $settings->widgetopts_visibility_taxonomies) && $visibility_opts == 'hide') {
					$hidden = true; //hide to all tags
				} elseif (!in_array($term->taxonomy, $settings->widgetopts_visibility_taxonomies) && $visibility_opts == 'show') {
					$hidden = true; //hide to all tags
				}

				//do return to bypass other conditions
				$hidden = apply_filters('widgetopts_beaver_visibility_taxonomies', $hidden);
				if ($hidden) {
					return false;
				}
			} elseif ($is_misc && is_archive()) {
				//archives page
				if (isset($settings->widgetopts_visibility_misc) && is_array($settings->widgetopts_visibility_misc) && in_array('archives', $settings->widgetopts_visibility_misc) && $visibility_opts == 'hide') {
					$hidden = true; //hide if checked on hidden pages
				} elseif ((!isset($settings->widgetopts_visibility_misc) || (isset($settings->widgetopts_visibility_misc) && is_array($settings->widgetopts_visibility_misc) && !in_array('archives', $settings->widgetopts_visibility_misc))) && $visibility_opts == 'show') {
					$hidden = true; //hide if not checked on visible pages
				}

				//do return to bypass other conditions
				$hidden = apply_filters('widgetopts_beaver_visibility_archives', $hidden);
				if ($hidden) {
					return false;
				}
			} elseif ($is_misc && is_404()) {
				//404 page
				if (isset($settings->widgetopts_visibility_misc) && is_array($settings->widgetopts_visibility_misc) && in_array('404', $settings->widgetopts_visibility_misc) && $visibility_opts == 'hide') {
					$hidden = true; //hide if checked on hidden pages
				} elseif ((!isset($settings->widgetopts_visibility_misc) || (isset($settings->widgetopts_visibility_misc) && is_array($settings->widgetopts_visibility_misc) && !in_array('404', $settings->widgetopts_visibility_misc))) && $visibility_opts == 'show') {
					$hidden = true; //hide if not checked on visible pages
				}

				//do return to bypass other conditions
				$hidden = apply_filters('widgetopts_beaver_visibility_404', $hidden);
				if ($hidden) {
					return false;
				}
			} elseif ($is_misc && is_search()) {
				if (isset($settings->widgetopts_visibility_misc) && is_array($settings->widgetopts_visibility_misc) && in_array('search', $settings->widgetopts_visibility_misc) && $visibility_opts == 'hide') {
					$hidden = true; //hide if checked on hidden pages
				} elseif ((!isset($settings->widgetopts_visibility_misc) || (isset($settings->widgetopts_visibility_misc) && is_array($settings->widgetopts_visibility_misc) && !in_array('search', $settings->widgetopts_visibility_misc))) && $visibility_opts == 'show') {
					$hidden = true;
				}

				//do return to bypass other conditions
				$hidden = apply_filters('widgetopts_beaver_visibility_search', $hidden);
				if ($hidden) {
					return false;
				}
			} elseif (is_single() && !is_page()) {
				global $wp_query;
				$post = $wp_query->post;

				if (!isset($settings->widgetopts_visibility_types) || ($is_types && !isset($settings->widgetopts_visibility_types)) || !is_array($settings->widgetopts_visibility_types)) {
					$settings->widgetopts_visibility_types = array();
				}

				if ($visibility_opts == 'hide' && in_array($post->post_type, $settings->widgetopts_visibility_types)) {
					$hidden = true; //hide if exists on hidden pages
				} elseif ($visibility_opts == 'show' && !in_array($post->post_type, $settings->widgetopts_visibility_types)) {
					$hidden = true; //hide if doesn't exists on visible pages
				}

				// do return to bypass other conditions
				$hidden = apply_filters('widgetopts_beaver_visibility_single', $hidden);


				// $taxonomy_names  = get_post_taxonomies( $post->ID );
				// $array_intersect = array_intersect( $tax_opts, $taxonomy_names );

				if (!isset($settings->widgetopts_visibility_tax_category)) {
					$settings->widgetopts_visibility_tax_category = array();
				}

				if (isset($settings->widgetopts_visibility_tax_category) && !empty($settings->widgetopts_visibility_tax_category)) {
					$cats	= wp_get_post_categories($post->ID);

					if (is_array($cats) && !empty($cats)) {
						$checked_cats   = $settings->widgetopts_visibility_tax_category;
						$intersect      = array_intersect($cats, $checked_cats);
						if (!empty($intersect) && $visibility_opts == 'hide') {
							$hidden = true;
						} elseif (!empty($intersect) && $visibility_opts == 'show') {
							$hidden = false;
						}

						$hidden = apply_filters('widgetopts_beaver_visibility_single_category', $hidden);
					}
				}

				if ($hidden) {
					return false;
				}
			} elseif ($is_types && is_page()) {
				global $wp_query;

				$post = $wp_query->post;

				//do post type condition first
				if (isset($settings->widgetopts_visibility_types) && is_array($settings->widgetopts_visibility_types) && in_array('page', $settings->widgetopts_visibility_types)) {

					if (!is_array($settings->widgetopts_visibility_types)) {
						$settings->widgetopts_visibility_types = array();
					}

					if ($visibility_opts == 'hide' && in_array('page', $settings->widgetopts_visibility_types)) {
						$hidden = true; //hide if exists on hidden pages
					} elseif ($visibility_opts == 'show' && !in_array('page', $settings->widgetopts_visibility_types)) {
						$hidden = true; //hide if doesn't exists on visible pages
					}
				} else {
					// print_r( $settings['widgetopts_pages'] );
					//do per pages condition
					if (!isset($settings->widgetopts_visibility_pages) || (isset($settings->widgetopts_visibility_pages) && !is_array($settings->widgetopts_visibility_pages))) {
						$settings->widgetopts_visibility_pages = array();
					}

					if ($visibility_opts == 'hide' && in_array($post->ID, $settings->widgetopts_visibility_pages)) {
						$hidden = true; //hide if exists on hidden pages
					} elseif ($visibility_opts == 'show' && !in_array($post->ID, $settings->widgetopts_visibility_pages)) {
						$hidden = true; //hide if doesn't exists on visible pages
					}
				}

				// //do return to bypass other conditions
				$hidden = apply_filters('widgetopts_beaver_visibility_page', $hidden);
				if ($hidden) {
					return false;
				}
			}

			//ACF
			if (isset($widget_options['acf']) && 'activate' == $widget_options['acf']) {
				if (isset($settings->widgetopts_acf_field) && !empty($settings->widgetopts_acf_field)) {
					$acf = get_field_object($settings->widgetopts_acf_field);
					if ($acf && is_array($acf)) {
						$acf_visibility    = isset($settings->widgetopts_acf_visibility) ? $settings->widgetopts_acf_visibility : 'hide';

						//handle repeater fields
						if (isset($acf['value'])) {
							if (is_array($acf['value'])) {
								$acf['value'] = implode(', ', array_map(function ($acf_array_value) {
									$acf_implode = '';
									if (is_array($acf_array_value)) {
										$acf_implode = implode(',', array_filter($acf_array_value));
									} else {
										$acf_implode = $acf_array_value;
									}
									return $acf_implode;
								}, $acf['value']));
							}
						}

						switch ($settings->widgetopts_acf_condition) {
							case 'equal':
								if (isset($acf['value'])) {
									if ('show' == $acf_visibility && $acf['value'] == $settings->widgetopts_acf_value) {
										$hidden = false;
									} else if ('show' == $acf_visibility && $acf['value'] != $settings->widgetopts_acf_value) {
										$hidden = true;
									} else if ('hide' == $acf_visibility && $acf['value'] == $settings->widgetopts_acf_value) {
										$hidden = true;
									} else if ('hide' == $acf_visibility && $acf['value'] != $settings->widgetopts_acf_value) {
										$hidden = false;
									}
								}
								break;

							case 'not_equal':
								if (isset($acf['value'])) {
									if ('show' == $acf_visibility && $acf['value'] == $settings->widgetopts_acf_value) {
										$hidden = true;
									} else if ('show' == $acf_visibility && $acf['value'] != $settings->widgetopts_acf_value) {
										$hidden = false;
									} else if ('hide' == $acf_visibility && $acf['value'] == $settings->widgetopts_acf_value) {
										$hidden = false;
									} else if ('hide' == $acf_visibility && $acf['value'] != $settings->widgetopts_acf_value) {
										$hidden = true;
									}
								}
								break;

							case 'contains':
								if (isset($acf['value'])) {
									if ('show' == $acf_visibility && strpos($acf['value'], $settings->widgetopts_acf_value) !== false) {
										$hidden = false;
									} else if ('show' == $acf_visibility && strpos($acf['value'], $settings->widgetopts_acf_value) === false) {
										$hidden = true;
									} else if ('hide' == $acf_visibility && strpos($acf['value'], $settings->widgetopts_acf_value) !== false) {
										$hidden = true;
									} else if ('hide' == $acf_visibility && strpos($acf['value'], $settings->widgetopts_acf_value) === false) {
										$hidden = false;
									}
								}
								break;

							case 'not_contains':
								if (isset($acf['value'])) {
									if ('show' == $acf_visibility && strpos($acf['value'], $settings->widgetopts_acf_value) !== false) {
										$hidden = true;
									} else if ('show' == $acf_visibility && strpos($acf['value'], $settings->widgetopts_acf_value) === false) {
										$hidden = false;
									} else if ('hide' == $acf_visibility && strpos($acf['value'], $settings->widgetopts_acf_value) !== false) {
										$hidden = false;
									} else if ('hide' == $acf_visibility && strpos($acf['value'], $settings->widgetopts_acf_value) === false) {
										$hidden = true;
									}
								}
								break;

							case 'empty':
								if ('show' == $acf_visibility && empty($acf['value'])) {
									$hidden = false;
								} else if ('show' == $acf_visibility && !empty($acf['value'])) {
									$hidden = true;
								} elseif ('hide' == $acf_visibility && empty($acf['value'])) {
									$hidden = true;
								} else if ('hide' == $acf_visibility && !empty($acf['value'])) {
									$hidden = false;
								}
								break;

							case 'not_empty':
								if ('show' == $acf_visibility && empty($acf['value'])) {
									$hidden = true;
								} else if ('show' == $acf_visibility && !empty($acf['value'])) {
									$hidden = false;
								} elseif ('hide' == $acf_visibility && empty($acf['value'])) {
									$hidden = false;
								} else if ('hide' == $acf_visibility && !empty($acf['value'])) {
									$hidden = true;
								}
								break;

							default:
								# code...
								break;
						}

						// //do return to bypass other conditions
						$hidden = apply_filters('widgetopts_beaver_visibility_acf', $hidden);
						if ($hidden) {
							return false;
						}
					}
				}
			}

			//widget logic
			if (isset($widget_options['logic']) && 'activate' == $widget_options['logic']) {
				// New snippet-based system
				if (isset($settings->widgetopts_logic_snippet_id) && !empty($settings->widgetopts_logic_snippet_id)) {
					$snippet_id = $settings->widgetopts_logic_snippet_id;
					if (class_exists('WidgetOpts_Snippets_API')) {
						$result = WidgetOpts_Snippets_API::execute_snippet($snippet_id);
						if ($result === false) {
							return false;
						}
					}
				}
				// Legacy support for old inline logic
				elseif (isset($settings->widgetopts_settings_logic) && !empty($settings->widgetopts_settings_logic)) {
					// Flag that legacy migration is needed
					if (!get_option('wopts_display_logic_migration_required', false)) {
						update_option('wopts_display_logic_migration_required', true);
					}

					$display_logic = stripslashes(trim($settings->widgetopts_settings_logic));
					$display_logic = apply_filters('widget_options_logic_override', $display_logic);
					$display_logic = apply_filters('extended_widget_options_logic_override', $display_logic);
					if ($display_logic === false) {
						return false;
					}
					if ($display_logic === true) {
						return true;
					}
					$display_logic = htmlspecialchars_decode($display_logic, ENT_QUOTES);
					try {
						if (!widgetopts_safe_eval($display_logic)) {
							return false;
						}
					} catch (ParseError $e) {
						return false;
					}
				}
			}

			return $is_visible;
		}
		function widgetopts_plugin_check()
		{
			if (!defined('WIDGETOPTS_PLUGIN_NAME')) { ?>
				<div class="widgetopts_activated_notice notice-error notice" style="box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);">
					<p>
						<?php _e('<strong>Widget Options Plugin</strong> is required for the <em>Widget Options for Beaver Builder</em> to work properly. Please get the plugin <a href="https://wordpress.org/plugins/widget-options/" target="_blank">here</a>. Thanks!', 'widget-options'); ?>
					</p>
				</div>
<?php }
		}
	}

	add_action('plugins_loaded', array('WP_Widget_Options_Beaver', 'init'));
// new WP_Widget_Options_Beaver();

	/**
	 * Protect legacy display logic from modification/injection on BB save.
	 * Always restores the DB value for widgetopts_settings_logic per node.
	 * Prevents code injection via console AND data loss before migration.
	 * 
	 * @since 5.1
	 */
	add_filter('fl_builder_before_save_layout', function($data) {
		if (!is_array($data) || !class_exists('FLBuilderModel')) {
			return $data;
		}

		$post_id = FLBuilderModel::get_post_id();
		if (!$post_id) {
			return $data;
		}

		// Build map: node_id => old widgetopts_settings_logic from DB
		$old_logic = array();
		foreach (array('_fl_builder_data', '_fl_builder_draft') as $mk) {
			$old_nodes = get_post_meta($post_id, $mk, true);
			if (is_array($old_nodes)) {
				foreach ($old_nodes as $nid => $node) {
					if (is_object($node) && isset($node->settings) && is_object($node->settings)
						&& !empty($node->settings->widgetopts_settings_logic)) {
						$old_logic[$nid] = $node->settings->widgetopts_settings_logic;
					}
				}
			}
		}

		// Protect each node
		foreach ($data as $nid => &$node) {
			if (!is_object($node) || !isset($node->settings) || !is_object($node->settings)) {
				continue;
			}

			$wopt_ver = isset($node->settings->widgetopts_wopt_version) ? $node->settings->widgetopts_wopt_version : '';
			$has_snippet = !empty($node->settings->widgetopts_logic_snippet_id);
			$has_legacy = !empty($node->settings->widgetopts_settings_logic);

			// If wopt_version >= 4.2: legacy logic is obsolete — clear it
			if ($wopt_ver !== '' && version_compare($wopt_ver, '4.2', '>=')) {
				if ($has_legacy) {
					$node->settings->widgetopts_settings_logic = '';
				}
				continue;
			}

			// Auto-clear legacy logic if snippet_id is already set (migration done)
			if ($has_snippet && $has_legacy) {
				$node->settings->widgetopts_settings_logic = '';
				$node->settings->widgetopts_wopt_version = WIDGETOPTS_VERSION;
				continue;
			}

			// User intentionally cleared legacy logic via Clear button
			if (!empty($node->settings->widgetopts_logic_cleared)) {
				$node->settings->widgetopts_settings_logic = '';
				unset($node->settings->widgetopts_logic_cleared);
				$node->settings->widgetopts_wopt_version = WIDGETOPTS_VERSION;
				continue;
			}

			$old_val = isset($old_logic[$nid]) ? $old_logic[$nid] : '';
			$new_val = isset($node->settings->widgetopts_settings_logic) ? $node->settings->widgetopts_settings_logic : '';

			if ($new_val !== $old_val) {
				if ($old_val !== '') {
					$node->settings->widgetopts_settings_logic = $old_val;
				} else {
					unset($node->settings->widgetopts_settings_logic);
				}
			}

		}

		return $data;
	}, 10, 1);

endif;
