<?php
/**
 * WordPress API for media display.
 *
 * @package WordPress
 * @subpackage Media
 */

/**
 * Scale down the default size of an image.
 *
 * This is so that the image is a better fit for the editor and theme.
 *
 * The `$size` parameter accepts either an array or a string. The supported string
 * values are 'thumb' or 'thumbnail' for the given thumbnail size or defaults at
 * 128 width and 96 height in pixels. Also supported for the string value is
 * 'medium' and 'full'. The 'full' isn't actually supported, but any value other
 * than the supported will result in the content_width size or 500 if that is
 * not set.
 *
 * Finally, there is a filter named {@see 'editor_max_image_size'}, that will be
 * called on the calculated array for width and height, respectively. The second
 * parameter will be the value that was in the $size parameter. The returned
 * type for the hook is an array with the width as the first element and the
 * height as the second element.
 *
 * @since 2.5.0
 *
 * @param int          $width   Width of the image in pixels.
 * @param int          $height  Height of the image in pixels.
 * @param string|array $size    Optional. Size or array of sizes of what the result image
 *                              should be. Accepts any valid image size name. Default 'medium'.
 * @param string       $context Optional. Could be 'display' (like in a theme) or 'edit'
 *                              (like inserting into an editor). Default null.
 * @return array Width and height of what the result image should resize to.
 */
function image_constrain_size_for_editor( $width, $height, $size = 'medium', $context = null ) {
	global $content_width, $_wp_additional_image_sizes;

	if ( ! $context )
		$context = is_admin() ? 'edit' : 'display';

	if ( is_array($size) ) {
		$max_width = $size[0];
		$max_height = $size[1];
	}
	elseif ( $size == 'thumb' || $size == 'thumbnail' ) {
		$max_width = intval(get_option('thumbnail_size_w'));
		$max_height = intval(get_option('thumbnail_size_h'));
		// last chance thumbnail size defaults
		if ( !$max_width && !$max_height ) {
			$max_width = 128;
			$max_height = 96;
		}
	}
	elseif ( $size == 'medium' ) {
		$max_width = intval(get_option('medium_size_w'));
		$max_height = intval(get_option('medium_size_h'));
		// if no width is set, default to the theme content width if available
	}
	elseif ( $size == 'large' ) {
		/*
		 * We're inserting a large size image into the editor. If it's a really
		 * big image we'll scale it down to fit reasonably within the editor
		 * itself, and within the theme's content width if it's known. The user
		 * can resize it in the editor if they wish.
		 */
		$max_width = intval(get_option('large_size_w'));
		$max_height = intval(get_option('large_size_h'));
		if ( intval($content_width) > 0 )
			$max_width = min( intval($content_width), $max_width );
	} elseif ( isset( $_wp_additional_image_sizes ) && count( $_wp_additional_image_sizes ) && in_array( $size, array_keys( $_wp_additional_image_sizes ) ) ) {
		$max_width = intval( $_wp_additional_image_sizes[$size]['width'] );
		$max_height = intval( $_wp_additional_image_sizes[$size]['height'] );
		if ( intval($content_width) > 0 && 'edit' == $context ) // Only in admin. Assume that theme authors know what they're doing.
			$max_width = min( intval($content_width), $max_width );
	}
	// $size == 'full' has no constraint
	else {
		$max_width = $width;
		$max_height = $height;
	}

	/**
	 * Filter the maximum image size dimensions for the editor.
	 *
	 * @since 2.5.0
	 *
	 * @param array        $max_image_size An array with the width as the first element,
	 *                                     and the height as the second element.
	 * @param string|array $size           Size of what the result image should be.
	 * @param string       $context        The context the image is being resized for.
	 *                                     Possible values are 'display' (like in a theme)
	 *                                     or 'edit' (like inserting into an editor).
	 */
	list( $max_width, $max_height ) = apply_filters( 'editor_max_image_size', array( $max_width, $max_height ), $size, $context );

	return wp_constrain_dimensions( $width, $height, $max_width, $max_height );
}

/**
 * Retrieve width and height attributes using given width and height values.
 *
 * Both attributes are required in the sense that both parameters must have a
 * value, but are optional in that if you set them to false or null, then they
 * will not be added to the returned string.
 *
 * You can set the value using a string, but it will only take numeric values.
 * If you wish to put 'px' after the numbers, then it will be stripped out of
 * the return.
 *
 * @since 2.5.0
 *
 * @param int|string $width  Image width in pixels.
 * @param int|string $height Image height in pixels.
 * @return string HTML attributes for width and, or height.
 */
function image_hwstring( $width, $height ) {
	$out = '';
	if ($width)
		$out .= 'width="'.intval($width).'" ';
	if ($height)
		$out .= 'height="'.intval($height).'" ';
	return $out;
}

/**
 * Scale an image to fit a particular size (such as 'thumb' or 'medium').
 *
 * Array with image url, width, height, and whether is intermediate size, in
 * that order is returned on success is returned. $is_intermediate is true if
 * $url is a resized image, false if it is the original.
 *
 * The URL might be the original image, or it might be a resized version. This
 * function won't create a new resized copy, it will just return an already
 * resized one if it exists.
 *
 * A plugin may use the 'image_downsize' filter to hook into and offer image
 * resizing services for images. The hook must return an array with the same
 * elements that are returned in the function. The first element being the URL
 * to the new image that was resized.
 *
 * @since 2.5.0
 *
 * @param int          $id   Attachment ID for image.
 * @param array|string $size Optional. Image size to scale to. Accepts a registered image size
 *                           or flat array of height and width values. Default 'medium'.
 * @return bool|array False on failure, array on success.
 */
