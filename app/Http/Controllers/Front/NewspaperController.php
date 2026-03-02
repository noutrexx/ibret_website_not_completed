<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Services\Newspapers\NewspaperService;
use Illuminate\Http\Request;

class NewspaperController extends Controller
{
    public function index(Request $request, NewspaperService $newspaperService)
    {
        $result = $newspaperService->getFrontPages('today');

        return view('frontend.newspapers', [
            'newspaperItems' => collect($result['items'] ?? []),
            'newspaperError' => $result['error'] ?? null,
            'newspaperSource' => $result['source'] ?? 'gazeteoku',
            'selectedDate' => $result['selected_date'] ?? now()->toDateString(),
            'selectedDateLabel' => $result['selected_label'] ?? 'Bugun',
        ]);
    }
}
