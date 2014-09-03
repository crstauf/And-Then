jQuery(function($) {

	$("#misc-publishing-actions .misc-pub-section-last").removeClass("misc-pub-section-last");
	$("#misc-publishing-actions .misc-pub-section").last().addClass("misc-pub-section-last");

	$("a#andthen-edit.edit-andthen").click(function(ev) {
		ev.preventDefault();
		$(this).hide();
		$("#misc-pub-andthen-select").slideDown();
		$("#andthen > #misc-pub-andthen-select > ul > li > select").chosen({disable_search_threshold: 50});
	});

	$("#misc-pub-andthen-select > ul > li > input[type=radio]").click(function() {
		$("#misc-pub-andthen-select > ul > li .andthen-options").slideUp(200);
		$(this).parent().find('.andthen-options').slideDown(200);

		/*$("#misc-pub-andthen-select .andthen-add-options").hide();

		if ($("input#andthen-add-order").is(':checked'))
			$("#misc-pub-andthen-select .andthen-add-options-order").slideDown(200);
		else
			$("#misc-pub-andthen-select .andthen-add-options-order").slideUp(200);*/
	});

	$("input#andthen-action-add").click(function() {
		var opts = $(this).parent().find('.andthen-add-options');
		var pt = $("input#post_type").val();
		if (opts.is(':hidden') && pt == $("#andthen select#andthen-add").find('option:selected').val()) {
			opts.show();
			$("#misc-pub-andthen-select .andthen-add-options select").chosen({disable_search_threshold: 50});
		}
	});

	$("input#andthen-action-goto").click(function() {
		var opts = $(this).parent().find('.andthen-goto-options');
		var pt = $("input#post_type").val();
		if (pt != $("select#andthen-goto").find('option:selected').val()) {
			$("#misc-pub-andthen-select .andthen-goto-options").slideUp(200,function() {
				$("#misc-pub-andthen-select .andthen-goto-options input[type=checkbox]").prop('checked','');
			});
		} else $("#misc-pub-andthen-select .andthen-goto-options").slideDown(200);
	});

		$("select#andthen-goto").on('change',function() {
			var pt = $("input#post_type").val();
			if (pt != $(this).find('option:selected').val()) {
				$("#misc-pub-andthen-select .andthen-options-goto").slideUp(200,function() {
					$("#misc-pub-andthen-select .andthen-options-goto input[type=checkbox]").prop('checked','');
				});
			} else $("#misc-pub-andthen-select .andthen-options-goto").slideDown(200);
		});

	$("#andthen").on('change',"select#andthen-add",function() {
		var pt = $("input#post_type").val();
		if (pt != $(this).find('option:selected').val()) {
			$("#misc-pub-andthen-select .andthen-add-options").slideUp(200,function() {
				$("#misc-pub-andthen-select .andthen-add-options input[type=checkbox]").prop('checked','');
			});
		} else $("#misc-pub-andthen-select .andthen-add-options").slideDown(200);
	});

	$("#pageparentdiv").on('change',"select#parent_id",function() {
		if ('' == $(this).find('option:selected').val())
			$("#andthen .andthen-add-options-parent").slideUp(200,function() {
				$(this).prop('checked','');
			});
		else
			$("#andthen .andthen-add-options-parent").slideDown(200);
	}).on('change',"#page_template",function() {
		if ('' == $(this).find('option:selected').val() || 'default' == $(this).find('option:selected').val())
			$("#andthen .andthen-add-options-template").slideUp(200,function() {
				if ($("#andthen-add-template").is(':checked')) {
					$("#andthen-add-template").click();
					$("#andthen .save-post-andthen").click();
				}
			});
		else
			$("#andthen .andthen-add-options-template").slideDown(200);
	});

	/*$("input#andthen-add-order").click(function() {
		var opts = $(this).parent().find('.andthen-add-options-order');
		if ($(this).is(':checked')) opts.slideDown(200);
		else opts.slideUp(200);
	});*/

	$("#andthen .save-post-andthen").click(function(ev) {
		ev.preventDefault();

		$("#misc-pub-andthen-select").slideUp();
		$("a#andthen-edit.edit-andthen").show();

		var checked = $("#misc-pub-andthen-select > ul > li > input:checked");
		var display = checked.parent().find('label').html();

		$("#andthen-action").val(checked.val());

		if ('add' == checked.val()) {
			display = 'New ';
			if ($("#andthen-add-template").is(':checked')) display += 'templated ';
			if ($("#andthen-add-parent").is(':checked')) display += 'child ';
			display += $("#andthen-add > option:selected").html().toLowerCase();
			if ($("#andthen-add-order").is(':checked')) {
				if ('increment' == $("#andthen-add-order-turn").val()) display += '++';
				else display += '--';
			}
		} else if ('goto' == checked.val()) {
			display = 'Go to ' + $("#andthen-goto > option:selected").html();
			if ($("#andthen-goto-position").is(':checked'))
				display += ' (jump)';
		}

		$("#andthen #andthen-add-display").html(display);
	});

	$("#andthen .cancel-post-andthen").click(function(ev) {
		ev.preventDefault();
		$("#misc-pub-andthen-select").slideUp();
		$("a#andthen-edit.edit-andthen").show();
	});

});
