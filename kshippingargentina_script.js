var kijam_update_checkout_request = false;
var kshippingargentina_old_service_val = {};
function checkOfficeSelectedArgentinaOffice(ev) {
	const selector = ev.target;
	jQuery('.method_office_name', jQuery(selector).closest('.custom-office_kshippingargentina')).val(jQuery('option:selected', selector).text());
}
function checkKShippingArgentinaOffice(postcode, instance_id) {
	let $ = jQuery;
	$.post(wc_kshippingargentina_context.ajax_url, { nonce: wc_kshippingargentina_context.token, cmd: "offices_rcv", instance_id: instance_id, postcode: postcode }, function(list_json) {
		$('.method_instance_id-'+instance_id+' option').remove();
		// $('.method_instance_id-'+instance_id+' .method_office_name').val(list_json);
		let list = jQuery.parseJSON(list_json);
		/*
		if(typeof $('.method_instance_id-'+instance_id+' select').selectWoo != 'undefined') {
			$('.method_instance_id-'+instance_id+' select').selectWoo('destroy');
		} else if(typeof $('#billing_city').select2 != 'undefined') {
			$('.method_instance_id-'+instance_id+' select').select2('destroy');
		}
		*/
		
		let found = false;
		for (let i in list) {
			let o = list[i];
			let selected = '';
			if (wc_kshippingargentina_context.office_kshippingargentina == o.iso + '#' + o.id) {
				found = true;
				selected = 'selected';
				$('.method_instance_id-'+instance_id+' .method_office_name').val(o.description + ' - ' + o.address);
			}
			$('.method_instance_id-'+instance_id+' select').append('<option ' + selected + ' data-map="https://maps.google.com/?q=' + o.lat + ',' + o.lng + '" value="' + o.iso + '#' + o.id + '">' + o.description + ' - ' + o.address + '</option>');
		}
		if (!found) {
			$('.method_instance_id-'+instance_id+' .method_office_name').val($('.method_instance_id-'+instance_id+' select option:selected').text());
		}
		let opts_list = $('.method_instance_id-'+instance_id+' select').find('option');
		opts_list.sort(function(a, b) { return $(a).text() > $(b).text() ? 1 : -1; });
		$('.method_instance_id-'+instance_id+' select').html('').append(opts_list);
		if(typeof $('.method_instance_id-'+instance_id+' select').selectWoo != 'undefined') {
			$('.method_instance_id-'+instance_id+' select').selectWoo();
		} else if(typeof $('.method_instance_id-'+instance_id+' select').select2 != 'undefined') {
			$('.method_instance_id-'+instance_id+' select').select2();
		}
		$('.method_instance_id-'+instance_id+' select').on('change', checkOfficeSelectedArgentinaOffice);
	});
}
jQuery(document).ready(function() {
	let $ = jQuery;
	jQuery(document.body).on('change', 'input[name="payment_method"]', function() {
		if (kijam_update_checkout_request) clearTimeout(kijam_update_checkout_request);
		kijam_update_checkout_request = setTimeout(function() { jQuery('body').trigger('update_checkout'); }, 2000);
	});
	setInterval(function() {
		$('.custom-office_kshippingargentina:not(.eventAddedShippingArgentina)').each(function() {
			$(this).addClass('eventAddedShippingArgentina');
			$('select', this).css('max-width', '100%');
			$(this).css('flex-wrap', 'wrap');
			$('.optional', this).hide();
			let ship_to_different_address = $('input[name=ship_to_different_address]').is(':checked');
			if (ship_to_different_address) {
				checkKShippingArgentinaOffice($('#shipping_postcode').val(), $(this).attr('data-instance_id'));
			} else {
				checkKShippingArgentinaOffice($('#billing_postcode').val(), $(this).attr('data-instance_id'));
			};
		});
		$('.custom-office_kshippingargentina').each(function() {
			let li = $('select', this).closest('li');
			let radio = $('input[type=radio]', li);
			if(radio.length == 0 || $(radio).is(':checked')) {
				$(this).show();
			} else {
				$(this).hide();
			}
		});
		if ($('#billing_country:not(.eventAddedKShippingArgentina)').length > 0) {
			$('#billing_country').addClass('eventAddedKShippingArgentina');
			if(typeof $('#billing_vat_type').selectWoo != 'undefined') {
				$('#billing_vat_type').selectWoo();
			} else if(typeof $('#billing_vat_type').select2 != 'undefined') {
				$('#billing_vat_type').select2();
			}
			$('#billing_state').change(function() {
				if ($('#billing_country').val() == 'AR') {
					let class_city = $('#billing_city').attr('class');
					let val_city = $('#billing_city').val();
					$('input#billing_city').replaceWith('<select class="'+class_city+'" name="billing_city" id="billing_city" /></select>');
					$('#billing_city option').remove();
					$('#billing_city').append('<option value="">Cargando...</option>');
					if(typeof $('#billing_city').selectWoo != 'undefined') {
						$('#billing_city').selectWoo();
					} else if(typeof $('#billing_city').select2 != 'undefined') {
						$('#billing_city').select2();
					}
					$.post(wc_kshippingargentina_context.ajax_url, { nonce: wc_kshippingargentina_context.token, cmd: "cities", state: $(this).val() }, function(list_json) {
						$('#billing_city option').remove();
						//$('#billing_city').append('<option value="">Seleccione...</option>');
						let list = jQuery.parseJSON(list_json);
						let city = val_city;
						for (let i in list) {
							let o = list[i];
							let selected = '';
							if (city == o) {
								found = true;
								selected = 'selected';
							}
							$('#billing_city').append('<option ' + selected + ' value="' + o + '">' + o + '</option>');
						}
						let sel = $('#billing_city');
						let selected = sel.val(); // cache selected value, before reordering
						let opts_list = sel.find('option');
						opts_list.sort(function(a, b) { return $(a).text() > $(b).text() ? 1 : -1; });
						sel.html('').append(opts_list);
						sel.val(selected);
						if(typeof $('#billing_city').selectWoo != 'undefined') {
							$('#billing_city').selectWoo();
						} else if(typeof $('#billing_city').select2 != 'undefined') {
							$('#billing_city').select2();
						}
					});
				} else {
					if ($('select#billing_city').length == 0)
						return;
					let val_city = $('#billing_city').val();
					let class_city = $('#billing_city').attr('class');
					if(typeof $('select#billing_city').selectWoo != 'undefined') {
						$('select#billing_city').selectWoo('destroy');
					} else if(typeof $('#billing_city').select2 != 'undefined') {
						$('select#billing_city').select2('destroy');
					}
					$('select#billing_city').replaceWith('<input type="text" class="'+class_city+'" name="billing_city" id="billing_city" />');
				}
			});
			$('#billing_postcode').change(function() {
				let ship_to_different_address = $('input[name=ship_to_different_address]').is(':checked');
				if (!ship_to_different_address) {
					if ($('#billing_country').val() == 'AR') {
						let postcode = $(this).val();
						$('.custom-office_kshippingargentina').each(function(){
							checkKShippingArgentinaOffice(postcode, $(this).attr('data-instance_id'));
						});
					}
				}
			});
			$('#billing_country').change(function() {
				let ship_to_different_address = $('input[name=ship_to_different_address]').is(':checked');
				if (!ship_to_different_address) {
					if ($(this).val() == 'AR') {
						$('#billing_vat_type').closest('p').addClass('validate-required').show();
						$('#billing_vat').closest('p').addClass('validate-required').show();
						$('#billing_kphone').closest('p').addClass('validate-required').show();
						$('#billing_vat_field').show();
						$('#billing_vat_type_field').show();
						$('#billing_kphone_field').show();
						$('#billing_kphone_prefix_field').show();
					} else {
						$('#billing_vat_type').closest('p').removeClass('validate-required').hide();
						$('#billing_vat').closest('p').removeClass('validate-required').hide();
						$('#billing_kphone').closest('p').removeClass('validate-required').hide();
						$('#billing_vat_field').hide();
						$('#billing_vat_type_field').hide();
						$('#billing_kphone_field').hide();
						$('#billing_kphone_prefix_field').hide();
					}
				}
				$('#billing_state').trigger('change');
				$('#billing_postcode').trigger('change');
			}).trigger('change');
			$('input[name=ship_to_different_address]').change(function() {
				$('#billing_country').trigger('change');
				$('#shipping_country').trigger('change');
			});
		}

		if ($('#shipping_country:not(.eventAddedKShippingArgentina)').length > 0) {
			$('#shipping_country').addClass('eventAddedKShippingArgentina');
			$('#shipping_state').change(function() {
				if ($('#shipping_country').val() == 'AR') {
					let class_city = $('#shipping_city').attr('class');
					let val_city = $('#shipping_city').val();
					$('input#shipping_city').replaceWith('<select class="'+class_city+'" name="shipping_city" id="shipping_city" /></select>');
					$('#shipping_city option').remove();
					$('#shipping_city').append('<option value="">Cargando...</option>');
					if(typeof $('select#shipping_city').selectWoo != 'undefined') {
						$('select#shipping_city').selectWoo();
					} else if(typeof $('#shipping_city').select2 != 'undefined') {
						$('select#shipping_city').select2();
					}
					$.post(wc_kshippingargentina_context.ajax_url, { nonce: wc_kshippingargentina_context.token, cmd: "cities", state: $(this).val() }, function(list_json) {
						$('#shipping_city option').remove();
						//$('#shipping_city').append('<option value="">Seleccione...</option>');
						let list = jQuery.parseJSON(list_json);
						let city = $('#shipping_city').val();
						for (let i in list) {
							let o = list[i];
							let selected = '';
							if (city == o) {
								found = true;
								selected = 'selected';
							}
							$('#shipping_city').append('<option ' + selected + ' value="' + o + '">' + o + '</option>');
						}
						let sel = $('select#shipping_city');
						let selected = sel.val(); // cache selected value, before reordering
						let opts_list = sel.find('option');
						opts_list.sort(function(a, b) { return $(a).text() > $(b).text() ? 1 : -1; });
						sel.html('').append(opts_list);
						sel.val(selected);
						if(typeof $('select#shipping_city').selectWoo != 'undefined') {
							$('select#shipping_city').selectWoo();
						} else if(typeof $('#shipping_city').select2 != 'undefined') {
							$('select#shipping_city').select2();
						}
					});
				} else {
					if ($('select#shipping_city').length == 0)
						return;
					let val_city = $('#shipping_city').val();
					let class_city = $('#shipping_city').attr('class');
					if(typeof $('select#shipping_city').selectWoo != 'undefined') {
						$('select#shipping_city').selectWoo('destroy');
					} else if(typeof $('#shipping_city').select2 != 'undefined') {
						$('select#shipping_city').select2('destroy');
					}
					$('select#shipping_city').replaceWith('<input type="text" class="'+class_city+'" name="shipping_city" id="shipping_city" />');
				}
			});
			$('#shipping_postcode').change(function() {
				let ship_to_different_address = $('input[name=ship_to_different_address]').is(':checked');
				if (ship_to_different_address) {
					if ($('#shipping_country').val() == 'AR') {
						let postcode = $(this).val();
						$('.custom-office_kshippingargentina').each(function(){
							checkKShippingArgentinaOffice(postcode, $(this).attr('data-instance_id'));
						});
					}
				}
			});
			$('#shipping_country').change(function() {
				let ship_to_different_address = $('input[name=ship_to_different_address]').is(':checked');
				if (ship_to_different_address) {
					if ($(this).val() == 'AR') {
						$('#billing_vat_type').closest('p').addClass('validate-required').show();
						$('#billing_vat').closest('p').addClass('validate-required').show();
						$('#billing_kphone').closest('p').addClass('validate-required').show();
						$('#billing_vat_field').show();
						$('#billing_vat_type_field').show();
						$('#billing_kphone_field').show();
						$('#billing_kphone_prefix_field').show();
					} else {
						$('#billing_vat_type').closest('p').removeClass('validate-required').hide();
						$('#billing_vat').closest('p').removeClass('validate-required').hide();
						$('#billing_kphone').closest('p').removeClass('validate-required').hide();
						$('#billing_vat_field').hide();
						$('#billing_vat_type_field').hide();
						$('#billing_kphone_field').hide();
						$('#billing_kphone_prefix_field').hide();
					}
				}
				$('#shipping_state').trigger('change');
				$('#shipping_postcode').trigger('change');
			}).trigger('change');
		};
		$('select[name="woocommerce_kshippingargentina-shipping_service_type"]:not(.eventAddedKShippingArgentina)').each(function() {
			$(this).addClass('eventAddedKShippingArgentina');
			let instance_id = $('input[name="instance_id"]', $(this).closest('form')).val();
			kshippingargentina_old_service_val[instance_id] = $(this).val();
			$(this).change(function() {
				let instance_id = $('input[name="instance_id"]', $(this).closest('form')).val();
				$.post(wc_kshippingargentina_context.ajax_url, { nonce: wc_kshippingargentina_context.token, cmd: "offices_sender", service_type: $(this).val() }, function(list_json) {
					$('select[name="woocommerce_kshippingargentina-shipping_office_src"] option').remove();
					let list = jQuery.parseJSON(list_json);
					for (let i in list) {
						let o = list[i];
						let selected = o.iso + '#' + o.id == kshippingargentina_old_service_val[instance_id]?'selected':'';
						$('select[name="woocommerce_kshippingargentina-shipping_office_src"]').append('<option ' + selected + ' data-map="https://maps.google.com/?q=' + o.lat + ',' + o.lng + '" value="' + o.iso + '#' + o.id + '">' + o.description + ' - ' + o.address + '</option>');
					}
				});
			});
		});
	}, 500);
	
	if(typeof $('#kshipping_vat_type').selectWoo != 'undefined') {
		$('#kshipping_vat_type').selectWoo();
	} else if(typeof $('#kshipping_vat_type').select2 != 'undefined') {
		$('#kshipping_vat_type').select2();
	}
	
	if(typeof $('#kshipping_state').selectWoo != 'undefined') {
		$('#kshipping_state').selectWoo();
	} else if(typeof $('#kshipping_state').select2 != 'undefined') {
		$('#kshipping_state').select2();
	}
});
function kshipping_new_box() {
	let $ = jQuery;
	let text_remove = $('.kshippingargentina-boxes').attr('data-remove-text');
	let model = $('.kshippingargentina-box').first().clone().appendTo('.kshippingargentina-boxes');
	$(model).append('<a href="javascript:;" onclick="kshipping_remove_box(this)">'+text_remove+'</a>');
	$('input', model).val('');
}
function kshipping_remove_box(el) {
	let $ = jQuery;
	$(el).closest('.kshippingargentina-box').remove();
}
let kshipping_generate_label_loading = false;
function kshipping_generate_label(btn) {
	let $ = jQuery;
	if (kshipping_generate_label_loading) return;
	kshipping_generate_label_loading = true;
	$(btn).html($(btn).attr('data-text-loading')).attr('disabled', 'disabled');
	let data_to_send = {};
	$('input,select', '#kshippingargentina-container').each(function() {
		data_to_send[$(this).attr('name')] = $(this).val();
	});
	$.post(wc_kshippingargentina_context.ajax_url, data_to_send, function(data_json) {
		kshipping_generate_label_loading = false;
		$(btn).removeAttr('disabled');
		console.log(data_json);
		try {
			let data = jQuery.parseJSON(data_json);
			console.log(data);
			if (data.ok) {
				document.location.reload();
			} else {
				alert(data.error);
			}
		} catch(e) {
			alert('Internal server error 2');
		}

	}).fail(function() {
		$(btn).removeAttr('disabled');
		kshipping_generate_label_loading = false;
		$(btn).html($(btn).attr('data-text'));
		alert('Internal server error 1');
	});
}
function kshipping_delete_label(btn, order_id, service_type) {
	let $ = jQuery;
	if (kshipping_generate_label_loading) return;
	kshipping_generate_label_loading = true;
	$(btn).html($(btn).attr('data-text-loading')).attr('disabled', 'disabled');
	let data_to_send = {
		delete_label: order_id,
		service_type: service_type,
		kshippingargentina_delete_label_nonce: $('#kshippingargentina_delete_label_nonce').val()
	};
	$.post(wc_kshippingargentina_context.ajax_url, data_to_send, function(data_json) {
		kshipping_generate_label_loading = false;
		$(btn).removeAttr('disabled');
		$(btn).html($(btn).attr('data-text'));
		console.log(data_json);
		try {
			let data = jQuery.parseJSON(data_json);
			console.log(data);
			if (data.ok) {
				document.location.reload();
			} else {
				alert(data.error);
			}
		} catch(e) {
			alert('Internal server error 2');
		}

	}).fail(function() {
		$(btn).removeAttr('disabled');
		kshipping_generate_label_loading = false;
		$(btn).html($(btn).attr('data-text'));
		alert('Internal server error 1');
	});
}
function kshipping_save_tracking_code(btn, order_id) {
	let $ = jQuery;
	if (kshipping_generate_label_loading) return;
	kshipping_generate_label_loading = true;
	$(btn).html($(btn).attr('data-text-loading')).attr('disabled', 'disabled');
	let data_to_send = {
		save_tracking_code: order_id,
		tracking_code: $('#kshipping_tracking_code').val(),
		instance_id: $('#kshippingargentina_instance_id').val(),
		kshippingargentina_tracking_code_nonce: $('#kshippingargentina_tracking_code_nonce').val()
	};
	$.post(wc_kshippingargentina_context.ajax_url, data_to_send, function(data_json) {
		kshipping_generate_label_loading = false;
		$(btn).removeAttr('disabled');
		$(btn).html($(btn).attr('data-text'));
		console.log(data_json);
		try {
			let data = jQuery.parseJSON(data_json);
			console.log(data);
			if (data.ok) {
				document.location.reload();
			} else {
				alert(data.error);
			}
		} catch(e) {
			alert('Internal server error 2');
		}

	}).fail(function() {
		$(btn).removeAttr('disabled');
		kshipping_generate_label_loading = false;
		$(btn).html($(btn).attr('data-text'));
		alert('Internal server error 1');
	});
}
