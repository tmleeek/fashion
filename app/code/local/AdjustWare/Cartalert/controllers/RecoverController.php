<?php
/**
 * Product:     Abandoned Carts Alerts Pro for 1.3.x-1.7.0.0 - 22/05/12
 * Package:     AdjustWare_Cartalert_3.0.7_0.2.3_397295
 * Purchase ID: z5YXD3ukNeCswAIzzfleVbg4dF1tD99hcVluCaOu0k
 * Generated:   2012-10-10 10:37:37
 * File path:   app/code/local/AdjustWare/Cartalert/controllers/RecoverController.php
 * Copyright:   (c) 2012 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'AdjustWare_Cartalert')){ pPpUpjwIkDDwTkmY('ad658350bbd2813c585b5fb83842c2ad'); ?><?php
class AdjustWare_Cartalert_RecoverController extends Mage_Core_Controller_Front_Action
{
    public function cartAction()
    {
        $code = (string) $this->getRequest()->getParam('code');
        $id   = (int) $this->getRequest()->getParam('id');
        
        $history = Mage::getModel('adjcartalert/history')->load($id);
        if (!$history->getId() || $history->getRecoverCode() != $code){
            $this->_redirect('/');
            return;
        }
        
        $s = Mage::getSingleton('customer/session');
        if ($s->isLoggedIn()){
            if ($history->getCustomerId() == $s->getCustomerId()){
                $this->redirectToCart($history);
                return;
            }
            else 
                $s->logout();
        }
        // customer. login
        if ($history->getCustomerId()){
            $customer = Mage::getModel('customer/customer')->load($history->getCustomerId());
            if ($customer->getId())
                $s->setCustomerAsLoggedIn($customer);
        }
        elseif ($history->getQuoteId()){
            //visitor. restore quote in the session
            $quote = Mage::getModel('sales/quote')->load($history->getQuoteId());
            if ($quote){
                Mage::getSingleton('checkout/session')->replaceQuote($quote); 
            }
            
        }
        
        $this->redirectToCart($history);
    }
    
    // added in 1.2.1
    private function redirectToCart($history){
        if (!is_null($history)){
            $history->setRecoveredAt(now());
            if (isset($_SERVER['REMOTE_ADDR'])) {
                $history->setRecoveredFrom($_SERVER['REMOTE_ADDR']);
            } 
            
            if (Mage::getStoreConfig('catalog/adjcartalert/stop_after_visit')){
                $cartalert = Mage::getResourceModel('adjcartalert/cartalert')
                    ->cancelAlertsFor($history->getCustomerEmail());
            }
            
            $history->save();
        } 

        $this->_redirect('checkout/cart');
    } 
    

} } 