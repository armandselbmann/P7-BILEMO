<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\CustomerUser;
use App\Entity\Employee;
use App\Entity\Image;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = new Factory();
        $faker = $faker::create('fr_FR');

        // Apple Product & Image
        for($i = 0; $i < 10; $i++) {
            $product = new Product;
            $product->setReference($faker->numberBetween(1000, 10000000));
            $product->setReleaseDate($faker->dateTimeBetween('-10 years', 'now', 'Europe/Paris'));
            $product->setSeries('A' . $faker->numberBetween(1000,6000));
            $product->setName('iPhone '.$i);
            $product->setDescription($faker->text(80));
            $product->setMaker('Apple');
            $product->setPrice($faker->numberBetween(400, 950));
            $product->setColor($faker->colorName());
            $product->setPlatform('IOS');
            $product->setNetwork($faker->numberBetween(3,5).'G');
            $product->setConnector('USB');
            $product->setBattery($faker->numberBetween(1000, 4500).'mAh');
            $product->setRAM($faker->randomElement(['8Go', '16Go', '32Go', '128Go', '256Go']));
            $product->setROM($faker->randomElement(['8Go', '16Go', '32Go']));
            $product->setBrandCPU('Apple');
            $product->setSpeedCPU($faker->numberBetween(1,3).'Ghz');
            $product->setCoresCPU($faker->numberBetween(1,4));
            $product->setMainCam($faker->numberBetween(3, 20).'MP');
            $product->setSubCam($faker->numberBetween(1,3).'MP');
            $product->setDisplayType($faker->randomElement(['OLED', 'ASV', 'IPS', 'POLED']));
            $product->setDisplaySize($faker->randomFloat(1, 3,6 ));
            $product->setDoubleSIM($faker->boolean);
            $product->setCardReader($faker->boolean);
            $product->setFoldable($faker->boolean);
            $product->setESIM($faker->boolean);
            $product->setWidth($faker->numberBetween(55,90));
            $product->setHeight($faker->numberBetween(100,160));
            $product->setDepth($faker->numberBetween(7,19));
            $product->setWeight($faker->numberBetween(100,220));

            for ($j=0; $j < 3; $j++) {
                $image = new Image();
                $image->setName('imgiPhone'.$i.'_'.$j.'.jpg');
                $image->setProducts($product);
                $manager->persist($image);
            }
            $manager->persist($product);
        }

        // Samsung Product & Image
        for($i = 0; $i < 10; $i++) {
            $product = new Product;
            $product->setReference($faker->numberBetween(1000, 10000000));
            $product->setReleaseDate($faker->dateTimeBetween('-10 years', 'now', 'Europe/Paris'));
            $product->setSeries('S' . $faker->numberBetween(1000,6000));
            $product->setName('Samsung S'.$i);
            $product->setDescription($faker->text(80));
            $product->setMaker('Samsung');
            $product->setPrice($faker->numberBetween(400, 950));
            $product->setColor($faker->colorName());
            $product->setPlatform('Android');
            $product->setNetwork($faker->numberBetween(3,5).'G');
            $product->setConnector('USB');
            $product->setBattery($faker->numberBetween(1000, 4500).'mAh');
            $product->setRAM($faker->randomElement(['8Go', '16Go', '32Go', '128Go', '256Go']));
            $product->setROM($faker->randomElement(['8Go', '16Go', '32Go']));
            $product->setBrandCPU('Snapdragon865');
            $product->setSpeedCPU($faker->numberBetween(1,3).'Ghz');
            $product->setCoresCPU($faker->numberBetween(1,4));
            $product->setMainCam($faker->numberBetween(3, 20).'MP');
            $product->setSubCam($faker->numberBetween(1,3).'MP');
            $product->setDisplayType($faker->randomElement(['OLED', 'ASV', 'IPS', 'POLED']));
            $product->setDisplaySize($faker->randomFloat(1, 3,6 ));
            $product->setDoubleSIM($faker->boolean);
            $product->setCardReader($faker->boolean);
            $product->setFoldable($faker->boolean);
            $product->setESIM($faker->boolean);
            $product->setWidth($faker->numberBetween(55,90));
            $product->setHeight($faker->numberBetween(100,160));
            $product->setDepth($faker->numberBetween(7,19));
            $product->setWeight($faker->numberBetween(100,220));

            for ($j=0; $j < 3; $j++) {
                $image = new Image();
                $image->setName('imgSamsungS'.$i.'_'.$j.'.jpg');
                $image->setProducts($product);
                $manager->persist($image);
            }
            $manager->persist($product);
        }

        // Huawei Product & Image
        for($i = 0; $i < 10; $i++) {
            $product = new Product;
            $product->setReference($faker->numberBetween(1000, 10000000));
            $product->setReleaseDate($faker->dateTimeBetween('-10 years', 'now', 'Europe/Paris'));
            $product->setSeries('H' . $faker->numberBetween(1000,6000));
            $product->setName('Huawei P'.$i);
            $product->setDescription($faker->text(80));
            $product->setMaker('Huawei');
            $product->setPrice($faker->numberBetween(400, 950));
            $product->setColor($faker->colorName());
            $product->setPlatform('Android');
            $product->setNetwork($faker->numberBetween(3,5).'G');
            $product->setConnector('USB');
            $product->setBattery($faker->numberBetween(1000, 4500).'mAh');
            $product->setRAM($faker->randomElement(['8Go', '16Go', '32Go', '128Go', '256Go']));
            $product->setROM($faker->randomElement(['8Go', '16Go', '32Go']));
            $product->setBrandCPU('Kirin990');
            $product->setSpeedCPU($faker->numberBetween(1,3).'Ghz');
            $product->setCoresCPU($faker->numberBetween(1,4));
            $product->setMainCam($faker->numberBetween(3, 20).'MP');
            $product->setSubCam($faker->numberBetween(1,3).'MP');
            $product->setDisplayType($faker->randomElement(['OLED', 'ASV', 'IPS', 'POLED']));
            $product->setDisplaySize($faker->randomFloat(1, 3,6 ));
            $product->setDoubleSIM($faker->boolean);
            $product->setCardReader($faker->boolean);
            $product->setFoldable($faker->boolean);
            $product->setESIM($faker->boolean);
            $product->setWidth($faker->numberBetween(55,90));
            $product->setHeight($faker->numberBetween(100,160));
            $product->setDepth($faker->numberBetween(7,19));
            $product->setWeight($faker->numberBetween(100,220));

            for ($j=0; $j < 3; $j++) {
                $image = new Image();
                $image->setName('imgHuaweiP'.$i.'_'.$j.'.jpg');
                $image->setProducts($product);
                $manager->persist($image);
            }
            $manager->persist($product);
        }

        // Motorola Product & Image
        for($i = 0; $i < 10; $i++) {
            $product = new Product;
            $product->setReference($faker->numberBetween(1000, 10000000));
            $product->setReleaseDate($faker->dateTimeBetween('-10 years', 'now', 'Europe/Paris'));
            $product->setSeries('M' . $faker->numberBetween(1000,6000));
            $product->setName('Moto G'.$i);
            $product->setDescription($faker->text(80));
            $product->setMaker('Motorola');
            $product->setPrice($faker->numberBetween(400, 950));
            $product->setColor($faker->colorName());
            $product->setPlatform('Android');
            $product->setNetwork($faker->numberBetween(3,5).'G');
            $product->setConnector('USB');
            $product->setBattery($faker->numberBetween(1000, 4500).'mAh');
            $product->setRAM($faker->randomElement(['8Go', '16Go', '32Go', '128Go', '256Go']));
            $product->setROM($faker->randomElement(['8Go', '16Go', '32Go']));
            $product->setBrandCPU('MSM8937');
            $product->setSpeedCPU($faker->numberBetween(1,3).'Ghz');
            $product->setCoresCPU($faker->numberBetween(1,4));
            $product->setMainCam($faker->numberBetween(3, 20).'MP');
            $product->setSubCam($faker->numberBetween(1,3).'MP');
            $product->setDisplayType($faker->randomElement(['OLED', 'ASV', 'IPS', 'POLED']));
            $product->setDisplaySize($faker->randomFloat(1, 3,6 ));
            $product->setDoubleSIM($faker->boolean);
            $product->setCardReader($faker->boolean);
            $product->setFoldable($faker->boolean);
            $product->setESIM($faker->boolean);
            $product->setWidth($faker->numberBetween(55,90));
            $product->setHeight($faker->numberBetween(100,160));
            $product->setDepth($faker->numberBetween(7,19));
            $product->setWeight($faker->numberBetween(100,220));

            for ($j=0; $j < 3; $j++) {
                $image = new Image();
                $image->setName('imgMotoG'.$i.'_'.$j.'.jpg');
                $image->setProducts($product);
                $manager->persist($image);
            }
            $manager->persist($product);
        }

        // Customers & CustomerUsers
        for ($i=0; $i < 4; $i++) {
            $customer = new Customer;
            $customer->setCompany($faker->company);
            $customer->setLastName($faker->lastName);
            $customer->setFirstName($faker->firstName);
            $customer->setPostalCode($faker->postcode);
            $customer->setAdress($faker->streetAddress);
            $customer->setCity($faker->city);
            $customer->setCountry('France');
            $customer->setPhone($faker->phoneNumber);
            $customer->setTVANumber($faker->vat);
            $customer->setSIRET($faker->siret);
            $customer->setCreatedAt($faker->dateTimeBetween('-1 year', 'now' ));

            for ($j=0; $j < 20; $j++) {
                $customerUser = new CustomerUser();
                $customerUser->setLastName($faker->lastName);
                $customerUser->setFirstName($faker->firstName);
                $customerUser->setEmail($faker->email);
                $customerUser->setPostalCode($faker->postcode);
                $customerUser->setAdress($faker->streetAddress);
                $customerUser->setCity($faker->city);
                $customerUser->setCountry('France');
                $customerUser->setPhone($faker->phoneNumber);
                $customerUser->setCustomers($customer);
                $customerUser->setCreatedAt($faker->dateTimeBetween('-1 year', 'now' ));

                $manager->persist($customerUser);
            }
            $manager->persist($customer);

            $user = new User();
            $user->setEmail('customer'.($i+1).'@gmail.com');
            $user->setPassword($this->userPasswordHasher->hashPassword($user, 'password'.($i+1)));
            $user->setRoles(["ROLE_CLIENT"]);
            $user->setCustomers($customer);

            $manager->persist($user);
        }

        // Employees with admin role
        $employee = new Employee();
        $employee->setLastName($faker->lastName);
        $employee->setFirstName($faker->firstName);
        $employee->setPhone($faker->phoneNumber);
        $employee->setCreatedAt($faker->dateTimeBetween('-1 year', 'now' ));

        $manager->persist($employee);

        $user = new User();
        $user->setEmail('employee@bilemo.com');
        $user->setPassword($this->userPasswordHasher->hashPassword($user, 'password'));
        $user->setRoles(["ROLE_ADMIN"]);
        $user->setEmployees($employee);

        $manager->persist($user);

        // Employee with super admin role
        $employee = new Employee();
        $employee->setLastName($faker->lastName);
        $employee->setFirstName($faker->firstName);
        $employee->setPhone($faker->phoneNumber);
        $employee->setCreatedAt($faker->dateTimeBetween('-1 year', 'now' ));

        $manager->persist($employee);

        $user = new User();
        $user->setEmail('bilemo@bilemo.com');
        $user->setPassword($this->userPasswordHasher->hashPassword($user, 'bilemo'));
        $user->setRoles(["ROLE_SUPER_ADMIN"]);
        $user->setEmployees($employee);

        $manager->persist($user);

        $manager->flush();
    }
}
