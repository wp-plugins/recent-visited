//Javacsript Document

(function($){

	$(document).ready(function(){

		$('#slider').on('change mousemove', function(){

			$('.slide_val').empty().html($(this).val());			
		});

		$('.hide_settings').delay( 1000 ).fadeOut('slow');

	});


}(jQuery));