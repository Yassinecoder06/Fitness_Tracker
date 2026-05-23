<?php

namespace App\Controller;

use App\Entity\DiaryNote;
use App\Entity\ExerciseLog;
use App\Entity\Meal;
use App\Entity\WaterIntake;
use App\Entity\User;
use App\Form\ExerciseLogType;
use App\Form\MealType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('PUBLIC_ACCESS')]
#[Route('/diary')]
class DiaryController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    /**
     * ── MÉTHODE PRIVÉE POUR CRÉER/RÉCUPÉRER UN USER FICTIF ───────────────────
     */
    private function getTestUser(): ?\App\Entity\User
    {
        $user = $this->getUser();

        if (!$user) {
            $user = $this->em->getRepository(User::class)->findOneBy(['email' => 'test@fittrack.local']);

            if (!$user) {
                $user = new User();
                $user->setEmail('test@fittrack.local');
                $user->setPassword('fake_password_123');
                $user->setUsername('Testeur');

                $this->em->persist($user);
                $this->em->flush();
            }
        }

        return $user;
    }

    #[Route('', name: 'app_diary', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $selectedDate = $request->query->get('date', date('Y-m-d'));
        $date         = new \DateTime($selectedDate);
        $user         = $this->getTestUser(); // <-- Corrigé pour l'affichage

        $meals = $this->em->getRepository(Meal::class)->findBy(
            ['user' => $user, 'date' => $date],
            ['mealType' => 'ASC']
        );

        $exercises = $this->em->getRepository(ExerciseLog::class)->findBy(
            ['user' => $user, 'date' => $date]
        );

        $notes = $this->em->getRepository(DiaryNote::class)->findBy(
            ['user' => $user, 'date' => $date],
            ['createdAt' => 'DESC']
        );

        $water       = $this->em->getRepository(WaterIntake::class)->findOneBy(['user' => $user, 'date' => $date]);
        $waterGlasses = $water?->getGlasses() ?? 0;

        // Totals
        $totalCal   = array_sum(array_map(fn(Meal $m) => $m->getCalories(), $meals));
        $totalPro   = array_sum(array_map(fn(Meal $m) => $m->getProtein(), $meals));
        $totalCarbs = array_sum(array_map(fn(Meal $m) => $m->getCarbs(), $meals));
        $totalFat   = array_sum(array_map(fn(Meal $m) => $m->getFat(), $meals));
        $totalBurned = array_sum(array_map(fn(ExerciseLog $e) => $e->getCaloriesBurned(), $exercises));

        // Group meals by type
        $grouped = [];
        foreach ($meals as $meal) {
            $grouped[$meal->getMealType()][] = $meal;
        }

        // Forms
        $mealForm     = $this->createForm(MealType::class, null, ['action' => $this->generateUrl('app_diary_add_meal')]);
        $exerciseForm = $this->createForm(ExerciseLogType::class, null, ['action' => $this->generateUrl('app_diary_add_exercise')]);

        return $this->render('index.html.twig', [
            'selected_date' => $selectedDate,
            'display_date'  => $date->format('l, F j Y'),
            'prev_date'     => (clone $date)->modify('-1 day')->format('Y-m-d'),
            'next_date'     => (clone $date)->modify('+1 day')->format('Y-m-d'),
            'today'         => date('Y-m-d'),
            'grouped_meals' => $grouped,
            'exercises'     => $exercises,
            'notes'         => $notes,
            'water_glasses' => $waterGlasses,
            'total_cal'     => $totalCal,
            'total_pro'     => $totalPro,
            'total_carbs'   => $totalCarbs,
            'total_fat'     => $totalFat,
            'total_burned'  => $totalBurned,
            'goal_cal'      => 2200,
            'meal_form'     => $mealForm,
            'exercise_form' => $exerciseForm,
            'mealForm'         => $mealForm->createView(),
            'exerciseForm'     => $exerciseForm->createView(),
        ]);
    }

    #[Route('/meal/add', name: 'app_diary_add_meal', methods: ['POST'])]
    public function addMeal(Request $request): JsonResponse
    {
        $meal = new Meal();
        $form = $this->createForm(MealType::class, $meal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $date = $request->request->get('date', date('Y-m-d'));
            $meal->setUser($this->getTestUser()); // <-- Corrigé
            $meal->setDate(new \DateTime($date));
            $this->em->persist($meal);
            $this->em->flush();

            return $this->json(['success' => true]);
        }

        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }

        return $this->json(['success' => false, 'errors' => $errors], 422);
    }

    #[Route('/meal/delete/{id}', name: 'app_diary_delete_meal', methods: ['POST'])]
    public function deleteMeal(int $id): JsonResponse
    {
        $meal = $this->em->getRepository(Meal::class)->find($id);

        if (!$meal || $meal->getUser() !== $this->getTestUser()) { // <-- Corrigé
            return $this->json(['success' => false], 403);
        }

        $this->em->remove($meal);
        $this->em->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/exercise/add', name: 'app_diary_add_exercise', methods: ['POST'])]
    public function addExercise(Request $request): JsonResponse
    {
        $exercise = new ExerciseLog();
        $form     = $this->createForm(ExerciseLogType::class, $exercise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $date = $request->request->get('date', date('Y-m-d'));
            $exercise->setUser($this->getTestUser()); // <-- Déjà bon
            $exercise->setDate(new \DateTime($date));
            $this->em->persist($exercise);
            $this->em->flush();

            return $this->json(['success' => true]);
        }

        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }

        return $this->json(['success' => false, 'errors' => $errors], 422);
    }

    #[Route('/exercise/delete/{id}', name: 'app_diary_delete_exercise', methods: ['POST'])]
    public function deleteExercise(int $id): JsonResponse
    {
        $exercise = $this->em->getRepository(ExerciseLog::class)->find($id);

        if (!$exercise || $exercise->getUser() !== $this->getTestUser()) { // <-- Corrigé
            return $this->json(['success' => false], 403);
        }

        $this->em->remove($exercise);
        $this->em->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/notes/save', name: 'app_diary_save_notes', methods: ['POST'])]
    public function saveNotes(Request $request): JsonResponse
    {
        $noteText = trim($request->request->get('notes', ''));
        $dateStr  = $request->request->get('date', date('Y-m-d'));

        if ($noteText === '') {
            return $this->json(['success' => false, 'error' => 'Note cannot be empty'], 422);
        }

        $note = new DiaryNote();
        $note->setUser($this->getTestUser()); // <-- Corrigé
        $note->setDate(new \DateTime($dateStr));
        $note->setNote($noteText);

        $this->em->persist($note);
        $this->em->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/water/update', name: 'app_diary_update_water', methods: ['POST'])]
    public function updateWater(Request $request): JsonResponse
    {
        $glasses = (int) $request->request->get('water', 0);
        $dateStr = $request->request->get('date', date('Y-m-d'));
        $date    = new \DateTime($dateStr);
        $user    = $this->getTestUser(); // <-- Corrigé

        $water = $this->em->getRepository(WaterIntake::class)->findOneBy(['user' => $user, 'date' => $date]);

        if (!$water) {
            $water = new WaterIntake();
            $water->setUser($user);
            $water->setDate($date);
            $this->em->persist($water);
        }

        $water->setGlasses($glasses);
        $this->em->flush();

        return $this->json(['success' => true]);
    }
}
