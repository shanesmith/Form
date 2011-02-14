<?php

include_once 'CAP.inc.php';
include_once 'Medal.class.php';
include_once '../Form.class.php';
//include_once 'SQLBuilder.old.class.php';
include_once 'UPLOAD.class.php';

class NOMINATION_FORM extends FORM {

	/**
	* @var MEDAL_NOMINATION
	*/
	protected $nomination;

	function __construct($nomination_id=null, $options=array()) {
		// Setup form
		parent::__construct('nomination', $options);

		// Database
		$cpdb = CAP::CapSiteDB();

		// Setup nomination
		$this->nomination = MEDAL::nom($nomination_id);

		// Useful arrays
		$medals = $cpdb->FetchAllWithKey("SELECT id, CONCAT('', keyword, ' // ', name) name FROM medal_info ORDER BY id", null, 'id', 'name');

		$societies = MEDAL::societies();

		$nominee_society_options = array();
		foreach ($societies as $society) $nominee_society_options[$society['acronym']] = "{$society['name']} ({$society['acronym']})";

		$salutations = array("Dr.", "Mr.", "Mrs.", "Miss", "Ms.", "Prof.");

		$documents = MEDAL::supporting_documents($this->nomination('medal_id'));

		// Nomination ID
		$this->info('nomination_id', 'Nomination ID', $this->nomination('id'))->addClass('top_fieldset');

		// Define the major fieldsets
		$fieldset_medal = 		$this->fieldset('medal')->addClass('top_fieldset');
		$fieldset_nominator = 	$this->fieldset('nominator', 'Nominator')->addClass('top_fieldset');
		$fieldset_nominee = 	$this->fieldset('nominee', 'Nominee')->addClass('top_fieldset');
		$fieldset_documents = 	$this->fieldset('documents', 'Required Supporting Documents')->id('documents')->addClass('top_fieldset');
		$fieldset_citation = 	$this->fieldset('citation', '300 Word Citation')->addClass('top_fieldset');
		$fieldset_notes = 		$this->fieldset('notes', "Special Notes")->addClass('top_fieldset');
		$fieldset_buttons = 	$this->fieldset('buttons')->addClass('top_fieldset');

		// Medal fieldset
		$fieldset_medal->select('medal', 'Medal', $medals, $this->nomination('medal_id'));

		// Nominator fieldset
		$fieldset_nominator
			->text('nominator[constit_id]', 'constit_id', $this->nominator('constit_id'))->parent()
			->info('nominator[name]', 'name', $this->nominator('name'))->parent()
			->info('nominator[email]', 'email', $this->nominator('email'));

		$fieldset_nominator_societies = $fieldset_nominator->fieldset('nominator_societies', 'societies');
		foreach ($societies as $society) {
			$in_society = in_array($society['acronym'], explode(',', $this->nominator('societies')));
			$fieldset_nominator_societies->checkbox("nominator[societies][{$society['acronym']}]", $society['acronym'], $in_society)->attr('title', $society['name']);
		}

		// Nominee fieldset
		$fieldset_nominee
			->select('nominee[society]', 'society', $nominee_society_options, $this->nominee('society'))->parent()
			->radio_list('nominee[salutation]', 'salutation', $this->nominee('salutation'))
				->addMultipleRadios(array_map(create_function('$str', 'return array_fill(0, 3, $str);'), $salutations))
				->parent()
			->fieldset('nominee_name', '')
				->text('nominee[first_name]', 'first name', $this->nominee('first_name'))->parent()
				->text('nominee[last_name]', 'last name', $this->nominee('last_name'))->parent()
				->parent()
			->fieldset('nominee_institute', 'institute')
				->text('nominee[institute][en]', 'en', $this->nominee('institute'))->parent()
				->text('nominee[institute][fr]', 'fr', $this->nominee('institute_fr'))->parent()
				->parent()
			->fieldset('nominee_telephone')
				->text('nominee[telephone]', 'telephone', $this->nominee('telephone'))->parent()
				->text('nominee[telephone_ext]', 'ext.', $this->nominee('telephone_ext'))->parent()
				->parent()
			->text('nominee[email]', 'email', $this->nominee('email'))->parent()
			->radio_list('nominee[working_career]', 'Working Career', $this->nominee('working_career'))
				->addRadio('major_time', 'major_time', " The nominee has spent the major part (i.e. more than one-half) of his/her career in Canada.")->parent()
				->addRadio('major_contribution', 'major_contribution', "The nominee has made a major contribution to  mathematical physics  after returning to a permanent position in Canada.");

		// Documents fieldset
		$fieldset_documents->info('description', '', "
			<ul>
				<li>Click a document title to see a description of what is required.</li>
				<li class='important'>Please ensure that the size of your files are under 3MB each and come to a total of less than 20MB or saving the nomination may fail.</li>
				<li class='important'>Only the following file formats are acctepted: PDF (.pdf), MS Word (.doc, .docx), Text (.txt, .rtf), Image (.jpg, .jpeg, .png, .gif, .tiff)</li>
			</ul>
		")->addClass('description');

		foreach($documents as $doc) {
			$fieldset_documents
				->field_renderer('file', 'NOMINATION_FORM::file_field_renderer')
				->file($doc['key'], $doc['name'])
					->process_options(array(
						'allowed' => array_merge(Upload::MIME_MSWORD(), Upload::MIME_PDF(), Upload::MIME_IMAGE())
					))
					->error_messages(array(
						'incorrect_file' => "The file you have uploaded for <i>\"{$doc['name']}\"</i> (%FILE_SRC_NAME% [%FILE_SRC_MIME%]) is not an accepted file type."
					));
		}

		// Citation fieldset
		$fieldset_citation->textarea('citation', null, $this->nomination('citation'));

		// Special Notes fieldset
		$fieldset_notes
			->info('description', '', 'Use this area to note important information not covered in supporting documentation.')
				->addClass('description')
				->parent()
			->textarea('note', null, $this->nomination('note'));

		// Buttons fieldset
		$fieldset_buttons
			->submit_button('save', 'Save')->parent()
			->button('cancel', 'Cancel');

	}

	function save() {

		$success = true;

		$nomination_id = $this->nomination->id();

		$values = $this->values();

		$values['nominator']['societies'] = implode(',', array_keys($values['nominator']['societies']));

		$sql = new SQLBuilder();

		$cpdb = CAP::CapSiteDB();

		$cpdb->Transaction();

			$cpdb->Execute(
				$sql->reset()
					->Update('medal_nominations')
						->set('citation=?', 's', $values['citation'])
						->set('note=?', 	's', $values['note'])
						->where('id=?', 	'i', $nomination_id)
			);

			$nominator = $values['nominator'];
			$cpdb->Execute(
				$sql->reset()
					->Update('medal_nominators')
						->set('societies=?', 	's', $nominator['societies'])
						->where('constit_id=?', 'i', $this->nominator('constit_id'))
			);


			$nominee = $values['nominee'];
			$cpdb->Execute(
				$sql->reset()
					->Update('medal_nominees')
						->set('salutation=?', 	  's', $nominee['salutation'])
						->set('first_name=?', 	  's', $nominee['first_name'])
						->set('last_name=?', 	  's', $nominee['last_name'])
						->set('institute=?', 	  's', $nominee['institute']['en'])
						->set('institute_fr=?',   's', $nominee['institute']['fr'])
						->set('telephone=?', 	  's', $nominee['telephone'])
						->set('telephone_ext=?',  's', $nominee['telephone_ext'])
						->set('email=?', 		  's', $nominee['email'])
						->set('working_career=?', 's', $nominee['working_career'])
						->where('id=?', 		  'i', $this->nominee('id'))
			);

		$success &= $cpdb->ResolveTransaction(false);

		$success &= $this->processFileFields("D:/TEST/");

		return $success;

	}

	function head() { return $this->css()."\n".$this->js(); }

	function js() {
		return CAP::jquery()."<script type='text/javascript' src='nomination.form.js'></script>";
	}

	function css() {
		return "<link rel='stylesheet' href='nomination.form.css' />";
	}

	function nomination($key) {
		if (!isset($this->nomination)) return null;

		return $this->nomination[$key];
	}

	function nominator($key) {
		if (!isset($this->nomination)) return null;

		return $this->nomination['nominator'][$key];
	}

	function nominee($key) {
		if (!isset($this->nomination)) return null;

		return $this->nomination['nominee'][$key];
	}

	static function file_field_renderer($element) {
		$nomination_id = $element->form()->nomination('id');

		$doc = $element->name();

		//$file = MEDAL::nom($nomination_id)->documentPath($doc);

		/*$folder = MEDAL::nominationFolder($element->form()->nomination('id'));

		$file = "{$folder}{$element->name()}.*";

		$files = glob($file);

		$found = !empty($files);

		if ($found) $filename = current($files);*/

		$class = $file ? 'status-uploaded' : 'status-no-file';

		$html = $element->html();
		return "
			<div class='{$class}'>
				<div class='panel options'>
					<img alt='uploaded' src='/img/icons/submitted.jpg'/>
					<a class='view' alt='{$doc}' nomid='{$nomination_id}' href='#'>view</a>
					<span style='font-weight: normal;'>or</span>
					<a class='change' href='#'>change</a>
				</div>
				<div class='panel field'>{$html}</div>
				<div class='panel ready'> Ready to upload: <span></span> (<a class='cancel' href='#'>cancel</a>)</div>
			</div>
		";
	}


}

/*$nomination_form = new NOMINATION_FORM(720);

if ($nomination_form->isPosted()) {
	if ($nomination_form->validate()) {
		if ($nomination_form->save()) {
			echo "<b style='color:green;'>Saved successfully.</b>";
		} else {
			echo "<i style='color:red;'>ERROR WHILE SAVING!</i>";
		}
	} else {
		echo "<b style='color:red;'>There are errors in your nomination file!</b>";
	}

}*/

?>



<?//=$nomination_form?>
