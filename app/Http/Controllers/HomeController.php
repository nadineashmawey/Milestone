<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('public.home', [
            'content' => config('milestone_home'),
            'accountLinks' => $this->accountLinks(),
        ]);
    }

    public function help(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $needle = $this->normalise($query);
        $resultCount = 0;

        // Every article is rendered so the page script can filter live; ?q= also filters without JavaScript.
        $topics = [];
        foreach (config('milestone_home.help_topics') as $name => $articles) {
            $topic = ['name' => $name, 'articles' => [], 'matches' => 0];
            foreach ($articles as [$question, $answer]) {
                $match = $needle === '' || Str::contains($this->normalise($question.' '.$answer), $needle);
                $topic['articles'][] = ['question' => $question, 'answer' => $answer, 'match' => $match];
                $topic['matches'] += (int) $match;
            }
            $resultCount += $topic['matches'];
            $topics[] = $topic;
        }

        $user = $request->user();

        return view('public.help', [
            'query' => $query,
            'topics' => $topics,
            'resultCount' => $resultCount,
            'categories' => config('milestone_home.report_categories'),
            'prefill' => ['name' => $user?->name, 'email' => $user?->email],
            'accountLinks' => $this->accountLinks(),
        ]);
    }

    /**
     * @return array{login: string, register: string, dashboard: string}
     */
    private function accountLinks(): array
    {
        // TODO(Jana): Sign in and Sign up register these routes. Until then the links use Laravel's standard paths.
        // TODO(team): point "dashboard" at the role-based dashboard route once it exists.
        return [
            'login' => Route::has('login') ? route('login') : url('/login'),
            'register' => Route::has('register') ? route('register') : url('/register'),
            'dashboard' => Route::has('dashboard') ? route('dashboard') : url('/dashboard'),
        ];
    }

    private function normalise(string $text): string
    {
        return Str::lower(str_replace(['’', '‘'], "'", $text));
    }
}
