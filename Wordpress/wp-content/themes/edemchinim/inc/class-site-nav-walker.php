<?php
/** Menu markup shared by the desktop and mobile site header. */

class Edemchinim_Site_Nav_Walker extends Walker_Nav_Menu
{
    private $parents = array();

    private function menu_url($url)
    {
        $url = (string) $url;
        return ! is_front_page() && strpos($url, '#') === 0 && $url !== '#'
            ? home_url('/' . $url)
            : $url;
    }

    public function start_lvl(&$output, $depth = 0, $args = null)
    {
        $parent = $this->parents[$depth] ?? array();
        $submenu_id = $parent['submenu_id'] ?? '';
        $class = $depth === 0 ? 'landing-hero__submenu' : 'landing-hero__subsubmenu';
        $output .= '<ul class="' . esc_attr($class) . '" id="' . esc_attr($submenu_id) . '">';

        $url = $parent['url'] ?? '';
        if ($url && $url !== '#' && empty($parent['current'])) {
            $title = $parent['title'] ?? 'разделе';
            $label = $depth === 0 && $title === 'Услуги' ? 'Все услуги' : 'Все о ' . $title;
            $calculator = is_front_page() && wp_parse_url($url, PHP_URL_FRAGMENT) === 'calculator' ? ' data-open-calculator' : '';
            $output .= '<li class="landing-hero__all-link"><a href="' . esc_url($this->menu_url($url)) . '"' . $calculator . '>' . esc_html($label) . '</a></li>';
        }
    }

    public function end_lvl(&$output, $depth = 0, $args = null)
    {
        $output .= '</ul>';
    }

    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
    {
        $item_classes = (array) $item->classes;
        $has_children = $depth < 2 && in_array('menu-item-has-children', $item_classes, true);
        $has_fragment = (bool) wp_parse_url((string) $item->url, PHP_URL_FRAGMENT);
        $is_current = ! $has_fragment && (! empty($item->current) || in_array('current-menu-item', $item_classes, true));
        $class = $depth === 0 ? 'landing-hero__nav-item' : ($depth === 1 ? 'landing-hero__submenu-item' : 'landing-hero__subsubmenu-item');
        if ($depth === 0 && $has_children) {
            $class .= ' landing-hero__nav-item--services';
        }
        if ($has_children) {
            $this->parents[$depth] = array(
                'submenu_id' => 'landing-menu-submenu-' . (int) $item->ID,
                'url' => (string) $item->url,
                'title' => wp_strip_all_tags($item->title),
                'current' => $is_current,
            );
        }

        $output .= '<li class="' . esc_attr($class) . '"' . ($has_children ? ' data-menu-item' : '') . '>';
        $link_class = $has_children ? ($depth === 0 ? 'landing-hero__nav-button' : 'landing-hero__submenu-button') : '';
        $title = esc_html(wp_strip_all_tags($item->title));
        if ($is_current) {
            $span_class = trim($link_class . ' landing-hero__nav-current');
            $output .= '<span class="' . esc_attr($span_class) . '" aria-current="page"';
            if ($has_children) {
                $output .= ' role="button" tabindex="0" aria-expanded="false" aria-controls="' . esc_attr($this->parents[$depth]['submenu_id']) . '" data-menu-expand';
            }
            $output .= '>' . $title;
        } else {
            $url = (string) $item->url;
            $output .= '<a href="' . esc_url($this->menu_url($url ?: '#')) . '"';
            if ($link_class) {
                $output .= ' class="' . esc_attr($link_class) . '" aria-expanded="false" aria-controls="' . esc_attr($this->parents[$depth]['submenu_id']) . '" data-menu-expand';
            }
            if (is_front_page() && wp_parse_url($url, PHP_URL_FRAGMENT) === 'calculator') {
                $output .= ' data-open-calculator';
            }
            if (! empty($item->target)) {
                $output .= ' target="' . esc_attr($item->target) . '"';
            }
            if (! empty($item->xfn)) {
                $output .= ' rel="' . esc_attr($item->xfn) . '"';
            }
            $output .= '>' . $title;
        }
        if ($has_children) {
            $output .= '<span class="landing-hero__chevron" aria-hidden="true"></span>';
        }
        $output .= $is_current ? '</span>' : '</a>';
    }

    public function end_el(&$output, $item, $depth = 0, $args = null)
    {
        $output .= '</li>';
    }
}
