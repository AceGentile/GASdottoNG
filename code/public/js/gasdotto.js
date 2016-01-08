var userBlood = new Bloodhound({
	datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
	queryTokenizer: Bloodhound.tokenizers.whitespace,
	remote: {
		url: '/users/search?term=%QUERY',
		wildcard: '%QUERY'
	}
});

userBlood.initialize();

function generalInit() {
	$('input.date').datepicker({
		format: 'DD dd MM yyyy',
		autoclose: true,
		language: 'it',
		clearBtn: true
	});

	$('.tagsinput').tagsinput();

	$('.nav-tabs a').click(function (e) {
		e.preventDefault();
		$(this).tab('show');
	});

	$('.many-rows').each(function() {
		manyRowsAddDeleteButtons($(this));
	});

	$('.bookingSearch').each(function() {
		if($(this).hasClass('tt-hint') == true) {
			return;
		}

		if($(this).hasClass('tt-input') == false) {
			$(this).typeahead(null, {
				name: 'users',
				displayKey: 'value',
				source: userBlood.ttAdapter()
			}).on('typeahead:selected', function(obj, result, name) {
				var aggregate_id = $(this).attr('data-aggregate');
				$.get('/booking/' + aggregate_id + '/user/' + result.id, function(form) {
					$('.other-booking').empty().append(form);
				});
			});
		}
	});

	setupVariantsEditor();
	testListsEmptiness();
}

function filteredSerialize(form) {
	return $(':not(.skip-on-submit)', form).serializeArray();
}

function voidForm(form) {
	form.find('input[type!=hidden]').val('');
	form.find('textarea').val('');
}

function sortList(mylist) {
	var listitems = mylist.children('a').get();
	listitems.sort(function(a, b) {
		return $(a).text().toUpperCase().localeCompare($(b).text().toUpperCase());
	});

	$.each(listitems, function(idx, itm) {
		mylist.append(itm);
	});
}

function closeMainForm(form) {
	var container = form.closest('.list-group-item');
	var head = container.prev();
	head.removeClass('active');
	container.remove();
	return head;
}

function manyRowsAddDeleteButtons(node) {
	var fields = node.find('.row');
	if (fields.length > 1 && node.find('.delete-many-rows').length == 0) {
		fields.each(function() {
			var button = '<div class="btn btn-danger delete-many-rows"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></div>';
			$(this).append(button);
		});
	}
	else if (fields.length == 1) {
		node.find('.delete-many-rows').remove();
	}
}

function testListsEmptiness() {
	$('.loadablelist').each(function() {
		var id = $(this).attr('id');
		var c = $(this).find('a').length;
		var alert = $('#empty-' + id);

		if (c == 0)
			alert.show();
		else
			alert.hide();
	});
}

function loadingPlaceholder() {
	return $('<div class="progress"><div class="progress-bar progress-bar-striped active" style="width: 100%"></div></div>');
}

function setupVariantsEditor() {
	$('.variants-editor').on('click', '.delete-variant', function() {
		var editor = $(this).closest('.variants-editor');
		var id = $(this).closest('.row').find('input:hidden[name=variant_id]').val();

		$.ajax({
			method: 'DELETE',
			url: '/variants/' + id,
			dataType: 'html',

			success: function(data) {
				editor.replaceWith(data);
			}
		});

	}).on('click', '.edit-variant', function() {
		var row = $(this).closest('.row');
		var id = row.find('input:hidden[name=variant_id]').val();
		var name = row.find('.variant_name').text().trim();
		var offset = row.find('input:hidden[name=variant_offset]').val();
		var values = row.find('.exploded_values').contents().clone();

		var form = $(this).closest('.list-group').find('.creating-variant-form');
		form.find('input:hidden[name=variant_id]').val(id);
		form.find('input[name=name]').val(name);
		form.find('.values_table').empty().append(values);

		if (offset == 'true') {
			form.find('input[name=has_offset]').attr('checked', 'checked');
			form.find('input[name*=price_offset]').closest('.form-group').show();
		}
		else {
			form.find('input[name=has_offset]').removeAttr('checked');
			form.find('input[name*=price_offset]').val('0').closest('.form-group').hide();
		}

		form.closest('.modal').modal('show');

	}).on('click', '.add-variant', function() {
		var row = $(this).closest('.list-group');
		var form = row.find('.creating-variant-form');
		var modal = row.find('.create-variant');
		form.find('input:text').val('');
		form.find('input:hidden[name=variant_id]').val('');
		form.find('input:checkbox').removeAttr('checked');

		values = form.find('.many-rows');
		values.find('.row:not(:first)').remove();
		manyRowsAddDeleteButtons(values);

		form.find('input[name*=price_offset]').val('0').closest('.form-group').hide();
		modal.modal('show');
	});

	$('.creating-variant-form').on('change', 'input:checkbox[name=has_offset]', function() {
		var has = $(this).is(':checked');
		var form = $(this).closest('form');

		if (has == true)
			form.find('input[name*=price_offset]').closest('.form-group').show();
		else
			form.find('input[name*=price_offset]').val('0').closest('.form-group').hide();

	}).submit(function(e) {
		e.preventDefault();
		var modal = $(this).closest('.modal');
		var editor = $(this).closest('.list-group').find('.variants-editor');
		var data = $(this).serializeArray();

		editor.empty().append(loadingPlaceholder());

		$.ajax({
			method: 'POST',
			url: '/variants',
			data: data,
			dataType: 'html',

			success: function(data) {
				editor.replaceWith(data);
				modal.modal('hide');
			}
		});

		return false;
	});
}

