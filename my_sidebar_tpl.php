<!-- begin sidebar -->
<div id="sidebar">
	<?php

	global $post;
	$custom = get_post_custom($post->ID);
	$link = $custom["_sidebar"][0];
	if ($link != '') {
		echo '<ul id="widgets">';
		if (!function_exists('dynamic_sidebar') || !dynamic_sidebar($link)) :
		endif;
		echo '</ul>';
	}

	?>
</div>
<!-- end sidebar -->