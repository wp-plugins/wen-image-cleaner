//	Vars
var missingFiles = [];
var unusedAttachments = [];
var unusedAttachmentsCount = 0;

//	Doc Load
jQuery(function($) {

	//	Setup the Informations
	wen_image_cleaner_set_both();

	//	Init Progressbar
	$("#wen-ic-progress").progressbar();

	//	Listen for Click
	$("#wen-remove-missings").click(function(e) {

		//	Confirm
		if(confirm(WEN_IMAGE_CLEANER_INFO.i18n.confirm_remove_leftover_images)) {

			//	Run
			clear_the_missings(function() {

				//	Setup the Stats
				wen_image_cleaner_set_stats();
			});
		}

		//	Prevent Default
		e.preventDefault();
		return false;
	});

	//	Listen for Click
	$("#wen-remove-unused-attachments").click(function(e) {

		//	Confirm
		if(confirm(WEN_IMAGE_CLEANER_INFO.i18n.confirm_remove_unused_attachments)) {

			//	Run
			clear_the_attachments(function() {

				//	Setup the Stats
				wen_image_cleaner_set_stats();
			});
		}

		//	Prevent Default
		e.preventDefault();
		return false;
	});

	//	Listen Click
	$("#wen-remove-both").click(function(e) {

		//	Confirm
		if(confirm(WEN_IMAGE_CLEANER_INFO.i18n.confirm_remove_both)) {

			//	Clear the Missings
			clear_the_missings(function() {

				//	Clear the Attachments
				clear_the_attachments(function() {

					//	Setup the Stats
					wen_image_cleaner_set_stats();
				});
			});
		}

		//	Prevent Default
		e.preventDefault();
		return false;
	});

	//	Listen Click
	$("#wen-refresh-info").click(function(e) {

		//	Refresh
		wen_image_cleaner_set_stats();

		//	Prevent Default
		e.preventDefault();
		return false;
	});

	//	Listen Year Change
	$("#wen-ic-year-dropdown").change(function() {

		//	Set Values
		mediaYear = $(this).val();
		mediaMonth = $('#wen-ic-month-dropdown').val();
	});

	//	Listen Year Change
	$("#wen-ic-month-dropdown").change(function() {

		//	Set Value
		mediaMonth = $(this).val();

		//	Load the Stats
		wen_image_cleaner_set_stats();
	});

	//	Listen Click
	$("#wen-ic-progress-status").on('click', '.task-done', function() {

		//	Fadeout
		$(this).fadeOut(500, function() {

			//	Remove
			$(this).remove();
		});
	});
});


