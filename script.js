$(function() {

	$('.project_name').click(function() {
		if($(this).parent().hasClass('closed')) {
			$('.project_name').each(function() {
				$(this).next('.project_info').slideUp();
				$(this).parent().addClass('closed');
			});
			$(this).next('.project_info').slideDown();
			$(this).parent().removeClass('closed');
		} else {
			$(this).next().slideUp(200, function() {
				$('.project').addClass('closed');
			});
		}
	});

});