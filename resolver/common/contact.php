<?php
use \Magento\Framework\App\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use \Magento\Framework\App\Area;
use \Magento\Store\Model\Store;

class ResolverCommonContact extends Resolver
{
    private $codename = "d_vuefront";

    public function get()
    {
        $objectManager =ObjectManager::getInstance();
        $scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');

        return array(
            'store' => $scopeConfig->getValue('general/store_information/name', ScopeInterface::SCOPE_STORE),
            'email' => $scopeConfig->getValue('trans_email/ident_general/email', ScopeInterface::SCOPE_STORE),
            'address' => $this->store->getFormattedAddress(),
            'geocode' => '',
            'locations' => array(),
            'telephone' => $scopeConfig->getValue('general/store_information/phone', ScopeInterface::SCOPE_STORE),
            'fax' => '',
            'open' => $scopeConfig->getValue('general/store_information/hours', ScopeInterface::SCOPE_STORE),
            'comment' => ''
        );
    }

    public function send($args)
    {
        $objectManager =ObjectManager::getInstance();
        $inlineTranslation = $objectManager->get('\Magento\Framework\Translate\Inline\StateInterface');
        $transportBuilder = $objectManager->get('\Magento\Framework\Mail\Template\TransportBuilder');
        $scopeConfig = $objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');

        $inlineTranslation->suspend();
        try {
            $sender = [
                'name' => $args['name'],
                'email' => $args['email'],
            ];

            $storeScope = ScopeInterface::SCOPE_STORE;
            $transport = $transportBuilder
            ->setTemplateIdentifier('send_email_email_template')
            ->setTemplateOptions(
                [
                    'area' => Area::AREA_FRONTEND,
                    'store' => Store::DEFAULT_STORE_ID,
                ]
            )
            ->setTemplateVars($args)
            ->setFrom($sender)
            ->addTo($scopeConfig->getValue('trans_email/ident_general/email', $storeScope))
            ->getTransport();

            $transport->sendMessage();
            $inlineTranslation->resume();
        } catch (\Exception $e) {
            $inlineTranslation->resume();
            throw new \Exception($e->getMessage());
        }

        return array(
            "status" => true
        );
    }
}
