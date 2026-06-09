<?php

use App\Http\Controllers\AchievementController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminStatsController;
use App\Http\Controllers\Admin\LessonAdminController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\CharacterController;
use App\Http\Controllers\ChestController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\MistakeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfilePageController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\TravelController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\Content\ManagerCourseController;
use App\Http\Controllers\Content\PublicContentController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/verify-code', [VerificationController::class, 'show'])
    ->name('verification.form');

Route::post('/verify-code', [VerificationController::class, 'verify'])
    ->name('verification.check');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [LessonController::class, 'index'])->name('dashboard');
    Route::get('/lessons/{lesson}', [LessonController::class, 'show'])->name('lessons.show');
    Route::post('/exercises/{exercise}/submit', [ExerciseController::class, 'submit'])->name('exercises.submit');

    Route::get('/character', [CharacterController::class, 'index'])->name('character');
    Route::post('/character/buy-skin', [CharacterController::class, 'buySkin'])->name('character.buySkin');

    Route::get('/shop', [ShopController::class, 'index'])->name('shop');
    Route::post('/shop/buy', [ShopController::class, 'buy'])->name('shop.buy');
    Route::post('/shop/equip', [ShopController::class, 'equip'])->name('shop.equip');

    Route::post('/lesson-chest/claim', [LessonController::class, 'claimLessonChest'])
    ->name('lesson-chest.claim');

    Route::get('/achievements', [AchievementController::class, 'index'])->name('achievements');

    Route::get('/games', [GameController::class, 'index'])->name('games.index');
    Route::get('/games/translation/{level?}', [GameController::class, 'translation'])->name('games.translation');
    Route::get('/games/picture/{level?}', [GameController::class, 'picture'])->name('games.picture');
    Route::get('/games/memory/{level}', [GameController::class, 'memory'])->name('games.memory');
    Route::get('/games/sentence/{level?}', [GameController::class, 'sentence'])->name('games.sentence');
    Route::get('/games/typing/{level?}', [GameController::class, 'typing'])->name('games.typing');
    Route::get('/games/listening/{level?}', [GameController::class, 'listening'])->name('games.listening');
    Route::get('/games/mistakes/{level?}', [GameController::class, 'mistakes'])->name('games.mistakes');
    Route::post('/games/reward', [GameController::class, 'reward'])->name('games.reward');

    Route::get('/stats', [StatsController::class, 'index'])->name('stats.index');

    Route::get('/exam', [ExamController::class, 'intro'])->name('exam.intro');
    Route::get('/exam/{exam}/start', [ExamController::class, 'start'])->whereNumber('exam')->name('exam.start');
    Route::post('/exam/{exam}/submit', [ExamController::class, 'submit'])->whereNumber('exam')->name('exam.submit');

    Route::get('/travel', [TravelController::class, 'index'])->name('travel.index');
    Route::get('/travel/games', [TravelController::class, 'games'])->name('travel.games');
    Route::post('/travel/games/reward', [TravelController::class, 'gameReward'])->name('travel.games.reward');
    Route::get('/travel/scenario', [TravelController::class, 'scenario'])->name('travel.scenario');
    Route::post('/travel/scenario/chat', [TravelController::class, 'scenarioChat'])->name('travel.scenario.chat');
    Route::post('/travel/scenario/reset', [TravelController::class, 'scenarioReset'])->name('travel.scenario.reset');

    Route::get('/chests', [ChestController::class, 'index'])->name('chests.index');
    Route::post('/chests/open', [ChestController::class, 'open'])->name('chests.open');

    Route::get('/mistakes', [MistakeController::class, 'index'])->name('mistakes.index');
    Route::get('/my-profile', [ProfilePageController::class, 'index'])->name('profile.page');


    // Контент-менеджеры и пользовательские курсы
