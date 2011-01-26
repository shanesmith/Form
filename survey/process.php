<?php session_start();
	include_once 'CAP.inc.php';
	include_once './survey.form.php';

	//dump($form);

	if (!$form->validate()) {
		$form->remember();
		CAP::Redirect('index.php');
	}
?>
<h1>HOORAY!</h1>