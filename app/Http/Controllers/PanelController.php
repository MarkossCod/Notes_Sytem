<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Category;
use App\Models\Note;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PanelController extends Controller
{
    private const ALLOWED_PERIODS = [7, 30, 90];

    /** Builds the current user's dashboard from saved data and recorded movements. */
    public function index(Request $request): View
    {
        $userName = (string) session('user_name');
        $requestedPeriod = (int) $request->integer('period', 30);
        $period = in_array($requestedPeriod, self::ALLOWED_PERIODS, true) ? $requestedPeriod : 30;
        $start = now()->startOfDay()->subDays($period - 1);

        $notes = Note::query()->where('user_name', $userName);
        $totalNotes = (clone $notes)->count();
        $completedNotes = (clone $notes)->where('status', 'concluida')->count();

        $summary = [
            'notes' => $totalNotes,
            'completed' => $completedNotes,
            'categories' => Category::where('user_name', $userName)->count(),
            'trash' => Note::onlyTrashed()->where('user_name', $userName)->count(),
            'completion_rate' => $totalNotes > 0 ? (int) round(($completedNotes / $totalNotes) * 100) : 0,
            'movements' => Activity::where('user_name', $userName)->where('created_at', '>=', $start)->count(),
        ];

        $dailyTotals = Activity::query()
            ->where('user_name', $userName)
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as activity_date, COUNT(*) as total')
            ->groupBy('activity_date')
            ->pluck('total', 'activity_date');

        $dailyChart = collect(range(0, $period - 1))->map(function (int $offset) use ($start, $dailyTotals): array {
            $date = $start->copy()->addDays($offset);

            return [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('d/m'),
                'tooltip' => $date->format('d/m/Y'),
                'total' => (int) ($dailyTotals[$date->format('Y-m-d')] ?? 0),
            ];
        });

        // Ninety daily columns become unreadable on smaller screens, so this period is summarized by week.
        $chartGranularity = $period === 90 ? 'weekly' : 'daily';
        $chart = $period === 90
            ? $dailyChart->chunk(7)->map(function ($week): array {
                $firstDate = Carbon::parse($week->first()['date']);
                $lastDate = Carbon::parse($week->last()['date']);

                return [
                    'date' => $firstDate->format('Y-m-d'),
                    'label' => $firstDate->format('d/m'),
                    'tooltip' => $firstDate->format('d/m').' a '.$lastDate->format('d/m/Y'),
                    'total' => (int) $week->sum('total'),
                ];
            })->values()
            : $dailyChart;

        $recentActivities = Activity::query()
            ->where('user_name', $userName)
            ->latest('created_at')
            ->limit(30)
            ->get();

        $groupTotals = $recentActivities
            ->groupBy(fn (Activity $activity): string => $activity->group)
            ->map->count();

        return view('panel.index', compact(
            'summary',
            'chart',
            'chartGranularity',
            'recentActivities',
            'groupTotals',
            'period',
        ));
    }
}