//	Clear the Missings
function clear_the_missings(callback) {

	//	Missings
	var cMissings = missingFiles.slice(0);

	//	Check
	if(cMissings.length > 0) {

		//	Disable Buttons
		wen_image_cleaner_disable_buttons(true);

		//	Create Deferred
		var dfd = new jQuery.Deferred();

		//	Total Images
		var totalImages = cMissings.length;

		//	Stats
		var theCounts = {completed: 0, failed: 0, processing: 0, processed: 0, storage_saved: 0};

		//	Add New Progress Log Holder
		wen_image_cleaner_add_progress_log();

		//	Run
		loopback_clear_missings(dfd, cMissings);

		//	Set the Progress
		wen_image_cleaner_update_progress(0.1, WEN_IMAGE_CLEANER_INFO.i18n.processing + '...');

		//	Run Loopback
		jQuery.when(dfd.promise()).then(function() {}, function() {}, function(nAction) {

			//	Check
			if(nAction == 'processed_file') {

				//	Add the Processed Count
				theCounts.processed++;

				//	Store Saved Storage
				theCounts.storage_saved += arguments[4].filesize;

				//	Add Count
				if(arguments[2] === true)	theCounts.completed++;
				else	theCounts.failed++;
			}
			else if(nAction == 'processing_file') {

				//	Add the Processing Count
				theCounts.processing++;

				//	Update Progress
				wen_image_cleaner_update_progress(Math.ceil((theCounts.processed / totalImages) * 100) + 0.1, '[ ' + theCounts.processing + '/' + totalImages + ' ] ' + WEN_IMAGE_CLEANER_INFO.i18n.processing + ' ' + arguments[1].name + '...');
			}
		}).done(function() {

			//	Deleted Str
			var deletedStr = WEN_IMAGE_CLEANER_INFO.i18n.deleted_x_files;

			//	Failed Str
			var failedStr = WEN_IMAGE_CLEANER_INFO.i18n.failed_x_files;

			//	Finish the Progress
			wen_image_cleaner_update_progress(100, deletedStr.replace(":deleted", theCounts.completed) + (theCounts.failed > 0 ? failedStr.replace(":failed", theCounts.failed) : '') + ' <em>' + WEN_IMAGE_CLEANER_INFO.i18n.saved_storage_space + bytesToSize(theCounts.storage_saved) + '</em>');

			//	Enable Buttons
			wen_image_cleaner_disable_buttons(false);

			//	Check
			if(typeof callback == 'function') {

				//	Run Callback
				callback(theCounts);
			}
		});
	} else {

		//	Check
		if(typeof callback == 'function') {

			//	Run Callback
			callback(theCounts);
		}
	}
}

//	Loop Clear Missings Function for Callback
function loopback_clear_missings(dfd, cMissings, countNow, _lastResponse) {

	//	Check
	if(!countNow || countNow == undefined)	countNow = 1;

	//	Check
	if(cMissings.length == 0) {

		//	Resolve
		dfd.resolve('completed', _lastResponse);

		//	Return
		return false;
	}

	//	Run Ajax
	jQuery.ajax({
		data: {
			file: cMissings[0],
			action: 'wen_image_cleaner_delete_image',
			media_year: mediaYear,
			media_month: mediaMonth
		},
		type: 'POST',
		url: WEN_IMAGE_CLEANER_INFO.ajax_url,
		beforeSend: function() {

			//	Notify
			dfd.notify('processing_file', cMissings[0]);
		},
		success: function(response) {

			//	Check
			if(response.success) {

				//	Set
				_lastResponse = response;
			}

			//	Notify
			dfd.notify('processed_file', countNow, response.success, cMissings[0], _lastResponse);

			//	Splice
			cMissings.splice(0, 1);

			//	Run Again
			loopback_clear_missings(dfd, cMissings, countNow + 1, response);
		}
	});
}

