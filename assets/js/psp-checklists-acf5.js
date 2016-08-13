jQuery(document).ready(function($) {

    psp_make_checkboxes();


    $('.acf-field-5436e8a5e06c9 input[type="checkbox"]').change(function() {
		
        psp_checkbox_changed(this);

    });


    $('.psp-phase-meta-indicator').each(function() {

        var phase_id = $(this).attr('data-phase_id');
        var status = $(this).val();
		
        if(status == 'yes') {

            var target = $('#acf-repeater-' + phase_id).find('input.psp-make-checkbox');
						
			$(target).prop('checked',true);
			
			$(target).addClass('correct');
			
            $(target).parents('.field_key-field_527d5dfd2fa2d div.repeater').addClass('psp-checkbox-phase');
            $(target).parents('.field_key-field_527d5dfd2fa2d div.repeater').find('.psp-meta-checkbox-field').val('yes');

        }

    });


    function psp_make_checkboxes() {

		var phase_count = 1;

        $('.acf-field-5436e8a5e06c9 input[type="checkbox"]').each(function() {
			
			console.log('found one!');
						
			$(this).attr( 'data-phase-target' , phase_count );

			phase_count++;

			var phase_target = $(this).parent().parent().parent().parent().parent().parent().parent().children('.field_key-field_527d5dfd2fa2d');
			
			if($(this).prop('checked')) {
				
				$(this).parents('tr.acf-row').addClass('checkbox-tasks-phase').removeClass('non-checkbox-tasks-phase');
								
				$('tr.checkbox-tasks-phase td.acf-field-527d5e0e2fa2f select').each(function() { 
								
                    var current_value = $(this).val();
                    var select_id = $(this).attr('id');
                    var new_checkbox_id = 'checkbox-' + select_id;

                    if (!$(this).hasClass('psp-is-checkbox')) {

                        $(this).hide();
                        $(this).addClass('psp-is-checkbox');


                        var current_html = $(this).parent().html();

                        if (current_value == 100) {
                            var checked = 'checked';
                        } else {
                            var checked = '';
                        }

                        var new_checkbox = '<input class="psp-dynamic-checkbox" type="checkbox" id="' + new_checkbox_id + '" ' + checked + '>';

                        $(this).parent().html(current_html + ' ' + new_checkbox);

                        psp_bind_changer(new_checkbox_id);

                    } else {

                        if (current_value == 100) {
                            $(this).siblings('input').prop('checked', true);
                        } else {
                            $(this).siblings('input').prop('checked', false);
                        }

                        psp_bind_changer(new_checkbox_id);

                    }
					
				
				});
				
			} else { 
				
				$(this).parents('tr.acf-row').removeClass('checkbox-tasks-phase').addClass('non-checkbox-tasks-phase');
				
				$('tr.non-checkbox-tasks-phase td.acf-field-527d5e0e2fa2f select').each(function() { 
				
                	$(this).show();
                	$(this).removeClass('psp-is-checkbox');
                	$(this).siblings('input.psp-dynamic-checkbox').remove();
			
				});
			
			}

        });

    }

    function psp_bind_changer(new_checkbox_id) {

        $('#'+new_checkbox_id).change(function() {

            if ($(this).is(':checked')) {

                $(this).siblings('select').val(100);

            } else {

                $(this).siblings('select').val(0);

            }

        });

    }

    function psp_checkbox_changed(psp_checkbox) {

        psp_make_checkboxes();

    }

    $('body.post-type-psp_projects .acf-field-527d5e0e2fa2f .add-row').click(function() {

        setTimeout(function() {
            psp_make_checkboxes();
        }, 500);

    });

    $('.acf-button[data-event="add-row"]').click(function() {
		
        setTimeout(function() {

            $('.acf-field-5436e8a5e06c9 input[type="checkbox"]').change(function() {

                psp_checkbox_changed(this);

            });

        }, 500);

    });

    // psp_make_checkboxes();

});