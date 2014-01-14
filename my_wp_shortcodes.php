<?php

/*
 * ShortCode Library
 * 
 */

/**
 * Shortcode to display data only for admins
 *
 * @example [note]This is a personal note that only admins can see![/note]
 * @param type $atts
 * @param type $content
 * @return string
 */
function fs_admin_note( $atts, $content = null ) {
	if ( current_user_can( 'manage_options' ) )
		return '<div class="note">' . $content . '</div>';
	return '';

}

add_shortcode( 'note', 'fs_admin_note' );

/**
 * Shrtocode to display chart using google chart api
 *
 * @example [chart data="41.52,37.79,20.67,0.03" bg="F7F9FA" labels="Reffering+sites|Search+Engines|Direct+traffic|Other" colors="058DC7,50B432,ED561B,EDEF00" size="488x200" title="Traffic Sources" type="pie"]
 *
 * @param type $atts
 * @return type
 */
function chart_shortcode( $atts ) {
	extract( shortcode_atts( array(
		'data' => '',
		'colors' => '',
		'size' => '400x200',
		'bg' => 'ffffff',
		'title' => '',
		'labels' => '',
		'advanced' => '',
		'type' => 'pie'
					), $atts ) );

	switch ( $type ) {
		case 'line' :
			$charttype = 'lc';
			break;
		case 'xyline' :
			$charttype = 'lxy';
			break;
		case 'sparkline' :
			$charttype = 'ls';
			break;
		case 'meter' :
			$charttype = 'gom';
			break;
		case 'scatter' :
			$charttype = 's';
			break;
		case 'venn' :
			$charttype = 'v';
			break;
		case 'pie' :
			$charttype = 'p3';
			break;
		case 'pie2d' :
			$charttype = 'p';
			break;
		default :
			$charttype = $type;
			break;
	}

	if ( $title )
		$string .= '&chtt=' . $title . '';
	if ( $labels )
		$string .= '&chl=' . $labels . '';
	if ( $colors )
		$string .= '&chco=' . $colors . '';
	$string .= '&chs=' . $size . '';
	$string .= '&chd=t:' . $data . '';
	$string .= '&chf=' . $bg . '';

	return '<img title="' . $title . '" src="http://chart.apis.google.com/chart?cht=' . $charttype . '' . $string . $advanced . '" alt="' . $title . '" />';

}

add_shortcode( 'chart', 'chart_shortcode' );

/**
 * Displays current year
 *
 * @example [fs_year]
 *
 * @param type $atts
 * @return type
 */
function fs_display_year( $atts ) {

	// Attributes
	extract( shortcode_atts(
					array(
		'plus' => '',
		'minus' => '',
					), $atts )
	);

	// Code

	return date( 'Y' );

}

add_shortcode( 'fs_year', 'fs_display_year' );

add_shortcode( 'action-button', 'action_button_shortcode' );

/**
 * Shortcode to display an action button.
 * @example <b>[action-button color="blue" class="alignleft" title="Download Now" subtitle="Version 1.0.1 – Mac OSX" url="#"]</b>
 *
 * @param mixed $atts
 * @return string
 */
function action_button_shortcode( $atts ) {
	extract( shortcode_atts(
					array(
		'color' => 'blue',
		'class' => '',
		'desc' => '',
		'title' => 'Title',
		'subtitle' => '',
		'url' => '',
		'target' => '_self',
					), $atts
	) );

	$html = HtmlTag::createElement( 'span' );
	$html->addClass( 'action-button' )
			->addClass( $color . '-button' );
	if ( $class ) {
		$aClasses = explode( ' ', $class );
		foreach ( $aClasses as $aClass ) {
			$html->addClass( $aClass );
		}
	}
	$html->addElement( 'a' )
			->set( 'href', $url )
			->set( 'title', ($desc ? $desc : $title ) )
			->set( 'target', $target )
			->set( 'role', 'button' )
			->setText( $title );
	if ( $subtitle ) {
		$html->addElement( 'span' )
				->addClass( 'subtitle' )
				->setText( $subtitle );
	}
	$return = $html;

	return $return;

}

/**
 * Add Bookmark shortcode [bookmark linkid=8]
 *
 * @example
 *
 * @param type $atts
 * @param type $content
 * @return string <a title="Extreme Web Design" href="http://www.extremewebdesign.biz/" target="_blank">Extreme Web Design</a>
 */
function bookmark_shortcode( $atts, $content = null ) {
	extract( shortcode_atts( array(
		'linkid' => '0'
					), $atts ) );
	$bookmark = get_bookmark( $linkid );
	return '<a title="' . $bookmark->link_description . '" href="' . $bookmark->link_url . '" target="' . $bookmark->link_target . '">' . $bookmark->link_name . '</a>';

}

add_shortcode( "bookmark", "bookmark_shortcode" );

//Activate the Link Manager built in to the WordPress admin
//add_filter( 'pre_option_link_manager_enabled', '__return_true' );