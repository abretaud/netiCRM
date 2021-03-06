<?php
/**
 * This
 * contacts.
 */
class CRM_Contact_Form_Task_AnnualReceipt extends CRM_Contact_Form_Task {

  /**
   * Are we operating in "single mode", i.e. updating the task of only
   * one specific contribution?
   *
   * @var boolean
   */

  protected $_tmpreceipt = NULL;

  protected $_year = NULL;

  /**
   * build all the data structures needed to build the form
   *
   * @return void
   * @access public
   */
  function preProcess() {
    $cid = CRM_Utils_Request::retrieve('cid', 'Positive', $this, FALSE);
    if ($cid) {
      $this->_contactIds = array($cid);
    }
    else {
      parent::preProcess();
      $session = CRM_Core_Session::singleton();
      $year = $session->get('year', 'AnnualReceipt');
      if(!empty($year)){
        $this->_year = $year;
      }
    }

    // this session comes from custom search
    CRM_Utils_System::appendBreadCrumb($breadCrumb);
    CRM_Utils_System::setTitle(ts('Print Annual Receipt'));
  }

  /**
   * Build the form
   *
   * @access public
   *
   * @return void
   */
  public function buildQuickForm() {
    // make receipt target popup new tab
    $this->updateAttributes(array('target' => '_blank'));

    $years = array();
    if(!empty($this->_year)){
      $years[$this->_year] = $this->_year;
      $ele = $this->addElement('select', 'year', ts('Receipt Year'), $years);
    }
    else{
      for($year = date('Y'); $year < date('Y') + 10; $year++) {
        $years[$year - 9] = $year - 9;
      }
      $this->addElement('select', 'year', ts('Receipt Year'), $years);
    }

    $contribution_type = CRM_Contribute_PseudoConstant::contributionType(NULL, 'is_deductible', TRUE);
    $deductible = array( 0 => '- '.ts('All').' '.ts('Deductible').' -');
    $contribution_type = $deductible + $contribution_type;
    $attrs = array('multiple' => 'multiple');
    $this->addElement('select', 'contribution_type_id', ts('Contribution Type'), $contribution_type, $attrs);

    $contribution_type = CRM_Contribute_PseudoConstant::contributionType();
    $is_recur = array(
      '' => '- '.ts('All').' -' ,
      -1 => ts('Non-Recurring Contribution'),
      1 => ts('Recurring Contribution'),
    );
    $this->addElement('select', 'is_recur', ts('Find Recurring Contributions?'), $is_recur);

    $this->addButtons(array(
        array(
          'type' => 'next',
          'name' => ts('Download Receipt(s)'),
          'isDefault' => TRUE,
        ),
      )
    );
  }

  function setDefaultValues() {
    $defaults = array();
    $defaults['year'] = date('m') == '12' ? date('Y') : date('Y') - 1;
    return $defaults;
  }

  /**
   * process the form after the input has been submitted and validated
   *
   * @access public
   *
   * @return None
   */
  public function postProcess() {
    $params = $this->controller->exportValues($this->_name);
    set_time_limit(1800);
    if(!empty($params['year'])){
      $session = CRM_Core_Session::singleton();
      $session->resetScope('AnnualReceipt');
      $this->_year = $params['year'];

      $this->option = array();
      foreach($params as $k => $p){
        if($k != 'qfKey' && !empty($p)){
          $this->option[$k] = $p;
        }
      }
      CRM_Utils_Hook::postProcess(get_class($this), $this);
      self::makeReceipt($this->_contactIds, $this->option);
      self::makePDF();
    }
    CRM_Utils_System::civiExit();
  }

  public function pushFile($html) {
    // tmp directory
    file_put_contents($this->_tmpreceipt, $html, FILE_APPEND);
  }
  public function popFile() {
    $return = file_get_contents($this->_tmpreceipt);
    unlink($this->_tmpreceipt);
    return $return;
  }

  public function makePDF($download = TRUE) {
    $template = &CRM_Core_Smarty::singleton();
    $pages = self::popFile();
    $template->assign('pages', $pages);
    $pages = $template->fetch('CRM/common/AnnualReceipt.tpl');
    $filename = 'AnnualReceipt'.$this->_year.'.pdf';
    $pdf_real_filename = CRM_Utils_PDF_Utils::html2pdf($pages, $filename, 'portrait', 'a4', $download);
    if(!$download){
      return $pdf_real_filename;
    }
  }

  public function makeReceipt($contactIds, $option) {
    $this->_tmpreceipt = tempnam('/tmp', 'receiptyear');
    $count = 0;

    foreach ($contactIds as $contact_id){
      $template = &CRM_Core_Smarty::singleton();
      if ($count) {
        $html = '<div class="page-break" style="page-break-after: always;"></div>';
      }
      $html .= CRM_Contribute_BAO_Contribution::getAnnualReceipt($contact_id, $option, $template);
      self::pushFile($html);

      // reset template values before processing next transactions
      $template->clearTemplateVars();
      $count++;
      unset($html);
    }
  }
}

