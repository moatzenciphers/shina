<?php
namespace Edemchinim\Theme;

use Edemchinim\Calculator\Config;

if (! defined('ABSPATH')) {
	exit;
}

/** Enqueue frontend resources and browser-side configuration. */
final class Assets
{
	public static function enqueue()
	{
		$calculator_config = function_exists('get_field') ? get_field('shina_calculator_config', 'options') : array();
		$calculator_config = Config::prepare($calculator_config);

		$landing_css = get_template_directory() . '/css/landing.css';
		$landing_js = get_template_directory() . '/js/landing.js';
		if (file_exists($landing_css) && file_exists($landing_js)) {
			wp_enqueue_style('edemchinim-landing', get_template_directory_uri() . '/css/landing.css', array(), (string) filemtime($landing_css));
			wp_enqueue_script('edemchinim-main', get_template_directory_uri() . '/js/landing.js', array(), (string) filemtime($landing_js), true);
			wp_add_inline_script(
				'edemchinim-main',
				'window.shinaCalculatorConfig = ' . wp_json_encode($calculator_config) . ';'
					. "\nwindow.__CALCULATOR_URL__ = " . wp_json_encode(Config::url(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';',
				'before'
			);
		}
		if (Config::isPage()) {
			wp_enqueue_script('edemchinim-calculator', get_template_directory_uri() . '/js/calculator.js', array(),  _S_VERSION, true);
		}
	}

	public static function enqueueLinks()
	{
		wp_register_script('custom-inline', false, [], null, true);
		wp_enqueue_script('custom-inline');

		wp_add_inline_script('custom-inline', <<<'JS'
document.addEventListener('click', (e) => {
    const el = e.target.closest('[data-href]');
    if (!el) return;

    const url = el.dataset.href;
    const target = el.dataset.target || '_self';

    if (target === '_blank') {
        window.open(url, '_blank', 'noopener,noreferrer');
    } else {
        window.location.href = url;
    }
});
JS);
	}
}
