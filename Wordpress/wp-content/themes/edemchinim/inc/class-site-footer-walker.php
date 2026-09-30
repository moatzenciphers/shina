<?php
/** Footer menu groups and current-page items. */

class Edemchinim_Site_Footer_Walker extends Walker_Nav_Menu
{
    private $group_id = '';
    private $service_group = false;

    public function start_lvl(&$output, $depth = 0, $args = null)
    {
        if ($depth !== 0) {
            return;
        }

        $class = 'site-footer__links' . ($this->service_group ? ' site-footer__service-columns--menu' : '');
        $output .= '<div class="' . esc_attr($class) . '" id="' . esc_attr($this->group_id) . '"><ul class="site-footer__link-list">';
    }

    public function end_lvl(&$output, $depth = 0, $args = null)
    {
        if ($depth === 0) {
            $output .= '</ul></div>';
        }
    }

    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
    {
        $title = wp_strip_all_tags($item->title);
        $classes = (array) $item->classes;

        if ($depth === 0) {
            $this->group_id = 'footer-menu-' . (int) $item->ID;
            $this->service_group = in_array($title, array('Услуги', 'УСЛУГИ'), true) || in_array('footer-services', $classes, true);
            $has_children = in_array('menu-item-has-children', $classes, true);
            $group_class = 'site-footer__group' . ($this->service_group ? ' site-footer__group--services' : '');
            $output .= '<div class="' . esc_attr($group_class) . '">';
            if ($has_children) {
                $output .= '<button class="site-footer__toggle" type="button" aria-expanded="false" aria-controls="' . esc_attr($this->group_id) . '" data-footer-toggle><span>' . esc_html($title) . '</span><span class="site-footer__chevron" aria-hidden="true"></span></button>';
            } else {
                $output .= '<span class="site-footer__toggle site-footer__toggle--plain">' . esc_html($title) . '</span>';
            }
            return;
        }

        if ($depth !== 1) {
            return;
        }

        $url = (string) $item->url;
        $has_fragment = (bool) wp_parse_url($url, PHP_URL_FRAGMENT);
        $is_current = ! $has_fragment && (! empty($item->current) || in_array('current-menu-item', $classes, true));
        $output .= '<li>';
        if ($is_current) {
            $output .= '<span class="site-footer__active" aria-current="page">' . esc_html($title) . '</span>';
            return;
        }

        if (! is_front_page() && strpos($url, '#') === 0 && $url !== '#') {
            $url = home_url('/' . $url);
        }
        $output .= '<a href="' . esc_url($url ?: '#') . '"';
        if (! empty($item->target)) {
            $output .= ' target="' . esc_attr($item->target) . '"';
        }
        if (! empty($item->xfn)) {
            $output .= ' rel="' . esc_attr($item->xfn) . '"';
        }
        if (is_front_page() && wp_parse_url($url, PHP_URL_FRAGMENT) === 'calculator') {
            $output .= ' data-open-calculator';
        }
        $output .= '>' . esc_html($title) . '</a>';
    }

    public function end_el(&$output, $item, $depth = 0, $args = null)
    {
        if ($depth === 0) {
            $output .= '</div>';
        } elseif ($depth === 1) {
            $output .= '</li>';
        }
    }
}
