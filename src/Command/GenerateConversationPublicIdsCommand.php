<?php

namespace App\Command;

use App\Entity\Conversation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:generate-conversation-public-ids',
    description: 'Generate public IDs for existing conversations'
)]
class GenerateConversationPublicIdsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $conversations = $this->em
            ->getRepository(Conversation::class)
            ->findBy(['publicId' => null]);

        foreach ($conversations as $conversation) {
            $conversation->setPublicId(
                substr(bin2hex(random_bytes(16)), 0, 12)
            );
        }

        $this->em->flush();

        $output->writeln(count($conversations).' conversations mises à jour.');

        return Command::SUCCESS;
    }
}