function image_downsize( $id, $size = 'medium' ) {

	if ( !wp_attachment_is_image($id) )
		return false;

	/**
	 * Filter whether to preempt the output of image_downsize().
	 *
	 * Passing a truthy value to the filter will effectively short-circuit
	 * down-sizing the image, returning that value as output instead.
	 *
	 * @since 2.5.0
	 *
	 * @param bool         $downsize Whether to short-circuit the image downsize. Default false.
	 * @param int          $id       Attachment ID for image.
	 * @param array|string $size     Size of image, either array or string. Default 'medium'.
	 */
	if ( $out = apply_filters( 'image_downsize', false, $id, $size ) ) {
		return $out;
	}

	$img_url = wp_get_attachment_url($id);
	$meta = wp_get_attachment_metadata($id);
	$width = $height = 0;
	$is_intermediate = false;
	$img_url_basename = wp_basename($img_url);

	// try for a new style intermediate size
	if ( $intermediate = image_get_intermediate_size($id, $size) ) {
		$img_url = str_replace($img_url_basename, $intermediate['file'], $img_url);
		$width = $intermediate['width'];
		$height = $intermediate['height'];
		$is_intermediate = true;
	}
	elseif ( $size == 'thumbnail' ) {
		// fall back to the old thumbnail
		if ( ($thumb_file = wp_get_attachment_thumb_file($id)) && $info = getimagesize($thumb_file) ) {
			$img_url = str_replace($img_url_basename, wp_basename($thumb_file), $img_url);
			$width = $info[0];
			$height = $info[1];
			$is_intermediate = true;
		}
	}
	if ( !$width && !$height && isset( $meta['width'], $meta['height'] ) ) {
		// any other type: use the real image
		$width = $meta['width'];
		$height = $meta['height'];
	}

	if ( $img_url) {
		// we have the actual image size, but might need to further constrain it if content_width is narrower
		list( $width, $height ) = image_constrain_size_for_editor( $width, $height, $size );

		return array( $img_url, $width, $height, $is_intermediate );
	}
	return false;

}

/**
 * Register a new image size.
 *
 * Cropping behavior for the image size is dependent on the value of $crop:
 * 1. If false (default), images will be scaled, not cropped.
 * 2. If an array in the form of array( x_crop_position, y_crop_position ):
 *    - x_crop_position accepts 'left' 'center', or 'right'.
 *    - y_crop_position accepts 'top', 'center', or 'bottom'.
 *    Images will be cropped to the specified dimensions within the defined crop area.
 * 3. If true, images will be cropped to the specified dimensions using center positions.
 *
 * @since 2.9.0
 *
 * @global array $_wp_additional_image_sizes Associative array of additional image sizes.
 *
 * @param string     $name   Image size identifier.
 * @param int        $width  Image width in pixels.
 * @param int        $height Image height in pixels.
 * @param bool|array $crop   Optional. Whether to crop images to specified height and width or resize.
 *                           An array can specify positioning of the crop area. Default false.
 */
function add_image_size( $name, $width = 0, $height = 0, $crop = false ) {
	global $_wp_additional_image_sizes;

	$_wp_additional_image_sizes[ $name ] = array(
		'width'  => absint( $width ),
		'height' => absint( $height ),
		'crop'   => $crop,
	);
}

/**
 * Check if an image size exists.
 *
 * @since 3.9.0
 *
 * @param string $name The image size to check.
 * @return bool True if the image size exists, false if not.
 */
function has_image_size( $name ) {
	global $_wp_additional_image_sizes;

	return isset( $_wp_additional_image_sizes[ $name ] );
}

/**
 * Remove a new image size.
 *
 * @since 3.9.0
 *
 * @param string $name The image size to remove.
 * @return bool True if the image size was successfully removed, false on failure.
 */
function remove_image_size( $name ) {
	global $_wp_additional_image_sizes;

	if ( isset( $_wp_additional_image_sizes[ $name ] ) ) {
		unset( $_wp_additional_image_sizes[ $name ] );
		return true;
	}

	return false;
}

/**
 * Registers an image size for the post thumbnail.
 *
 * @since 2.9.0
 *
 * @see add_image_size() for details on cropping behavior.
 *
 * @param int        $width  Image width in pixels.
 * @param int        $height Image height in pixels.
 * @param bool|array $crop   Optional. Whether to crop images to specified height and width or resize.
 *                           An array can specify positioning of the crop area. Default false.
 */
function set_post_thumbnail_size( $width = 0, $height = 0, $crop = false ) {
	add_image_size( 'post-thumbnail', $width, $height, $crop );
}

/**
 * Gets an img tag for an image attachment, scaling it down if requested.
 *
 * The filter 'get_image_tag_class' allows for changing the class name for the
 * image without having to use regular expressions on the HTML content. The
 * parameters are: what WordPress will use for the class, the Attachment ID,
 * image align value, and the size the image should be.
 *
 * The second filter 'get_image_tag' has the HTML content, which can then be
 * further manipulated by a plugin to change all attribute values and even HTML
 * content.
 *
 * @since 2.5.0
 *
 * @param int          $id    Attachment ID.
 * @param string       $alt   Image Description for the alt attribute.
 * @param string       $title Image Description for the title attribute.
 * @param string       $align Part of the class name for aligning the image.
 * @param string|array $size  Optional. Registered image size to retrieve a tag for, or flat array
 *                            of height and width values. Default 'medium'.
 * @return string HTML IMG element for given image attachment
 */
function get_image_tag( $id, $alt, $title, $align, $size = 'medium' ) {

	list( $img_src, $width, $height ) = image_downsize($id, $size);
	$hwstring = image_hwstring($width, $height);

	$title = $title ? 'title="' . esc_attr( $title ) . '" ' : '';

	$class = 'align' . esc_attr($align) .' size-' . esc_attr($size) . ' wp-image-' . $id;

	/**
	 * Filter the value of the attachment's image tag class attribute.
	 *
	 * @since 2.6.0
	 *
	 * @param string $class CSS class name or space-separated list of classes.
	 * @param int    $id    Attachment ID.
	 * @param string $align Part of the class name for aligning the image.
	 * @param string $size  Optional. Default is 'medium'.
	 */
	$class = apply_filters( 'get_image_tag_class', $class, $id, $align, $size );

	$html = '<img src="' . esc_attr($img_src) . '" alt="' . esc_attr($alt) . '" ' . $title . $hwstring . 'class="' . $class . '" />';

	/**
	 * Filter the HTML content for the image tag.
	 *
	 * @since 2.6.0
	 *
	 * @param string $html  HTML content for the image.
	 * @param int    $id    Attachment ID.
	 * @param string $alt   Alternate text.
	 * @param string $title Attachment title.
	 * @param string $align Part of the class name for aligning the image.
	 * @param string $size  Optional. Default is 'medium'.
	 */
	$html = apply_filters( 'get_image_tag', $html, $id, $alt, $title, $align, $size );

	return $html;
}

