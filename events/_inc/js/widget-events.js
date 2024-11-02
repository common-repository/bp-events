jQuery(document).ready( function() {
	jQuery("div#events-list-options a").livequery('click',
		function() {
			jQuery('#ajax-loader-events').toggle();

			jQuery("div#events-list-options a").removeClass("selected");
			jQuery(this).addClass('selected');

			jQuery.post( ajaxurl, {
				action: 'widget_events_list',
				'cookie': encodeURIComponent(document.cookie),
				'_wpnonce': jQuery("input#_wpnonce-events").val(),
				'max-events': jQuery("input#events_widget_events_list_max_events").val(),
				'filter': jQuery(this).attr('id')
			},
			function(response)
			{
				jQuery('#ajax-loader-events').toggle();
				events_wiget_response(response);
			});

			return false;
		}
	);
});

function events_wiget_response(response) {
	response = response.substr(0, response.length-1);
	response = response.split('[[SPLIT]]');

	if ( response[0] != "-1" ) {
		jQuery("ul#events-list").fadeOut(200,
			function() {
				jQuery("ul#events-list").html(response[1]);
				jQuery("ul#events-list").fadeIn(200);
			}
		);

	} else {
		jQuery("ul#events-list").fadeOut(200,
			function() {
				var message = '<p>' + response[1] + '</p>';
				jQuery("ul#events-list").html(message);
				jQuery("ul#events-list").fadeIn(200);
			}
		);
	}
}