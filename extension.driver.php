<?php

/**
 * Include Symphony classes.
 */
@require_once(TOOLKIT . '/class.entrymanager.php');

/**
 * Include external library for form validation.
 */
@require_once(EXTENSIONS . '/formvalidation/lib/validation.php');

/**
 * Form validation extension class
 *
 * @package formvalidation
 * @author Thomas Off - retiolum.de <info@retiolum.de>
 **/
class extension_formvalidation extends Extension {

	/**
	 * Set array containing all the data for the 'About' page in the extension administration
	 *
	 * @return void
	 **/
	public function about() {
		return array(
			'name' 			=> 'Form Validation',
			'version' 		=> '1.0',
			'release-date' 	=> '2009-02-16',
			'author' 		=> array(
				'name' 		=> 'Thomas Off',
				'website' 	=> 'http://www.retiolum.de',
				'email' 	=> 'info@retiolum.de',
			),
			'description' 	=> 'Allows you to add form validation based on regular expressions.'
 		);
	}
	
	/**
	 * Return an array containing the delegates that this extension subscribes itself to.
	 *
	 * @return array
	 **/
	public function getSubscribedDelegates() {
		return array(
			array(
				'page' 		=> '/blueprints/events/new/',
				'delegate' 	=> 'AppendEventFilter',
				'callback' 	=> 'addFilterToEventEditor',
			),
			array(
				'page' 		=> '/blueprints/events/edit/',
				'delegate' 	=> 'AppendEventFilter',
				'callback' 	=> 'addFilterToEventEditor',
			),
			array(
				'page' 		=> '/blueprints/events/new/',
				'delegate' 	=> 'AppendEventFilterDocumentation',
				'callback' 	=> 'addFilterDocumentationToEvent',
			),					
			array(
				'page' 		=> '/blueprints/events/edit/',
				'delegate' 	=> 'AppendEventFilterDocumentation',
				'callback' 	=> 'addFilterDocumentationToEvent',
			),
			array(
				'page' 		=> '/frontend/',
				'delegate' 	=> 'EventPreSaveFilter',
				'callback' 	=> 'processEventData',
			),
			array(
				'page' 		=> '/system/preferences/',
				'delegate' 	=> 'AddCustomPreferenceFieldsets',
				'callback' 	=> 'appendPreferences',
			),
		);
	}
	
	/**
	 * Add the event filter to the list for creating events.
	 *
	 * @param array $context
	 * @return void
	 **/
	public function addFilterToEventEditor($context) {
		$context['options'][] = array(
			'formvalidation',
			@in_array('formvalidation', $context['selected']) ,
			'Form Validation',
		);
	}
	
	/**
	 * Add documentation for the filter to the event page.
	 *
	 * @param array $context
	 * @return array
	 **/
	public function addFilterDocumentationToEvent($context) {
		if (!in_array('formvalidation', $context['selected'])) {
			return;
		}
		
		$context['documentation'][] = new XMLElement('h3', 'Form Validation');
		$context['documentation'][] = new XMLElement('p', 'This filter gives you the possibility to add form validation based on regular expressions to your forms.');
	}
	
	/**
	 * Process the data from a form when the event is called.
	 *
	 * @param array $context
	 * @return array
	 **/
	public function processEventData($context) {
		// Check whether something should be done at all.
		if (!in_array('formvalidation', $context['event']->eParamFILTERS)) {
			return;
		}
		
		// Fetch data for this filter from the form ...
		$mapping = $_POST['formvalidation'];
		
		// ... and check it for completeness.
		if (!isset($mapping['formname'])) {
			$context['messages'][] = array(
				'formvalidation',
				false,
				'The name of the form validation ruleset must be given.',
			);
			return;
		}
		
		// Load the specified ruleset.
		$ruleset = $this->fetchRuleset($mapping['formname']);
		
		// Continue only if a ruleset was found.
		if (is_array($ruleset) && !empty($ruleset)) {
			// Do the validation using the loaded rules.
			$errors = validateFields($context['fields'], $ruleset);
			$result = empty($errors);
		}
		else {
			// Validation impossible and thus failed.
			$result = false;
		}
		
		// Return the result.
		$context['messages'][] = array(
			'formvalidation',
			$result,
			(!$result ? 'Errors detected in the form.' : NULL),
		);
	}
	
	/**
	 * Fetch the ruleset stored in the specified record in the section and field as set in the preferences.
	 *
	 * @param string $entryId
	 * @return array
	 */
	protected function fetchRuleset($entryId) {
		// Initialize a SectionManager and fetch the section ID.
		$sectionManager = new SectionManager($this->_Parent);
		$sectionId = $sectionManager->fetchIDFromHandle($this->getSection());

		// Initialize an EntryManager and fetch the entry.
		$entryManager = new EntryManager($this->_Parent);
		$entries = $entryManager->fetch($entryId, $sectionId);
		
		// Check the result.
		if (!is_array($entries) || empty($entries)) {
			return false;
		}
		
		// Initialize a FieldManager and fetch the ruleset.
		$fieldManager = new FieldManager($this->_Parent);
		$fieldId = $fieldManager->fetchFieldIDFromElementName($this->getField());
		$fieldData = $entries[0]->getData($fieldId);
		$field = $fieldData['value'];
		
		// Split the ruleset by newlines into an array.
		$field = preg_replace('/\r\n/', '\n', $field);
		$field = preg_replace('/\r/', '\n', $field);
		return explode('\n', $field);
	}	
	
	/**
	 * Add a new preferences field for the name of the section where form valiation rulesets are stored.
	 *
	 * @param array $context
	 * @return void
	 */
	public function appendPreferences($context) {
		// Initialize the group.
		$group = new XMLElement('fieldset');
		$group->setAttribute('class', 'settings');
		$group->appendChild(new XMLElement('legend', 'Form Validation'));

		// Add the field for the section name.
		$label = Widget::Label('Section Name');
		$label->appendChild(Widget::Input('settings[formvalidation][section]', General::Sanitize($this->getSection())));		
		$group->appendChild($label);
		
		// Add the field for the field name.
		$label = Widget::Label('Field Name');
		$label->appendChild(Widget::Input('settings[formvalidation][field]', General::Sanitize($this->getField())));		
		$group->appendChild($label);
		
		// Append everything to the wrapper.
		$context['wrapper']->appendChild($group);
	}

	/**
	 * Get the preference setting for the section name where form validation rulesets are stored in.
	 *
	 * @return string
	 */
	public function getSection() {
		if (class_exists('ConfigurationAccessor')) {
			return ConfigurationAccessor::get('section', 'formvalidation');
		}	
		return $this->_Parent->Configuration->get('section', 'formvalidation');
	}

	/**
	 * Get the preference setting for the name of the field inside the section where form validation rulesets are stored in.
	 *
	 * @return string
	 */
	public function getField() {
		if (class_exists('ConfigurationAccessor')) {
			return ConfigurationAccessor::get('field', 'formvalidation');
		}	
		return $this->_Parent->Configuration->get('field', 'formvalidation');
	}
	
	/**
	 * Do some uninstall work by removing the set preferences.
	 *
	 * @return void
	 */
	public function uninstall() {
		if (class_exists('ConfigurationAccessor')) {
			ConfigurationAccessor::remove('formvalidation');
		}
		else {
			$this->_Parent->Configuration->remove('formvalidation');
		}		
		$this->_Parent->saveConfig();
	}
}