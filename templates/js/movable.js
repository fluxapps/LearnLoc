
$(function() {
	$(".sortable").sortable({
		change: function(event, ui)
		{
			//$("li.FSXsort:first").addClass("FSXfirst");
		}
	});
	
	$( ".sortable" ).bind( "sortchange", function(event, ui) {
		//alert("!");
		$("li.FSXsort").first(function(e){
			e.addClass("FSXfirst");
		});
	});
	
	
	$("#sortable").disableSelection();

});

$(document).ready(function(){
	var keys     = [];
	var konami  = '38,38,40,40,37,39,37,39,66,65';
	$(document).keydown(function(e){
		keys.push( e.keyCode );
		if ( keys.toString().indexOf( konami ) >= 0 ){
			alert("!!!");
			//jQuery.getScript("http://gravityscript.googlecode.com/svn/trunk/gravityscript.js");
			//jQuery.getScript("http://kathack.com/js/kh.js");
			
			keys = [];
		}
	}
	);
});