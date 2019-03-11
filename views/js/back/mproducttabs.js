$(document).on('change', '.tab-toggle', function() {

	var status = $(this).prop('checked');

	if(status) {
		$(this).parent().next().removeClass('hidden');
	} else {
		$(this).parent().next().addClass('hidden');
	}

});