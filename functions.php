<?php
/*#############################################
HERE YOU CAN ADD YOUR OWN FUNCTIONS OR REPLACE FUNCTIONS OF THE PARENT THEME
#############################################*/
// Here we enqueue the parent styles before child theme styles
add_action( 'wp_enqueue_scripts', 'sd_enqueue_styles' );
function sd_enqueue_styles() {
    wp_enqueue_script( 'supreme', get_stylesheet_directory_uri() . '/js/supreme.js', array(), '1.0.0', true ); 
    wp_enqueue_style( 'directory-theme-child-style', get_stylesheet_uri(), array( 'directory-theme-style', 'directory-theme-style-responsive' ) );
 
}

// Here we defind the textdomain for the child theme, if changing you should also replace it in the function below. 
if (!defined('SD_CHILD')) define('SD_CHILD', 'supreme-directory');


add_action('after_setup_theme', 'sd_theme_setup');
function sd_theme_setup(){
   // load_child_theme_textdomain( SD_CHILD, get_stylesheet_directory() . '/languages' ); // uncomment this if you plan to use translation
}





// add extra classes via body_class filter
add_filter('body_class','sd_custom_body_class');

function sd_custom_body_class($classes) {
    // add 'sd' to the default autogenerated classes, for this we need to modify the $classes array. 
    $classes[] = 'sd';
    if ( geodir_is_page('location') ) {	
    $classes[] = 'sd-location';
    }
    if ( geodir_is_page('preview') ) {	
    $classes[] = 'sd-preview';
    }
    // return the modified $classes array
    return $classes;
}

//remove breadcrumb from search, listings and detail page
remove_action('geodir_search_before_main_content', 'geodir_breadcrumb', 20);
remove_action('geodir_listings_before_main_content', 'geodir_breadcrumb', 20);
remove_action('geodir_detail_before_main_content', 'geodir_breadcrumb', 20);

//add search widget on top of search results and in listings page
add_action('geodir_search_content','sd_search_form_on_search_page', 1);
add_action('geodir_listings_content','sd_search_form_on_search_page', 1);

function sd_search_form_on_search_page() {echo do_shortcode('[gd_advanced_search]');}

//add map in right sidebar of search results and listings page

add_action('geodir_search_sidebar_right_inside','sd_map_right');
add_action('geodir_listings_sidebar_right_inside','sd_map_right');
function sd_map_right() {echo do_shortcode('[gd_listing_map width=100% autozoom=true]'); }

