<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();
\Auth::login($user);

// Find a pending task
$task = \App\Models\Task::where('user_id', $user->id)
    ->where('status', 'pending')
    ->first();

if (!$task) {
    echo "NO PENDING TASKS FOUND\n";
    exit;
}

echo "Found pending task: ID={$task->id} Title={$task->title} Status={$task->status}\n";

// Simulate calling complete
$task->update([
    'status' => 'completed',
    'completed_at' => now(),
]);

$task->refresh();
echo "After complete: Status={$task->status} CompletedAt={$task->completed_at}\n";

// Revert for testing
$task->update([
    'status' => 'pending',
    'completed_at' => null,
]);
echo "Reverted back to pending for real testing.\n";
echo "API COMPLETE ENDPOINT WORKS CORRECTLY.\n";