//	Run the Clear Attachments
function clear_the_attachments(callback) {

	//	Unused
	var cuAttachments = unusedAttachments.slice(0);

	//	Check
	if(cuAttachments.length > 0) {

		//	Disable Buttons
		wen_image_cleaner_disable_buttons(true);

		//	Create Deferred
		var dfd = new jQuery.Deferred();

		//	Total Images
		var totalAttachments = cuAttachments.length;

		//	Stats
		var theCounts = {completed: 0, failed: 0, processing: 0, processed: 0, storage_saved: 0};

		//	Add New Progress Log Holder
		wen_image_cleaner_add_progress_log();

		//	Run
		loopback_clear_unused_attachments(dfd, cuAttachments);

		//	Set the Progress
		wen_image_cleaner_update_progress(0.1, WEN_IMAGE_CLEANER_INFO.i18n.processing + '...');

		//	Run Loopback
		jQuery.when(dfd.promise()).then(function() {}, function() {}, function(nAction) {

			//	Check
			if(nAction == 'processed_attachment') {

				//	Add the Processed Count
				theCounts.processed++;

				//	Store Saved Storage
				theCounts.storage_saved += arguments[4].filesize;

				//	Add Count
				if(arguments[2] === true)	theCounts.completed++;
				else	theCounts.failed++;
			}
			else if(nAction == 'processing_attachment') {

				//	Add the Processing Count
				theCounts.processing++;

				//	Update Progress
				wen_image_cleaner_update_progress(Math.ceil((theCounts.processed / totalAttachments) * 100) + 0.1, '[ ' + theCounts.processing + '/' + totalAttachments + ' ] ' + WEN_IMAGE_CLEANER_INFO.i18n.processing + ' ' + arguments[1].name + '...');
			}
		}).done(function() {

			//	Deleted Str
			var deletedStr = WEN_IMAGE_CLEANER_INFO.i18n.deleted_x_attachments;

			//	Failed Str
			var failedStr = WEN_IMAGE_CLEANER_INFO.i18n.failed_x_attachments;

			//	Finish the Progress
			wen_image_cleaner_update_progress(100, deletedStr.replace(':deleted', theCounts.completed) + (theCounts.failed > 0 ? failedStr.replace(':failed', theCounts.failed) : '') + ' <em>' + WEN_IMAGE_CLEANER_INFO.i18n.saved_storage_space + bytesToSize(theCounts.storage_saved) + '</em>');

			//	Enable Buttons
			wen_image_cleaner_disable_buttons(false);

			//	Check
			if(typeof callback == 'function') {

				//	Run Callback
				callback(theCounts);
			}
		});
	} else {

		//	Check
		if(typeof callback == 'function') {

			//	Run Callback
			callback(theCounts);
		}
	}
}

//	Loop Remove Unused Attachments Function for Callback
function loopback_clear_unused_attachments(dfd, cuAttachments, countNow, _lastResponse) {

	//	Check
	if(!countNow || countNow == undefined)	countNow = 1;

	//	Check
	if(cuAttachments.length == 0) {

		//	Resolve
		dfd.resolve('completed', _lastResponse);

		//	Return
		return false;
	}

	//	Run Ajax
	jQuery.ajax({
		data: {
			attachment: cuAttachments[0],
			action: 'wen_image_cleaner_delete_attachment',
			media_year: mediaYear,
			media_month: mediaMonth
		},
		type: 'POST',
		url: WEN_IMAGE_CLEANER_INFO.ajax_url,
		beforeSend: function() {

			//	Notify
			dfd.notify('processing_attachment', cuAttachments[0]);
		},
		success: function(response) {

			//	Check
			if(response.success) {

				//	Set
				_lastResponse = response;
			}

			//	Notify
			dfd.notify('processed_attachment', countNow, response.success, cuAttachments[0], _lastResponse);

			//	Splice
			cuAttachments.splice(0, 1);

			//	Run Again
			loopback_clear_unused_attachments(dfd, cuAttachments, countNow + 1, response);
		}
	});
}

//	Add New Log Bar
function wen_image_cleaner_add_progress_log() {

	//	Append
	jQuery("#wen-ic-progress-status").prepend('<p></p>');
}

//	Change the Progress
function wen_image_cleaner_update_progress(percent, status) {

	//	Check
	if(percent > 0) {

		//	Update
		jQuery("#wen-ic-progress").progressbar('option', 'value', percent);
		jQuery("#wen-ic-progress-status>p:first").html(status);

		//	Show
		jQuery("#wen-ic-progress").stop(true, true).show(0);
	} else {

		//	Hide
		jQuery("#wen-ic-progress").hide(0);
	}

	//	Check
	if(percent >= 100) {

		//	Set Timeout
		setTimeout(function() {

			//	Fade Out
			jQuery("#wen-ic-progress").fadeOut(500);

			//	Add Class
			jQuery("#wen-ic-progress-status>p:first").addClass('task-done');
		}, 1000);
	}
}

//	Disable Buttons
function wen_image_cleaner_disable_buttons(dis) {

	//	Disable
	dis = !!dis;

	//	Check
	if(dis) {

		//	Disable
		jQuery('.wen-ic-button').attr('disabled', true);
		jQuery('.wen-ic-dropdown').attr('disabled', true);
	} else {

		//	Enable
		jQuery('.wen-ic-button').attr('disabled', false);
		jQuery('.wen-ic-dropdown').attr('disabled', false);
	}
}