add_action('geodir_listings_content','sd_mobile_map_buttons', 0);
add_action('geodir_search_content','sd_mobile_map_buttons', 0);
function sd_mobile_map_buttons() {
		echo '<div class="sd-mobile-search-controls">
			<a class="dt-btn" id="showSearch" href="#">
				<i class="fa fa-search"></i> '. __('SEARCH LISTINGS', directory-starter) .'</a> 
			<a class="dt-btn" id="hideMap" href="#"><i class="fa fa-th-large">
				</i> '. __('SHOW LISTINGS', directory-starter) .'</a>
			<a class="dt-btn" id="showMap" href="#"><i class="fa fa-map-o">
				</i> '. __('SHOW MAP', directory-starter) .'</a>
			</div>'; }

/*################################
      DETAIL PAGE FUNCTONS
##################################*/


//remove the preview page code to move it inside the featured area
remove_action('geodir_detail_before_main_content', 'geodir_action_geodir_preview_code', 9);

// Add featured banner and listing details above wrapper
add_action('geodir_wrapper_open','sup_add_feat_img_head',4,1);
function sup_add_feat_img_head($page){
  if($page=='details-page'){
    global $preview,$post;
    $default_img_url = "http://url.com/img.png";
    if($preview){
      geodir_action_geodir_set_preview_post();//Set the $post value if previewing a post.
      $post_images = array();
           if (isset($post->post_images) && !empty($post->post_images)) {
               $post->post_images = trim($post->post_images, ",");
               $post_images = explode(",", $post->post_images);
           }
      $full_image_url = (isset($post_images[0])) ? $post_images[0] : $default_img_url;
    }else{
      if ( has_post_thumbnail() ) {
      $full_image_urls = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );
	$full_image_url = $full_image_urls[0];
	}else{
        $full_image_url = $default_img_url;
      }
    }
    
    ?>
    <div class="featured-area">


<div class="featured-img" style="background-image: url(<?php echo $full_image_url; ?>);" ></div>
<?php if ($preview) { echo geodir_action_geodir_preview_code(); } ?>
</div>
    <?php
    
$post_avgratings = geodir_get_post_rating($post->ID);
		$post_ratings = geodir_get_rating_stars($post_avgratings, $post->ID);
		ob_start();
		geodir_comments_number($post->rating_count);
		$n_comments = ob_get_clean();
		$entry_author = get_avatar( get_the_author_meta( 'email' ), 100 );
		$author_link  = get_author_posts_url( get_the_author_meta( 'ID' ) );
		$author_name = get_the_author();
		$postlink = get_permalink(geodir_add_listing_page_id());
              $editlink = geodir_getlink($postlink, array('pid' => $post->ID), false);
		$user_id = get_current_user_id();
		$post_type = $post->post_type;
		$post_type_data = get_post_type_object( $post_type );
		$post_type_slug = $post_type_data->rewrite['slug'];
		$post_tax = $post_type."category";
		$post_cats = $post->$post_tax;
		$cats_arr = array_filter(explode(",", $post_cats));
		$cat_icons = geodir_get_term_icon();
		
		?>
		<div class="sd-detail-details">
		<div class="container">
			<div class="sd-detail-author">
		<?php 
		printf( '<div class="author-avatar"><a href="%s">%s</a></div>', $author_link, $entry_author );
		printf( '<div class="author-link"><a href="%s">%s</a></div>', $author_link, $author_name );
		if (is_user_logged_in() && $user_id == $post->post_author) {
                            ?>
				<a href="<?php echo $editlink; ?>" class="supreme-btn supreme-btn-small"><i class="fa fa-edit"></i> <?php echo __('Edit', directory-starter); ?></a>
                            <?php } ?>
			</div><!-- sd-detail-suthor end -->
			<div class="sd-detail-info">
		<?php
		echo '<h1 class="sd-entry-title">'.get_the_title().'</h1>';
		echo '<div class="sd-address">'.$post->post_city.', '.$post->post_region.', '.$post->post_country.'</div>';
		echo '<div class="sd-ratings">'.$post_ratings.' - <a href="'.get_comments_link().'" class="geodir-pcomments">'.$n_comments.'</a></div>';
		echo '<div class="sd-contacts">';
		if(isset($post->geodir_website) && $post->geodir_website){
		echo '<a href="'.$post->geodir_website.'"><i class="fa fa-external-link-square"></i></a>'; }
		if(isset($post->geodir_facebook) && $post->geodir_facebook){
		echo '<a href="'.$post->geodir_facebook.'"><i class="fa fa-facebook-official"></i></a>'; }
		if(isset($post->geodir_twitter) && $post->geodir_twitter){
		echo '<a href="'.$post->geodir_twitter.'"><i class="fa fa-twitter-square"></i></a>'; }
		if(isset($post->geodir_contact) && $post->geodir_contact){
		echo '<a href="tel:'.$post->geodir_contact.'"><i class="fa fa-phone-square"></i>&nbsp;:&nbsp;'.$post->geodir_contact.'</a>'; }
		echo '</div>';
		echo '<div class="sd-detail-cat-links"><ul>';
		foreach($cats_arr as $cat){
		$term_arr = get_term( $cat, $post_tax);
		$term_icon = $cat_icons[$cat];
		$term_url = get_term_link( intval($cat), $post_tax );
		echo '<li><a href="'.$term_url.'"><img src="'.$term_icon.'">';
		echo '<span class="cat-link">'.$term_arr->name.'</span>';
		echo '</a></li>';
		}
		echo '</ul></div> <!-- sd-detail-cat-links end --> </div> <!-- sd-detail-info end -->';
		echo '<div class="sd-detail-cta"><a class="dt-btn" href="'.get_the_permalink().'#respond">'.__('Write a Review', 'supreme-directory').'</a>';	
		?>
		<div class="geodir_more_info geodir_email"><span style="" class="geodir-i-email"><i class="fa fa-envelope"></i><a href="javascript:void(1);" class="b_send_inquiry2" onclick="jQuery( '.b_send_inquiry' ).click();">Send Enquiry</a> | <a class="b_sendtofriend" href="javascript:void(0);">Send To Friend</a></span></div>
		<?php
		geodir_favourite_html($post->post_author, $post->ID);
		echo '</div><!-- sd-detail-cta end -->';?>
		

</div><!-- container end -->
		</div><!-- sd-detail-details end -->



<?php }   

}


