<?php

use \Magento\Framework\App\ObjectManager;

class ResolverCommonAccount extends Resolver
{
    public function login($args)
    {
        // $objectManager =ObjectManager::getInstance();

        // $customerModel = $objectManager->get('\Magento\Customer\Model\Customer');
        // $customerSession = $objectManager->get('\Magento\Customer\Model\Session');
        // $customerModel->setWebsiteId($this->store->getWebsiteId());
        // $customer = $customerModel->loadByEmail($args["email"]);

        // if ($customer->validatePassword($args["password"])) {
        //     $customerSession->setCustomerAsLoggedIn($customer);

        //     return $this->get($customer->getId());
        // } else {
        //     throw new Exception('Warning: No match for E-Mail Address and/or Password.');
        // }
    }

    public function logout($args)
    {
        // $objectManager =ObjectManager::getInstance();
        // $customerSession = $objectManager->get('\Magento\Customer\Model\Session');

        // $customerSession->logout();

        // return array(
        //     'status' => true
        // );
    }

    public function register($args)
    {
        // $customer = $args['customer'];

        // $objectManager =ObjectManager::getInstance();
        // try {
        //     $customerFactory = $objectManager->get('\Magento\Customer\Model\CustomerFactory');
        //     $newCustomer = $customerFactory->create();

        //     $newCustomer->setWebsiteId($this->store->getWebsiteId());
        //     $newCustomer->setEmail($customer['email']);
        //     $newCustomer->setFirstname($customer['firstName']);
        //     $newCustomer->setLastname($customer['lastName']);
        //     $newCustomer->setPassword($customer['password']);
        //     $newCustomer->save();

        //     return $this->get($newCustomer->getId());
        // } catch (Exception $e) {
        //     throw new Exception($e->getMessage());
        // }
    }

    public function edit($args)
    {
        // $customerData = $args['customer'];

        // $objectManager =ObjectManager::getInstance();

        // $customerSession = $objectManager->get('\Magento\Customer\Model\Session');

        // $customer = $customerSession->getCustomer();

        // $customer->setEmail($customerData['email']);
        // $customer->setFirstname($customerData['firstName']);
        // $customer->setLastname($customerData['lastName']);

        // $customer->save();

        // return $this->get($customer->getId());
    }

    public function editPassword($args)
    {
        // $objectManager =ObjectManager::getInstance();

        // $customerSession = $objectManager->get('\Magento\Customer\Model\Session');

        // $customer = $customerSession->getCustomer();

        // $customer->setPassword($args['password']);

        // $customer->save();

        // return $this->get($customer->getId());
    }

    public function get($user_id)
    {
        // $objectManager =ObjectManager::getInstance();

        // $customerFactory = $objectManager->get('\Magento\Customer\Api\CustomerRepositoryInterfaceFactory');
        // $customerRepository = $customerFactory->create();
        // $customer = $customerRepository->getById($user_id);

        // return array(
        //     'id' => $customer->getId(),
        //     'email' => $customer->getEmail(),
        //     'firstName' => $customer->getFirstname(),
        //     'lastName' => $customer->getLastname(),
        // );
    }

    public function isLogged($args)
    {
        // $objectManager =ObjectManager::getInstance();

        // $customerSession = $objectManager->get('\Magento\Customer\Model\Session');

        // $customer = array();

        // if ($customerSession->isLoggedIn()) {
        //     $customer = $this->get($customerSession->getCustomerId());
        // }

        return array(
            'status' => false
            // 'customer' => $customer
        );
    }

    public function address($args)
    {
        // $this->load->model('common/address');

        // $result = $this->model_common_address->getAddress($args['id']);

        // return array(
        //     'id' => $args['id'],
        //     'firstName' => $result['firstname'],
        //     'lastName' => $result['lastname'],
        //     'company' => $result['company'],
        //     'address1' => $result['street'],
        //     'address2' => '',
        //     'zoneId' => $result['region_id'],
        //     'zone' => $this->load->resolver('common/zone/get', array(
        //         'id' => $result['region_id']
        //     )),
        //     'country' => $this->load->resolver('common/country/get', array(
        //         'id' => $result['country_id']
        //     )),
        //     'countryId' => $result['country_id'],
        //     'city' => $result['city'],
        //     'zipcode' => $result['postcode']
        // );
    }

    public function addressList($args)
    {
        // $this->load->model('common/address');

        // $objectManager =ObjectManager::getInstance();

        // $customerSession = $objectManager->get('\Magento\Customer\Model\Session');

        // $customer = $customerSession->getCustomer();

        // $address = array();

        // $result = $this->model_common_address->getAddresses($customer->getId());

        // foreach ($result as $value) {
        //     $address[] = $this->address(array('id' => $value['address_id']));
        // }

        // return $address;
    }

    public function editAddress($args)
    {
        // $this->load->model('common/address');
        // $this->model_common_address->editAddress($args['id'], $args['address']);

        // return $this->address($args);
    }

    public function addAddress($args)
    {
        // $objectManager = ObjectManager::getInstance();

        // $customerSession = $objectManager->get('\Magento\Customer\Model\Session');

        // $this->load->model('common/address');
        // $address_id = $this->model_common_address->addAddress($customerSession->getCustomerId(), $args['address']);

        // return $this->address(array('id' => $address_id));
    }

    public function removeAddress($args)
    {
        // $objectManager = ObjectManager::getInstance();

        // $addressRepository = $objectManager->get('\Magento\Customer\Api\AddressRepositoryInterface');
        // $addressRepository->deleteById($args['id']);

        // return $this->addressList($args);
    }
}
