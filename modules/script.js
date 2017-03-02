$(function(){

	if( $('.settlenewarticle-step-0').length ) {

		$('#settlenewarticle_pageCategory').on('change', function(){
			if( $(this).val() ) {
				$('.step1-geolocation-set').fadeIn('fast');
			}else{
				$('.step1-geolocation-set').fadeOut('fast');
			}
		});

	}

	if( $('.settlenewarticle-step-1').length ) {

		var similarTimeout = null;

		$('#settlenewarticle_title').on('keyup', function(e){

			clearTimeout(similarTimeout);
			similarTimeout = setTimeout(showSimilarArticles, 500);

		});

		if( $('#settlenewarticle_title').val().length ) {

			clearTimeout(similarTimeout);
			similarTimeout = setTimeout(showSimilarArticles, 500);

		}

	}

	function showSimilarArticles() {

		//$('#similar-articles').hide();
		$('#step1submit').prop('disabled', true);

		var value = $('#settlenewarticle_title').val();
		var geoids = $('#settlenewarticle_title').data('geo-ids');
		var category_id = $('#settlenewarticle_title').data('category-id');

		if( !value.length || value.length < 3 ) {
			return false;
		}

		var apiUrl = mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/api.php?action=settlenewarticle&format=json';

		$.get(apiUrl + '&category_id='+category_id+'&geo_ids='+geoids+'&title_value='+value, function( response ) {
			var resp = response.settlenewarticle;
			var status = resp.status;
			switch (status) {
				case 0:
					// Nothing found
					$('#similar-articles').hide();
					$('#step1submit').prop('disabled', false);
					$('#settlenewarticle_title').parents('.form-group')
						.removeClass('has-warning')
						.removeClass('has-error');
					break;
				case 1:
					// Exact match
					$('#step1submit').prop('disabled', true);
					$('#settlenewarticle_title').parents('.form-group')
						.removeClass('has-warning')
						.removeClass('has-error')
						.addClass('has-error');
					break;
				case 2:
					// Fuzzy match
					$('#step1submit').prop('disabled', false);
					$('#settlenewarticle_title').parents('.form-group')
						.removeClass('has-warning')
						.removeClass('has-error')
						.addClass('has-warning');
					break;
			}

			$('#similar-articles .similar-articles-heading').html( resp.message );

			$('#similar-articles > .row').html('');

			var articleTemplate = mw.template.get( 'ext.settlenewarticle.main', 'jsarticle.mustache' );
			var html = articleTemplate.render( resp );

			$('#similar-articles > .row').html( html );

			$('#similar-articles').show();

            //updateButtonLink();

		});

	}

	function updateButtonLink() {

		// http://settlein.local/index.php/Special:FormEdit/Card/
		// ?Card[Title]=Sample&Card[Category]=18&Card[Country]=Algeria&Card[State]=Annaba&Card[City]=Drean

        var value = $('#settlenewarticle_title').val();
        var category_id = $('#settlenewarticle_title').data('category-id');

        var linkUrl = mw.config.get('wgServer') + mw.config.get('wgScriptPath') + '/index.php/Special:FormEdit/Card/';

        linkUrl += '?Card[Title]=' + value;
        linkUrl += '&Card[Category]=' + category_id;

        if( $('#d-country-name').length ) {
        	linkUrl += '&Card[Country]=' + $('#d-country-name').text();
		}

        if( $('#d-state-name').length ) {
            linkUrl += '&Card[State]=' + $('#d-state-name').text();
        }

        if( $('#d-city-name').length ) {
            linkUrl += '&Card[City]=' + $('#d-city-name').text();
        }

        $('#settlenewarticle-form-step-1').attr('action', linkUrl);

	}

});