//remove title from listing detail page
remove_action('geodir_details_main_content', 'geodir_action_page_title', 20);

//remove slider from listing detail page
remove_action( 'geodir_details_main_content', 'geodir_action_details_slider',30);

// remove details from sidebar
function my_change_sidebar_content_order() {
    return array( 
'geodir_detail_page_more_info',							 
);
}
add_filter('geodir_detail_page_sidebar_content', 'my_change_sidebar_content_order');

// Remove taxonomies from detail page content
remove_action('geodir_details_main_content', 'geodir_action_details_taxonomies', 40);

add_action('geodir_details_main_content', 'sd_listing_owner', 0);

function sd_listing_owner() {
	
}


// Remove tabs and reorder the content in a tall vertical page
remove_action( 'geodir_details_main_content', 'geodir_show_detail_page_tabs',60);

add_action( 'geodir_details_main_content', 'my_geodir_show_detail_page_tabs',60);

function my_geodir_show_detail_page_tabs()
{
 
    global $post, $post_images, $video, $special_offers, $related_listing, $geodir_post_detail_fields;
 
    $post_id = !empty($post) && isset($post->ID) ? (int)$post->ID : 0;
    $request_post_id = !empty($_REQUEST['p']) ? (int)$_REQUEST['p'] : 0;
    $is_backend_preview = (is_single() && !empty($_REQUEST['post_type']) && !empty($_REQUEST['preview']) && !empty($_REQUEST['p'])) && is_super_admin() ? true : false; // skip if preview from backend
 
    if ($is_backend_preview && !$post_id > 0 && $request_post_id > 0) {
        $post = geodir_get_post_info($request_post_id);
        setup_postdata($post);
    }
 
    $geodir_post_detail_fields = geodir_show_listing_info('detail');
 
    if (geodir_is_page('detail')) {
 
        $video = geodir_get_video($post->ID);
        $special_offers = geodir_get_special_offers($post->ID);
        $related_listing_array = array();
        if (get_option('geodir_add_related_listing_posttypes'))
            $related_listing_array = get_option('geodir_add_related_listing_posttypes');
 
        $related_listing = '';
        if (in_array($post->post_type, $related_listing_array)) {
            $request = array('post_number' => get_option('geodir_related_post_count'),
                'relate_to' => get_option('geodir_related_post_relate_to'),
                'layout' => get_option('geodir_related_post_listing_view'),
                'add_location_filter' => get_option('geodir_related_post_location_filter'),
                'list_sort' => get_option('geodir_related_post_sortby'),
                'character_count' => get_option('geodir_related_post_excerpt'));
 
            $related_listing = geodir_related_posts_display($request);
        }
 
        $post_images = geodir_get_images($post->ID, 'thumbnail');
        $count_images = count((array)$post_images);
        $thumb_image = '';
        if (!empty($post_images)) {
            foreach ($post_images as $image) {
                $thumb_image .= '<a href="' . $image->src . '">';
                $thumb_image .= geodir_show_image($image, 'thumbnail', true, false);
                $thumb_image .= '</a>';
            }
        }
 
        $map_args = array();
        $map_args['map_canvas_name'] = 'detail_page_map_canvas';
        $map_args['width'] = '600';
        $map_args['height'] = '300';
        if ($post->post_mapzoom) {
            $map_args['zoom'] = '' . $post->post_mapzoom . '';
        }
        $map_args['autozoom'] = false;
        $map_args['child_collapse'] = '0';
        $map_args['enable_cat_filters'] = false;
        $map_args['enable_text_search'] = false;
        $map_args['enable_post_type_filters'] = false;
        $map_args['enable_location_filters'] = false;
        $map_args['enable_jason_on_load'] = true;
        $map_args['enable_map_direction'] = true;
        $map_args['map_class_name'] = 'geodir-map-detail-page';
 
    } elseif (geodir_is_page('preview')) {
 
        $video = isset($post->geodir_video) ? $post->geodir_video : '';
        $special_offers = isset($post->geodir_special_offers) ? $post->geodir_special_offers : '';
 
        if (isset($post->post_images))
            $post->post_images = trim($post->post_images, ",");
 
        if (isset($post->post_images) && !empty($post->post_images))
            $post_images = explode(",", $post->post_images);
 	 $count_images = count($post_images);			
        $thumb_image = '';
        if (!empty($post_images)) {
            foreach ($post_images as $image) {
                if ($image != '') {
                    $thumb_image .= '<a href="' . $image . '">';
                    $thumb_image .= geodir_show_image(array('src' => $image), 'thumbnail', true, false);
                    $thumb_image .= '</a>';
                }
            }
        }
 
        global $map_jason;
        $map_jason[] = $post->marker_json;
 
        $address_latitude = isset($post->post_latitude) ? $post->post_latitude : '';
        $address_longitude = isset($post->post_longitude) ? $post->post_longitude : '';
        $mapview = isset($post->post_mapview) ? $post->post_mapview : '';
        $mapzoom = isset($post->post_mapzoom) ? $post->post_mapzoom : '';
        if (!$mapzoom) {
            $mapzoom = 12;
        }
 
        $map_args = array();
        $map_args['map_canvas_name'] = 'preview_map_canvas';
        $map_args['width'] = '950';
        $map_args['height'] = '300';
        $map_args['child_collapse'] = '0';
        $map_args['maptype'] = $mapview;
        $map_args['autozoom'] = false;
        $map_args['zoom'] = "$mapzoom";
        $map_args['latitude'] = $address_latitude;
        $map_args['longitude'] = $address_longitude;
        $map_args['enable_cat_filters'] = false;
        $map_args['enable_text_search'] = false;
        $map_args['enable_post_type_filters'] = false;
        $map_args['enable_location_filters'] = false;
        $map_args['enable_jason_on_load'] = true;
        $map_args['enable_map_direction'] = true;
        $map_args['map_class_name'] = 'geodir-map-preview-page';
 
    }
 
    ?>
 
    <div class="geodir-singleview" id="gd-singleview" style="position:relative;">
                    <?php    if (geodir_is_page('detail')) {
                                    the_content();
                                } else {
                                    /** This action is documented in geodirectory_template_actions.php */
                                    echo apply_filters('the_content', stripslashes($post->post_desc));
                                }
 
                                echo $geodir_post_detail_fields;?>
                                <!-- <div id="geodir-post-gallery" class="clearfix"><?php
                                echo $thumb_image; echo $count_images;?></div> --><?php
                                /** This action is documented in geodirectory_template_actions.php */
                                echo apply_filters('the_content', stripslashes($video));// we apply the_content filter so oembed works also;
                                echo wpautop(stripslashes($special_offers));
                                //geodir_draw_map($map_args);
                                ?><div id="reviewsTab"><?php comments_template();?></div><?php 
                                //echo $related_listing; 
								?>
 
</div>
<?php }

