<?php

namespace App\Http\Controllers;

use App\Support\DashboardRoute;
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
     * @return array{login: string, register: string, dashboard: string, logout: string|null}
     */
    private function accountLinks(): array
    {
        // Until Jana's Sign in and Sign up routes exist, the links use Laravel's standard paths.
        return [
            'login' => Route::has('login') ? route('login') : url('/login'),
            'register' => Route::has('register') ? route('register') : url('/register'),
            'dashboard' => DashboardRoute::url(auth()->user()),
            'logout' => Route::has('logout') ? route('logout') : null,
        ];
    }

    private function normalise(string $text): string
    {
        return Str::lower(str_replace(['’', '‘'], "'", $text));
    }
}
