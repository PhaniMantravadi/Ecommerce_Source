<?php

class Magestore_Sociallogin_YaloginController extends Mage_Core_Controller_Front_Action{
	
	// url to login
    public function loginAction() {
		if(!Mage::helper('magenotification')->checkLicenseKeyFrontController($this)){return;}
		$yalogin = Mage::getModel('sociallogin/yalogin');
		$hasSession = $yalogin->hasSession();
		if($hasSession == FALSE) {
			$authUrl = $yalogin->getAuthUrl();			
			$this->_redirectUrl($authUrl);
		}else{
			$session = $yalogin->getSession();
			$userSession = $session->getSessionedUser();
			$profile = $userSession->loadProfile();
			$emails = $profile->emails;
			$user = array();
			foreach($emails as $email){
				if($email->primary == 1)
					$user['email'] = $email->handle;
			}
			$user['firstname'] = $profile->givenName;
			$user['lastname'] = $profile->familyName;
			
			//get website_id and sote_id of each stores
			$store_id = Mage::app()->getStore()->getStoreId();
			$website_id = Mage::app()->getStore()->getWebsiteId();
			
			$customer = Mage::helper('sociallogin')->getCustomerByEmail($user['email'], $website_id);
			if(!$customer || !$customer->getId()){
				//Login multisite
				$customer = Mage::helper('sociallogin')->createCustomerMultiWebsite($user, $website_id, $store_id );
				if (Mage::getStoreConfig('sociallogin/yalogin/is_send_password_to_customer')){
					$customer->sendPasswordReminderEmail();
				}
			}
				// fix confirmation
			if ($customer->getConfirmation())
			{
				try {
					$customer->setConfirmation(null);
					$customer->save();
				}catch (Exception $e) {
				}
	  		}
			Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
			die("<script type=\"text/javascript\">if(navigator.userAgent.match('CriOS')){window.location.href=\"".$this->_loginPostRedirect()."\";}else{try{window.opener.location.href=\"".$this->_loginPostRedirect()."\";}catch(e){window.opener.location.reload(true);} window.close();}</script>");
			//$this->_redirectUrl(Mage::helper('customer')->getDashboardUrl());
		}
		
    }
	protected function _loginPostRedirect()
    {
//        $session = Mage::getSingleton('customer/session');
//
//        if (!$session->getBeforeAuthUrl() || $session->getBeforeAuthUrl() == Mage::app()->getStore()->getBaseUrl()) {
//            // Set default URL to redirect customer to
//            $session->setBeforeAuthUrl(Mage::helper('customer')->getDashboardUrl());
//            
//        } else if ($session->getBeforeAuthUrl() == Mage::helper('customer')->getLogoutUrl()) {
//            $session->setBeforeAuthUrl(Mage::helper('customer')->getDashboardUrl());
//        } else {
//            if (!$session->getAfterAuthUrl()) {
//                $session->setAfterAuthUrl($session->getBeforeAuthUrl());
//            }
//            if ($session->isLoggedIn()) {
//                $session->setBeforeAuthUrl($session->getAfterAuthUrl(true));
//            }
//        }
//		
//        return $session->getBeforeAuthUrl(true);
            $selecturl= Mage::getStoreConfig(('sociallogin/general/select_url'),Mage::app()->getStore()->getId());
	if($selecturl==0) return Mage::getUrl('customer/account');
	if($selecturl==1) return Mage::getUrl('checkout/cart');
	if($selecturl==2) return Mage::getUrl();
	if($selecturl==3) return $_SESSION["socialogin_currentpage"];
	if($selecturl==4) return Mage::getStoreConfig(('sociallogin/general/custom_page'),Mage::app()->getStore()->getId());
    }
	
	public function testAction(){
		
	}
}