// @codekit-prepend "js.cookie.js"

jQuery(function($){

	var	cookieName 	= 'be_rate_content',
		liking		= false;

	// Get liked content
	var likedContent = Cookies.get( cookieName );
	if( likedContent ) {
		likedContent = JSON.parse( likedContent );
	} else {
		likedContent = { like: [], dislike: [] };
	}

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

	    if( rated( 'like', postID ) ) {
	        $this.addClass( likeClass );
	    }

	    if( rated( 'dislike', postID ) ) {
	        $this.addClass( dislikeClass );
	    }
	});

	// Like on click
	$(document).on('click', '.be-rate-content', function(e){
		e.preventDefault();
		var	$button	= $(this),
			postID	= $button.data('postid'),
			type	= $button.data('type');

		if( ! liking && ! rated( 'like', postID ) && ! rated( 'dislike', postID ) ) {

			liking = true;
			$button.addClass('liking');

			var	data = {
				action:		'be_rate_content',
				post_id:	postID,
				type:		type,
			};
			$.post( be_rate_content.url, data, function(res){
				if( res.success ) {
					$button.removeClass('liking').addClass('active').find('.count').html(res.data);
					$button.siblings().addClass('disable');

					var liking = false;
					likedContent[type].push( postID );
					Cookies.set( cookieName, JSON.stringify( likedContent ), { expires: 365 } );
				}
			});
		}
	});

	// Check if already liked/disliked
	function rated( type, postID ) {
		return -1 !== likedContent[type].indexOf( postID );
	}

});
