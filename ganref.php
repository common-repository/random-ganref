<?php
/*
Plugin Name:  Random Ganref Widget
Plugin URI: http://www.vjcatkick.com/?page_id=10022
Description: Display image from ganref.jp randomly with thumbnails
Version: 0.0.2
Author: V.J.Catkick
Author URI: http://www.vjcatkick.com/
*/

/*
License: GPL
Compatibility: WordPress 2.6 with Widget-plugin.

Installation:
Place the widget_single_photo folder in your /wp-content/plugins/ directory
and activate through the administration panel, and then go to the widget panel and
drag it to where you would like to have it!
*/

/*  Copyright V.J.Catkick - http://www.vjcatkick.com/

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


/* Changelog
* May 28 2009 - v0.0.1
- Initial release
* May 29 2009 - v0.0.2
- IE issue - fixed
*/


function widget_random_ganref_init() {
	if ( !function_exists('register_sidebar_widget') )
		return;

	function widget_random_ganref( $args ) {
		extract($args);

		$options = get_option('widget_random_ganref');
		$title = $options['widget_random_ganref_title'];
		$widget_random_ganref_username =  $options['widget_random_ganref_username'];
		$widget_random_ganref_disp_title = $options['widget_random_ganref_disp_title'];

		$widget_random_ganref_disp_random = $options['widget_random_ganref_disp_random'];
		$widget_random_ganref_disp_date = $options['widget_random_ganref_disp_date'];
		$widget_random_ganref_disp_thumbs = $options['widget_random_ganref_disp_thumbs'];
		$widget_random_ganref_num_thumbs = $options['widget_random_ganref_num_thumbs'];
		$widget_random_ganref_thumbs_randomly = $options['widget_random_ganref_thumbs_randomly'];

		$widget_random_ganref_width_main = $options['widget_random_ganref_width_main'];
		$widget_random_ganref_width_thumb = $options['widget_random_ganref_width_thumb'];
		$widget_random_ganref_margin_thumb = $options['widget_random_ganref_margin_thumb'];

		$widget_random_ganref_disp_ganref_link = $options['widget_random_ganref_disp_ganref_link'];

		$widget_random_ganref_cached = $options['widget_random_ganref_cached'];
		$widget_random_ganref_cached_time = $options['widget_random_ganref_cached_time'];

		$output = '<div id="widget_random_ganref"><ul>';

		// section main logic from here 

		$baseurl = 'http://ganref.jp/m/' . $widget_random_ganref_username . '/';
		$portfolios_url = $baseurl . 'portfolios';
		$photo_list_url = $portfolios_url . '/' . 'photo_list/page:';
		$photo_detail_url = $portfolios_url . '/' . 'photo_detail';

		if( strlen( $widget_random_ganref_username ) > 0 ) {

			$time_offset = 60 * 60;
			if( $widget_random_ganref_cached_time + $time_offset < time() ) {

				$page_counter = 1;
				$num_matches = 0;
				$result_array = array();
				do {

					$photo_list_url_paged = $photo_list_url . $page_counter;
					$has_next = 0;
					$filedata = @file_get_contents( $photo_list_url_paged );
					if( $filedata ) {
						$has_next = preg_match( '/class="tabNext">/s', $filedata, $tmp_matches );
						$num_hit = preg_match_all( '/<li class="heightNone">(.+?)<\/li>/s', $filedata, $matches );
						if( $num_hit  > 0 ) {
							$hit_result = $matches[1];
							$result_array = array_merge( $result_array, $hit_result );
							$num_matches += $num_hit;
						}else{
							break;
						} /* if else */
					}else{
						$output .= 'Ganref server down<br />';
						break;
					} /* if else */
					if( $has_next == 0 || !$has_next ) break;
					$page_counter = $page_counter + 1;
					if( $page_counter > 40 ) { $output .= 'err: overflow<br />'; break; }

				} while( $page_counter );

				$options['widget_random_ganref_cached'] = $result_array;
				$options['widget_random_ganref_cached_time'] = time();
				update_option('widget_random_ganref', $options);
			}else{
					$result_array = $options['widget_random_ganref_cached'];
					$output .= '<!-- random ganref cached -->';
			} /* else */


			if( count( $result_array ) > 0 ) {

				if( $widget_random_ganref_disp_random ) {
					$rnum = rand( 0, count( $result_array ) - 1 );
					$r_image = $result_array[ $rnum ];
				}else{
					$r_image = $result_array[0];
				} /* if else */

				$output .= widget_random_ganref_disp_one_image( $r_image, $widget_random_ganref_disp_title, $widget_random_ganref_disp_date, $widget_random_ganref_width_main );

				if( $widget_random_ganref_disp_thumbs ) {
					$i = 0;
					$disp_num = $widget_random_ganref_num_thumbs;
					$output .= '<div class="ganref_thumbs" style="margin-top: 4px; margin-left:' . $widget_random_ganref_margin_thumb . '; text-align:center;" >';
					if( $widget_random_ganref_thumbs_randomly ) shuffle( $result_array );
					foreach( $result_array as $rt ) {
						if( !$widget_random_ganref_disp_random ) { if( $i++ == 0 ) continue; }
						$output .= widget_random_ganref_disp_one_thumb( $rt, $widget_random_ganref_width_thumb, $widget_random_ganref_margin_thumb );
						if( --$disp_num == 0 ) break;
					} /* foreach */
					$output .= '</div>';
				} /* if */

				if( $widget_random_ganref_disp_ganref_link )
					$output .= widget_random_ganref_disp_url( $widget_random_ganref_username );

			} /* if */
		} /* if */

		// These lines generate the output
		$output .= '</ul></div>';

		echo $before_widget . $before_title . $title . $after_title;
		echo $output;
		echo $after_widget;
	} /* widget_random_ganref() */

	function widget_random_ganref_disp_one_thumb( $src, $size_css_str, $margin_size ) {
		$op = '';
		$m_size = '1px';
		$css_str = '';
		if( strlen( $size_css_str ) > 0 ) $css_str = 'width:' . $size_css_str . ';';

		if( strlen( $margin_size ) > 0 ) $m_size = $margin_size;
		if( preg_match( '/<div class="thumbMy">(.+?)<\/div>/s', $src, $tmp_matches ) > 0 ) {
			$r = preg_replace( '/<a href="\/m\//s', '<a target="_blank" href="http://ganref.jp/m/', $tmp_matches[1] );
			$op .= preg_replace( '/<img src=/s', '<img style="' . $css_str . ' margin-right: ' . $m_size . '; margin-bottom: ' . $m_size . ';" src=', $r );
		} /* if */

		return $op;
	} /* widget_random_ganref_disp_one_thumb() */

	function widget_random_ganref_disp_one_image( $src, $is_disp_title, $is_disp_date, $size_css_str ) {
		$op = '';
		$op .= '<div class="ganref_image" style="width: 100%; text-align:center;" >';
		$css_str = '';
		if( strlen( $size_css_str ) > 0 ) $css_str = 'width:' . $size_css_str . ';';

		if( preg_match( '/<div class="thumbMy">(.+?)<\/div>/s', $src, $tmp_matches ) > 0 ) {
			$r = preg_replace( '/<a href="\/m\//s', '<a target="_blank" href="http://ganref.jp/m/', $tmp_matches[1] );
			$op .= preg_replace( '/<img src=/s', '<img style="' . $css_str . ' " src=', $r );
			$op .= '<br />';
		} /* if */

		if( $is_disp_title ) {
			if( preg_match( '/<span class="title">(.+?)<\/span>/s', $src, $tmp_matches ) > 0 ) {
				$op .= preg_replace( '/<a href="\/m\//s', '<a target="_blank" href="http://ganref.jp/m/', $tmp_matches[1] );
				$op .= '<br />';
			} /* if */
		} /* if */

		if( $is_disp_date ) {
			if( preg_match( '/<span class="date">(.+?)<\/span>/s', $src, $tmp_matches ) > 0 ) {
				$op .= $tmp_matches[1];
//				$op .= preg_replace( '/<span class="date">/s', '<br /><span class="title">', $tmp_matches[1] );
			} /* if */
		} /* if */

		$op .= '</div>';

		return $op;
	} /* widget_random_ganref_disp_one_image() */

	function widget_random_ganref_disp_url( $username ) {
		$op = '';
		$op .= '<div class="ganref_url" style="text-align:center; font-size: 10px; color: #888;" >';
		$op .= '- <a href="http://ganref.jp/m/' . $username . '" target="_blank" >Ganref</a> -';
		$op .= '</div>';
		return $op;
	} /* widget_random_ganref_disp_url() */

	function widget_random_ganref_control() {
		$options = $newoptions = get_option('widget_random_ganref');
		if ( $_POST["widget_random_ganref_submit"] ) {
			$newoptions['widget_random_ganref_title'] = strip_tags(stripslashes($_POST["widget_random_ganref_title"]));
			$newoptions['widget_random_ganref_username'] = $_POST["widget_random_ganref_username"];

			$newoptions['widget_random_ganref_disp_random'] = (boolean)$_POST["widget_random_ganref_disp_random"];
			$newoptions['widget_random_ganref_disp_title'] = (boolean)$_POST["widget_random_ganref_disp_title"];
			$newoptions['widget_random_ganref_disp_date'] = (boolean)$_POST["widget_random_ganref_disp_date"];
			$newoptions['widget_random_ganref_disp_thumbs'] = (boolean)$_POST["widget_random_ganref_disp_thumbs"];
			$newoptions['widget_random_ganref_num_thumbs'] = (int) $_POST["widget_random_ganref_num_thumbs"];
			$newoptions['widget_random_ganref_thumbs_randomly'] = (boolean)$_POST["widget_random_ganref_thumbs_randomly"];

			$newoptions['widget_random_ganref_width_main'] = $_POST["widget_random_ganref_width_main"];
			$newoptions['widget_random_ganref_width_thumb'] = $_POST["widget_random_ganref_width_thumb"];
			$newoptions['widget_random_ganref_margin_thumb'] = $_POST["widget_random_ganref_margin_thumb"];

			$newoptions['widget_random_ganref_disp_ganref_link'] = (boolean)$_POST["widget_random_ganref_disp_ganref_link"];

			$newoptions['widget_random_ganref_cached'] = "";
			$newoptions['widget_random_ganref_cached_time'] = 0;
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('widget_random_ganref', $options);
		}

		// those are default value
		if ( !$options['widget_random_ganref_num_thumbs'] ) $options['widget_random_ganref_num_thumbs'] = 12;
		if ( !$options['widget_random_ganref_width_main'] ) $options['widget_random_ganref_width_main'] = '';
		if ( !$options['widget_random_ganref_width_thumb'] ) $options['widget_random_ganref_width_thumb'] = '42px';
		if ( !$options['widget_random_ganref_margin_thumb'] ) $options['widget_random_ganref_margin_thumb'] = '1px';

		$widget_random_ganref_username = $options['widget_random_ganref_username'];
		$widget_random_ganref_disp_title = $options['widget_random_ganref_disp_title'];

		$widget_random_ganref_disp_random = $options['widget_random_ganref_disp_random'];
		$widget_random_ganref_disp_date = $options['widget_random_ganref_disp_date'];
		$widget_random_ganref_disp_thumbs = $options['widget_random_ganref_disp_thumbs'];
		$widget_random_ganref_num_thumbs = $options['widget_random_ganref_num_thumbs'];
		$widget_random_ganref_thumbs_randomly = $options['widget_random_ganref_thumbs_randomly'];
		$widget_random_ganref_disp_ganref_link = $options['widget_random_ganref_disp_ganref_link'];

		$widget_random_ganref_width_main = $options['widget_random_ganref_width_main'];
		$widget_random_ganref_width_thumb = $options['widget_random_ganref_width_thumb'];
		$widget_random_ganref_margin_thumb = $options['widget_random_ganref_margin_thumb'];

		$title = htmlspecialchars($options['widget_random_ganref_title'], ENT_QUOTES);
?>

	    <?php _e('Title:'); ?> <input style="width: 170px;" id="widget_random_ganref_title" name="widget_random_ganref_title" type="text" value="<?php echo $title; ?>" /><br />

        <?php _e('User Name:'); ?> <input style="width: 85px;" id="widget_random_ganref_username" name="widget_random_ganref_username" type="text" value="<?php echo $widget_random_ganref_username; ?>" /><br />
		&nbsp;&nbsp;<span style="color: #888;" >http://ganref.jp/m/<span style="color: red;" >xxxxxx</span>/portfolios</span><br /><br />

        <input id="widget_random_ganref_disp_random" name="widget_random_ganref_disp_random" type="checkbox" value="1" <?php if( $widget_random_ganref_disp_random ) echo 'checked';?>/><?php _e(' Display randomly'); ?><br />

        <input id="widget_random_ganref_disp_title" name="widget_random_ganref_disp_title" type="checkbox" value="1" <?php if( $widget_random_ganref_disp_title ) echo 'checked';?>/><?php _e(' Display title'); ?><br />

        <input id="widget_random_ganref_disp_date" name="widget_random_ganref_disp_date" type="checkbox" value="1" <?php if( $widget_random_ganref_disp_date ) echo 'checked';?>/><?php _e(' Display date'); ?><br />

        <input id="widget_random_ganref_disp_thumbs" name="widget_random_ganref_disp_thumbs" type="checkbox" value="1" <?php if( $widget_random_ganref_disp_thumbs ) echo 'checked';?>/><?php _e(' Display recent thumbnails'); ?> <br />

        &nbsp;&nbsp;<?php _e('Number of thumbnails:'); ?> <input style="width: 75px;" id="widget_random_ganref_num_thumbs" name="widget_random_ganref_num_thumbs" type="text" value="<?php echo $widget_random_ganref_num_thumbs; ?>" /><br />

        <input id="widget_random_ganref_thumbs_randomly" name="widget_random_ganref_thumbs_randomly" type="checkbox" value="1" <?php if( $widget_random_ganref_thumbs_randomly ) echo 'checked';?>/><?php _e(' Thumbnails randomly'); ?> <br /><br />


		CSS section<br />
		&nbsp;&nbsp;<span style="color: #888;" >example: 40px, 100% etc</span><br />
        <?php _e('Main image width:'); ?> <input style="width: 75px;" id="widget_random_ganref_width_main" name="widget_random_ganref_width_main" type="text" value="<?php echo $widget_random_ganref_width_main; ?>" /><br />

        <?php _e('Thumbnail width:'); ?> <input style="width: 75px;" id="widget_random_ganref_width_thumb" name="widget_random_ganref_width_thumb" type="text" value="<?php echo $widget_random_ganref_width_thumb; ?>" /><br />
        <?php _e('Thumbnail margin:'); ?> <input style="width: 75px;" id="widget_random_ganref_margin_thumb" name="widget_random_ganref_margin_thumb" type="text" value="<?php echo $widget_random_ganref_margin_thumb; ?>" /><br /><br />


        <input id="widget_random_ganref_disp_ganref_link" name="widget_random_ganref_disp_ganref_link" type="checkbox" value="1" <?php if( $widget_random_ganref_disp_ganref_link ) echo 'checked';?>/><?php _e(' Display link to your Ganref page'); ?><br />
		
  	    <input type="hidden" id="widget_random_ganref_submit" name="widget_random_ganref_submit" value="1" />

<?php
	} /* widget_random_ganref_control() */

	register_sidebar_widget('Random Ganref', 'widget_random_ganref');
	register_widget_control('Random Ganref', 'widget_random_ganref_control' );
} /* widget_random_ganref_init() */

add_action('plugins_loaded', 'widget_random_ganref_init');

?>