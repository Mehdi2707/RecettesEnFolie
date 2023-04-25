<?php

namespace App\Security\Voter;

use App\Entity\Recipes;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class RecipesVoter extends Voter
{
    const EDIT = 'RECIPE_EDIT';
    const DELETE = 'RECIPE_DELETE';

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $recipe): bool
    {
        if(!in_array($attribute, [self::EDIT, self::DELETE]))
            return false;

        if(!$recipe instanceof Recipes)
            return false;

        return true;
    }

    protected function voteOnAttribute(string $attribute, $recipe, TokenInterface $token): bool
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