// Add the new image gallery to top of right sidebar
add_action('geodir_detail_sidebar_inside','sd_map1_right', 1);
function sd_map1_right() {

 global $post, $post_images, $video, $special_offers, $related_listing, $geodir_post_detail_fields;
 
    $post_id = !empty($post) && isset($post->ID) ? (int)$post->ID : 0;
    $request_post_id = !empty($_REQUEST['p']) ? (int)$_REQUEST['p'] : 0;
    $is_backend_preview = (is_single() && !empty($_REQUEST['post_type']) && !empty($_REQUEST['preview']) && !empty($_REQUEST['p'])) && is_super_admin() ? true : false; // skip if preview from backend
 
    if ($is_backend_preview && !$post_id > 0 && $request_post_id > 0) {
        $post = geodir_get_post_info($request_post_id);
        setup_postdata($post);
    }
 
    $geodir_post_detail_fields = geodir_show_listing_info('detail');
 
    if (geodir_is_page('detail')) {

        $post_images = geodir_get_images($post->ID, 'thumbnail');
        $thumb_image = '';
        if (!empty($post_images)) {
            foreach ($post_images as $image) {
                $thumb_image .= '<a href="' . $image->src . '">';
                $thumb_image .= geodir_show_image($image, 'thumbnail', true, false);
                $thumb_image .= '</a>';
            }
        }
 
    } elseif (geodir_is_page('preview')) {

        if (isset($post->post_images))
            $post->post_images = trim($post->post_images, ",");
 
        if (isset($post->post_images) && !empty($post->post_images))
        $post_images = explode(",", $post->post_images);
        $thumb_image = '';
        if (!empty($post_images)) {
            foreach ($post_images as $image) {
                if ($image != '') {
                    $thumb_image .= '<a href="' . $image . '">';
                    $thumb_image .= geodir_show_image(array('src' => $image), 'thumbnail', true, false);
                    $thumb_image .= '</a>';
                }
            }
        }
 
    }
 
    ?>

<div id="geodir-post-gallery" class="clearfix"><?php echo $thumb_image;?></div> <?php

}

