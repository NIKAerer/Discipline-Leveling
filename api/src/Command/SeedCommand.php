<?php

namespace App\Command;

use App\Entity\Activity;
use App\Entity\Discipline;
use App\Entity\DisciplineTracking;
use App\Entity\LolMatch;
use App\Entity\Quest;
use App\Entity\QuestTemplate;
use App\Entity\User;
use App\Repository\DisciplineRepository;
use App\Repository\UserRepository;
use App\Service\AccountDeleter;
use App\Service\RankCalculator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Remplit la base avec les données dont l'appli a besoin pour fonctionner
 * (disciplines et modèles de quêtes), et optionnellement un compte de démo.
 *
 * La commande peut être relancée sans risque : les disciplines existantes
 * ne sont pas dupliquées et le compte de démo est recréé à neuf.
 */
#[AsCommand(name: 'app:seed', description: 'Crée les disciplines, les modèles de quêtes et (avec --demo) le compte de démo')]
class SeedCommand extends Command
{
    public const DEMO_EMAIL = 'demo@discipline-leveling.fr';
    public const DEMO_PASSWORD = 'Demo1234!';
    private const DEMO_NAME = 'Joueur Démo';
    private const DEMO_HISTORY_DAYS = 30;

    /**
     * Disciplines proposées à l'inscription. Les quêtes avec une XP négative
     * sont des malus (ex : sauter une séance fait perdre de l'XP).
     * Le nom "LoL" est utilisé par LolController pour activer le tracker de games.
     */
    private const DISCIPLINES = [
        'LoL' => [
            'icon' => '🎮',
            'templates' => [
                ['Regarder une replay de ses games', 20],
                ['Faire 15 min de last-hit en entraînement', 15],
                ['Jouer 3 games classées concentré', 25],
                ['Étudier un matchup difficile', 15],
                ['Tilt : continuer à jouer après 2 défaites', -20],
            ],
        ],
        'Code' => [
            'icon' => '💻',
            'templates' => [
                ['Coder 1 h sur un projet perso', 25],
                ['Résoudre un exercice d\'algorithmie', 15],
                ['Lire de la documentation technique', 10],
                ['Pousser au moins un commit', 10],
                ['Procrastiner au lieu de coder', -15],
            ],
        ],
        'Sport' => [
            'icon' => '🏋️',
            'templates' => [
                ['Séance de musculation', 25],
                ['30 min de cardio', 20],
                ['10 000 pas dans la journée', 10],
                ['Sauter la séance prévue', -20],
            ],
        ],
        'Lecture' => [
            'icon' => '📚',
            'templates' => [
                ['Lire 20 pages', 15],
                ['Prendre des notes sur un chapitre', 10],
                ['Scroller au lieu de lire', -10],
            ],
        ],
    ];

    /** Disciplines suivies par le compte de démo, avec leur objectif. */
    private const DEMO_TRACKINGS = [
        'LoL' => 'Passer Platine avant la fin de la saison',
        'Code' => 'Finir mon projet portfolio',
        'Sport' => '3 séances par semaine',
    ];

    private const DEMO_CHAMPIONS = [
        ['Ahri', 'Mid', 'Zed'],
        ['Ahri', 'Mid', 'Syndra'],
        ['Orianna', 'Mid', 'Yasuo'],
        ['Viktor', 'Mid', 'Ahri'],
        ['Orianna', 'Mid', 'LeBlanc'],
    ];

    public function __construct(
        private EntityManagerInterface $em,
        private DisciplineRepository $disciplineRepository,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private AccountDeleter $accountDeleter,
        private RankCalculator $rankCalculator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('demo', null, InputOption::VALUE_NONE, 'Crée (ou recrée) le compte de démo avec un historique de 30 jours');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $created = $this->seedDisciplines();
        $io->success(sprintf('%d discipline(s) créée(s), %d déjà présente(s).', $created, count(self::DISCIPLINES) - $created));

        if ($input->getOption('demo')) {
            $this->seedDemoAccount();
            $io->success(sprintf('Compte de démo prêt : %s / %s', self::DEMO_EMAIL, self::DEMO_PASSWORD));
        }

        return Command::SUCCESS;
    }