/**
 * Calculates the new dimensions for a down-sampled image.
 *
 * If either width or height are empty, no constraint is applied on
 * that dimension.
 *
 * @since 2.5.0
 *
 * @param int $current_width  Current width of the image.
 * @param int $current_height Current height of the image.
 * @param int $max_width      Optional. Max width in pixels to constrain to. Default 0.
 * @param int $max_height     Optional. Max height in pixels to constrain to. Default 0.
 * @return array First item is the width, the second item is the height.
 */
function wp_constrain_dimensions( $current_width, $current_height, $max_width = 0, $max_height = 0 ) {
	if ( !$max_width && !$max_height )
		return array( $current_width, $current_height );

	$width_ratio = $height_ratio = 1.0;
	$did_width = $did_height = false;

	if ( $max_width > 0 && $current_width > 0 && $current_width > $max_width ) {
		$width_ratio = $max_width / $current_width;
		$did_width = true;
	}

	if ( $max_height > 0 && $current_height > 0 && $current_height > $max_height ) {
		$height_ratio = $max_height / $current_height;
		$did_height = true;
	}

	// Calculate the larger/smaller ratios
	$smaller_ratio = min( $width_ratio, $height_ratio );
	$larger_ratio  = max( $width_ratio, $height_ratio );

	if ( (int) round( $current_width * $larger_ratio ) > $max_width || (int) round( $current_height * $larger_ratio ) > $max_height ) {
 		// The larger ratio is too big. It would result in an overflow.
		$ratio = $smaller_ratio;
	} else {
		// The larger ratio fits, and is likely to be a more "snug" fit.
		$ratio = $larger_ratio;
	}

	// Very small dimensions may result in 0, 1 should be the minimum.
	$w = max ( 1, (int) round( $current_width  * $ratio ) );
	$h = max ( 1, (int) round( $current_height * $ratio ) );

	// Sometimes, due to rounding, we'll end up with a result like this: 465x700 in a 177x177 box is 117x176... a pixel short
	// We also have issues with recursive calls resulting in an ever-changing result. Constraining to the result of a constraint should yield the original result.
	// Thus we look for dimensions that are one pixel shy of the max value and bump them up

	// Note: $did_width means it is possible $smaller_ratio == $width_ratio.
	if ( $did_width && $w == $max_width - 1 ) {
		$w = $max_width; // Round it up
	}

	// Note: $did_height means it is possible $smaller_ratio == $height_ratio.
	if ( $did_height && $h == $max_height - 1 ) {
		$h = $max_height; // Round it up
	}

	/**
	 * Filter dimensions to constrain down-sampled images to.
	 *
	 * @since 4.1.0
	 *
	 * @param array $dimensions     The image width and height.
	 * @param int 	$current_width  The current width of the image.
	 * @param int 	$current_height The current height of the image.
	 * @param int 	$max_width      The maximum width permitted.
	 * @param int 	$max_height     The maximum height permitted.
	 */
	return apply_filters( 'wp_constrain_dimensions', array( $w, $h ), $current_width, $current_height, $max_width, $max_height );
}

/**
 * Retrieves calculated resize dimensions for use in WP_Image_Editor.
 *
 * Calculates dimensions and coordinates for a resized image that fits
 * within a specified width and height.
 *
 * Cropping behavior is dependent on the value of $crop:
 * 1. If false (default), images will not be cropped.
 * 2. If an array in the form of array( x_crop_position, y_crop_position ):
 *    - x_crop_position accepts 'left' 'center', or 'right'.
 *    - y_crop_position accepts 'top', 'center', or 'bottom'.
 *    Images will be cropped to the specified dimensions within the defined crop area.
 * 3. If true, images will be cropped to the specified dimensions using center positions.
 *
 * @since 2.5.0
 *
 * @param int        $orig_w Original width in pixels.
 * @param int        $orig_h Original height in pixels.
 * @param int        $dest_w New width in pixels.
 * @param int        $dest_h New height in pixels.
 * @param bool|array $crop   Optional. Whether to crop image to specified height and width or resize.
 *                           An array can specify positioning of the crop area. Default false.
 * @return bool|array False on failure. Returned array matches parameters for `imagecopyresampled()`.
 */
function image_resize_dimensions($orig_w, $orig_h, $dest_w, $dest_h, $crop = false) {

	if ($orig_w <= 0 || $orig_h <= 0)
		return false;
	// at least one of dest_w or dest_h must be specific
	if ($dest_w <= 0 && $dest_h <= 0)
		return false;

	/**
	 * Filter whether to preempt calculating the image resize dimensions.
	 *
	 * Passing a non-null value to the filter will effectively short-circuit
	 * image_resize_dimensions(), returning that value instead.
	 *
	 * @since 3.4.0
	 *
	 * @param null|mixed $null   Whether to preempt output of the resize dimensions.
	 * @param int        $orig_w Original width in pixels.
	 * @param int        $orig_h Original height in pixels.
	 * @param int        $dest_w New width in pixels.
	 * @param int        $dest_h New height in pixels.
	 * @param bool|array $crop   Whether to crop image to specified height and width or resize.
	 *                           An array can specify positioning of the crop area. Default false.
	 */
	$output = apply_filters( 'image_resize_dimensions', null, $orig_w, $orig_h, $dest_w, $dest_h, $crop );
	if ( null !== $output )
		return $output;

	if ( $crop ) {
		// crop the largest possible portion of the original image that we can size to $dest_w x $dest_h
		$aspect_ratio = $orig_w / $orig_h;
		$new_w = min($dest_w, $orig_w);
		$new_h = min($dest_h, $orig_h);

		if ( ! $new_w ) {
			$new_w = (int) round( $new_h * $aspect_ratio );
		}

		if ( ! $new_h ) {
			$new_h = (int) round( $new_w / $aspect_ratio );
		}

		$size_ratio = max($new_w / $orig_w, $new_h / $orig_h);

		$crop_w = round($new_w / $size_ratio);
		$crop_h = round($new_h / $size_ratio);

		if ( ! is_array( $crop ) || count( $crop ) !== 2 ) {
			$crop = array( 'center', 'center' );
		}

		list( $x, $y ) = $crop;

		if ( 'left' === $x ) {
			$s_x = 0;
		} elseif ( 'right' === $x ) {
			$s_x = $orig_w - $crop_w;
		} else {
			$s_x = floor( ( $orig_w - $crop_w ) / 2 );
		}

		if ( 'top' === $y ) {
			$s_y = 0;
		} elseif ( 'bottom' === $y ) {
			$s_y = $orig_h - $crop_h;
		} else {
			$s_y = floor( ( $orig_h - $crop_h ) / 2 );
		}
	} else {
		// don't crop, just resize using $dest_w x $dest_h as a maximum bounding box
		$crop_w = $orig_w;
		$crop_h = $orig_h;

		$s_x = 0;
		$s_y = 0;

		list( $new_w, $new_h ) = wp_constrain_dimensions( $orig_w, $orig_h, $dest_w, $dest_h );
	}

	// if the resulting image would be the same size or larger we don't want to resize it
	if ( $new_w >= $orig_w && $new_h >= $orig_h && $dest_w != $orig_w && $dest_h != $orig_h ) {
		return false;
	}

	// the return array matches the parameters to imagecopyresampled()
	// int dst_x, int dst_y, int src_x, int src_y, int dst_w, int dst_h, int src_w, int src_h
	return array( 0, 0, (int) $s_x, (int) $s_y, (int) $new_w, (int) $new_h, (int) $crop_w, (int) $crop_h );

}

