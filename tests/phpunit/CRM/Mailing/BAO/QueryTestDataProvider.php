<?php  // vim: set si ai expandtab tabstop=4 shiftwidth=4 softtabstop=4:

/**
 *  Provide data to the CRM_Mailing_BAO_QueryTest class
 *
 *  @package CiviCRM
 */
class CRM_Mailing_BAO_QueryTestDataProvider implements Iterator
{
    /**
     *  @var integer
     */
    private $i = 0;

    /**
     *  @var mixed[]
     *  This dataset describes various form values and what contact
     *  IDs should be selected when the form values are applied to the
     *  database in dataset.xml
     */
    private $dataset = array(
              array( 'fv' => array( 'mailing_name' => 'First%', 'mailing_open_status' => 'Y' ),
                     'id' => array( 109, 110, 111, 112 ),
                     ),
              array( 'fv' => array( 'mailing_name' => 'First%', 'mailing_open_status' => 'N' ),
                     'id' => array( 102, 103, 104, 105, 108 ),
                     ),
              array( 'fv' => array( 'mailing_name' => 'First%', 'mailing_delivery_status' /*bounce*/ => 'Y' ),
                     'id' => array( 105 ),
                     ),
              array( 'fv' => array( 'mailing_name' => 'First%', 'mailing_delivery_status' /*bounce*/ => 'N' ),
                     'id' => array( 102, 103, 104, 108, 109, 110, 111, 112 ),
                     ),
              array( 'fv' => array( 'mailing_name' => 'First%', 'mailing_reply_status' => 'Y' ),
                     'id' => array( 103, 108, 110, 112 ),
                     ),
              array( 'fv' => array( 'mailing_name' => 'First%', 'mailing_reply_status' => 'N' ),
                     'id' => array( 102, 104, 105, 109, 111 ),
                     ),
              array( 'fv' => array( 'mailing_name' => 'First%', 'mailing_click_status' => 'Y' ),
                     'id' => array( 104, 108, 111, 112 ),
                     ),
              array( 'fv' => array( 'mailing_name' => 'First%', 'mailing_click_status' => 'N' ),
                     'id' => array( 102, 103, 105, 109, 110 ),
                     ),
              array( 'fv' => array( 'mailing_name' => 'Second%', 'mailing_delivery_status' /*bounce*/ => 'Y' ),
                     'id' => array( ),
                     ),
              array( 'fv' => array( 'mailing_name' => 'Second%', 'mailing_delivery_status' /*bounce*/ => 'N' ),
                     'id' => array( 102, 103, 104, 108, 109, 110, 111, 112 ),
                     ),
              array( 'fv' => array( 'mailing_name' => 'Second%', 'mailing_reply_status' => 'Y' ),
                     'id' => array( 103 ),
                     ),
              array( 'fv' => array( 'mailing_name' => 'Second%', 'mailing_click_status' => 'Y' ),
                     'id' => array( 104 ),
                     ),
              array( 'fv' => array( 'mailing_name' => 'Second%', 'mailing_click_status' => 'N' ),
                     'id' => array( 102, 103, 108, 109, 110, 111, 112 ),
                     ),
              array( 'fv' => array( 'mailing_date_high' => '2011-05-25', 'mailing_open_status' => 'Y' ),
                     'id' => array( 109, 110, 111, 112 ),
                     ),
              array( 'fv' => array( 'mailing_date_high' => '2011-05-25', 'mailing_open_status' => 'N' ),
                     'id' => array( 102, 103, 104, 105, 108 ),
                     ),
              array( 'fv' => array( 'mailing_date_low' => '2011-05-26', 'mailing_open_status' => 'Y' ),
                     'id' => array( 102 ),
                     ),
              array( 'fv' => array( 'mailing_date_low' => '2011-05-26', 'mailing_open_status' => 'N' ),
                     'id' => array( 103, 104, 108, 109, 110, 111, 112 ),
                     ),
//              array( 'fv' => array( ),
//                     'id' => array( ),
//                     ),
            );

    public function _construct( )
    {
        $this->i = 0;
    }

    public function rewind( )
    {
        $this->i = 0;
    }

    public function current( )
    {
        $count = count( $this->dataset[$this->i]['id'] );
        $ids   = $this->dataset[$this->i]['id'];
        $full  = array();
        foreach( $this->dataset[$this->i]['id'] as $key => $value ) {
            $full[] = array( 'contact_id' => $value,
                             'contact_type' => 'Individual',
                             'sort_name'    => "Test Contact $value"
                             );
        }
        return array( $this->dataset[$this->i]['fv'], $count, $ids, $full );
    }

    public function key( )
    {
        return $this->i;
    }

    public function next( )
    {
        $this->i++;
    }

    public function valid( )
    {
        return isset($this->dataset[$this->i]);
    }

} // class CRM_Mailing_BAO_QueryTestDataProvider

// -- set Emacs parameters --
// Local variables:
// mode: php;
// tab-width: 4
// c-basic-offset: 4
// c-hanging-comment-ender-p: nil
// indent-tabs-mode: nil
// End:
