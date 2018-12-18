jQuery(document).ready(function($) {

	if($('.psp-phase-meta-indicator').length) {

		$('.psp-phase-meta-indicator').each(function() {

			if($(this).val() == 'Yes') {
				$(this).parent().addClass('checklist-phase');
				$('.checklist-phase ul.psp-task-list').addClass('checklist');
			}

		});

	}

	$('#psp-projects ul.psp-task-list.checklist li strong').click(function(e) {

		e.stopPropagation();

        var projectID 		= $(this).parents('li').find('.task-save-button').attr('data-project');
        var phaseID 		= $(this).parents('li').find('.task-save-button').attr('data-phase');
        var taskID 			= $(this).parents('li').find('.task-save-button').attr('data-task');
        var phase_progress 	= $(this).parents('li').find('.task-save-button').attr('data-phase-auto');
        var total_progress 	= $(this).parents('li').find('.task-save-button').attr('data-overall-auto');

		if( $(this).parents('li').hasClass('psp-is-sequential') ) {

			var parent = $(this).parents('li');
			if( $(parent).is(":first-child") ) {

				// This is a first task, check the previous phase
				var phase_parent = $(this).parents('.psp-phase');
				if( !$(phase_parent).prev().hasClass('phase-complete') ) {
					return false;
				}

			} else {
				if ( !$(parent).prev().hasClass('complete') ) {
					return false;
				}
			}

		}

		var ajaxurl = $('#psp-ajax-url').val();

		if($(this).parents().hasClass('complete')) {

			var progress = 0;

		} else {

			var progress = 100;

		}

        $.ajax({
            url: ajaxurl + "?action=psp_update_task_fe",
            type: 'post',
            data: {
                project_id : projectID,
                phase_id: phaseID,
                task_id: taskID,
                progress: progress
            },
            success: function(data) {

		        if(total_progress == 'Yes') {
		            psp_update_total_progress_checklist(projectID);
		        }

            },
            error: function(data) {
                console.log(data);
            }
        });

        var the_parent = $(this).parents('li.task-item-'+taskID);
        target = $(the_parent).children('span').children('em');

        if(progress == '100') { $(the_parent).addClass('complete'); } else { $(the_parent).removeClass('complete'); }

        $(target).removeClass();
        $(target).addClass('status');
        $(target).addClass('psp-' + progress);

        $(the_parent).attr('data-progress',progress);

        if(phase_progress == 'Yes') {
            psp_update_phase_completion_checklist(projectID,phaseID);
        }

        return false;


	});

    function psp_update_total_progress_checklist(projectID) {

		var ajaxurl = $('#psp-ajax-url').val();

        $.ajax({
            url: ajaxurl + "?action=psp_update_total_fe",
            type: 'post',
            data: {
                project_id : projectID,
            },
            success: function(new_progress) {

                $('.psp-progress span').removeClass();
                $('.psp-progress span').addClass('psp-' + new_progress);
                $('.psp-progress span').html('<b>' + new_progress + '%</b>');

                var milestones = [20,25,40,50,60,75,80];

                for(m = 0; m < milestones.length; m++) {
                    // psp_toggle_marker(new_progress,milestones[m]);
                }

            },
            error: function(data) {
                console.log(data);
            }
        });

    }

    function psp_update_phase_completion_checklist(projectID,phaseID) {

        var tasks = 0;
        var task_completion = 0;
        var tasks_completed = 0;
		var phaseIndex = phaseID;

		phaseID++;

        $('#phase-'+phaseID+' ul.psp-task-list li').each(function() {

            var task_status = $(this).attr('data-progress');
            task_status = parseInt(task_status);
            task_completion = task_completion + task_status;
            tasks++;
            if(task_status == 100) { tasks_completed++; }

        });

        completion = Math.ceil(task_completion / tasks);
        remaining = 100 - completion;

        allCharts[phaseIndex].segments[0].value = completion;
        allCharts[phaseIndex].segments[1].value = remaining;
        allCharts[phaseIndex].update();

        $('#phase-'+phaseID+' .psp-chart-complete').html(completion + '%');
        $('#phase-'+phaseID+' .task-list-toggle span b').html(tasks_completed);
        $('#phase-'+phaseID+' .psp-top-complete span').html(completion + '%');
		$('#phase-'+phaseID+' .psp-phase-overview').removeClass().addClass('psp-phase-overview cf psp-phase-progress-' + completion);

		if( completion == 100 ) {
			$('#phase-' + phaseID ).addClass('phase-complete');
		} else {
			$('#phase-' + phaseID ).removeClass('phase-complete');
		}

	    if($(window).width() > 768) {
			$('.psp-phase-info').css('height','auto');
	        pspEqualHeight($(".psp-phase-info"));
	    }

    }

});
