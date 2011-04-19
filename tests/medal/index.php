<?php
	include 'nomination.form.php';

	$nomination_form = new NOMINATION_FORM(992);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title></title>
	<?=$nomination_form->head()?>
</head>
<body>

	<?=$nomination_form?>

</body>
</html>