//	Format the Filesize
function bytesToSize(bytes) {
	var sizes = ['byte', 'kb', 'mb', 'gb', 'tb'];
	if (bytes == 0) return '0 Byte';
	var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
	//return Math.round(bytes / Math.pow(1000, i), 2) + ' ' + sizes[i];
	return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
}

//	Set Both
function wen_image_cleaner_set_both() {

	//	Setup
	wen_image_cleaner_set_dirs(function() {
		wen_image_cleaner_set_stats();
	});
}

//	Set the Dirs Scan Data
function wen_image_cleaner_set_dirs(callback) {

	//	Run Ajax
	jQuery.ajax({
		data: {
			action: 'wen_image_cleaner_get_dir_data'
		},
		type: 'POST',
		url: WEN_IMAGE_CLEANER_INFO.ajax_url,
		beforeSend: function() {

			//	Disable Buttons
			wen_image_cleaner_disable_buttons(true);
		},
		success: function(response) {

			//	Check Success
			if(response.success) {

				//	Dir Data
				var dirData = response.data;

				//	Truncate Year & Month Dropdown
				jQuery("#wen-ic-year-dropdown").html('');
				jQuery("#wen-ic-month-dropdown").html('');

				//	Loop Each
				for(var i in dirData) {

					//	Add Year
					jQuery("#wen-ic-year-dropdown").append('<option value="' + i + '"' + (i == mediaYear ? ' selected="selected"' : '') + '>' + i + '</option>');

					//	Loop Each Months
					for(var j in dirData[i]) {

						//	Add Month
						jQuery("#wen-ic-month-dropdown").append('<option value="' + dirData[i][j] + '"' + (i == mediaYear && dirData[i][j] == mediaMonth ? ' selected="selected"' : '') + ' data-year="' + i + '">' + dirData[i][j] + '</option>');
					}
				}

				//	Trigger Change
				jQuery("#wen-ic-year-dropdown").change();
			}

			//	Enable Buttons
			wen_image_cleaner_disable_buttons(false);

			//	Check
			if(typeof callback == 'function') {

				//	Run Callback
				callback(response);
			}
		}
	});
}

//	Set the File Stats
function wen_image_cleaner_set_stats(callback) {

	//	Reset
	missingFiles = [];
	unusedAttachments = [];
	unusedAttachmentsCount = 0;

	//	Run Ajax
	jQuery.ajax({
		data: {
			action: 'wen_image_cleaner_get_media_data',
			media_year: mediaYear,
			media_month: mediaMonth
		},
		type: 'POST',
		url: WEN_IMAGE_CLEANER_INFO.ajax_url,
		beforeSend: function() {

			//	Disable Buttons
			wen_image_cleaner_disable_buttons(true);
		},
		success: function(response) {

			//	Check Success
			if(response.success) {

				//	Data
				var fileData = response.data;

				//	Loop Each
				for(var i in fileData) {

					//	Check
					if(isNaN(i)) {

						//	Add Count
						unusedAttachments.push({name: i, files: fileData[i]});
						unusedAttachmentsCount += fileData[i].length;
					} else {

						//	Add
						missingFiles.push(fileData[i]);
					}
				}

				//	Set the Counts
				jQuery('#missing-files-count').text(missingFiles.length + ' file(s)');
				jQuery('#unwanted-attachments-count').html(unusedAttachments.length + ' attachment(s)' + (unusedAttachmentsCount > 0 ? ' - <em>' + unusedAttachmentsCount + ' file(s)</em>' : ''));
			}

			//	Enable Buttons
			wen_image_cleaner_disable_buttons(false);

			//	Check
			if(typeof callback == 'function') {

				//	Run Callback
				callback(response);
			}
		}
	});
}