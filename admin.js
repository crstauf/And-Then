jQuery(function($) {

	$("#misc-publishing-actions .misc-pub-section-last").removeClass("misc-pub-section-last");
	$("#misc-publishing-actions .misc-pub-section").last().addClass("misc-pub-section-last");

	$("a#next-edit.edit-next").click(function(ev) {
		ev.preventDefault();
		$(this).hide();
		$("#misc-pub-next-select").slideDown();
		$("#next > #misc-pub-next-select > ul > li > select").chosen({disable_search_threshold: 50});
	});

	$("#misc-pub-next-select > ul > li > input[type=radio]").click(function() {
		$("#misc-pub-next-select .next-add-options").hide();

		if ($("input#next-add-order").is(':checked')) $("#misc-pub-next-select .next-add-options-order").slideDown(200);
		else $("#misc-pub-next-select .next-add-options-order").slideUp(200);
	});

	$("input#next-action-add").click(function() {
		var opts = $(this).parent().find('.next-add-options');
		var pt = $("input#post_type").val();
		if (opts.is(':hidden') && pt == $("#next select#next-add").find('option:selected').val()) {
			opts.show();
			$("#misc-pub-next-select .next-add-options select").chosen({disable_search_threshold: 50});
		}
	});

	$("#next").on('change',"select#next-add",function() {
		var pt = $("input#post_type").val();
		if (pt != $(this).find('option:selected').val()) {
			$("#misc-pub-next-select .next-add-options").slideUp(200,function() {
				$("#misc-pub-next-select .next-add-options input[type=checkbox]").prop('checked','');
			});
		} else $("#misc-pub-next-select .next-add-options").slideDown(200);
	});

	$("#pageparentdiv").on('change',"select#parent_id",function() {
		if ('' == $(this).find('option:selected').val()) $("#next .next-add-options-parent").slideUp(200,function() { $(this).prop('checked',''); });
		else $("#next .next-add-options-parent").slideDown(200);
	});

	/*$("input#next-add-order").click(function() {
		var opts = $(this).parent().find('.next-add-options-order');
		if ($(this).is(':checked')) opts.slideDown(200);
		else opts.slideUp(200);
	});*/

	$("#next .save-post-next").click(function(ev) {
		ev.preventDefault();

		$("#misc-pub-next-select").slideUp();
		$("a#next-edit.edit-next").show();

		var checked = $("#misc-pub-next-select > ul > li > input:checked");
		var display = checked.parent().find('label').html();

		$("#next-action").val(checked.val());

		if ('add' == checked.val()) {
			display = 'Add new ';
			if ($("#next-add-parent").is(':checked')) display += 'child ';
			display += $("#next-add > option:selected").html().toLowerCase();
			if ($("#next-add-order").is(':checked')) {
				if ('increment' == $("#next-add-order-turn").val()) display += '++';
				else display += '--';
			}
		} else if ('goto' == checked.val())
			display = 'Go to ' + $("#next-goto > option:selected").html();

		$("#next #next-display").html(display);
	});

	$("#next .cancel-post-next").click(function(ev) {
		ev.preventDefault();
		$("#misc-pub-next-select").slideUp();
		$("a#next-edit.edit-next").show();
	});

});