// add map below gallery in listing details page
add_action( 'geodir_detail_sidebar_inside', 'sd_map_in_detail_page_sidebar',2);

function sd_map_in_detail_page_sidebar()
{
 
    global $post, $post_images, $video, $special_offers, $related_listing, $geodir_post_detail_fields;
 
    $post_id = !empty($post) && isset($post->ID) ? (int)$post->ID : 0;
    $request_post_id = !empty($_REQUEST['p']) ? (int)$_REQUEST['p'] : 0;
    $is_backend_preview = (is_single() && !empty($_REQUEST['post_type']) && !empty($_REQUEST['preview']) && !empty($_REQUEST['p'])) && is_super_admin() ? true : false; // skip if preview from backend
 
    if ($is_backend_preview && !$post_id > 0 && $request_post_id > 0) {
        $post = geodir_get_post_info($request_post_id);
        setup_postdata($post);
    }
 
    $geodir_post_detail_fields = geodir_show_listing_info('detail');
 
    if (geodir_is_page('detail')) {
 
        $map_args = array();
        $map_args['map_canvas_name'] = 'detail_page_map_canvas';
        $map_args['width'] = '300';
        $map_args['height'] = '400';
        if ($post->post_mapzoom) {
            $map_args['zoom'] = '' . $post->post_mapzoom . '';
        }
        $map_args['autozoom'] = false;
        $map_args['child_collapse'] = '0';
        $map_args['enable_cat_filters'] = false;
        $map_args['enable_text_search'] = false;
        $map_args['enable_post_type_filters'] = false;
        $map_args['enable_location_filters'] = false;
        $map_args['enable_jason_on_load'] = true;
        $map_args['enable_map_direction'] = true;
        $map_args['map_class_name'] = 'geodir-map-detail-page';
 
    } elseif (geodir_is_page('preview')) {
 
        global $map_jason;
        $map_jason[] = $post->marker_json;
 
        $address_latitude = isset($post->post_latitude) ? $post->post_latitude : '';
        $address_longitude = isset($post->post_longitude) ? $post->post_longitude : '';
        $mapview = isset($post->post_mapview) ? $post->post_mapview : '';
        $mapzoom = isset($post->post_mapzoom) ? $post->post_mapzoom : '';
        if (!$mapzoom) {
            $mapzoom = 12;
        }
 
        $map_args = array();
        $map_args['map_canvas_name'] = 'preview_map_canvas';
        $map_args['width'] = '300';
        $map_args['height'] = '400';
        $map_args['child_collapse'] = '0';
        $map_args['maptype'] = $mapview;
        $map_args['autozoom'] = false;
        $map_args['zoom'] = "$mapzoom";
        $map_args['latitude'] = $address_latitude;
        $map_args['longitude'] = $address_longitude;
        $map_args['enable_cat_filters'] = false;
        $map_args['enable_text_search'] = false;
        $map_args['enable_post_type_filters'] = false;
        $map_args['enable_location_filters'] = false;
        $map_args['enable_jason_on_load'] = true;
        $map_args['enable_map_direction'] = true;
        $map_args['map_class_name'] = 'geodir-map-preview-page';
 
    }  
     if (geodir_is_page('detail') || geodir_is_page('preview')) {?>
	<div class="sd-map-in-sidebar-detail"><?php geodir_draw_map($map_args);?>
 
</div>
<?php } }

