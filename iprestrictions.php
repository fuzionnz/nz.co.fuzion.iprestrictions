<?php

require_once 'iprestrictions.civix.php';
use CRM_Iprestrictions_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function iprestrictions_civicrm_config(&$config) {
  _iprestrictions_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function iprestrictions_civicrm_xmlMenu(&$files) {
  _iprestrictions_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function iprestrictions_civicrm_install() {
  CRM_Core_DAO::executeQuery("CREATE TABLE `civicrm_ip_tracker` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `entity_id` int(11) DEFAULT NULL,
    `entity_name` varchar(255) DEFAULT NULL,
    `ip_address` varchar(255) DEFAULT NULL,
    `counter` int(11) DEFAULT NULL,
    `last_submitted` timestamp NULL DEFAULT NULL,
    `modified_date` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
  _iprestrictions_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function iprestrictions_civicrm_postInstall() {
  _iprestrictions_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function iprestrictions_civicrm_uninstall() {
  _iprestrictions_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function iprestrictions_civicrm_enable() {
  _iprestrictions_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function iprestrictions_civicrm_disable() {
  _iprestrictions_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function iprestrictions_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _iprestrictions_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function iprestrictions_civicrm_managed(&$entities) {
  _iprestrictions_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function iprestrictions_civicrm_caseTypes(&$caseTypes) {
  _iprestrictions_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function iprestrictions_civicrm_angularModules(&$angularModules) {
  _iprestrictions_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function iprestrictions_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _iprestrictions_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function iprestrictions_civicrm_entityTypes(&$entityTypes) {
  _iprestrictions_civix_civicrm_entityTypes($entityTypes);
}

function iprestrictions_civicrm_cron($jobManager) {
  $interval = Civi::settings()->get('block_interval');
  if ($interval) {
    CRM_Core_DAO::executeQuery("DELETE FROM civicrm_ip_tracker
    WHERE modified_date < DATE_SUB(NOW(), INTERVAL {$interval} MINUTE)");
  }
}

function iprestrictions_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  $blockInterval = Civi::settings()->get('block_interval');
  $maxTrials = Civi::settings()->get('no_of_trials');
  $maxTrialsInterval = Civi::settings()->get('time_for_trials');
  if (empty($blockInterval) || empty($maxTrials) || empty($maxTrialsInterval)) {
    return;
  }
  if ($formName == 'CRM_Contribute_Form_Contribution_Main') {
    CRM_Core_DAO::executeQuery("DELETE FROM civicrm_ip_tracker
    WHERE modified_date < DATE_SUB(NOW(), INTERVAL {$blockInterval} MINUTE)");

    $currentTime = date('YmdHis');
    $pageId = $form->getVar( '_id' );
    $ipAddress = CRM_Utils_System::ipAddress();
    $query = "SELECT counter, last_submitted
    FROM civicrm_ip_tracker
    WHERE ip_address = '{$ipAddress}'
      AND entity_name = 'civicrm_contribution_page'
      AND entity_id = {$pageId}
      AND last_submitted > DATE_SUB(NOW(), INTERVAL {$maxTrialsInterval} MINUTE)";

    $dao = CRM_Core_DAO::executeQuery($query);
    if ($dao->fetch()) {
      if ($dao->counter >= $maxTrials) {
        $errors[] = $errorMsg = ts('You have exceeded the maximum number of payment attempts. Please wait a minute and try again.');
        CRM_Core_Session::setStatus($errorMsg, '', 'error');
        return;
      }
      $dao->counter += 1;
      CRM_Core_DAO::executeQuery("UPDATE civicrm_ip_tracker
      SET counter = {$dao->counter}, modified_date = {$currentTime}
      WHERE ip_address = '{$ipAddress}'");
    }
    elseif ($dao->N == 0) {
      CRM_Core_DAO::executeQuery("INSERT INTO civicrm_ip_tracker(entity_id, entity_name, ip_address, counter, last_submitted, modified_date)
        VALUES ({$pageId}, 'civicrm_contribution_page', '{$ipAddress}', 1, '{$currentTime}', '{$currentTime}');
      ");
    }
  }
  elseif ($formName == 'CRM_Contribute_Form_Contribution_Confirm') {
    $pageId = $form->getVar( '_id' );
    $currentTime = date('YmdHis');
    $ipAddress = CRM_Utils_System::ipAddress();
    $query = "SELECT counter
    FROM civicrm_ip_tracker
    WHERE ip_address = '{$ipAddress}'
    AND entity_name = 'civicrm_contribution_page'
    AND entity_id = {$pageId}
    AND last_submitted > DATE_SUB(NOW(), INTERVAL {$maxTrialsInterval} MINUTE)";

    $dao = CRM_Core_DAO::executeQuery($query);
    if ($dao->fetch()) {
      if ($dao->counter >= $maxTrials) {
        $errors[] = $errorMsg = ts('You have exceeded the maximum number of payment attempts. Please wait a minute and try again.');
        CRM_Core_Session::setStatus($errorMsg, '', 'error');
        return;
      }
      $dao->counter += 1;
      CRM_Core_DAO::executeQuery("UPDATE civicrm_ip_tracker
      SET counter = {$dao->counter}, modified_date = {$currentTime}
      WHERE ip_address = '{$ipAddress}'");
    }
  }
  return;
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function iprestrictions_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function iprestrictions_civicrm_navigationMenu(&$menu) {
  _iprestrictions_civix_insert_navigation_menu($menu, 'Mailings', array(
    'label' => E::ts('New subliminal message'),
    'name' => 'mailing_subliminal_message',
    'url' => 'civicrm/mailing/subliminal',
    'permission' => 'access CiviMail',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _iprestrictions_civix_navigationMenu($menu);
} // */