    private function seedDisciplines(): int
    {
        $created = 0;

        foreach (self::DISCIPLINES as $name => $config) {
            if ($this->disciplineRepository->findOneBy(['name' => $name])) {
                continue;
            }

            $discipline = new Discipline();
            $discipline->setName($name);
            $discipline->setIcon($config['icon']);

            foreach ($config['templates'] as [$label, $expValue]) {
                $template = new QuestTemplate();
                $template->setLabel($label);
                $template->setExpValue($expValue);
                $discipline->addQuestTemplate($template);
                $this->em->persist($template);
            }

            $this->em->persist($discipline);
            $created++;
        }

        $this->em->flush();

        return $created;
    }

    private function seedDemoAccount(): void
    {
        $existing = $this->userRepository->findOneBy(['email' => self::DEMO_EMAIL]);
        if ($existing) {
            $this->accountDeleter->delete($existing);
        }

        $user = new User();
        $user->setName(self::DEMO_NAME);
        $user->setEmail(self::DEMO_EMAIL);
        $user->setPassword($this->passwordHasher->hashPassword($user, self::DEMO_PASSWORD));
        $user->setAvatar('avatar-1');
        $user->setRank('E');
        $user->setExpTotal(0);
        $user->setCreatedAt(new \DateTimeImmutable(sprintf('-%d days', self::DEMO_HISTORY_DAYS)));
        $this->em->persist($user);

        // Graine fixe : l'historique généré est le même à chaque lancement.
        mt_srand(42);

        $userExp = 0;
        foreach (self::DEMO_TRACKINGS as $disciplineName => $goal) {
            $tracking = $this->createDemoTracking($user, $disciplineName, $goal);
            $userExp += $tracking->getExp();

            if ($disciplineName === 'LoL') {
                $this->createDemoMatches($tracking);
            }
        }

        $user->setExpTotal($userExp);
        $user->setRank($this->rankCalculator->rankForExp($userExp));

        $this->em->flush();
    }

    private function createDemoTracking(User $user, string $disciplineName, string $goal): DisciplineTracking
    {
        $discipline = $this->disciplineRepository->findOneBy(['name' => $disciplineName]);

        $tracking = new DisciplineTracking();
        $tracking->setUser($user);
        $tracking->setDiscipline($discipline);
        $tracking->setGoal($goal);
        $this->em->persist($tracking);

        $exp = 0;
        foreach ($discipline->getQuestTemplates() as $template) {
            $quest = new Quest();
            $quest->setLabel($template->getLabel());
            $quest->setExpValue($template->getExpValue());
            $tracking->addQuest($quest);
            $this->em->persist($quest);

            // Une bonne habitude est validée environ 2 jours sur 3, un malus 1 jour sur 6.
            $chance = $template->getExpValue() > 0 ? 65 : 15;

            for ($daysAgo = self::DEMO_HISTORY_DAYS; $daysAgo >= 1; $daysAgo--) {
                if (mt_rand(1, 100) > $chance) {
                    continue;
                }

                $activity = new Activity();
                $activity->setDate(new \DateTimeImmutable(sprintf('today -%d days', $daysAgo)));
                $activity->setExpWon($quest->getExpValue());
                $quest->addActivity($activity);
                $this->em->persist($activity);

                $exp = max(0, $exp + $quest->getExpValue());
            }
        }

        $tracking->setExp($exp);
        $tracking->setRank($this->rankCalculator->rankForExp($exp));

        return $tracking;
    }

    private function createDemoMatches(DisciplineTracking $tracking): void
    {
        $tracking->setLpStarting(0);
        $tracking->setLpGoal(300);

        for ($i = 20; $i >= 1; $i--) {
            [$champion, $role, $matchup] = self::DEMO_CHAMPIONS[mt_rand(0, count(self::DEMO_CHAMPIONS) - 1)];
            $win = mt_rand(1, 100) <= 58;

            $match = new LolMatch();
            $match->setPlayedAt(new \DateTimeImmutable(sprintf('-%d days 21:00', (int) ceil($i * 1.4))));
            $match->setChampion($champion);
            $match->setRole($role);
            $match->setMatchup($matchup);
            $match->setKills($win ? mt_rand(5, 12) : mt_rand(1, 6));
            $match->setDeaths($win ? mt_rand(1, 4) : mt_rand(4, 9));
            $match->setAssists(mt_rand(3, 14));
            $match->setGameDurationMinutes(mt_rand(22, 38));
            $match->setCs(mt_rand(160, 290));
            $match->setWin($win);
            $match->setLpChange($win ? mt_rand(18, 24) : -mt_rand(15, 21));
            $tracking->addLolMatch($match);
            $this->em->persist($match);
        }
    }
}