/*################################
      BLOG FUNCTONS
##################################*/

// redesign entry metas for blog entries

function supreme_entry_meta() {
	if ( is_sticky() && is_home() && ! is_paged() ) {
		printf( '<span class="sticky-post">%s</span>', __( 'Featured', 'directory-starter' ) );
	}

	$format = get_post_format();
	if ( current_theme_supports( 'post-formats', $format ) ) {
		printf( '<span class="entry-format">%1$s<a href="%2$s">%3$s</a></span>',
			sprintf( '<span class="screen-reader-text">%s </span>', _x( 'Format', 'Used before post format.', 'directory-starter' ) ),
			esc_url( get_post_format_link( $format ) ),
			get_post_format_string( $format )
		);
	}

	if ( in_array( get_post_type(), array( 'post', 'attachment' ) ) ) {
		$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';

//		if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
//			$time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
//		}

		$time_string = sprintf( $time_string,
			esc_attr( get_the_date( 'c' ) ),
			get_the_date(),
			esc_attr( get_the_modified_date( 'c' ) ),
			get_the_modified_date()
		);

		printf( '<span class="posted-on"><span class="screen-reader-text">%1$s </span><a href="%2$s" rel="bookmark">%3$s</a></span>',
			_x( 'Posted on', 'Used before publish date.', 'directory-starter' ),
			esc_url( get_permalink() ),
			$time_string
		);
	}

	if ( 'post' == get_post_type() ) {
		if ( is_singular() || is_multi_author() ) {
			printf( '<span class="byline"><span class="author vcard"><span class="screen-reader-text">%1$s </span><a class="url fn n" href="%2$s">%3$s</a></span></span>',
				_x( 'Author', 'Used before post author name.', 'directory-starter' ),
				esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
				get_the_author()
			);
		}

		$categories_list = get_the_category_list( _x( ', ', 'Used between list items, there is a space after the comma.', 'directory-starter' ) );
		if ( $categories_list ) {
			printf( '<span class="cat-links"><span class="screen-reader-text">%1$s </span>%2$s</span>',
				_x( 'Categories', 'Used before category names.', 'directory-starter' ),
				$categories_list
			);
		}

		$tags_list = get_the_tag_list( '', _x( ', ', 'Used between list items, there is a space after the comma.', 'directory-starter' ) );
		if ( $tags_list ) {
			printf( '<span class="tags-links"><span class="screen-reader-text">%1$s </span>%2$s</span>',
				_x( 'Tags', 'Used before tag names.', 'directory-starter' ),
				$tags_list
			);
		}
	}

}

/*##########################################
 DO NOT DELETE THE FOLLOWING PHP CLOSING TAG 
############################################*/
?>