/**
 * Resizes an image to make a thumbnail or intermediate size.
 *
 * The returned array has the file size, the image width, and image height. The
 * filter 'image_make_intermediate_size' can be used to hook in and change the
 * values of the returned array. The only parameter is the resized file path.
 *
 * @since 2.5.0
 *
 * @param string $file   File path.
 * @param int    $width  Image width.
 * @param int    $height Image height.
 * @param bool   $crop   Optional. Whether to crop image to specified height and width or resize.
 *                       Default false.
 * @return bool|array False, if no image was created. Metadata array on success.
 */
function image_make_intermediate_size( $file, $width, $height, $crop = false ) {
	if ( $width || $height ) {
		$editor = wp_get_image_editor( $file );

		if ( is_wp_error( $editor ) || is_wp_error( $editor->resize( $width, $height, $crop ) ) )
			return false;

		$resized_file = $editor->save();

		if ( ! is_wp_error( $resized_file ) && $resized_file ) {
			unset( $resized_file['path'] );
			return $resized_file;
		}
	}
	return false;
}

/**
 * Retrieves the image's intermediate size (resized) path, width, and height.
 *
 * The $size parameter can be an array with the width and height respectively.
 * If the size matches the 'sizes' metadata array for width and height, then it
 * will be used. If there is no direct match, then the nearest image size larger
 * than the specified size will be used. If nothing is found, then the function
 * will break out and return false.
 *
 * The metadata 'sizes' is used for compatible sizes that can be used for the
 * parameter $size value.
 *
 * The url path will be given, when the $size parameter is a string.
 *
 * If you are passing an array for the $size, you should consider using
 * add_image_size() so that a cropped version is generated. It's much more
 * efficient than having to find the closest-sized image and then having the
 * browser scale down the image.
 *
 * @since 2.5.0
 *
 * @param int          $post_id Attachment ID.
 * @param array|string $size    Optional. Registered image size to retrieve or flat array of height
 *                              and width dimensions. Default 'thumbnail'.
 * @return bool|array False on failure or array of file path, width, and height on success.
 */
function image_get_intermediate_size( $post_id, $size = 'thumbnail' ) {
	if ( !is_array( $imagedata = wp_get_attachment_metadata( $post_id ) ) )
		return false;

	// get the best one for a specified set of dimensions
	if ( is_array($size) && !empty($imagedata['sizes']) ) {
		$areas = array();

		foreach ( $imagedata['sizes'] as $_size => $data ) {
			// already cropped to width or height; so use this size
			if ( ( $data['width'] == $size[0] && $data['height'] <= $size[1] ) || ( $data['height'] == $size[1] && $data['width'] <= $size[0] ) ) {
				$file = $data['file'];
				list($width, $height) = image_constrain_size_for_editor( $data['width'], $data['height'], $size );
				return compact( 'file', 'width', 'height' );
			}
			// add to lookup table: area => size
			$areas[$data['width'] * $data['height']] = $_size;
		}
		if ( !$size || !empty($areas) ) {
			// find for the smallest image not smaller than the desired size
			ksort($areas);
			foreach ( $areas as $_size ) {
				$data = $imagedata['sizes'][$_size];
				if ( $data['width'] >= $size[0] || $data['height'] >= $size[1] ) {
					// Skip images with unexpectedly divergent aspect ratios (crops)
					// First, we calculate what size the original image would be if constrained to a box the size of the current image in the loop
					$maybe_cropped = image_resize_dimensions($imagedata['width'], $imagedata['height'], $data['width'], $data['height'], false );
					// If the size doesn't match within one pixel, then it is of a different aspect ratio, so we skip it, unless it's the thumbnail size
					if ( 'thumbnail' != $_size && ( !$maybe_cropped || ( $maybe_cropped[4] != $data['width'] && $maybe_cropped[4] + 1 != $data['width'] ) || ( $maybe_cropped[5] != $data['height'] && $maybe_cropped[5] + 1 != $data['height'] ) ) )
						continue;
					// If we're still here, then we're going to use this size
					$file = $data['file'];
					list($width, $height) = image_constrain_size_for_editor( $data['width'], $data['height'], $size );
					return compact( 'file', 'width', 'height' );
				}
			}
		}
	}

	if ( is_array($size) || empty($size) || empty($imagedata['sizes'][$size]) )
		return false;

	$data = $imagedata['sizes'][$size];
	// include the full filesystem path of the intermediate file
	if ( empty($data['path']) && !empty($data['file']) ) {
		$file_url = wp_get_attachment_url($post_id);
		$data['path'] = path_join( dirname($imagedata['file']), $data['file'] );
		$data['url'] = path_join( dirname($file_url), $data['file'] );
	}
	return $data;
}

/**
 * Gets the available intermediate image sizes.
 *
 * @since 3.0.0
 *
 * @global array $_wp_additional_image_sizes
 *
 * @return array Returns a filtered array of image size strings.
 */
