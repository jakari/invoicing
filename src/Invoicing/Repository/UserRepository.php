<?php

namespace Invoicing\Repository;

use Doctrine\ORM\EntityRepository;
use Invoicing\Entity\User;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

class UserRepository extends EntityRepository implements UserLoaderInterface
{
    public function loadUserByUsername($username)
    {
        return $this->findOneBy(['username' => $username]);
    }

    public function save(User $user)
    {
        $this->_em->persist($user);
        $this->_em->flush();
    }
}
