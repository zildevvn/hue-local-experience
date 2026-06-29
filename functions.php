<?php

if (!defined('HLE_WP_TOOLKIT_VER')) {
	define('HLE_WP_TOOLKIT_VER', '1.0.1');

}

define('TEMPLATE_DIRECTORY_URL', get_template_directory_uri());
define('SUPPORTED_LANGS', ['en', 'ja']);
define('DEFAULT_LANG', 'en');


require get_template_directory() . '/inc/reset.php';
require get_template_directory() . '/inc/initialize.php';
require get_template_directory() . '/inc/assets.php';
require get_template_directory() . '/inc/acf-options.php';
require get_template_directory() . '/inc/post-types.php';
require get_template_directory() . '/inc/meta.php';
require get_template_directory() . '/inc/images.php';
require get_template_directory() . '/inc/helpers.php';
require get_template_directory() . '/inc/template-tags.php';
require get_template_directory() . '/inc/template-func.php';
require get_template_directory() . '/inc/hooks.php';
require get_template_directory() . '/inc/menus.php';
require get_template_directory() . '/inc/admin.php';
require get_template_directory() . '/inc/ajax.php';