function get_intermediate_image_sizes() {
	global $_wp_additional_image_sizes;
	$image_sizes = array('thumbnail', 'medium', 'large'); // Standard sizes
	if ( isset( $_wp_additional_image_sizes ) && count( $_wp_additional_image_sizes ) )
		$image_sizes = array_merge( $image_sizes, array_keys( $_wp_additional_image_sizes ) );

	/**
	 * Filter the list of intermediate image sizes.
	 *
	 * @since 2.5.0
	 *
	 * @param array $image_sizes An array of intermediate image sizes. Defaults
	 *                           are 'thumbnail', 'medium', 'large'.
	 */
	return apply_filters( 'intermediate_image_sizes', $image_sizes );
}

/**
 * Retrieve an image to represent an attachment.
 *
 * A mime icon for files, thumbnail or intermediate size for images.
 *
 * @since 2.5.0
 *
 * @param int          $attachment_id Image attachment ID.
 * @param string|array $size          Optional. Registered image size to retrieve the source for or a flat
 *                                    array of height and width dimensions. Default 'thumbnail'.
 * @param bool         $icon          Optional. Whether the image should be treated as an icon. Default false.
 * @return bool|array Returns an array (url, width, height), or false, if no image is available.
 */
function wp_get_attachment_image_src( $attachment_id, $size = 'thumbnail', $icon = false ) {

	// get a thumbnail or intermediate image if there is one
	if ( $image = image_downsize($attachment_id, $size) )
		return $image;

	$src = false;

	if ( $icon && $src = wp_mime_type_icon($attachment_id) ) {
		/** This filter is documented in wp-includes/post.php */
		$icon_dir = apply_filters( 'icon_dir', ABSPATH . WPINC . '/images/media' );

		$src_file = $icon_dir . '/' . wp_basename($src);
		@list($width, $height) = getimagesize($src_file);
	}
	if ( $src && $width && $height )
		return array( $src, $width, $height );
	return false;
}

/**
 * Get an HTML img element representing an image attachment
 *
 * While `$size` will accept an array, it is better to register a size with
 * add_image_size() so that a cropped version is generated. It's much more
 * efficient than having to find the closest-sized image and then having the
 * browser scale down the image.
 *
 * @since 2.5.0
 *
 * @param int          $attachment_id Image attachment ID.
 * @param string|array $size          Optional. Registered image size or flat array of height and width
 *                                    dimensions. Default 'thumbnail'.
 * @param bool         $icon          Optional. Whether the image should be treated as an icon. Default false.
 * @param string|array $attr          Optional. Attributes for the image markup. Default empty.
 * @return string HTML img element or empty string on failure.
 */
function wp_get_attachment_image($attachment_id, $size = 'thumbnail', $icon = false, $attr = '') {

	$html = '';
	$image = wp_get_attachment_image_src($attachment_id, $size, $icon);
	if ( $image ) {
		list($src, $width, $height) = $image;
		$hwstring = image_hwstring($width, $height);
		$size_class = $size;
		if ( is_array( $size_class ) ) {
			$size_class = join( 'x', $size_class );
		}
		$attachment = get_post($attachment_id);
		$default_attr = array(
			'src'	=> $src,
			'class'	=> "attachment-$size_class",
			'alt'	=> trim(strip_tags( get_post_meta($attachment_id, '_wp_attachment_image_alt', true) )), // Use Alt field first
		);
		if ( empty($default_attr['alt']) )
			$default_attr['alt'] = trim(strip_tags( $attachment->post_excerpt )); // If not, Use the Caption
		if ( empty($default_attr['alt']) )
			$default_attr['alt'] = trim(strip_tags( $attachment->post_title )); // Finally, use the title

		$attr = wp_parse_args($attr, $default_attr);

		/**
		 * Filter the list of attachment image attributes.
		 *
		 * @since 2.8.0
		 *
		 * @param array        $attr       Attributes for the image markup.
		 * @param WP_Post      $attachment Image attachment post.
		 * @param string|array $size       Requested size.
		 */
		$attr = apply_filters( 'wp_get_attachment_image_attributes', $attr, $attachment, $size );
		$attr = array_map( 'esc_attr', $attr );
		$html = rtrim("<img $hwstring");
		foreach ( $attr as $name => $value ) {
			$html .= " $name=" . '"' . $value . '"';
		}
		$html .= ' />';
	}

	return $html;
}

/**
 * Adds a 'wp-post-image' class to post thumbnails. Internal use only.
 *
 * Uses the 'begin_fetch_post_thumbnail_html' and 'end_fetch_post_thumbnail_html' action hooks to
 * dynamically add/remove itself so as to only filter post thumbnails.
 *
 * @ignore
 * @since 2.9.0
 *
 * @param array $attr Thumbnail attributes including src, class, alt, title.
 * @return array Modified array of attributes including the new 'wp-post-image' class.
 */
function _wp_post_thumbnail_class_filter( $attr ) {
	$attr['class'] .= ' wp-post-image';
	return $attr;
}

/**
 * Adds '_wp_post_thumbnail_class_filter' callback to the 'wp_get_attachment_image_attributes'
 * filter hook. Internal use only.
 *
 * @ignore
 * @since 2.9.0
 *
 * @param array $attr Thumbnail attributes including src, class, alt, title.
 */
function _wp_post_thumbnail_class_filter_add( $attr ) {
	add_filter( 'wp_get_attachment_image_attributes', '_wp_post_thumbnail_class_filter' );
}

/**
 * Removes the '_wp_post_thumbnail_class_filter' callback from the 'wp_get_attachment_image_attributes'
 * filter hook. Internal use only.
 *
 * @ignore
 * @since 2.9.0
 *
 * @param array $attr Thumbnail attributes including src, class, alt, title.
 */
function _wp_post_thumbnail_class_filter_remove( $attr ) {
	remove_filter( 'wp_get_attachment_image_attributes', '_wp_post_thumbnail_class_filter' );
}

add_shortcode('wp_caption', 'img_caption_shortcode');
add_shortcode('caption', 'img_caption_shortcode');

