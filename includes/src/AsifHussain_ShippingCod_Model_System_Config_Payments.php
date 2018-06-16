<?php
 
class AsifHussain_ShippingCod_Model_System_Config_Payments
{
    public function toOptionArray()
    {
        $payments = Mage::getSingleton('payment/config')->getActiveMethods();
		$methods = array();
		foreach ($payments as $paymentCode=>$paymentModel) {
			if($paymentModel->canUseCheckout()==1) {
				$paymentTitle = Mage::getStoreConfig('payment/'.$paymentCode.'/title');
				$methods[] = array('value'=>$paymentCode,'label'=>$paymentTitle);
			}
		}
		return $methods;
    }
}