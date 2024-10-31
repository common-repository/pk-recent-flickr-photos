<?PHP
/*
Plugin Name: Flickr Recent Photoz
Plugin URI: http://phalkunz.com/
Description: 
Author: Phalkunz Ponlu
Version: 1.0
Author URI: http://phalkunz.com

Copyright YEAR  pk_recent_flickr_photos  (email : phalkunz@gmail.com)

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
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/
 
require ('flickrAPI.php');

// size suffixes of flickr photos
define ('SIZE_S', 75);
define ('SIZE_T', 100);
define ('SIZE_M', 240);
define ('SIZE__', 500);

// This gets called at the init action
function widget_pK_flickr_recent_photos_init()
{
	// Check for the required API functions
	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
		return;
	
	// Tell Dynamic Sidebar about our new widget and its control
	register_sidebar_widget('PK Flickr Recent Photos', 'widget_pk_flickr_recent_photos');
 	register_widget_control("PK Flickr Recent Photos", "widget_pk_flickr_recent_photos_control", 300, 250);
}

// This prints the widget
function widget_pk_flickr_recent_photos($args)
{
	extract($args);
	global $post;

	$path_to_image_php = get_option('blogurl').'wp-content/plugins/pk-flickr-recent-photos/image.php';
	$the_title = get_option('pk-flickr-recent-photos-title');
	$the_username = get_option('pk-flickr-recent-photos-username');
	$the_width = get_option('pk-flickr-recent-photos-width');
	$the_height = get_option('pk-flickr-recent-photos-height');
	$the_number = get_option('pk-flickr-recent-photos-number');
	
	echo "<!-- Start PK Flickr Photos -->\n";
	
	echo $before_widget . $before_title . $the_title . $after_title;


	$flickr = new FlickrAPI();
	// get Flickr userid from a given username, $the_username
	$uid = getUserId($flickr, $the_username);
	// if the username is invalid ($uid='')
	if (trim($uid)=='') {
		echo 'Invalid username';

	} 
	else {
		$photoFormat = '_m';
		
		// get the optimal photo format (size)
		$longest = 0;
		if ($the_width > $the_height) {
			$longest = $the_width;
		}
		else {
			$longest = $the_height;
		}
		
		if ($longest <= SIZE_S) {
			$photoFormat = '_s';	
		} 
		elseif ($longest <= SIZE_T) {
			$photoFormat = '_t';
		}
		elseif ($longest <= SIZE_M) {
			$photoFormat = '_m';
		}
		else {
			$photoFormat = '';
		}
		
		$number_of_photos = 3; // default
		if ($the_number>0 && is_numeric($the_number)) {
			$number_of_photos = $the_number;
		}
		
		$photos = getRecentPhotos($flickr, $uid, $number_of_photos, $photoFormat);
		echo '<ul>';
		foreach($photos as $p) {
			$id = $p['id'];
			$farm = $p['farm'];
			$server = $p['server'];
			$secret = $p['secret'];
			$title = $p['title'];
			
			// url to the flickr photo
			$url = 'http://farm'.$farm.'.static.flickr.com/'.$server.'/'.$id.'_'.$secret.$format.'.jpg';
			// url to image resize method
			$image_url = $path_to_image_php.'?url='.$url.'&width='.$the_width.'&height='.$the_height;
			// url the photo's page
			$link_url = 'http://flickr.com/photos/'.$the_username.'/'.$id;
			
			echo "<li><a href=\"".$link_url."\"><img title=\"".$title."\" src=\"".$image_url."\" /></a></li> \n";
		}
		// More photos link
		echo '<li><a href="http://flickr.com/photos/'.$uid.'">More photos</a><li>';
		echo '</ul>';
	}
	echo $after_widget;
}

function widget_pk_flickr_recent_photos_control($args=null) {
	
	$title = get_option('pk-flickr-recent-photos-title');
	$width = get_option('pk-flickr-recent-photos-width');
	$height = get_option('pk-flickr-recent-photos-height');
	$username = get_option('pk-flickr-recent-photos-username');
	$number = get_option('pk-flickr-recent-photos-number');
	
	// check if the new parameters are submitted
	if ($_POST['pk-flickr-recent-photos-title']) {
		$title = strip_tags(stripslashes($_POST['pk-flickr-recent-photos-title']));
		update_option('pk-flickr-recent-photos-title', $title);
	}
	if ($_POST['pk-flickr-recent-photos-username']) {
		$username = strip_tags(stripslashes($_POST['pk-flickr-recent-photos-username']));
		update_option('pk-flickr-recent-photos-username', $username);
	}
	if ($_POST['pk-flickr-recent-photos-number']) {
		$number = strip_tags(stripslashes($_POST['pk-flickr-recent-photos-number']));
		update_option('pk-flickr-recent-photos-number', $number);
	}
	if ($_POST['pk-flickr-recent-photos-width']) {
		$width = strip_tags(stripslashes($_POST['pk-flickr-recent-photos-width']));
		update_option('pk-flickr-recent-photos-width', $width);
	}
	if ($_POST['pk-flickr-recent-photos-height']) {
		$height = strip_tags(stripslashes($_POST['pk-flickr-recent-photos-height']));
		update_option('pk-flickr-recent-photos-height', $height);
	}
	
	// if the width is invalid, assign the default value, 75
	if ($width == '' || $width == 0 || $width == null || !is_numeric($width)) {
		 $width = 75;
		 update_option('pk-flickr-recent-photos-width', $width);
	}
	// if the height is invalid, assign the default value, 75
	if ($height == '' || $height == 0 || $height == null || !is_numeric($height)) {
		 $height = 75;
		 update_option('pk-flickr-recent-photos-width', $height);
	}
	// if the number of photos is invalid, assign the default value, 3
	if ($number == '' || $number == 0 || $number == null || !is_numeric($number)) {
		 $number = 3;
		 update_option('pk-flickr-recent-photos-width', $number);
	}
	
	?>
	<!-- control form -->
	<p>
		<label for="pk-flickr-recent-photos-title">Title</lable>
		<input name="pk-flickr-recent-photos-title" value="<?php echo $title ?>" />
	</p>
	<p>
		<label for="pk-flickr-recent-photos-username">Username (required)</label>
		<input name="pk-flickr-recent-photos-username" value="<?php echo $username ?>" />
	</p>
	<p>
		<label for="pk-flickr-recent-photos-number">Number of photos</label>
		<input name="pk-flickr-recent-photos-number" value="<?php echo $number ?>" />
	</p>
	<p>
		<label for="pk-flickr-recent-photos-width">Width</label>
		<input name="pk-flickr-recent-photos-width" value="<?php echo $width ?>" /> px
	</p>
	<p>
		<label for="pk-flickr-recent-photos-height">Height</label>
		<input name="pk-flickr-recent-photos-height" value="<?php echo $height ?>" /> px</p>
	</p>
		
	<?php
}
	
// Delay plugin execution to ensure Dynamic Sidebar has a chance to load first
add_action('init', 'widget_pK_flickr_recent_photos_init');
?>