/**
 * Builds the Caption shortcode output.
 *
 * Allows a plugin to replace the content that would otherwise be returned. The
 * filter is 'img_caption_shortcode' and passes an empty string, the attr
 * parameter and the content parameter values.
 *
 * The supported attributes for the shortcode are 'id', 'align', 'width', and
 * 'caption'.
 *
 * @since 2.6.0
 *
 * @param array  $attr {
 *     Attributes of the caption shortcode.
 *
 *     @type string $id      ID of the div element for the caption.
 *     @type string $align   Class name that aligns the caption. Default 'alignnone'. Accepts 'alignleft',
 *                           'aligncenter', alignright', 'alignnone'.
 *     @type int    $width   The width of the caption, in pixels.
 *     @type string $caption The caption text.
 *     @type string $class   Additional class name(s) added to the caption container.
 * }
 * @param string $content Shortcode content.
 * @return string HTML content to display the caption.
 */
function img_caption_shortcode( $attr, $content = null ) {
	// New-style shortcode with the caption inside the shortcode with the link and image tags.
	if ( ! isset( $attr['caption'] ) ) {
		if ( preg_match( '#((?:<a [^>]+>\s*)?<img [^>]+>(?:\s*</a>)?)(.*)#is', $content, $matches ) ) {
			$content = $matches[1];
			$attr['caption'] = trim( $matches[2] );
		}
	}

	/**
	 * Filter the default caption shortcode output.
	 *
	 * If the filtered output isn't empty, it will be used instead of generating
	 * the default caption template.
	 *
	 * @since 2.6.0
	 *
	 * @see img_caption_shortcode()
	 *
	 * @param string $output  The caption output. Default empty.
	 * @param array  $attr    Attributes of the caption shortcode.
	 * @param string $content The image element, possibly wrapped in a hyperlink.
	 */
	$output = apply_filters( 'img_caption_shortcode', '', $attr, $content );
	if ( $output != '' )
		return $output;

	$atts = shortcode_atts( array(
		'id'	  => '',
		'align'	  => 'alignnone',
		'width'	  => '',
		'caption' => '',
		'class'   => '',
	), $attr, 'caption' );

	$atts['width'] = (int) $atts['width'];
	if ( $atts['width'] < 1 || empty( $atts['caption'] ) )
		return $content;

	if ( ! empty( $atts['id'] ) )
		$atts['id'] = 'id="' . esc_attr( $atts['id'] ) . '" ';

	$class = trim( 'wp-caption ' . $atts['align'] . ' ' . $atts['class'] );

	if ( current_theme_supports( 'html5', 'caption' ) ) {
		return '<figure ' . $atts['id'] . 'style="width: ' . (int) $atts['width'] . 'px;" class="' . esc_attr( $class ) . '">'
		. do_shortcode( $content ) . '<figcaption class="wp-caption-text">' . $atts['caption'] . '</figcaption></figure>';
	}

	$caption_width = 10 + $atts['width'];

	/**
	 * Filter the width of an image's caption.
	 *
	 * By default, the caption is 10 pixels greater than the width of the image,
	 * to prevent post content from running up against a floated image.
	 *
	 * @since 3.7.0
	 *
	 * @see img_caption_shortcode()
	 *
	 * @param int    $caption_width Width of the caption in pixels. To remove this inline style,
	 *                              return zero.
	 * @param array  $atts          Attributes of the caption shortcode.
	 * @param string $content       The image element, possibly wrapped in a hyperlink.
	 */
	$caption_width = apply_filters( 'img_caption_shortcode_width', $caption_width, $atts, $content );

	$style = '';
	if ( $caption_width )
		$style = 'style="width: ' . (int) $caption_width . 'px" ';

	return '<div ' . $atts['id'] . $style . 'class="' . esc_attr( $class ) . '">'
	. do_shortcode( $content ) . '<p class="wp-caption-text">' . $atts['caption'] . '</p></div>';
}

add_shortcode('gallery', 'gallery_shortcode');

/**
 * Builds the Gallery shortcode output.
 *
 * This implements the functionality of the Gallery Shortcode for displaying
 * WordPress images on a post.
 *
 * @since 2.5.0
 *
 * @param array $attr {
 *     Attributes of the gallery shortcode.
 *
 *     @type string $order      Order of the images in the gallery. Default 'ASC'. Accepts 'ASC', 'DESC'.
 *     @type string $orderby    The field to use when ordering the images. Default 'menu_order ID'.
 *                              Accepts any valid SQL ORDERBY statement.
 *     @type int    $id         Post ID.
 *     @type string $itemtag    HTML tag to use for each image in the gallery.
 *                              Default 'dl', or 'figure' when the theme registers HTML5 gallery support.
 *     @type string $icontag    HTML tag to use for each image's icon.
 *                              Default 'dt', or 'div' when the theme registers HTML5 gallery support.
 *     @type string $captiontag HTML tag to use for each image's caption.
 *                              Default 'dd', or 'figcaption' when the theme registers HTML5 gallery support.
 *     @type int    $columns    Number of columns of images to display. Default 3.
 *     @type string $size       Size of the images to display. Default 'thumbnail'.
 *     @type string $ids        A comma-separated list of IDs of attachments to display. Default empty.
 *     @type string $include    A comma-separated list of IDs of attachments to include. Default empty.
 *     @type string $exclude    A comma-separated list of IDs of attachments to exclude. Default empty.
 *     @type string $link       What to link each image to. Default empty (links to the attachment page).
 *                              Accepts 'file', 'none'.
 * }
 * @return string HTML content to display gallery.
 */
