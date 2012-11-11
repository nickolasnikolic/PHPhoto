// JavaScript Document
$(document).ready(function () {
	/*changing the image to display none on page startup allows for the image to fadeIn*/
	// $(".portfolio").css({ display: "none" });	
	
	/*code to fade image in on document load*/
	$(".qdig-image").fadeIn(1600, "linear");

	/*when the image is clicked it will fade out*/
	$(".qdig-image").click(function(){
		$(".qdig-image").animate({
			opacity: '0%'
		  }, 400 );
	});
	
});