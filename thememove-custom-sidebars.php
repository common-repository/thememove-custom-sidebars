<?php
/**
 * Plugin Name: ThemeMove Custom Sidebars
 * Description: A simple and easy way to replace any sidebar or widget area in any WordPress theme without coding.
 * Author:      ThemeMove
 * Author URI:  https://thememove.com
 * Version:     1.0.0
 * License:     GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: tm-custom-sidebars
 * Domain Path: /languages
 *
 * ThemeMove Custom Sidebars is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * ThemeMove Custom Sidebars is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ThemeMove Core. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
 *
 * @package ThemeMove_Custom_Sidebars
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

define( 'TMCS_VER', '1.0.0' );
define( 'TMCS_DIR', plugin_dir_path( __FILE__ ) );
define( 'TMCS_URL', plugin_dir_url( __FILE__ ) );
define( 'TMCS_THEME_DIR', get_template_directory() );

require_once TMCS_DIR . 'inc/class-thememove-custom-sidebars.php';
ThemeMove_Custom_Sidebars::instance();
