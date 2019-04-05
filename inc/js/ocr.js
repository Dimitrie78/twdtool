$(function () {
	// Get on screen image
	var screenImage = $("#origin");
	var oriimageWidth = screenImage.width();
	var oriimageHeight = screenImage.height();
	// Create new offscreen image to test
	var theImage = new Image();
	
	theImage.src = screenImage.attr("src");

	// Get accurate measurements from that.
	var imageWidth = theImage.width;
	var imageHeight = theImage.height;
	
	var ratio = oriimageWidth/imageWidth;
	var ratio = ratio.toFixed(2)
	
	var key = 0;
	var name = '';
	var data = vars[0];
	
	$('#name').val(data.name);
	
	great_options();
	
	$('input[type=checkbox]').change(function() {
		if ($(this).prop("checked")) {
			$.post( "inc/ajaxupdateocr.php", { aktiv: 1, id: data.id });
			$.each(vars, function (index, value) {
				if(data.name!=value.name) {
					vars[index].aktiv=0;
				} else {
					vars[index].aktiv=1;
				}
			});
			return;
		}
		$.post( "inc/ajaxupdateocr.php", { aktiv: 0, id: data.id });
		$.each(vars, function (index, value) {
			vars[index].aktiv=0;
		});
	});
	
	$( "#name" ).change(function() {
		data.name = $(this).val();
		if(typeof(data.id) != "undefined" && data.id !== null) {
			vars[key].name = $(this).val();
		}
		
		$.post( "inc/ajaxupdateocr.php", { ocr: data })
			.done(function( daten ) {
				$( ".result" ).html( "Data Loaded: " + daten.id );
				var d = new Date();
				$('#ocrtest').attr('src', 'inc/img.php?id='+data.id+'?dummy=' + d.getTime());
				data.id = daten.id;
				vars = daten.data;
				great_options();
		});
	});
	
	$( "#selName" ).change(function() {
		name = $(this).val();
		$('#name').val(name);
		$.each(vars, function (index, value) {
			if(value.name==name){
				data = value;
				key = index;
				$().setPosition(data,ratio);
				var d = new Date();
				$('#ocrtest').attr('src', 'inc/img.php?id='+data.id+'?dummy=' + d.getTime());
				if(value.aktiv==1){
					$('input[type=checkbox]').prop("checked",true);
				} else {
					$('input[type=checkbox]').prop("checked",false);
				}
				return false;
			}
		});
		if(name=='ADD-NEW') {
			data.aktiv = 0;
			$('input[type=checkbox]').prop("checked",false);
			delete data.id;
		}
		
	});
	
	$('#remove').click(function() {
		$.post( "inc/ajaxupdateocr.php", { remove: data.id })
			.done(function( daten ) {
				vars = daten.data;
				data = vars[0];
				var d = new Date();
				$().setPosition(data,ratio);
				$('#ocrtest').attr('src', 'inc/img.php?id='+data.id+'?dummy=' + d.getTime());
				$('#name').val(data.name);
				great_options();
				if(data.aktiv==1){
					$('input[type=checkbox]').prop("checked",true);
				} else { 
					$('input[type=checkbox]').prop("checked",false); 
				}
		});
	});
	
	$('form input').on('keypress', function(e) {
		return e.which !== 13;
	});
	
	$().setPosition(data,ratio);
	
	$('#player').draggable({
		  containment: "#pic img"
		},{
		stop: function( event, ui ) {
			var xPos = ui.position.left/ratio-35;
            var yPos = ui.position.top/ratio;
			data.playerX = xPos.toFixed();
			data.playerY = yPos.toFixed();
			$.post( "inc/ajaxupdateocr.php", { ocr: data })
				.done(function( daten ) {
					var d = new Date();
					$( ".result" ).html( "Data Loaded: " + daten.id );
					$('#ocrtest').attr('src', 'inc/img.php?id='+data.id+'?dummy=' + d.getTime());
					data.id = daten.id;
			});
			$("#pos").html("<p><strong>X-Position: </strong>"+xPos.toFixed()+" | <strong>Y-Position: </strong>"+yPos.toFixed()+"</p>");
		}
    });
	$("#player").resizable({
		stop: function( event, ui ) {
			var wPos = ui.size.width/ratio;
			var hPos = ui.size.height/ratio;
			data.playerW = wPos.toFixed();
			data.playerH = hPos.toFixed();
			$.post( "inc/ajaxupdateocr.php", { ocr: data })
				.done(function( daten ) {
					var d = new Date();
					$( ".result" ).html( "Data Loaded: " + daten.id );
					$('#ocrtest').attr('src', 'inc/img.php?id='+data.id+'?dummy=' + d.getTime());
					data.id = daten.id;
			});
			$("#size").html("<p><strong>width: </strong>"+wPos.toFixed()+" | <strong>height: </strong>"+hPos.toFixed()+"</p>");
		}
	});
	$('#ep').draggable({
		  containment: "#pic img"
		},{
        stop: function( event, ui ) {
            var xPos = ui.position.left/ratio-35;
            var yPos = ui.position.top/ratio;
			data.epX = xPos.toFixed();
			data.epY = yPos.toFixed();
			$.post( "inc/ajaxupdateocr.php", { ocr: data })
				.done(function( daten ) {
					var d = new Date();
					$( ".result" ).html( "Data Loaded: " + daten.id );
					$('#ocrtest').attr('src', 'inc/img.php?id='+data.id+'?dummy=' + d.getTime());
					data.id = daten.id;
			});
            $("#pos").html("<p><strong>X-Position: </strong>"+xPos.toFixed()+" | <strong>Y-Position: </strong>"+yPos.toFixed()+"</p>");
        }
    });
	$("#ep").resizable({
		stop: function( event, ui ) {
			var wPos = ui.size.width/ratio;
			var hPos = ui.size.height/ratio;
			data.epW = wPos.toFixed();
			data.epH = hPos.toFixed();
			$.post( "inc/ajaxupdateocr.php", { ocr: data })
				.done(function( daten ) {
					var d = new Date();
					$( ".result" ).html( "Data Loaded: " + daten.id );
					$('#ocrtest').attr('src', 'inc/img.php?id='+data.id+'?dummy=' + d.getTime());
					data.id = daten.id;
			});
			$("#size").html("<p><strong>width: </strong>"+wPos.toFixed()+" | <strong>height: </strong>"+hPos.toFixed()+"</p>");
		}
	});
	$('#werte').draggable({
		  containment: "#pic img"
		},{
        stop: function( event, ui ) {
            var xPos = ui.position.left/ratio-35;
            var yPos = ui.position.top/ratio;
			data.werteX = xPos.toFixed();
			data.werteY = yPos.toFixed();
			$.post( "inc/ajaxupdateocr.php", { ocr: data })
				.done(function( daten ) {
					var d = new Date();
					$( ".result" ).html( "Data Loaded: " + daten.id );
					$('#ocrtest').attr('src', 'inc/img.php?id='+data.id+'?dummy=' + d.getTime());
					data.id = daten.id;
			});
            $("#pos").html("<p><strong>X-Position: </strong>"+xPos.toFixed()+" | <strong>Y-Position: </strong>"+yPos.toFixed()+"</p>");
        }
    });
	$("#werte").resizable({
		stop: function( event, ui ) {
			var wPos = ui.size.width/ratio;
			var hPos = ui.size.height/ratio;
			data.werteW = wPos.toFixed();
			data.werteH = hPos.toFixed();
			$.post( "inc/ajaxupdateocr.php", { ocr: data })
				.done(function( daten ) {
					var d = new Date();
					$( ".result" ).html( "Data Loaded: " + daten.id );
					$('#ocrtest').attr('src', 'inc/img.php?id='+data.id+'?dummy=' + d.getTime());
					data.id = daten.id;
			});
			$("#size").html("<p><strong>width: </strong>"+wPos.toFixed()+" | <strong>height: </strong>"+hPos.toFixed()+"</p>");
		}
	});
	
	function great_options() {
		$('#selName').empty();
		
		$.each(vars, function (index, value) {
			if(value.name==data.name){
				$('#selName').append('<option value="'+value.name+'" selected="selected">'+value.name+'</option>');
				if(value.aktiv==1){
					$('input[type=checkbox]').prop("checked",true);
				}
			} else {
				$('#selName').append('<option value="'+value.name+'">'+value.name+'</option>');
			}
		});
		$('#selName').append('<option value="ADD-NEW">ADD-NEW</option>');
	}
});

$.fn.setPosition = function(data,ratio) {
		$("#player").css({	"top": data.playerY*ratio,
							"left": data.playerX*ratio+16, 
							"width": data.playerW*ratio, 
							"height": data.playerH*ratio
						});
		$("#ep").css({		"top": data.epY*ratio,
							"left": data.epX*ratio+16, 
							"width": data.epW*ratio, 
							"height": data.epH*ratio
						});
		$("#werte").css({	"top": data.werteY*ratio,
							"left": data.werteX*ratio+16, 
							"width": data.werteW*ratio, 
							"height": data.werteH*ratio
						});
	}; 