<?php


/**
 * Drupal specific stuff goes here
 */
class CRM_Utils_System_Drupal6 {

  /**
   * Check if username and email exists in the drupal db
   *
   * @params $params    array   array of name and mail values
   * @params $emailName string  field label for the 'email'
   *
   * @return void
   */
  function checkUserNameEmailExists($params, $emailName = 'email') {
    $config = CRM_Core_Config::singleton();
    $errors = array();

    $dao   = new CRM_Core_DAO();
    $name  = $dao->escape(CRM_Utils_Array::value('name', $params));
    $email = $dao->escape(CRM_Utils_Array::value('mail', $params));
    _user_edit_validate(NULL, $params);
    $errors = form_get_errors();

    if ($errors) {
      if (CRM_Utils_Array::value('name', $errors)) {
        $errors['cms_name'] = $errors['name'];
      }
      if (CRM_Utils_Array::value('mail', $errors)) {
        $errors[$emailName] = $errors['mail'];
      }
      // also unset drupal messages to avoid twice display of errors
      unset($_SESSION['messages']);
    }

    // drupal api sucks do the name check manually
    $nameError = user_validate_name($params['name']);
    if ($nameError) {
      $errors['cms_name'] = $nameError;
    }

    $sql = "
  SELECT name, mail
  FROM {$config->userFrameworkUsersTableName}
  WHERE (LOWER(name) = LOWER('$name')) OR (LOWER(mail) = LOWER('$email'))";


    $db_cms = DB::connect($config->userFrameworkDSN);
    if (DB::isError($db_cms)) {
      die("Cannot connect to UF db via $dsn, " . $db_cms->getMessage());
    }
    $query = $db_cms->query($sql);
    $row = $query->fetchRow();
    if (!empty($row)) {
      $dbName = CRM_Utils_Array::value(0, $row);
      $dbEmail = CRM_Utils_Array::value(1, $row);
      if (strtolower($dbName) == strtolower($name)) {
        $errors['cms_name'] = ts('The username %1 is already taken. Please select another username.',
          array(1 => $name)
        );
      }
      if (strtolower($dbEmail) == strtolower($email)) {
        $errors[$emailName] = ts('This email %1 is already registered. Please select another email.',
          array(1 => $email)
        );
      }
    }
    return $errors;
  }

  /**
   * Function to create a user in Drupal.
   *
   * @param array  $params associated array
   * @param string $mail email id for cms user
   *
   * @return uid if user exists, false otherwise
   *
   * @access public
   */
  function createUser($params, $mail) {
    $form_state = array();
    $form_state['values'] = array(
      'name' => $params['cms_name'],
      'mail' => $params[$mail],
      'op' => 'Create new account',
    );
    if (!variable_get('user_email_verification', TRUE)) {
      $form_state['values']['pass']['pass1'] = $params['cms_pass'];
      $form_state['values']['pass']['pass2'] = $params['cms_pass'];
    }

    $config = CRM_Core_Config::singleton();

    // we also need to redirect b
    $config->inCiviCRM = TRUE;

    $form = drupal_retrieve_form('user_register', $form_state);
    $form['#post'] = $form_state['values'];
    drupal_prepare_form('user_register', $form, $form_state);

    // remove the captcha element from the form prior to processing
    unset($form['captcha']);

    drupal_process_form('user_register', $form, $form_state);

    $config->inCiviCRM = FALSE;

    if (form_get_errors() || !isset($form_state['user'])) {
      return FALSE;
    }

    return $form_state['user']->uid;
    
  }

  /**
   *  Change user name in host CMS
   *  
   *  @param integer $ufID User ID in CMS
   *  @param string $ufName User name
   */
  function updateCMSName($ufID, $ufName) {
    // CRM-5555
    if (function_exists('user_load')) {
      $user = user_load(array('uid' => $ufID));
      if ($user->mail != $ufName) {
        user_save($user, array('mail' => $ufName));
        $user = user_load(array('uid' => $ufID));
      }
    }
  }

  public static function languageNegotiationURL($url, $addLanguagePart = TRUE, $removeLanguagePart = FALSE) {
    if (function_exists('locale')) {
      global $language;

      //get the mode.
      $mode = variable_get('language_negotiation', LANGUAGE_NEGOTIATION_NONE);

      //url prefix / path.
      if (isset($language->prefix) &&
        $language->prefix &&
        in_array($mode, array(
          LANGUAGE_NEGOTIATION_PATH,
            LANGUAGE_NEGOTIATION_PATH_DEFAULT,
          ))
      ) {

        if ($addLanguagePart) {
          $url .= $language->prefix . '/';
        }
        if ($removeLanguagePart) {
          $url = str_replace("/{$language->prefix}/", '/', $url);
        }
      }
      if (isset($language->domain) &&
        $language->domain &&
        $mode == LANGUAGE_NEGOTIATION_DOMAIN
      ) {

        if ($addLanguagePart) {
          $url = CRM_Utils_File::addTrailingSlash($language->domain, '/');
        }
        if ($removeLanguagePart && defined('CIVICRM_UF_BASEURL')) {
          $url = str_replace('\\', '/', $url);
          $parseUrl = parse_url($url);

          //kinda hackish but not sure how to do it right
          //hope http_build_url() will help at some point.
          if (is_array($parseUrl) && !empty($parseUrl)) {
            $urlParts           = explode('/', $url);
            $hostKey            = array_search($parseUrl['host'], $urlParts);
            $ufUrlParts         = parse_url(CIVICRM_UF_BASEURL);
            $urlParts[$hostKey] = $ufUrlParts['host'];
            $url                = implode('/', $urlParts);
          }
        }
      }
    }
    return $url;
  }

}
