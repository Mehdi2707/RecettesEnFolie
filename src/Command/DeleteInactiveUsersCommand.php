<?php

namespace App\Command;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:delete-inactive-users',
    description: 'Delete inactive users disabled for two years.',
)]
class DeleteInactiveUsersCommand extends Command
{
    protected static $defaultName = 'app:delete-inactive-users';

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:delete-inactive-users')
            ->setDescription('Supprimer les utilisateurs inactifs qui ont été désactivés pendant deux ans.')
            ->setHelp('This command deletes users who have been disabled for two years and have not reactivated their accounts.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Date actuelle
        $currentDate = new \DateTimeImmutable();

        // Récupérer les utilisateurs désactivés
        $userRepository = $this->entityManager->getRepository(Users::class);
        $usersToDelete = $userRepository->findInactiveUsers();

        // Supprimer les utilisateurs et leurs données associées désactivés depuis deux ans ou plus
        foreach ($usersToDelete as $user)
        {
            $limitDate = $user->getDisabledAt()->modify('+2 years');

            if($currentDate >= $limitDate)
            {
                $this->entityManager->remove($user);
                $output->writeln(sprintf('User %d has been deleted.', $user->getId()));
            }
        }

        // Exécuter les opérations de suppression
        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
