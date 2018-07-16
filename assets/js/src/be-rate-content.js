// @codekit-prepend "js.cookie.js"

jQuery(function($){

	// Get liked content
	var cookieName = 'be_rate_content';
	var likedContent = Cookies.get( cookieName );
	if( likedContent ) {
		likedContent = JSON.parse( likedContent );
	} else {
		likedContent = { like: [], dislike: [] };
	}

	console.log( likedContent );
	console.log( likedContent.like );

	// Single post, set active if already liked
	$( '.be-rate-content' ).each( function(){
	    var $this        = $( this ),
	        postID       = $this.data( 'postid' ),
	        type         = $this.data( 'type' ),
	        likeClass    = 'active',
	        dislikeClass = 'disable';

	    if ( 'dislike' === type ) {
	        likeClass    = 'disable',
	        dislikeClass = 'active';
	    }

	    if( likedContent.like.indexOf( postID ) != -1 ) {
	        $this.addClass( likeClass );
	    }

	    if( likedContent.dislike.indexOf( postID ) != -1 ) {
	        $this.addClass( dislikeClass );
	    }
	});

	// Like on click
	var liking = false;
	$(document).on('click', '.be-rate-content', function(e){
		e.preventDefault();
		var $button = $(this),
			post_id = $button.data('postid'),
			type    = $button.data('type');

		if( ! liking && -1 == likedContent.like.indexOf( post_id ) && -1 == likedContent.dislike.indexOf( post_id ) ) {

			liking = true;
			$button.addClass('liking');
			var data = {
				action: 'be_rate_content',
				post_id: post_id,
				type: type,
			};
			$.post( be_rate_content.url, data, function(res){
				if( res.success ) {
					$button.removeClass('liking').addClass('active').find('.count').html(res.data);
					$button.siblings().addClass('disable');

					var liking = false;
					likedContent[type].push( post_id );
					Cookies.set( cookieName, JSON.stringify( likedContent ), { expires: 365 } );
					//console.log( res );
				} else {
					//console.log( res );
				}
			}).fail(function( xhr, textStatus, e ){
				//console.log( xhr.responseText );
			});
		}
	});

});
