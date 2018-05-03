/**
 * Opens a modal window in response to a click event in an html
 * button with a class="btn-modal".
 * 
 * data-title tag passes the model name to display in the modal's
 * header.
 */
$(function(){
	$(document).on('click', '.btn-modal', function() {
		var modal = $('#modalCreate').modal('show');
		modal.find('#modalContent').load($(this).attr('value'));
		modal.find('#title-model').html($(this).attr('data-title'));
	});
});

/**
 * Opens a modal window in response to a click event in an html
 * button with a class="btn-award".  It is specifically designed to 
 * work with a grid that has a Krajee RadioColumn
 * 
 * The desired row will have a class="success", which is the default
 * when a radio button is set.  The key from the row of that radio button 
 * is passed to a hidden input for the registration_id.
 */
$(function(){
	$(document).on('click', '.btn-award', function() {
		var radio = $('#registration-grid').find('.success');
		if (typeof(radio.data('key')) != 'undefined') {
			$('#modalCreate').on('shown.bs.modal', function() {
				$("#awardedbid-registration_id").val(radio.data('key'));
			});
			var modal = $('#modalCreate').modal('show');
			modal.find('#modalContent').load($(this).attr('value'));
			modal.find('#title-model').html('Project Start Date');			
		} else {
			alert('No row selected.  Please select a registration to award.');
		}
		 
	});
});

/**
 * Standard action for HTML <button> whose class includes btn-print
 */
$(function(){
	$(document).on('click', '.btn-print', function () {
		window.print();
	});
});

/**
 * Standard action for HTML <button> whose class includes btn-aslink
 */
$(function(){
	$(document).on('click', '.btn-aslink', function () {
		$(location).attr('href', $(this).attr('value'));
	});
});


/**
 * Forces a modal create window to route to that window's action. Identified
 * by form class .ajax-create
 * 
 * This is a workaround for modal forms that open from within an active 
 * update form.
 */
$(function(){
	$(document).on('beforeSubmit', '.ajax-create', function(e) {
		var modal = $(this);
		$.post(
			modal.attr("action"),
			modal.serialize()
		);
		return false;
	});
});

/**
 * Handler for the accordion activate event.  data-url tag holds the 
 * route to the data retrieval controller action.  Can have multiple
 * affected <div> in collection
 *  
 * @param event activate
 * @param ui collection of ui objects associated with event
 */
function fillPanel(event, ui) {
	var kids = $(ui.newPanel[0]).children('div');
	$(kids).each (function (index, element) {
		var $url = $(element).attr('data-url');
		$.getJSON($url, function (data) {
			$(element).html(data);
		});
	});
}
