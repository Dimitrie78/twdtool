$(function () {
	$body = $("body");

	$(document).on({
		ajaxStart: function() { $body.addClass("loading"); },
		ajaxStop: function() { $body.removeClass("loading"); }    
	});
	
	if(data.length!=0) {
		$.post( "inc/ajaxUploadToApi.php", { data: data })
			.done(function( result ) {
				console.log(result);
				$('#result').html(result);
				$('#result').append('<br><div class="alert alert-success"><strong>Fertig!</strong> Vorgang abgeschlossen. <br>Datum und Zeit des Uploads wurden auf der Startseite aktualisiert.</div>');
			});
	}
});