<?php
/**
 *
 *
 * @author Ashley Schroder (aschroder.com)
 * @copyright  Copyright (c) 2010 Ashley Schroder
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Sz_EmailLog_Model_Mysql4_Email_Log extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Resource model initialization
     */
    protected function _construct()
    {
        $this->_init('emaillog/email_log', 'email_id');
    }
}