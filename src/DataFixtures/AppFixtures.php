<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

use App\Entity\Comment;
use App\Entity\Conference;
use App\Entity\SuperAdmin;

class AppFixtures extends Fixture
{
    public function __construct(private PasswordHasherFactoryInterface $hasher)
    {

    }
    public function load(ObjectManager $manager): void
    {
        $minsk = new Conference();
        $minsk->setCity('Minsk');
        $minsk->setYear('2012');
        $minsk->setIsInternational(true);
        $manager->persist($minsk);

        $kiev = new Conference();
        $kiev->setCity('Kiev');
        $kiev->setYear('2022');
        $kiev->setIsInternational(false);
        $manager->persist($kiev);

        $minsk_comment = new Comment();
        $minsk_comment->setConference($minsk);
        $minsk_comment->setAuthor('Frodo');
        $minsk_comment->setEmail('frodo@shire.com');
        $minsk_comment->setText('Amazing');
        $manager->persist($minsk_comment);

        $admin = new SuperAdmin();
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setUsername('admin');
        $admin->setPassword($this->hasher->getPasswordHasher(SuperAdmin::class)->hash('admin'));
        $manager->persist($admin);

        $manager->flush();
    }
}