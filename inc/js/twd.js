$(document).ready(function(){
    $("#copypw").click(function(){
		var pw = $('#passsuggest').text();
		$('input#pwd').val(pw);
		var inp = document.createElement('input');
		document.body.appendChild(inp);
		inp.value = pw;
		inp.select();
		document.execCommand('copy',false);
		inp.remove();	});
    $(':file').on('fileselect', function(event, numFiles, label) {
        console.log(numFiles);
        console.log(label);	});	
    $("#active_p").click(function(){
     $(this).text(function(_, oldText) {
         return oldText === 'Aktiv' ? 'Inaktiv' : 'Aktiv';
        });
     });	
    $('table').on('scroll', function() {	$("#" + this.id + " > *").width($(this).width() + $(this).scrollLeft());
    });
});



$(document).on('change', ':file', function() {
    var input = $(this),
        numFiles = input.get(0).files ? input.get(0).files.length : 1,
        label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
    input.trigger('fileselect', [numFiles, label]);
});


(function(e,t,n){var r=e.querySelectorAll("html")[0];r.className=r.className.replace(/(^|\s)no-js(\s|$)/,"$1js$2")})(document,window,0);