function gallery_shortcode( $attr ) {
	$post = get_post();

	static $instance = 0;
	$instance++;

	if ( ! empty( $attr['ids'] ) ) {
		// 'ids' is explicitly ordered, unless you specify otherwise.
		if ( empty( $attr['orderby'] ) ) {
			$attr['orderby'] = 'post__in';
		}
		$attr['include'] = $attr['ids'];
	}

	/**
	 * Filter the default gallery shortcode output.
	 *
	 * If the filtered output isn't empty, it will be used instead of generating
	 * the default gallery template.
	 *
	 * @since 2.5.0
	 * @since 4.2.0 The `$instance` parameter was added.
	 *
	 * @see gallery_shortcode()
	 *
	 * @param string $output   The gallery output. Default empty.
	 * @param array  $attr     Attributes of the gallery shortcode.
	 * @param int    $instance Unique numeric ID of this gallery shortcode instance.
	 */
	$output = apply_filters( 'post_gallery', '', $attr, $instance );
	if ( $output != '' ) {
		return $output;
	}

	$html5 = current_theme_supports( 'html5', 'gallery' );
	$atts = shortcode_atts( array(
		'order'      => 'ASC',
		'orderby'    => 'menu_order ID',
		'id'         => $post ? $post->ID : 0,
		'itemtag'    => $html5 ? 'figure'     : 'dl',
		'icontag'    => $html5 ? 'div'        : 'dt',
		'captiontag' => $html5 ? 'figcaption' : 'dd',
		'columns'    => 3,
		'size'       => 'thumbnail',
		'include'    => '',
		'exclude'    => '',
		'link'       => ''
	), $attr, 'gallery' );

	$id = intval( $atts['id'] );

	if ( ! empty( $atts['include'] ) ) {
		$_attachments = get_posts( array( 'include' => $atts['include'], 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby'] ) );

		$attachments = array();
		foreach ( $_attachments as $key => $val ) {
			$attachments[$val->ID] = $_attachments[$key];
		}
	} elseif ( ! empty( $atts['exclude'] ) ) {
		$attachments = get_children( array( 'post_parent' => $id, 'exclude' => $atts['exclude'], 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby'] ) );
	} else {
		$attachments = get_children( array( 'post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby'] ) );
	}

	if ( empty( $attachments ) ) {
		return '';
	}

	if ( is_feed() ) {
		$output = "\n";
		foreach ( $attachments as $att_id => $attachment ) {
			$output .= wp_get_attachment_link( $att_id, $atts['size'], true ) . "\n";
		}
		return $output;
	}

	$itemtag = tag_escape( $atts['itemtag'] );
	$captiontag = tag_escape( $atts['captiontag'] );
	$icontag = tag_escape( $atts['icontag'] );
	$valid_tags = wp_kses_allowed_html( 'post' );
	if ( ! isset( $valid_tags[ $itemtag ] ) ) {
		$itemtag = 'dl';
	}
	if ( ! isset( $valid_tags[ $captiontag ] ) ) {
		$captiontag = 'dd';
	}
	if ( ! isset( $valid_tags[ $icontag ] ) ) {
		$icontag = 'dt';
	}

	$columns = intval( $atts['columns'] );
	$itemwidth = $columns > 0 ? floor(100/$columns) : 100;
	$float = is_rtl() ? 'right' : 'left';

	$selector = "gallery-{$instance}";

	$gallery_style = '';

	/**
	 * Filter whether to print default gallery styles.
	 *
	 * @since 3.1.0
	 *
	 * @param bool $print Whether to print default gallery styles.
	 *                    Defaults to false if the theme supports HTML5 galleries.
	 *                    Otherwise, defaults to true.
	 */
	if ( apply_filters( 'use_default_gallery_style', ! $html5 ) ) {
		$gallery_style = "
		<style type='text/css'>
			#{$selector} {
				margin: auto;
			}
			#{$selector} .gallery-item {
				float: {$float};
				margin-top: 10px;
				text-align: center;
				width: {$itemwidth}%;
			}
			#{$selector} img {
				border: 2px solid #cfcfcf;
			}
			#{$selector} .gallery-caption {
				margin-left: 0;
			}
			/* see gallery_shortcode() in wp-includes/media.php */
		</style>\n\t\t";
	}

	$size_class = sanitize_html_class( $atts['size'] );
	$gallery_div = "<div id='$selector' class='gallery galleryid-{$id} gallery-columns-{$columns} gallery-size-{$size_class}'>";

	/**
	 * Filter the default gallery shortcode CSS styles.
	 *
	 * @since 2.5.0
	 *
	 * @param string $gallery_style Default CSS styles and opening HTML div container
	 *                              for the gallery shortcode output.
	 */
	$output = apply_filters( 'gallery_style', $gallery_style . $gallery_div );

	$i = 0;
	foreach ( $attachments as $id => $attachment ) {

		$attr = ( trim( $attachment->post_excerpt ) ) ? array( 'aria-describedby' => "$selector-$id" ) : '';
		if ( ! empty( $atts['link'] ) && 'file' === $atts['link'] ) {
			$image_output = wp_get_attachment_link( $id, $atts['size'], false, false, false, $attr );
		} elseif ( ! empty( $atts['link'] ) && 'none' === $atts['link'] ) {
			$image_output = wp_get_attachment_image( $id, $atts['size'], false, $attr );
		} else {
			$image_output = wp_get_attachment_link( $id, $atts['size'], true, false, false, $attr );
		}
		$image_meta  = wp_get_attachment_metadata( $id );

		$orientation = '';
		if ( isset( $image_meta['height'], $image_meta['width'] ) ) {
			$orientation = ( $image_meta['height'] > $image_meta['width'] ) ? 'portrait' : 'landscape';
		}
		$output .= "<{$itemtag} class='gallery-item'>";
		$output .= "
			<{$icontag} class='gallery-icon {$orientation}'>
				$image_output
			</{$icontag}>";
		if ( $captiontag && trim($attachment->post_excerpt) ) {
			$output .= "
				<{$captiontag} class='wp-caption-text gallery-caption' id='$selector-$id'>
				" . wptexturize($attachment->post_excerpt) . "
				</{$captiontag}>";
		}
		$output .= "</{$itemtag}>";
		if ( ! $html5 && $columns > 0 && ++$i % $columns == 0 ) {
			$output .= '<br style="clear: both" />';
		}
	}

	if ( ! $html5 && $columns > 0 && $i % $columns !== 0 ) {
		$output .= "
			<br style='clear: both' />";
	}

	$output .= "
		</div>\n";

	return $output;
}

/**
 * Outputs the templates used by playlists.
 *
 * @since 3.9.0
 */
