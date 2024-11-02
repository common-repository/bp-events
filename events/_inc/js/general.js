jQuery(document).ready(function($) {

  if ($('input[name=event-allday]').is(':checked')) {
    $('#event-startdate-times select').attr("disabled","true");
	  $('#event-enddate-dates select').attr("disabled","true");
	  $('#event-enddate-times select').attr("disabled","true");
	} else {
    $('#event-startdate-times select').removeAttr("disabled");
	  $('#event-enddate-dates select').removeAttr("disabled");
	  $('#event-enddate-times select').removeAttr("disabled");
	}
		    	
	$('input[name=event-allday]').click(function(){
	  if ($(this).is(':checked')) {
	    $('#event-startdate-times select').attr("disabled","true");
		  $('#event-enddate-dates select').attr("disabled","true");
		  $('#event-enddate-times select').attr("disabled","true");
		} else {
	    $('#event-startdate-times select').removeAttr("disabled");
		  $('#event-enddate-dates select').removeAttr("disabled");
		  $('#event-enddate-times select').removeAttr("disabled");
		}
	});
  
});