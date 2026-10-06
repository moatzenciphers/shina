<?php
/** Menu markup shared by the desktop and mobile site header. */

class Edemchinim_Site_Nav_Walker extends Walker_Nav_Menu
{
    private $parents = array();

    private function normalise($title)
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', wp_strip_all_tags($title))));
    }

    private function is_popular($item)
    {
        $post_id = (int) ($item->object_id ?? 0);
        if (! $post_id || get_post_type($post_id) !== 'services') return false;
        foreach (edemchinim_service_areas($post_id) as $term) {
            if ($term->slug === 'popular') return true;
        }
        return false;
    }

    private function service_icon($popular = false)
    {
        $path = $popular
            ? 'M12 3c1 5 6 6 6 11a6 6 0 0 1-12 0c0-3 2-5 3-6 0 3 1 4 2 4 2-2 2-5 1-9Z'
            : 'M20 12a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z M12 4v16 M4 12h16 M6.3 6.3l11.4 11.4 M6.3 17.7 17.7 6.3';
        return '<svg class="' . ($popular ? 'header-services__flame' : 'header-services__icon') . '" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="' . $path . '" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    }

    private function menu_url($url)
    {
        $url = (string) $url;
        if (wp_parse_url($url, PHP_URL_FRAGMENT) === 'calculator') return edemchinim_calculator_url();
        return ! is_front_page() && strpos($url, '#') === 0 && $url !== '#'
            ? home_url('/' . $url)
            : $url;
    }

    public function start_lvl(&$output, $depth = 0, $args = null)
    {
        $parent = $this->parents[$depth] ?? array();
        $submenu_id = $parent['submenu_id'] ?? '';
        $class = $depth === 0 ? 'landing-hero__submenu' : 'landing-hero__subsubmenu';
        if (! empty($parent['services_menu'])) $class .= ' header-services';
        if (! empty($parent['category'])) $class .= ' header-services__list';
        $output .= '<ul class="' . esc_attr($class) . '" id="' . esc_attr($submenu_id) . '">';

        $url = $parent['url'] ?? '';
        if ($url && $url !== '#' && empty($parent['current']) && empty($parent['services_menu']) && empty($parent['category'])) {
            $title = $parent['title'] ?? 'разделе';
            $label = $depth === 0 && $title === 'Услуги' ? 'Все услуги' : 'Все о ' . $title;
            $calculator = wp_parse_url($url, PHP_URL_FRAGMENT) === 'calculator' ? ' data-open-calculator' : '';
            $output .= '<li class="landing-hero__all-link"><a href="' . esc_url($this->menu_url($url)) . '"' . $calculator . '>' . esc_html($label) . '</a></li>';
        }
    }

    public function end_lvl(&$output, $depth = 0, $args = null)
    {
        $output .= '</ul>';
    }

    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
    {
        if ($depth === 1 && ! empty($this->parents[0]['services_menu'])) {
            $this->parents[$depth] = array('submenu_id' => 'landing-menu-category-' . abs((int) $item->ID), 'category' => true);
            $output .= '<li class="header-services__category"><span class="header-services__heading">' . esc_html($item->title) . '</span>';
            return;
        }
        if ($depth === 2 && ! empty($this->parents[1]['category'])) {
            $current = ! empty($item->current) || in_array('current-menu-item', (array) $item->classes, true);
            $output .= '<li class="header-services__item">';
            $output .= $current ? '<span class="header-services__link landing-hero__nav-current" aria-current="page">' : '<a class="header-services__link" href="' . esc_url($this->menu_url($item->url)) . '"' . (wp_parse_url($item->url, PHP_URL_FRAGMENT) === 'calculator' ? ' data-open-calculator' : '') . (! empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '') . (! empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '') . '>';
            $output .= edemchinim_service_icon((int) ($item->object_id ?? 0), 'header-services__icon') . '<span>' . esc_html(wp_strip_all_tags($item->title)) . '</span>';
            if ($this->is_popular($item)) $output .= $this->service_icon(true) . '<span class="header-services__popular-label">Популярная услуга</span>';
            $output .= $current ? '</span>' : '</a>';
            return;
        }
        $item_classes = (array) $item->classes;
        $has_children = $depth < 2 && in_array('menu-item-has-children', $item_classes, true);
        $has_fragment = (bool) wp_parse_url((string) $item->url, PHP_URL_FRAGMENT);
        $is_current = ! $has_fragment && (! empty($item->current) || in_array('current-menu-item', $item_classes, true));
        $class = $depth === 0 ? 'landing-hero__nav-item' : ($depth === 1 ? 'landing-hero__submenu-item' : 'landing-hero__subsubmenu-item');
        if ($depth === 0 && $has_children) {
            $class .= ' landing-hero__nav-item--services';
            if ($this->normalise($item->title) === 'услуги') $class .= ' landing-hero__nav-item--catalog';
        }
        if ($has_children) {
            $this->parents[$depth] = array(
                'submenu_id' => 'landing-menu-submenu-' . (int) $item->ID,
                'url' => (string) $item->url,
                'title' => wp_strip_all_tags($item->title),
                'current' => $is_current,
                'services_menu' => $depth === 0 && $this->normalise($item->title) === 'услуги',
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
            if (wp_parse_url($url, PHP_URL_FRAGMENT) === 'calculator') {
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
            $output .= '<svg class="landing-hero__chevron" viewBox="0 0 16 16" fill="none" aria-hidden="true" focusable="false"><path d="M3 5.5 8 10.5 13 5.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        }
        $output .= $is_current ? '</span>' : '</a>';
    }

    public function end_el(&$output, $item, $depth = 0, $args = null)
    {
        $output .= '</li>';
    }
}