function wp_underscore_playlist_templates() {
?>
<script type="text/html" id="tmpl-wp-playlist-current-item">
	<# if ( data.image ) { #>
	<img src="{{ data.thumb.src }}"/>
	<# } #>
	<div class="wp-playlist-caption">
		<span class="wp-playlist-item-meta wp-playlist-item-title">&#8220;{{ data.title }}&#8221;</span>
		<# if ( data.meta.album ) { #><span class="wp-playlist-item-meta wp-playlist-item-album">{{ data.meta.album }}</span><# } #>
		<# if ( data.meta.artist ) { #><span class="wp-playlist-item-meta wp-playlist-item-artist">{{ data.meta.artist }}</span><# } #>
	</div>
</script>
<script type="text/html" id="tmpl-wp-playlist-item">
	<div class="wp-playlist-item">
		<a class="wp-playlist-caption" href="{{ data.src }}">
			{{ data.index ? ( data.index + '. ' ) : '' }}
			<# if ( data.caption ) { #>
				{{ data.caption }}
			<# } else { #>
				<span class="wp-playlist-item-title">&#8220;{{{ data.title }}}&#8221;</span>
				<# if ( data.artists && data.meta.artist ) { #>
				<span class="wp-playlist-item-artist"> &mdash; {{ data.meta.artist }}</span>
				<# } #>
			<# } #>
		</a>
		<# if ( data.meta.length_formatted ) { #>
		<div class="wp-playlist-item-length">{{ data.meta.length_formatted }}</div>
		<# } #>
	</div>
</script>
<?php
}

/**
 * Outputs and enqueue default scripts and styles for playlists.
 *
 * @since 3.9.0
 *
 * @param string $type Type of playlist. Accepts 'audio' or 'video'.
 */
function wp_playlist_scripts( $type ) {
	wp_enqueue_style( 'wp-mediaelement' );
	wp_enqueue_script( 'wp-playlist' );
?>
<!--[if lt IE 9]><script>document.createElement('<?php echo esc_js( $type ) ?>');</script><![endif]-->
<?php
	add_action( 'wp_footer', 'wp_underscore_playlist_templates', 0 );
	add_action( 'admin_footer', 'wp_underscore_playlist_templates', 0 );
}

/**
 * Builds the Playlist shortcode output.
 *
 * This implements the functionality of the playlist shortcode for displaying
 * a collection of WordPress audio or video files in a post.
 *
 * @since 3.9.0
 *
 * @param array $attr {
 *     Array of default playlist attributes.
 *
 *     @type string  $type         Type of playlist to display. Accepts 'audio' or 'video'. Default 'audio'.
 *     @type string  $order        Designates ascending or descending order of items in the playlist.
 *                                 Accepts 'ASC', 'DESC'. Default 'ASC'.
 *     @type string  $orderby      Any column, or columns, to sort the playlist. If $ids are
 *                                 passed, this defaults to the order of the $ids array ('post__in').
 *                                 Otherwise default is 'menu_order ID'.
 *     @type int     $id           If an explicit $ids array is not present, this parameter
 *                                 will determine which attachments are used for the playlist.
 *                                 Default is the current post ID.
 *     @type array   $ids          Create a playlist out of these explicit attachment IDs. If empty,
 *                                 a playlist will be created from all $type attachments of $id.
 *                                 Default empty.
 *     @type array   $exclude      List of specific attachment IDs to exclude from the playlist. Default empty.
 *     @type string  $style        Playlist style to use. Accepts 'light' or 'dark'. Default 'light'.
 *     @type bool    $tracklist    Whether to show or hide the playlist. Default true.
 *     @type bool    $tracknumbers Whether to show or hide the numbers next to entries in the playlist. Default true.
 *     @type bool    $images       Show or hide the video or audio thumbnail (Featured Image/post
 *                                 thumbnail). Default true.
 *     @type bool    $artists      Whether to show or hide artist name in the playlist. Default true.
 * }
 *
 * @return string Playlist output. Empty string if the passed type is unsupported.
 */
function wp_playlist_shortcode( $attr ) {
	global $content_width;
	$post = get_post();

	static $instance = 0;
	$instance++;

	if ( ! empty( $attr['ids'] ) ) {
		// 'ids' is explicitly ordered, unless you specify otherwise.
		if ( empty( $attr['orderby'] ) ) {
			$attr['orderby'] = 'post__in';
		}
		$attr['include'] = $attr['ids'];
	}

	/**
	 * Filter the playlist output.
	 *
	 * Passing a non-empty value to the filter will short-circuit generation
	 * of the default playlist output, returning the passed value instead.
	 *
	 * @since 3.9.0
	 * @since 4.2.0 The `$instance` parameter was added.
	 *
	 * @param string $output   Playlist output. Default empty.
	 * @param array  $attr     An array of shortcode attributes.
	 * @param int    $instance Unique numeric ID of this playlist shortcode instance.
	 */
	$output = apply_filters( 'post_playlist', '', $attr, $instance );
	if ( $output != '' ) {
		return $output;
	}

	$atts = shortcode_atts( array(
		'type'		=> 'audio',
		'order'		=> 'ASC',
		'orderby'	=> 'menu_order ID',
		'id'		=> $post ? $post->ID : 0,
		'include'	=> '',
		'exclude'   => '',
		'style'		=> 'light',
		'tracklist' => true,
		'tracknumbers' => true,
		'images'	=> true,
		'artists'	=> true
	), $attr, 'playlist' );

	$id = intval( $atts['id'] );

	if ( $atts['type'] !== 'audio' ) {
		$atts['type'] = 'video';
	}

	$args = array(
		'post_status' => 'inherit',
		'post_type' => 'attachment',
		'post_mime_type' => $atts['type'],
		'order' => $atts['order'],
		'orderby' => $atts['orderby']
	);

	if ( ! empty( $atts['include'] ) ) {
		$args['include'] = $atts['include'];
		$_attachments = get_posts( $args );

		$attachments = array();
		foreach ( $_attachments as $key => $val ) {
			$attachments[$val->ID] = $_attachments[$key];
		}
	} elseif ( ! empty( $atts['exclude'] ) ) {
		$args['post_parent'] = $id;
		$args['exclude'] = $atts['exclude'];
		$attachments = get_children( $args );
	} else {
		$args['post_parent'] = $id;
		$attachments = get_children( $args );
	}

	if ( empty( $attachments ) ) {
		return '';
	}

	if ( is_feed() ) {
		$output = "\n";
		foreach ( $attachments as $att_id => $attachment ) {
			$output .= wp_get_attachment_link( $att_id ) . "\n";
		}
		return $output;
	}

	$outer = 22; // default padding and border of wrapper

	$default_width = 640;
	$default_height = 360;

	$theme_width = empt