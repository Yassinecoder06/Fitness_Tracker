<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once "IDashboard.php";

class Dashboard implements IDashboard {

    protected PDO $pdo;
    protected string $id;

    public function __construct() {
        $this->pdo = get_pdo();
    }

    // calories restantes
    public function caloriesRemaining($date, $id): int {
        return 1500;
    }

    // calories consommées
    public function caloriesConsumed($date, $id): int {

        $query = $this->pdo->prepare("
            SELECT SUM(calories) AS tot
            FROM meals
            WHERE user_id = ? AND date = ?
        ");

        $query->execute([$id, $date]);

        $res = $query->fetch(PDO::FETCH_ASSOC);

        return (int)($res['tot'] ?? 0);
    }

    // calories burned
    public function caloriesBurned($date, $id): int {

        $query = $this->pdo->prepare("
            SELECT SUM(calories_burned) AS tot
            FROM exercise_logs
            WHERE user_id = ? AND date = ?
        ");

        $query->execute([$id, $date]);

        $res = $query->fetch(PDO::FETCH_ASSOC);

        return (int)($res['tot'] ?? 0);
    }

    public function stepsToday($day, $id): int {
        return 1520;
    }

    public function caloriesBudget($day, $id): int {
        return 2250;
    }

    // protein
    public function ProteinAmount($day, $id): int {

        $query = $this->pdo->prepare("
            SELECT SUM(protein) AS tot
            FROM meals
            WHERE user_id = ? AND date = ?
        ");

        $query->execute([$id, $day]);

        $res = $query->fetch(PDO::FETCH_ASSOC);

        return (int)($res['tot'] ?? 0);
    }

    // carbs
    public function CarbsAmount($day, $id): int {

        $query = $this->pdo->prepare("
            SELECT SUM(carbs) AS tot
            FROM meals
            WHERE user_id = ? AND date = ?
        ");

        $query->execute([$id, $day]);

        $res = $query->fetch(PDO::FETCH_ASSOC);

        return (int)($res['tot'] ?? 0);
    }

    // fat
    public function FatAmount($day, $id): int {

        $query = $this->pdo->prepare("
            SELECT SUM(fat) AS tot
            FROM meals
            WHERE user_id = ? AND date = ?
        ");

        $query->execute([$id, $day]);

        $res = $query->fetch(PDO::FETCH_ASSOC);

        return (int)($res['tot'] ?? 0);
    }

    // fiber
    
    // public function FiberAmount($day, $id): int {

    //     $query = $this->pdo->prepare("
    //         SELECT SUM(fiber) AS tot
    //         FROM meals
    //         WHERE user_id = ? AND date = ?
    //     ");

    //     $query->execute([$id, $day]);

    //     $res = $query->fetch(PDO::FETCH_ASSOC);

    //     return (int)($res['tot'] ?? 0);
    // }
    
    // water glasses
    public function nbreGlassesForToday($date, $id): int {

        $query = $this->pdo->prepare("
            SELECT SUM(glasses) AS tot
            FROM water_intake
            WHERE user_id = ? AND date = ?
        ");

        $query->execute([$id, $date]);

        $res = $query->fetch(PDO::FETCH_ASSOC);

        return (int)($res['tot'] ?? 0);
    }

    // traja3lk tous les exercice li t3amlou lyoum
    public function exercice_today($date,$id){
        $query = $this->pdo->prepare("
            select * from exercise_logs
            where id= ? and date = ?
        ");
        $query->execute([$id,$date]);
        $res = $query->fetchAll(PDO::FETCH_ASSOC);
        return $res;
    }

    #[Override]
    public function FiberAmount($day, $id): int
    {
        
        return 1;
    }
}
?>