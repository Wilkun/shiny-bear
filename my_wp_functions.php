<?php

function add_custom_field_automatically($post_ID)
{
	global $wpdb;
	if (!wp_is_post_revision($post_ID)) {
		add_post_meta($post_ID, 'FIELD_NAME', 'CUSTOM VALUE', true);
	}

}

add_action('publish_page', 'add_custom_field_automatically');
add_action('publish_post' . 'add_custom_field_automatically');

function posts_status_color()
{

	?>
	<style>
		.status-draft{background: #FCE3F2 !important;}
		.status-pending{background: #87C5D6 !important;}
		.status-publish{/* no background keep wp alternating colors */}
		.status-future{background: #C6EBF5 !important;}
		.status-private{background:#F2D46F;}
	</style>
	<?php

}

add_action('admin_footer', 'posts_status_color');

function jquery_register()
{
	if (!is_admin()) {
		wp_deregister_script('jquery');
		wp_register_script('jquery',
				( 'http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js'), false,
				null, true);
		wp_enqueue_script('jquery');
	}

}

add_action('init', 'jquery_register');

/**
 * my_browser_body_class()
 * Add browser detection classes to body tag
 *
 * @global bool $is_lynx
 * @global bool $is_gecko
 * @global bool $is_IE
 * @global bool $is_opera
 * @global bool $is_NS4
 * @global bool $is_safari
 * @global bool $is_chrome
 * @global bool $is_iphone
 * @global bool $post
 * @param type $classes
 * @return array
 */
function my_browser_body_class($classes)
{
	global $is_lynx, $is_gecko, $is_IE, $is_opera, $is_NS4, $is_safari, $is_chrome, $is_iphone;

	if ($is_lynx)
		$classes[] = 'lynx';

	elseif ($is_gecko)
		$classes[] = 'gecko';

	elseif ($is_opera)
		$classes[] = 'opera';

	elseif ($is_NS4)
		$classes[] = 'ns4';

	elseif ($is_safari)
		$classes[] = 'safari';

	elseif ($is_chrome)
		$classes[] = 'chrome';

	elseif ($is_IE) {
		$browser = $_SERVER['HTTP_USER_AGENT'];
		$browser = substr("$browser", 25, 8);

		if ($browser == "MSIE 7.0")
			$classes[] = 'ie7';

		elseif ($browser == "MSIE 6.0")
			$classes[] = 'ie6';

		elseif ($browser == "MSIE 8.0")
			$classes[] = 'ie8';
		else
			$classes[] = 'ie';
	} else
		$classes[] = 'unknown';

	if ($is_iphone)
		$classes[] = 'iphone';

	if (is_page()) {
		global $post;
		$classes[] = 'page-' . $post->post_name;
	}

	return array_unique($classes);

}

add_filter('body_class', 'my_browser_body_class');

//add some JS that will be used on all subpages
function defaultJS()
{
	if (!is_admin()) {
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-tabs');
		fs_addCustomJS('helper-plugins/jquery.touchSwipe.min.js');
	}

}

add_action("wp_enqueue_scripts", "defaultJS");

//add some CSS that will be used on all subpages
function defaultCSS()
{
	addCustomCSS('global.css');
	addCustomCSS('print.css', false, false, 'print');

}

add_action("wp_enqueue_scripts", "defaultCSS");

/**
 * addCustomCSS()
 * Add custom css to WP header
 *
 * @param mixed $mFile name of internal CSS file / external CSS url (string) or array with strings.
 * @return bool
 */
function addCustomCSS($mFile = false, $bClear = false, $bNow = false, $sMedia = 'screen')
{
	static $aStyles = array();
	if ($bClear == true) {
		$aStyles = array();
		return true;
	}
	$sMedia = trim(strval($sMedia));
	$sVersion = (defined('_FSVersion') ? constant('_FSVersion') : '');
	$sMin = false;

	if ($mFile == "") {
		foreach ($aStyles as $aStyle) {
			wp_register_style(
					$aStyle['prefix'], $aStyle['file'], $aStyle['dep'], $aStyle['version'],
					$aStyle['media']
			);
			wp_enqueue_style($aStyle['prefix']);
		}
		return true;
	}
	if (!is_array($mFile)) {
		$mFile = array($mFile);
	}
	foreach ($mFile as $sFile) {
		if (!preg_match('/http(s)?:\/\//i', $sFile)) {
			//$sFile = basename( $sFile );
			if (strripos($sFile, '.css') !== (strlen($sFile) - 4)) {
				$sFile = $sFile . '.css';
			}
			if (empty($sVersion)) {
				//live, check for min
				$sMin = str_replace('.css', '.min.css', $sFile);
				if (!file_exists(get_template_directory() . '/css/' . $sMin)) {
					$sMin = false;
				} else {
					$sFile = $sMin;
				}
			}
			if ($sMin OR file_exists(get_template_directory() . '/css/' . $sFile)) {
				$sPrefix = 'fs_css_' . substr($sFile, 0, strripos($sFile, '.css'));
				$aStyles[$sPrefix] = array(
					'prefix' => $sPrefix,
					'file' => get_template_directory_uri() . '/css/' . $sFile,
					'dep' => array(),
					'version' => $sVersion,
					'media' => $sMedia,
				);
			}
		} else {
			$sPrefix = 'fs_css_' . substr(basename($sFile), 0, strripos($sFile, '.'));
			$aStyles[$sPrefix] = array(
				'prefix' => $sPrefix,
				'file' => get_template_directory_uri() . '/css/' . $sFile,
				'dep' => array(),
				'version' => false,
				'media' => $sMedia,
			);
		}
	}

	if ($bNow == true) {
		$aStylesNow = array_slice($aStyles, -count($mFile), count($mFile));
		foreach ($aStylesNow as $aStyle) {
			wp_register_style(
					$aStyle['prefix'], $aStyle['file'], $aStyle['dep'], $aStyle['version'],
					$aStyle['media']
			);
			wp_enqueue_style($aStyle['prefix']);
		}
		return true;
	}
	return false;

}

add_action('wp_enqueue_scripts', 'addCustomCSS');

/**
 * addCustomJS()
 * Add custom js to WP header or footer
 *
 * @param mixed $mFile name of internal JS file / external JS url (string) or array with strings.
 * @param bool $bFooter include file in footer ?
 * @return bool
 */
function addCustomJS($mFile, $bFooter = true, $bClear = false, $bNow = false)
{
	static $aScripts = array();
	if ($bClear == true) {
		$aScripts = array();
		return true;
	}
	$sVersion = (defined('_FSVersion') ? constant('_FSVersion') : '');
	$sMin = false;

	if ($mFile == "") {
		foreach ($aScripts as $aScript) {
			wp_register_script(
					$aScript['prefix'], $aScript['file'], $aScript['dep'], $aScript['version'],
					$aScript['footer']
			);
			wp_enqueue_script($aScript['prefix']);
		}
		return true;
	}

	$bFooter = ( bool ) $bFooter;
	if (!is_array($mFile)) {
		$mFile = array($mFile);
	}
	foreach ($mFile as $sFile) {
		if (!preg_match('/http(s)?:\/\//i', $sFile)) {
			//$sFile = basename( $sFile );
			if (strripos($sFile, '.js') !== (strlen($sFile) - 3)) {
				$sFile = $sFile . '.js';
			}
			if (empty($sVersion)) {
				//live, check for min
				$sMin = str_replace('.js', '.min.js', $sFile);
				if (!file_exists(get_template_directory() . '/js/' . $sMin)) {
					$sMin = false;
				} else {
					$sFile = $sMin;
				}
			}
			if ($sMin OR file_exists(get_template_directory() . '/js/' . $sFile)) {
				$sPrefix = 'fs_js_' . substr($sFile, 0, strripos($sFile, '.js'));
				$aScripts[$sPrefix] = array(
					'prefix' => $sPrefix,
					'file' => get_template_directory_uri() . '/js/' . $sFile,
					'dep' => array(),
					'version' => $sVersion,
					'footer' => $bFooter
				);
			}
		} elseif (wp_script_is($mFile, $list = 'registered')) {
			wp_enqueue_script($mFile);
		} else {
			$sPrefix = 'fs_js_' . substr(basename($sFile), 0, strripos($sFile, '.'));
			$aScripts[$sPrefix] = array(
				'prefix' => $sPrefix,
				'file' => $sFile,
				'dep' => array(),
				'version' => false,
				'footer' => $bFooter
			);
		}
	}
	if ($bNow == true) {
		$aScriptsNow = array_slice($aScripts, -count($mFile), count($mFile));
		foreach ($aScriptsNow as $aScript) {
			wp_register_script(
					$aScript['prefix'], $aScript['file'], $aScript['dep'], $aScript['version'],
					$aScript['footer']
			);
			wp_enqueue_script($aScript['prefix']);
		}
		return true;
	}
	return false;

}

add_action('wp_enqueue_scripts', 'addCustomJS');

/**
 * Tell the media panel to add the new size to the dropbown
 *
 * @param array $sizes
 * @return array
 */
function custom_image_sizes($sizes)
{

	$addsizes = array(
		"blog-large" => __("X-Large")
	);

	$newsizes = array_merge($sizes, $addsizes);

	return $newsizes;

}

add_filter('image_size_names_choose', 'custom_image_sizes');

function auto_featured_image()
{
	global $post;

	if (!has_post_thumbnail($post->ID)) {
		$attached_image = get_children("post_parent=$post->ID&post_type=attachment&post_mime_type=image&numberposts=1");

		if ($attached_image) {
			foreach ($attached_image as $attachment_id => $attachment) {
				set_post_thumbnail($post->ID, $attachment_id);
			}
		}
	}

}

/* Use it temporary to generate all featured images */
//add_action('the_post', 'auto_featured_image');
/* Used for new posts */
add_action('save_post', 'auto_featured_image');
add_action('draft_to_publish', 'auto_featured_image');
add_action('new_to_publish', 'auto_featured_image');
add_action('pending_to_publish', 'auto_featured_image');
add_action('future_to_publish', 'auto_featured_image');

function rw_relative_urls()
{
	/*
	 * Don't do anything if:
	 * - In feed
	 * - In sitemap by WordPress SEO plugin
	 */
	if (is_feed() || get_query_var('sitemap')) {
		return;
	}
	$filters = array(
		'post_link',
		'post_type_link',
		'page_link',
		'attachment_link',
		'get_shortlink',
		'post_type_archive_link',
		'get_pagenum_link',
		'get_comments_pagenum_link',
		'term_link',
		'search_link',
		'day_link',
		'month_link',
		'year_link',
	);
	foreach ($filters as $filter) {
		add_filter($filter, 'wp_make_link_relative');
	}

}

add_action('template_redirect', 'rw_relative_urls');