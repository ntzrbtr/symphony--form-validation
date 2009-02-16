<?php

/**
 * Form validation extension class
 *
 * @package formvalidation
 * @author Thomas Off
 **/
class extension_formvalidation extends Extension {

	/**
	 * Set array containing all the data for the 'About' page in the extension administration
	 *
	 * @return void
	 * @author Thomas Off
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
	 * @author Thomas Off
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
		);
	}
	
	/**
	 * Add the event filter to the list for creating events.
	 *
	 * @param array $context
	 * @return void
	 * @author Thomas Off
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
	 * @author Thomas Off
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
	 * @author Thomas Off
	 **/
	public function processEventData($context) {
		if (!in_array('formvalidation', $context['event']->eParamFILTERS)) {
			return;
		}
		
		$mapping = $_POST['formvalidation'];
		
		if (!isset($mapping['formname'])) {
			$context['messages'][] = array(
				'formvalidation',
				false,
				'The name of the form validation ruleset must be given.',
			);
			return;
		}
		
		// Do the validation.
		$result = true;
		
		$context['messages'][] = array(
			'formvalidation',
			$result,
			(!$result ? 'Errors detected in the form.' : NULL),
		);
		
	}	
}