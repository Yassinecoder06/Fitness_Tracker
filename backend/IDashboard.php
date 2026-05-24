<?php

    interface IDashboard{
        public function caloriesRemaining($date,$id) : int;
        public function caloriesConsumed($date,$id) : int ;
        public function caloriesBurned($date,$id) : int;
        public function stepsToday($day,$id) : int;
        public function caloriesBudget($day,$id) : int;
        public function ProteinAmount($day,$id) : int;
        public function CarbsAmount($day,$id) : int;
        public function FatAmount($day,$id): int ;
        public function FiberAmount($day,$id) : int;
        public function exercice_today($date,$id);

        
    }






?>