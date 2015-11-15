/*
 * AJAX script for Related Content
*/

(function($){
	$('.get-related-posts').on( 'click', function(event){
		event.preventDefault();

		$('.get-related-posts').remove();
		$('.ajax-loader').show();

		var json_url = post_data.json_url;
		var post_id = post_data.post_id;

		// The AJAX
		$.ajax({
			dataType: 'json',
			url: json_url
		}).done(function(response){

			$('#related-posts').append('<h1 class="related-header">Related Post: </h1>');

			console.log(response);
			// Loop through each of the related posts
			$.each(response, function(index, object){

				if ( object.id == post_id ) {
					return;
				}

				var feat_img = '';

				if ( object.featured_image !== 0 ) {
					feat_img = '<figure class="related-featured">' +
									'<img src="' + object.featured_image_src + '" alt="" />' +
								'</figure>';
				}

				var related_links_list = '';

				if ( object.related_links !== null ) {
					var related_links_container = $('<ul class="inline-list"></ul>');

					$.each(object.related_links, function(index, value){
						var link_item = '<li><a href="' + value.url + '" target="_blank" >' + value.text + '</a></li>';

						related_links_container.append(link_item);
					});

					related_links_list = related_links_container[0].outerHTML;
				}

				var related_loop = 	'<aside class="related-post clear">' +
										'<a class="related-post-link" href="'+ object.link +'">' +
											'<h1>' +
												object.title.rendered +
											'</h1>' +

											'<div class="related-author">' +
												'Author: ' + object.author_name +
											'</div>' +
											
											feat_img +

											'<div class="realted-excerpt">' +
												object.excerpt.rendered +
											'</div>' +
										'</a>' +

										related_links_list +
									'</aside><!-- .related-post -->';

				$('.ajax-loader').remove();

				// Append HTML to existing content
				$('#related-posts').append(related_loop);
			});

		}).fail(function(){

			// console.log('Fail');

		}).always(function(){

			// console.log('Complete');

		});

	});
})(jQuery);