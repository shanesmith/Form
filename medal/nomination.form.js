$(function(){

	$(".form-field-file :file").change(setUpload);

	$(".form-field-file a.change").click(setChange);

	$(".form-field-file .field a.cancel").click(cancelChange);

	$(".form-field-file .ready a.cancel").click(cancelUpload);

	$("a.view").click(ajaxFileGet);

});

setUpload = function() {
	$slot = $(this).parents(".form-field-file");

	filename = basename($(this).val());

	$slot
		.find(".field").hide().end()
		.find(".ready")
			.find("span").text(filename).end()
		.show();
}

setChange = function() {
	$(this).parents(".form-field-file")
		.find(".options").hide().end()
		.find(".field")
			.find(":file").change(setUpload).end()
			.filter(":not(:has(a.cancel))")
				.append("(<a href='#' class='cancel'>cancel</a>)")
				.find("a.cancel").click(cancelChange).end()
			.end()
		.show();

	return false;
}

cancelUpload = function() {
	$slot = $(this).parents(".form-field-file");

	$slot
		.find(".field").show().end()
		.find(".ready").hide();

	$fileInput = $slot.find(":file");
	$newFileInput = $("<input type='file' />").attr({ name:$fileInput.attr("name"), id:$fileInput.attr("id") }).change(setUpload);

	$fileInput.replaceWith( $newFileInput );

	return false;
}

cancelChange = function() {
	$(this).parents(".form-field-file")
		.find(".field").hide().end()
		.find(".ready").hide().end()
		.find(".options").show();

	$fileInput = $(this).parents(".form-field-file").find(":file");
	$newFileInput = $("<input type='file' />").attr({ name:$fileInput.attr("name"), id:$fileInput.attr("id") }).change(setUpload);

	$fileInput.replaceWith( $newFileInput );

	return false;
}

// File Get with ajax (iframe)
ajaxFileGet = function() {
	var doc = $(this).attr("alt");
	var nomination_id = $(this).attr('nomid');
	var url = "/medal/scripts/file.php?nomination_id=" + nomination_id + "&doc=" + doc;

	if ($.browser.opera) open(url);
	else {
		if ( $("iframe#ajaxFileGet").size() == 0 ) $("body").append("<iframe id='ajaxFileGet' style='position:absolute; top:-1000px; left:-1000px;'/>");
		$("iframe#ajaxFileGet").attr("src", url);
	}

	return false;
}

basename = function(path) {
	var tmp=path.match(/[\/|\\]([^\\\/]+)$/);
	if (tmp)	return tmp[1];
	else return path;
}