Route::get('/content-managers', [PublicContentController::class, 'managers'])->name('content.managers.index');
Route::get('/content-managers/{manager}', [PublicContentController::class, 'manager'])->name('content.managers.show');
Route::post('/content-managers/{manager}/subscribe', [PublicContentController::class, 'subscribe'])->name('content.managers.subscribe');
Route::delete('/content-managers/{manager}/unsubscribe', [PublicContentController::class, 'unsubscribe'])->name('content.managers.unsubscribe');

Route::get('/my-subscriptions/courses', [PublicContentController::class, 'subscriptions'])->name('content.subscriptions');
Route::get('/community/courses/{course}', [PublicContentController::class, 'course'])->name('content.courses.show');
Route::get('/community/lessons/{lesson}', [PublicContentController::class, 'lesson'])->name('content.lessons.show');

Route::middleware(['content_manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/courses', [ManagerCourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/create', [ManagerCourseController::class, 'create'])->name('courses.create');
    Route::post('/courses', [ManagerCourseController::class, 'store'])->name('courses.store');

    Route::get('/courses/{course}/edit', [ManagerCourseController::class, 'edit'])->name('courses.edit');
    Route::put('/courses/{course}', [ManagerCourseController::class, 'update'])->name('courses.update');
    Route::delete('/courses/{course}', [ManagerCourseController::class, 'destroy'])->name('courses.destroy');

    Route::post('/courses/{course}/lessons', [ManagerCourseController::class, 'storeLesson'])->name('courses.lessons.store');
    Route::put('/lessons/{lesson}', [ManagerCourseController::class, 'updateLesson'])->name('lessons.update');
    Route::delete('/lessons/{lesson}', [ManagerCourseController::class, 'deleteLesson'])->name('lessons.destroy');

    Route::post('/lessons/{lesson}/exercises', [ManagerCourseController::class, 'storeExercise'])->name('lessons.exercises.store');
    Route::delete('/exercises/{exercise}', [ManagerCourseController::class, 'deleteExercise'])->name('exercises.destroy');

    Route::get('/courses', [PublicContentController::class, 'courses'])
    ->name('content.courses.index');

    Route::get('/courses/{course}', [PublicContentController::class, 'course'])
    ->name('content.courses.show');
});
});



Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.index');

    Route::get('/lessons', [LessonAdminController::class, 'index'])->name('admin.lessons');
    Route::get('/lessons/create', [LessonAdminController::class, 'create'])->name('admin.lessons.create');
    Route::post('/lessons', [LessonAdminController::class, 'store'])->name('admin.lessons.store');
    Route::get('/lessons/{lesson}/exercises', [LessonAdminController::class, 'exercises'])->name('admin.exercises');
    Route::put('/lessons/{lesson}', [LessonAdminController::class, 'updateLesson'])->name('admin.lessons.update');
    Route::post('/lessons/{lesson}/exercises', [LessonAdminController::class, 'storeExercise'])->name('admin.exercises.store');
    Route::put('/exercises/{exercise}', [LessonAdminController::class, 'updateExercise'])->name('admin.exercises.update');
    Route::delete('/exercises/{exercise}', [LessonAdminController::class, 'deleteExercise'])->name('admin.exercises.delete');
    Route::get('/lessons/{lesson}/preview', [LessonAdminController::class, 'preview'])->name('admin.lessons.preview');

    Route::get('/users', [UserAdminController::class, 'index'])->name('admin.users');
    Route::post('/users/{user}/toggle-block', [UserAdminController::class, 'toggleBlock'])->name('admin.users.toggleBlock');
    Route::post('/users/{user}/toggle-admin', [UserAdminController::class, 'toggleAdmin'])->name('admin.users.toggleAdmin');
    Route::post('/users/{user}/toggle-content-manager', [UserAdminController::class, 'toggleContentManager'])->name('admin.users.toggleContentManager');

    Route::get('/stats', [AdminStatsController::class, 'index'])->name('admin.stats');
});

require __DIR__.'/auth.php';