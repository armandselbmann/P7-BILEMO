<?php

namespace App\Services;

/**
 * Class who contains methods for update existing objects.
 */
class ExistingObjectConstructor {

    /**
     * Method for update an existing customer user
     *
     * @param $newCustomerUser
     * @param $currentCustomerUser
     * @return mixed
     */
    public function customerUserConstructor($newCustomerUser, $currentCustomerUser): mixed
    {
        if ($newCustomerUser->getLastName()){
            $currentCustomerUser->setLastName($newCustomerUser->getLastName());
        }
        if($newCustomerUser->getFirstName()){
            $currentCustomerUser->setFirstName($newCustomerUser->getFirstName());
        }
        if($newCustomerUser->getEmail()){
            $currentCustomerUser->setEmail($newCustomerUser->getEmail());
        }
        if ($newCustomerUser->getPostalCode()) {
            $currentCustomerUser->setPostalCode($newCustomerUser->getPostalCode());
        }
        if ($newCustomerUser->getAdress()) {
            $currentCustomerUser->setAdress($newCustomerUser->getAdress());
        }
        if ($newCustomerUser->getCity()) {
            $currentCustomerUser->setCity($newCustomerUser->getCity());
        }
        if ($newCustomerUser->getCountry()) {
            $currentCustomerUser->setCountry($newCustomerUser->getCountry());
        }
        if ($newCustomerUser->getPhone()) {
            $currentCustomerUser->setPhone($newCustomerUser->getPhone());
        }
        return $currentCustomerUser;
    }

    /**
     * Method for update an existing customer
     *
     * @param $newCustomer
     * @param $currentCustomer
     * @return mixed
     */
    public function customerConstructor($newCustomer, $currentCustomer): mixed
    {
        if($newCustomer->getCompany()){
            $currentCustomer->setCompany($newCustomer->getCompany());
        }
        if ($newCustomer->getLastName()){
            $currentCustomer->setLastName($newCustomer->getLastName());
        }
        if($newCustomer->getFirstName()){
            $currentCustomer->setFirstName($newCustomer->getFirstName());
        }
        if ($newCustomer->getPostalCode()) {
            $currentCustomer->setPostalCode($newCustomer->getPostalCode());
        }
        if ($newCustomer->getAdress()) {
            $currentCustomer->setAdress($newCustomer->getAdress());
        }
        if ($newCustomer->getCity()) {
            $currentCustomer->setCity($newCustomer->getCity());
        }
        if ($newCustomer->getCountry()) {
            $currentCustomer->setCountry($newCustomer->getCountry());
        }
        if ($newCustomer->getPhone()) {
            $currentCustomer->setPhone($newCustomer->getPhone());
        }
        if ($newCustomer->getTVANumber()) {
            $currentCustomer->setTVANumber($newCustomer->getTVANumber());
        }
        if ($newCustomer->getSIRET()) {
            $currentCustomer->setSIRET($newCustomer->getSIRET());
        }
        return $currentCustomer;
    }

    /**
     * Method for update an existing employee
     *
     * @param $newEmployee
     * @param $currentEmployee
     * @return mixed
     */
    public function employeeConstructor($newEmployee, $currentEmployee): mixed
    {
        if($newEmployee->getFirstName()){
            $currentEmployee->setFirstName($newEmployee->getFirstName());
        }
        if ($newEmployee->getLastName()){
            $currentEmployee->setLastName($newEmployee->getLastName());
        }
        if ($newEmployee->getPhone()) {
            $currentEmployee->setPhone($newEmployee->getPhone());
        }
        return $currentEmployee;
    }

    /**
     * Method for update an existing product
     *
     * @param $newProduct
     * @param $currentProduct
     * @return mixed
     */
    public function productConstructor($newProduct, $currentProduct)
    {
        if($newProduct->getReference()){
            $currentProduct->setReference($newProduct->getReference());
        }
        if($newProduct->getSeries()){
            $currentProduct->setSeries($newProduct->getSeries());
        }
        if($newProduct->getName()){
            $currentProduct->setName($newProduct->getName());
        }
        if($newProduct->getDescription()){
            $currentProduct->setDescription($newProduct->getDescription());
        }
        if($newProduct->getReference()){
            $currentProduct->setReference($newProduct->getReference());
        }
        if($newProduct->getMaker()){
            $currentProduct->setMaker($newProduct->getMaker());
        }
        if($newProduct->getPrice()){
            $currentProduct->setPrice($newProduct->getPrice());
        }
        if($newProduct->getColor()){
            $currentProduct->setColor($newProduct->getColor());
        }
        if($newProduct->getPlatform()){
            $currentProduct->setPlatform($newProduct->getPlatform());
        }
        if($newProduct->getNetwork()){
            $currentProduct->setNetwork($newProduct->getNetwork());
        }
        if($newProduct->getConnector()){
            $currentProduct->setConnector($newProduct->getConnector());
        }
        if($newProduct->getBattery()){
            $currentProduct->setBattery($newProduct->getBattery());
        }
        if($newProduct->getRAM()){
            $currentProduct->setRAM($newProduct->getRAM());
        }
        if($newProduct->getROM()){
            $currentProduct->setROM($newProduct->getROM());
        }
        if($newProduct->getBrandCPU()){
            $currentProduct->setBrandCPU($newProduct->getBrandCPU());
        }
        if($newProduct->getSpeedCPU()){
            $currentProduct->setSpeedCPU($newProduct->getSpeedCPU());
        }
        if($newProduct->getCoresCPU()){
            $currentProduct->setCoresCPU($newProduct->getCoresCPU());
        }
        if($newProduct->getSubCam()){
            $currentProduct->setSubCam($newProduct->getSubCam());
        }
        if($newProduct->getDisplayType()){
            $currentProduct->setDisplayType($newProduct->getDisplayType());
        }
        if($newProduct->getDisplaySize()){
            $currentProduct->setDisplaySize($newProduct->getDisplaySize());
        }
        if($newProduct->isDoubleSIM()){
            $currentProduct->setDoubleSIM($newProduct->isDoubleSIM());
        }
        if($newProduct->isCardReader()){
            $currentProduct->setCardReader($newProduct->isCardReader());
        }
        if($newProduct->isFoldable()){
            $currentProduct->setFoldable($newProduct->isFoldable());
        }
        if($newProduct->isESIM()){
            $currentProduct->setESIM($newProduct->isESIM());
        }
        if($newProduct->getWidth()){
            $currentProduct->setWidth($newProduct->getWidth());
        }
        if($newProduct->getHeight()){
            $currentProduct->setHeight($newProduct->getHeight());
        }
        if($newProduct->getDepth()){
            $currentProduct->setDepth($newProduct->getDepth());
        }
        if($newProduct->getWeight()){
            $currentProduct->setWeight($newProduct->getWeight());
        }
        return $currentProduct;
    }

}