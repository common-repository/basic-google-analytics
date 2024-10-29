<?php
/*
Plugin Name: Basic Google Analytics
Plugin URI: http://mjkjr.com/products/wordpress-plugins/basic-google-analytics/
Description: A no-frills plugin that adds the Google Analytics tracking code to your Wordpress site.  Includes option to output code in the footer.
Author: Michael John Kozubal Jr.
Version: 0.3.2
Author URI: http://mjkjr.com/
License: MIT License
License URI: http://opensource.org/licenses/MIT

Copyright (c) 2013 Michael John Kozubal Jr. <michael@mjkjr.com>

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
of the Software, and to permit persons to whom the Software is furnished to do
so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

namespace mjkjr_bga;

define('NS', __NAMESPACE__);

define('I18N', 'basic-google-analytics');
define('SLUG', 'basic-google-analytics');
define('VERSION', '0.3.2');
define('VER_KEY', 'mjkjr_bga_version');
define('OPTIONS', 'mjkjr_bga_options');

/**
 *	Load translations and setup hooks
 */
function plugins_loaded()
{
	\load_plugin_textdomain(I18N, false, \basename(dirname(__FILE__)));

	$options = \get_option(OPTIONS);

	if (isset($options['ga_output_hook']))
	{
		if ($options['ga_output_hook'] === 'head')
		{
			\add_action('wp_head', NS.'\output_tracking_code');
		}
		else if ($options['ga_output_hook'] === 'foot')
		{
			\add_action('wp_footer', NS.'\output_tracking_code');
		}
	}
}
\add_action('plugins_loaded', NS.'\plugins_loaded');

/**
 *	Register default settings (use existing settings if possible), perform upgrade steps if necessary
 */
function init()
{
	$defaults = array('ga_property_id' => '', 'ga_site_verification' => '', 'ga_output_hook' => 'head', 'ga_snippet' => "var _gaq = _gaq || [];\n_gaq.push(['_setAccount', '%s']);\n_gaq.push(['_trackPageview']);\n\n(function() {\n\tvar ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;\n\tga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';\n\tvar s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);\n})();");

	if (\add_option(VER_KEY, VERSION) === FALSE)
	{
		$version = \get_option(VER_KEY);

		if ($version === VERSION) { return; }

		switch ($version)	// add options that didn't exist in previous versions without trashing saved options
		{
			case '0.1':
				$options = \get_option(OPTIONS);
				$options['ga_output_hook'] = $defaults['ga_output_hook'];
				\update_option(OPTIONS, $options);
			case '0.2':
				$options = \get_option(OPTIONS);
				$options['ga_snippet']  = $defaults['ga_snippet'];
				\update_option(OPTIONS, $options);
		}

		\update_option(VER_KEY, VERSION);
	}
	else
	{
		\add_option(OPTIONS, $defaults);
	}
}
\add_action('init', NS.'\init');

/**
 *	Add a link to the settings on the plugin page
 */
function plugin_action_links($links, $file)
{
    if ($file == \plugin_basename(__FILE__))
	{
        $link = '<a href="options-general.php?page=' . SLUG . '">' . __('Settings', I18N) . '</a>';
        array_unshift($links, $link);
    }
    return $links;
}
\add_filter('plugin_action_links', NS.'\plugin_action_links', 10, 2);

/**
 *	Create settings page, sections, and fields
 */
function admin_menu()
{
	\add_submenu_page('options-general.php', \__('Basic Google Analytics', I18N), __('Google Analytics', I18N), 'manage_options', SLUG, NS.'\options_page');
	\register_setting(SLUG, OPTIONS, NS.'\options_validate');
	\add_settings_section(NS.'_settings', \__('Account Settings', I18N), NULL, SLUG);
	\add_settings_field('ga_property_id', \__('Property ID', I18N), NS.'\ga_property_id_field', SLUG, NS.'_settings');
	\add_settings_field('ga_output_hook', __('Where to output tracking code', I18N), NS.'\ga_output_hook_field', SLUG, NS.'_settings');
	\add_settings_field('ga_site_verification', __('Site Meta Verification Code', I18N), NS.'\ga_site_verification_field', SLUG, NS.'_settings');
	\add_settings_section(NS.'_snippet', \__('Code Snippet', I18N), NULL, SLUG);
	\add_settings_field('ga_snippet', \__('Customize code snippet', I18N), NS.'\ga_snippet_field', SLUG, NS.'_snippet');
	\add_settings_section(NS.'_delete', \__('Delete Plugin Settings', I18N), NULL, SLUG);
	\add_settings_field('ga_delete_data', \__('Delete Settings from Database?', I18N), NS.'\ga_delete_data_field', SLUG, NS.'_delete');
}
\add_action('admin_menu', NS.'\admin_menu');

/**
 *	Display settings page
 */
function options_page()
{
	echo '<div class="wrap">';
	echo 	'<div id="icon-options-general" class="icon32"><br></div>';
	echo 	'<h2>' . __('Basic Google Analytics Settings', I18N) . '</h2>';
	echo 	'<form action="options.php" method="post">';
				\settings_fields(SLUG);
				\do_settings_sections(SLUG);
	echo 		'<p class="sumbit"><input id="sumbit"  class="button button-primary" type="submit" value="' . \esc_attr(\__('Save Changes', I18N)) . '" name="Submit" /></p>';
	echo 	'</form>';
	echo '</div>';
}

