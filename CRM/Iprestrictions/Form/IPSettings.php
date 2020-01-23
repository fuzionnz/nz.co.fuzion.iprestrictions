<?php

use CRM_Iprestrictions_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_Iprestrictions_Form_IPSettings extends CRM_Core_Form {

   /**
   * Set default values for the form.
   */
  public function setDefaultValues() {
    $this->_defaults = [
      'no_of_trials' => Civi::settings()->get('no_of_trials'),
      'time_for_trials' => Civi::settings()->get('time_for_trials'),
      'block_interval' => Civi::settings()->get('block_interval'),
    ];
    return $this->_defaults;
  }

  public function buildQuickForm() {

    // add form elements
    $this->add('text', 'no_of_trials', ts('No of trials required to blacklist an IP'), [], TRUE);
    $this->add('text', 'time_for_trials', ts('Time under which the above trials are made(in minutes)'), [], TRUE);
    $this->add('text', 'block_interval', ts('Time interval for which the IP address should be blacklisted(in minutes)'), [], TRUE);
    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Submit'),
        'isDefault' => TRUE,
      ),
    ));

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  public function postProcess() {
    $values = $this->exportValues();
    Civi::settings()->set('no_of_trials', $values['no_of_trials']);
    Civi::settings()->set('time_for_trials', $values['time_for_trials']);
    Civi::settings()->set('block_interval', $values['block_interval']);
    parent::postProcess();
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

}
