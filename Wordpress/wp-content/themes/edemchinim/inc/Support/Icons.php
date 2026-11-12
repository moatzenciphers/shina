<?php
namespace Edemchinim\Support;

if (! defined('ABSPATH')) {
	exit;
}

/** Theme asset URLs and calculator icon rendering. */
final class Icons
{
	public static function assetUrl($path)
	{
		return trailingslashit(get_template_directory_uri()) . ltrim($path, '/');
	}

	public static function option($file)
	{
		$relative_path = 'img/' . ltrim($file, '/');

		if (file_exists(trailingslashit(get_template_directory()) . $relative_path)) {
			printf(
				'<img src="%s" alt="" loading="lazy" decoding="async">',
				esc_url(self::assetUrl($relative_path))
			);
			return;
		}

		echo '<span class="icon-circle" aria-hidden="true"></span>';
	}

	public static function phone()
	{
?>
		<svg class="icon-circle icon-circle--small" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
			<path fill="currentColor" d="M4.05 21q-.45 0-.75-.3t-.3-.75V15.9q0-.325.225-.587t.575-.363l3.45-.7q.35-.05.713.063t.587.337L10.9 17q.95-.55 1.8-1.213t1.625-1.437q.825-.8 1.513-1.662t1.187-1.788L14.6 8.45q-.2-.2-.275-.475T14.3 7.3l.65-3.5q.05-.325.325-.562T15.9 3h4.05q.45 0 .75.3t.3.75q0 3.125-1.362 6.175t-3.863 5.55t-5.55 3.863T4.05 21"></path>
		</svg>
<?php
	}
}
