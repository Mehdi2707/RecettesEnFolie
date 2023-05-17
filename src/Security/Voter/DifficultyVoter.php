<?php

namespace App\Security\Voter;

use App\Entity\DifficultyLevel;
use App\Entity\Unit;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class DifficultyVoter extends Voter
{
    const EDIT = 'DIFFICULTY_EDIT';
    const DELETE = 'DIFFICULTY_DELETE';

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $difficulty): bool
    {
        if(!in_array($attribute, [self::EDIT, self::DELETE]))
            return false;

        if(!$difficulty instanceof DifficultyLevel)
            return false;

        return true;
    }

    protected function voteOnAttribute(string $attribute, $difficulty, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if(!$user instanceof UserInterface)
            return false;

        if($this->security->isGranted('ROLE_ADMIN'))
            return true;

        switch($attribute)
        {
            case self::EDIT:
                return $this->canEdit();
                break;
            case self::DELETE:
                return $this->canDelete();
                break;
        }
    }

    private function canEdit()
    {
        return $this->security->isGranted('ROLE_RECIPE_ADMIN');
    }

    private function canDelete()
    {
        return $this->security->isGranted('ROLE_RECIPE_ADMIN');
    }
}