$(document).ready(function() {
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	$(document).ajaxComplete(function() {
		generalInit();
	});

	generalInit();

	$('body').on('click', '.loadablelist a.loadable-item', function(event) {
		event.preventDefault();

		if ($(this).hasClass('active')) {
			$(this).removeClass('active').next().remove();
		}
		else {
			$(this).find('a').removeClass('active');
			var node = $('<li>').addClass('list-group-item').append(loadingPlaceholder());
			$(this).addClass('active').after(node);

			$.ajax({
				method: 'GET',
				url: $(this).attr('href'),

				success: function(data) {
					node.empty().append(data);
				},
				error: function() {
					node.empty().append();
				}
			});
		}

		return false;
	});

	$('body').on('change', 'select.triggers-modal', function(event) {
		var val = $(this).find('option:selected').val();
		if (val == 'run_modal') {
			var modal = $(this).attr('data-trigger-modal');
			$('#' + modal).modal('show');
		}
	});

	$('body').on('submit', '.main-form', function(event) {
		event.preventDefault();
		var form = $(this);
		var data = form.serializeArray();

		form.find('.main-form-buttons button').attr('disabled', 'disabled');

		$.ajax({
			method: form.attr('method'),
			url: form.attr('action'),
			data: data,
			dataType: 'json',

			success: function(data) {
				var h = closeMainForm(form);
				h.empty().append(data.header).attr('href', data.url);
			}
		});
	});

	$('body').on('click', '.main-form-buttons .close-button', function(event) {
		event.preventDefault();
		var form = $(this).closest('.main-form');
		form.find('.main-form-buttons button').attr('disabled', 'disabled');
		closeMainForm(form);
	});

	$('body').on('click', '.main-form-buttons .delete-button', function(event) {
		event.preventDefault();
		var form = $(this).closest('.main-form');

		/*
			TODO: visualizzare nome dell'elemento che si sta rimuovendo
		*/

		if (confirm('Sei sicuro di voler eliminare questo elemento?')) {
			form.find('.main-form-buttons button').attr('disabled', 'disabled');

			$.ajax({
				method: 'DELETE',
				url: form.attr('action'),
				dataType: 'json',

				success: function(data) {
					var upper = closeMainForm(form);
					upper.remove();
					testListsEmptiness();
				}
			});
		}
	});

	$('body').on('submit', '.inner-form', function(event) {
		event.preventDefault();
		var form = $(this);
		var data = filteredSerialize(form);

		form.find('button[type=submit]').text('Attendere').attr('disabled', 'disabled');;

		$.ajax({
			method: form.attr('method'),
			url: form.attr('action'),
			data: data,
			dataType: 'json',

			success: function(data) {
				form.find('button[type=submit]').text('Salvato!');
				setInterval(function() {
					form.find('button[type=submit]').text('Salva').removeAttr('disabled');
				}, 2000);
			}
		});
	});

	$('body').on('submit', '.creating-form', function(event) {
		if (event.isDefaultPrevented())
			return;

		event.preventDefault();
		var form = $(this);
		var data = form.serializeArray();

		$.ajax({
			method: form.attr('method'),
			url: form.attr('action'),
			data: data,
			dataType: 'json',

			success: function(data) {
				voidForm(form);

				var modal = form.parents('.modal');
				if(modal.length != 0)
					modal.modal('hide');

				var test = form.find('input[name=update-list]');
				if (test.length != 0) {
					var listname = test.val();
					var list = $('#' + listname);
					list.append('<a href="' + data.url + '" class="loadable-item list-group-item">' + data.header + '</a>');
					sortList(list);
					testListsEmptiness();
				}

				var test = form.find('input[name=update-select]');
				if (test.length != 0) {
					var selectname = test.val();
					$('select[name=' + selectname + ']').each(function() {
						var o = $('<option value="' + data.id + '" selected="selected">' + data.name + '</option>');
						var trigger = $(this).find('option[value=run_modal]');
						if (trigger.length != 0)
							trigger.before(o);
						else
							$(this).append(0);
					});
				}
			}
		});
	});

	/*
		Interazioni dinamiche sul pannello prenotazioni
	*/

	$('body').on('keyup', '.booking-product-quantity input', function() {
		var v = $(this).val();
		var booked;

		if (v == '')
			booked = 0;
		else
			booked = parseInt(v);

		var row = $(this).closest('.booking-product');
		var variant_selector = row.find('.variant-selector');

		if (variant_selector.length != 0) {
			var variants = variant_selector.find('.row:not(.master-variant-selector)');
			if (variants.length != booked) {
				if (variants.length > booked) {
					var diff = variants.length - booked;
					for (var i = 0; i < diff; i++)
						variant_selector.find('.row:last').remove();
				}
				else if (variants.length < booked) {
					var diff = booked - variants.length;
					var master = variant_selector.find('.master-variant-selector');
					for (var i = 0; i < diff; i++)
						variant_selector.append(master.clone().removeClass('master-variant-selector'));
				}
			}
		}
	}).on('blur', function() {
		var v = $(this).val();
		if (v == '')
			$(this).val('0');
	});

	/*
		Widget generico multiriga
	*/

	$('body').on('click', '.delete-many-rows', function(event) {
		event.preventDefault();
		var container = $(this).closest('.many-rows');
		$(this).closest('.row').remove();
		manyRowsAddDeleteButtons(container);
		return false;
	});

	$('body').on('click', '.add-many-rows', function(event) {
		event.preventDefault();
		var container = $(this).closest('.many-rows');
		var row = container.find('.row').first().clone();
		row.find('input').val('');

		/*
			Questo è per forzare l'aggiornamento di eventuali campi
			tags all'interno del widget multiriga
		*/
		row.find('.bootstrap-tagsinput').remove();
		row.find('.tagsinput').tagsinput();

		container.find('.add-many-rows').before(row);
		manyRowsAddDeleteButtons(container);
		return false;
	});
});
