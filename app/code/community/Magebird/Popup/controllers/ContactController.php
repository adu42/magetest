<?php class Magebird_Popup_ContactController extends Mage_Core_Controller_Front_Action
{
    const XML_PATH_EMAIL_RECIPIENT = 'contacts/email/recipient_email';
    const XML_PATH_EMAIL_SENDER = 'contacts/email/sender_email_identity';
    const XML_PATH_EMAIL_TEMPLATE = 'contacts/email/email_template';
    const XML_PATH_ENABLED = 'contacts/contacts/enabled';

    public function submitAction()
    {
        $result = array();
        $post = $this->getRequest()->getPost();
        if ($post) {
            $translate = Mage::getSingleton('core/translate');
            $translate->setTranslateInline(false);
            try {
                $object = new Varien_Object();
                $object->setData($post);
                $emailTemplate = Mage::getModel('core/email_template');
                $emailTemplate->setDesignConfig(array('area' => 'frontend'))->setReplyTo($post['email'])->sendTransactional(Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE), Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER), Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT), null, array('data' => $object));
                if (!$emailTemplate->getSentSuccess()) {
                    throw new Exception();
                }
                $result = json_encode(array('success' => 'success', 'coupon' => ''));
                $this->getResponse()->setBody($result);
                return;
            } catch (Exception $e) {
                $result['exceptions'][] = $this->__('Unable to post the review.');
            }
        }
        $result = json_encode($result);
        $this->getResponse()->setBody($result);
    }
} ?>