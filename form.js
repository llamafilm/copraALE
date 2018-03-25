$(document).ready(function() {	
	// variable to hold request
	console.log("script started");
	var request;
	// bind to the submit event of our form
	$("#jquery-form").submit(function(event){
		if(counter > numFiles) {
			$('#jquery-form').fadeOut();
			$('#results').delay(400)
			.queue(function(n) {
				$(this).html("You have successfully entered the ALE files.");
				n();
			});
			return false;
		}		
		console.log("form submitted");
		// abort any pending request
		if (request) {
			request.abort();
		}
		// setup some local variables
		var $form = $(this);
		// select and cache all the fields
		var $inputs = $form.find("input, select, button, textarea");
		// serialize the data in the form
		var serializedData = $form.serialize();

		// disable the inputs for the duration of the ajax request
		$inputs.prop("disabled", true);

		// fire off the request to /submit.php
		request = $.ajax({
			url: "submit.php",
			type: "post",
			data: { main: serializedData, counter: counter }
		});

		// callback handler that will be called on success
		request.done(function (response, textStatus, jqXHR){
			// log a message to the console
			console.log("Hooray, it worked!");
			$('#results').html(response);
		});

		// callback handler that will be called on failure
		request.fail(function (jqXHR, textStatus, errorThrown){
			// log the error to the console
			console.error(
				"The following error occured: "+
				textStatus, errorThrown
			);
		});

		// callback handler that will be called regardless
		// if the request failed or succeeded
		request.always(function () {
			// reenable the inputs
			$inputs.prop("disabled", false);
		});

		// prevent default posting of form
		event.preventDefault();
		
		console.log(counter);
		counter++;
	});
});