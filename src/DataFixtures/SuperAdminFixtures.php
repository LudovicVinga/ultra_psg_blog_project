<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SuperAdminFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        // J'appelle le superAdmin crée
        $superAdmin = $this->createSuperAdmin();

        $manager->persist($superAdmin);
        $manager->flush();
    }

    /**
     * Cette méthode permet de créer le super admin.
     */
    private function createSuperAdmin(): User
    {
        $superAdmin = new User();

        $passwordHashed = $this->hasher->hashPassword($superAdmin, 'azerty1234A!');

        $superAdmin->setFirstName('Luis');
        $superAdmin->setLastName('Enrique');
        $superAdmin->setEmail('ultra-psg@gmail.com');
        $superAdmin->setRoles(['ROLE_SUPER_ADMIN', 'ROLE_ADMIN', 'ROLE_USER']);
        $superAdmin->setIsVerified(true);
        $superAdmin->setPassword($passwordHashed);
        $superAdmin->setCreatedAt(new \DateTimeImmutable());
        $superAdmin->setVerifiedAt(new \DateTimeImmutable());
        $superAdmin->setUpdatedAt(new \DateTimeImmutable());

        return $superAdmin;
    }
}