/**
 *	Display settings fields
 */
function ga_property_id_field()
{
	$options = \get_option(OPTIONS);
	echo '<input id="ga_property_id" name="' . OPTIONS . '[ga_property_id]" size="20" type="text" value="' . \esc_attr($options['ga_property_id']) . '" placeholder="UA-00000000-0"/>';
	echo '<p class="description">' . \sprintf(\__('Found in the Admin panel of your %1$sGoogle Analytics Account%2$s.', I18N), '<a href="http://www.google.com/analytics/" target="_blank">', '</a>') . '</p>';
}

function ga_site_verification_field()
{
	$options = \get_option(OPTIONS);
	echo '<input id="ga_site_verification" name="' . OPTIONS . '[ga_site_verification]" size="60" type="text" value="' . $options['ga_site_verification'] . '" placeholder="ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789+=-" />';
}

function ga_output_hook_field()
{
	$options = \get_option(OPTIONS);

	echo '<fieldset>';
		echo '<legend class="screen-reader-text"><span>' . \__('Where to output tracking code', I18N) . '</span></legend>';
		echo '<label><input name="' . OPTIONS . '[ga_output_hook]" type="radio"' . (($options['ga_output_hook'] == 'head') ? ' checked="true" ' : ' ') . 'value="head"> <span>' . \__('Header', I18N) . '</span></label><br>';
		echo '<label><input name="' . OPTIONS . '[ga_output_hook]" type="radio"' . (($options['ga_output_hook'] == 'foot') ? ' checked="true" ' : ' ') . 'value="foot"> <span>' . \__('Footer', I18N) . '</span></label><br>';
	echo '</fieldset>';
}

function ga_snippet_field()
{
	$options = \get_option(OPTIONS);

	echo '<fieldset>';
	echo 	'<legend class="screen-reader-text"><span>' . \__('Customize code snippet', I18N) . '</span></legend>';
	echo 	'<p><label for="snippet">' . __('Modify the snippet below as needed, use %s as a placeholder for the tracking code:', I18N) . '</label></p>';
	echo 	'<p><textarea id="ga_snippet" class="large-text code" cols="50" rows="10" name="' . OPTIONS . '[ga_snippet]">' . $options['ga_snippet'] . '</textarea></p>';
	echo '</fieldset>';
}

function ga_delete_data_field()
{
	echo '<fieldset>';
		echo '<legend class="screen-reader-text"><span>' . \__('Confirm Delete', I18N) . '</span></legend>';
		echo '<label for="ga_confirm_delete"><input id="ga_confirm_delete" name="' . OPTIONS . '[ga_confirm_delete]" type="checkbox" value="true"> ' . \__('Confirm Delete', I18N) . '</label>';
		echo '<p class="description">' . \__("Use this if not uninstalling this plugin from the Wordpress Plugins page.", I18N) . '</p>';
	echo '</fieldset>';
}

/**
 *	Validate settings input
 */
function options_validate($input)
{
	if (isset($input['ga_confirm_delete']) && $input['ga_confirm_delete'] == 'true')
	{
		if (\delete_option(OPTIONS) == TRUE && \delete_option(VER_KEY) === TRUE)
		{
			\add_settings_error('', 'mjkjr_bga_notice', \__('Settings deleted from database!', I18N), 'updated');
		}
		else
		{
			\add_settings_error('ga_confirm_delete', NS.'_error', \__('Unable to delete all settings from database!', I18N), 'error');
		}

		return NULL;
	}

	$valid = array();

	$valid['ga_property_id'] = \sanitize_text_field($input['ga_property_id']);
	if ($valid['ga_property_id'] != $input['ga_property_id'])
	{
		\add_settings_error('ga_property_id', NS.'_error', \__('Invalid Property ID!', I18N), 'error');
		unset($valid['ga_property_id']);
	}

	$valid['ga_output_hook'] = \sanitize_text_field($input['ga_output_hook']);
	if ($valid['ga_output_hook'] != 'head' && $valid['ga_output_hook'] != 'foot')
	{
		\add_settings_error('ga_output_hook', NS.'_error', \__('Unexpected Error!', I18N), 'error');
		$valid['ga_output_hook'] = 'head';
	}

	$valid['ga_snippet'] = $input['ga_snippet'];

	$valid['ga_site_verification'] = \sanitize_text_field($input['ga_site_verification']);
	if ($valid['ga_site_verification'] != $input['ga_site_verification'])
	{
		\add_settings_error('ga_site_verification', NS.'_error', \__('Invalid Meta Verification ID!', I18N), 'error');
		$valid['ga_site_verification'] = '';
	}

	return $valid;
}

/**
 *	Output the Google analytics tracking code
 */
function output_tracking_code()
{
	$options = \get_option(OPTIONS);

	if (empty($options['ga_property_id'])) { return; }

	if (!\is_admin() && !\current_user_can('manage_options'))
	{
		echo '<script type="text/javascript">' . \sprintf($options['ga_snippet'], $options['ga_property_id']) . '</script>';
	}
}

/**
 *	Output the Google meta verification tag
 */
function output_verification_code()
{
	$options = \get_option(OPTIONS);

	if (!empty($options['ga_site_verification']) && \is_front_page()) { echo '<meta name="google-site-verification" content="' . \esc_attr($options['ga_site_verification']) . '" />'; }
}
add_action('wp_head', NS.'\output_verification_code');
?>
