<?php

namespace Invoicing\Bundle\AppBundle\Command;

use Invoicing\Entity\User;
use Invoicing\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserCreateCommand extends ContainerAwareCommand
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    public function __construct(UserRepository $userRepository, UserPasswordEncoderInterface $encoder)
    {
        $this->userRepository = $userRepository;
        $this->encoder = $encoder;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('user:create')
            ->setDescription('...')
            ->addArgument('argument', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $username = trim($io->ask('Username'));
        $password = $io->askHidden('Password');

        $user = new User($username);
        $user->setPassword($this->encoder->encodePassword($user, $password));

        $this->userRepository->save($user);

        $io->success("User " . $username . " successfully created.");
    }

}
