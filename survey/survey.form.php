<?php
	require_once '../Form.class.php';

	$items = array(
		1 => "That NSERC’s budget for 'basic' research (i.e. Discovery Grants) be increased",
		2 => "That NSERC’s overall budget be increased, with increases applied to basic and applied areas roughly in proportion to the current allocation (i.e. no more de-emphasizing of 'basic' work).",
		3 => "That the scientific community be fully engaged in helping to determine any priority or targeted areas for scientific research funding, within the granting councils or elsewhere.",
		4 => "That significant new funding be provided for the ongoing costs of operating major science infrastructure.",
		5 => "That the government permit each granting council to award scholarships on the basis of excellence only and not area of study",
		6 => "That the funding for the indirect costs of research rise to represent 40 percent of the direct costs allocated to the granting councils.",
		7 => "That funding for the IRAP program, which has a proven track-record of creating high-quality jobs based upon scientific research, be further increased.",
		8 => "That Canada create a program based on the U.S. SBIR program, to help bridge the gap between research and commercialization.",
		9 => "Other (please specify)"
	);



/*Academia: student, post-doc, RA, etc.
Academia: faculty member or equivalent
Government
Industry
Consultant
Other (please specify)	 */

	$rank = array_merge(array(" "), range(1,9));

	$form = new FORM('poll', array('session'=>true, 'session_erase_after_load'=>true));
	$form->attr('action', "process.php");
	$pinfo = $form->fieldset('participant_info', "Participant Information");
		$pinfo->text('name', "Name (optional)");
		$pinfo->text('academic', "Academic Rank or Position(optional)");
		$pinfo->text('institute', "Institute")->required("The institute is required!");
		$sector = $pinfo->fieldset('sector', "Please indicate the sector(s) in which you work, or most recently worked");
			$sector->checkbox('academia_student', "Academia: student, post-doc, RA, etc.");
			$sector->checkbox('academia_faculty', "Academia: faculty member or equivalent");
			$sector->checkbox('government', "Government");
			$sector->checkbox('industry', "Industry");
			$sector->checkbox('consultant', "Consultant");
			$sector->checkbox('other', "Other (please specify)");
			$sector->text('other_sector');
	$survey = $form->fieldset('survey', "Survey")->attr('id', 'survey');
		foreach ($items as $i=>$q) {
			$fs = $survey->fieldset("q{$i}", "{$q}");
				$fs->select("q{$i}[rank]", "Rank", $rank);
				$fs->textarea("q{$i}[comment]", "Comment");
		}
		$survey->textarea("info", "If you have information that may help us to answer the HCFC’s second question, please provide it here.");
	$form->submit_button('submit', "Submit");

?>