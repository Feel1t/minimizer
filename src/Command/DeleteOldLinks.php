<?php

namespace App\Command;

use App\Repository\LinkRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:delete-outdated-links',
    description: 'Deletes overdue links from the database'
)]
class DeleteOldLinks extends Command
{
    const ENTITIES_LIMIT = 1000;

    public function __construct(
        private LinkRepository $linkRepository,
        private EntityManagerInterface $manager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->deleteOutdatedLinks();

        return Command::SUCCESS;
    }

    private function deleteOutdatedLinks(): void
    {
        $entities = $this->linkRepository->findOutdatedLinks(self::ENTITIES_LIMIT);
        if (!empty($entities)) {
            foreach ($entities as $entity) {
                $this->manager->remove($entity);
            }

            $this->manager->flush();
        }

        (count($entities) === self::ENTITIES_LIMIT) && $this->deleteOutdatedLinks();
    }
}