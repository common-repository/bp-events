
var xmlhttp;

function eventView(str)
{
alert('hello world!');

xmlhttp=GetXmlHttpObject();
if (xmlhttp==null)
  {
  alert ("Browser does not support HTTP Request");
  return;
  }
var url="eventview.php";
url=url+"?q="+str;
url=url+"&sid="+Math.random();
xmlhttp.onreadystatechange=stateChanged;
xmlhttp.open("GET",url,true);
xmlhttp.send(null);
}

function stateChanged()
{
if (xmlhttp.readyState==4)
{
document.getElementById("events_main").innerHTML=xmlhttp.responseText;
}
}

function GetXmlHttpObject()
{
if (window.XMLHttpRequest)
  {
  // code for IE7+, Firefox, Chrome, Opera, Safari
  return new XMLHttpRequest();
  }
if (window.ActiveXObject)
  {
  // code for IE6, IE5
  return new ActiveXObject("Microsoft.XMLHTTP");
  }
return null;
}

jQuery(document).ready( function() {
	jQuery("ul#letter-list li a").livequery('click',
		function() {
			jQuery('#ajax-loader-events').toggle();

			jQuery("div#events-list-options a").removeClass("selected");
			jQuery(this).addClass('selected');
			jQuery("input#events_search").val('');

			var letter = jQuery(this).attr('id')
			letter = letter.split('-');

			jQuery.post( ajaxurl, {
				action: 'directory_events',
				'cookie': encodeURIComponent(document.cookie),
				'_wpnonce': jQuery("input#_wpnonce-event-filter").val(),
				'letter': letter[1],
				'page': 1
			},
			function(response)
			{
				response = response.substr(0, response.length-1);
				jQuery("#event-dir-list").fadeOut(200,
					function() {
						jQuery('#ajax-loader-events').toggle();
						jQuery("#event-dir-list").html(response);
						jQuery("#event-dir-list").fadeIn(200);
					}
				);
			});

			return false;
		}
	);

	jQuery("form#search-events-form").submit( function() {
			jQuery('#ajax-loader-events').toggle();

			jQuery.post( ajaxurl, {
				action: 'directory_events',
				'cookie': encodeURIComponent(document.cookie),
				'_wpnonce': jQuery("input#_wpnonce-event-filter").val(),
				's': jQuery("input#events_search").val(),
				'page': 1
			},
			function(response)
			{
				response = response.substr(0, response.length-1);
				jQuery("#event-dir-list").fadeOut(200,
					function() {
						jQuery('#ajax-loader-events').toggle();
						jQuery("#event-dir-list").html(response);
						jQuery("#event-dir-list").fadeIn(200);
					}
				);
			});

			return false;
		}
	);

	jQuery("div#event-dir-pag a").livequery('click',
		function() {
			jQuery('#ajax-loader-events').toggle();

			var page = jQuery(this).attr('href');
			page = page.split('gpage=');

			if ( !jQuery("input#selected_letter").val() )
				var letter = '';
			else
				var letter = jQuery("input#selected_letter").val();

			if ( !jQuery("input#search_terms").val() )
				var search_terms = '';
			else
				var search_terms = jQuery("input#search_terms").val();

			jQuery.post( ajaxurl, {
				action: 'directory_events',
				'cookie': encodeURIComponent(document.cookie),
				'_wpnonce': jQuery("input#_wpnonce").val(),
				'gpage': page[1],
				'_wpnonce': jQuery("input#_wpnonce-event-filter").val(),

				'letter': letter,
				's': search_terms
			},
			function(response)
			{
				response = response.substr(0, response.length-1);
				jQuery("#event-dir-list").fadeOut(200,
					function() {
						jQuery('#ajax-loader-events').toggle();
						jQuery("#event-dir-list").html(response);
						jQuery("#event-dir-list").fadeIn(200);
					}
				);
			});

			return false;
		}
	);

	jQuery("div.event-button a").livequery('click',
		function() {
			var gid = jQuery(this).parent().attr('id');
			gid = gid.split('-');
			gid = gid[1];

			var nonce = jQuery(this).attr('href');
			nonce = nonce.split('?_wpnonce=');
			nonce = nonce[1].split('&');
			nonce = nonce[0];

			var thelink = jQuery(this);

			jQuery.post( ajaxurl, {
				action: 'joinleave_event',
				'cookie': encodeURIComponent(document.cookie),
				'gid': gid,
				'_wpnonce': nonce
			},
			function(response)
			{
				response = response.substr(0, response.length-1);
				var parentdiv = thelink.parent();

				jQuery(parentdiv).fadeOut(200,
					function() {
						parentdiv.fadeIn(200).html(response);
					}
				);
			});
			return false;
		}
	);
});