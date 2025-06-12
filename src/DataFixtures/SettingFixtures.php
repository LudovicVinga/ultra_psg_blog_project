<?php

namespace App\DataFixtures;

use App\Entity\Setting;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SettingFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $setting = $this->createSetting();

        $manager->persist($setting);
        $manager->flush();
    }

    // On prevoit une méthode pour créer les parametres

    /**
     * Cette méthode permet de créer les parametres.
     */
    private function createSetting(): Setting
    {
        $setting = new Setting();

        return $setting
             ->setEmail('ultra-psg@gmail.com')
             ->setPhone('07 12 34 56 78')
             ->setUser($this->getReference('super-admin', User::class))
             ->setCreatedAt(new \DateTimeImmutable())
             ->setUpdatedAt(new \DateTimeImmutable());
    }

    public function getDependencies(): array
    {
        return [
            SuperAdminFixtures::class,
        ];
    }
}
