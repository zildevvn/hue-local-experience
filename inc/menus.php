<?php
add_action('after_setup_theme', function () {
	register_nav_menus([
		'primary-menu' => esc_html__('Primary Menu', 'hle'),
		'footer-menu' => esc_html__('Footer Menu', 'hle'),
	]);
});

/**
 * @param $classes
 * @param $item
 * @param $args
 *
 * @return mixed
 */
add_filter('nav_menu_css_class', 'filter_bootstrap_nav_menu_css_class', 10, 3);
function filter_bootstrap_nav_menu_css_class($classes, $item, $args)
{
	if (isset($args->bootstrap)) {

		$classes[] = ($item->object_id == get_the_ID()) ? 'nav-item current' : 'nav-item';

		if (in_array('menu-item-has-children', $classes)) {
			$classes[] = 'dropdown';
		}

		if (in_array('dropdown-header', $classes)) {
			unset($classes[array_search('dropdown-header', $classes)]);
		}
	}

	return $classes;
}

/**
 * Add icon to menu item title.
 *
 * @param string $title The menu item's title.
 * @param WP_Post $item The menu item object.
 * @param stdClass $args An object of wp_nav_menu() arguments.
 * @param int $depth Depth of menu item.
 *
 * @return string
 */
add_filter('nav_menu_item_title', 'filter_hle_nav_menu_item_title', 10, 4);
function filter_hle_nav_menu_item_title($title, $item, $args, $depth)
{
	if ($args->theme_location === 'primary-menu') {
		$icon = get_field('icon', $item);
		if ($icon) {
			$title = '<img src="' . esc_url($icon) . '" alt="' . esc_attr($item->title) . '" class="menu-icon"><span>' . $title . '</span>';
		}
	}

	return $title;
}

/**
 * Add has-icon class to menu items with icons.
 *
 * @param array $classes The CSS classes that are applied to the menu item's <li> element.
 * @param WP_Post $item The current menu item.
 * @param stdClass $args An object of wp_nav_menu() arguments.
 *
 * @return array
 */
add_filter('nav_menu_css_class', 'filter_hle_nav_menu_css_class', 15, 3);
function filter_hle_nav_menu_css_class($classes, $item, $args)
{
	if ($args->theme_location === 'primary-menu') {
		$icon = get_field('icon', $item);
		if ($icon) {
			$classes[] = 'has-icon';
		}
	}

	return $classes;
}

/**
 * Add bootstrap attributes to individual link elements.
 *
 * @param $atts
 * @param $item
 * @param $args
 * @param $depth
 *
 * @return mixed
 */

add_filter('nav_menu_link_attributes', 'filter_bootstrap_nav_menu_link_attributes', 10, 4);
function filter_bootstrap_nav_menu_link_attributes($atts, $item, $args, $depth)
{

	if (isset($args->bootstrap)) {
		if (!isset($atts['class'])) {
			$atts['class'] = '';
		}

		if ($depth > 0) {
			if (in_array('dropdown-header', $item->classes)) {
				$atts['class'] = 'dropdown-header';
			} else {
				$atts['class'] .= 'dropdown-item';
			}

			if ($item->description) {
				$atts['class'] .= ' has-description';
			}
		} else {
			$atts['class'] .= 'nav-link';
			if (in_array('menu-item-has-children', $item->classes)) {
				$atts['class'] .= ' dropdown-toggle';
				$atts['role'] = 'button';
				$atts['data-toggle'] = 'dropdown';
				$atts['aria-haspopup'] = 'true';
				$atts['aria-expanded'] = 'false';
			}
		}
	}

	return $atts;
}

/**
 * Add bootstrap classes to dropdown menus.
 *
 * @param $classes
 * @param $args
 * @param $depth
 *
 * @return mixed
 */

add_filter('nav_menu_submenu_css_class', 'filter_bootstrap_nav_menu_submenu_css_class', 10, 3);
function filter_bootstrap_nav_menu_submenu_css_class($classes, $args, $depth)
{
	if (isset($args->bootstrap)) {
		$classes[] = 'dropdown-menu';
	}

	return $classes;
}