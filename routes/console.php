<?php

use Illuminate\Support\Facades\Schedule;

// Marca como vencidos los aptos físicos cuya fecha de vencimiento ya pasó.
Schedule::command('gym:actualizar-vencimientos')->dailyAt('00:05');
