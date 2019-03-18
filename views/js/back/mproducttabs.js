$(document).on('change', '.tab-toggle', function() {

	var status = $(this).prop('checked');

	if(status) {
		$(this).parent().next().next().removeClass('hidden');
	} else {
		$(this).parent().next().next().addClass('hidden');
	}

});