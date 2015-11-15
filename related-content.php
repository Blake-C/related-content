<?php
	/*
	Plugin Name: Related Content
	Plugin URI: http://digitalblake.com
	Description: Shows related content within a post
	Version: 0.0.1
	Author: Blake Cerecero
	Author URI: http://digitalblake.com
	Text Domain: related_content
	Domain Path: /Languages

	============================================================================================================
	This software is provided "as is" and any express or implied warranties, including, but not limited to, the
	implied warranties of merchantibility and fitness for a particular purpose are disclaimed. In no event shall
	the copyright owner or contributors be liable for any direct, indirect, incidental, special, exemplary, or
	consequential damages(including, but not limited to, procurement of substitute goods or services; loss of
	use, data, or profits; or business interruption) however caused and on any theory of liability, whether in
	contract, strict liability, or tort(including negligence or otherwise) arising in any way out of the use of
	this software, even if advised of the possibility of such damage.

	For full license details see license.txt
	============================================================================================================
	*/

	defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

	// Add various fields to the JSON output
	function related_content_register_fields() {
		// Add Author Name
		register_api_field('post',
			'author_name',
			array(
				'get_callback' 		=> 'related_content_author_name',
				'update_callback' 	=> null,
				'schema' 			=> null
			)
		);

		// Add Featured Image
		register_api_field('post',
			'featured_image_src',
			array(
				'get_callback' 		=> 'related_content_get_image_src',
				'update_callback' 	=> null,
				'schema' 			=> null
			)
		);

		// Add Related Links
		register_api_field('post',
			'related_links',
			array(
				'get_callback' 		=> 'related_content_related_links',
				'update_callback' 	=> null,
				'schema' 			=> null
			)
		);
	}
	function related_content_author_name( $object, $field_name, $request ) {
		return get_the_author_meta( 'display_name' );
	}
	function related_content_get_image_src( $object, $field_name, $request ) {
		$featured_img_array = wp_get_attachment_image_src ( $object['featured_image'], 'thumbnail', true );

		return $featured_img_array[0];
	}
	function related_content_related_links( $object, $field_name, $request ) {
		// check if the repeater field has rows of data
		if( have_rows('related_links') ):

			$related_links_link_text_array = array();

			// loop through the rows of data
			while ( have_rows('related_links') ) : the_row();

				// display a sub field value
				$related_links_link_text_array[] = array( 
					'url' => get_sub_field('link_url'), 
					'text' => get_sub_field('link_text')
				);

			endwhile;
		endif;

		return $related_links_link_text_array;
	}
	add_action( 'rest_api_init', 'related_content_register_fields' );

	// Enqueue scripts and styles.
	function related_content_scripts() {
		if ( is_single() & is_main_query() ){

			/* Import CSS */
			wp_enqueue_style( 'related_content_styles', plugin_dir_url(__FILE__) . 'css/styles.css', '0.0.1', 'all' );
			wp_enqueue_script( 'related_content_scripts', plugin_dir_url(__FILE__) . 'js/related-content.ajax.js', array('jquery'), '20151114', true );

			global $post;
			$post_id = $post->ID;

			wp_localize_script( 'related_content_scripts', 'post_data', array(
				'post_id' => $post_id,
				'json_url' => related_content_get_json_query()
			));
		}
	}
	add_action( 'wp_enqueue_scripts', 'related_content_scripts', 1 );


	/**
	 * Create REST API url
	 * - Get the current categories
	 * - Get the category IDs
	 * - Create the arguments for categories and posts-per-page
	 * - Create the url
	*/
	function related_content_get_json_query() {
		$cats = get_the_category();
		$cat_ids = array();

		foreach ( $cats as $cat ) {
			$cat_ids[] = $cat->term_id;
		}

		$args = array(
			'filter[cat]' 				=> implode( ",", $cat_ids ),
			'filter[posts_per_page]' 	=> 5,
			'filter[orderby]'			=> 'rand'
		);

		$url = add_query_arg( $args, rest_url( 'wp/v2/posts' ) );

		return $url;
	}

	// Base HTML t obe addd to the bottom of a post
	function related_content_baseline_html() {
		$baseline = '<section id="related-posts" class="related-posts">';
		$baseline .= '<a href="#" class="get-related-posts button expand">Get related posts</a>';
		$baseline .= '<div class="ajax-loader hide"><img src="' . plugin_dir_url(__FILE__) . 'css/spinner.svg' . '" /></div>';
		$baseline .= '</section><!-- .related-posts -->';
		return $baseline;
	}

	// Bootstrap this whole thing onto the bottom of the single posts
	function related_content_display($content){
		if ( is_single() & is_main_query() ){
			$content .= related_content_baseline_html();
			return $content;
		}
	}
	add_filter( 'the_content', 'related_content_display' );
?>