<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiPollController extends Controller
{
    /**
     * Display a listing of the authenticated user's polls.
     */
    public function index(Request $request)
    {
        $polls = $request->user()->polls()->orderBy('created_at', 'desc')->get();

        return $polls;
    }

    /**
     * Display the specified poll by its secret token.
     */
    public function show(string $token)
    {
        $poll = Poll::with(['options' => function ($query) {
            $query->withCount('votes');
        }])->where('secret_token', $token)->first();

        if (!$poll) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        return $poll;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:255',
            'title'    => 'nullable|string|max:255',
        ]);

        $poll = new Poll();
        $poll->user_id = $request->user()->id;
        $poll->question = $validated['question'];
        $poll->title = $validated['title'] ?? null;
        $poll->secret_token = Str::uuid();
        $poll->save();

        return response()->json($poll, 201);
    }

    public function destroy(Request $request, int $id) {
        sleep(1);
        $poll = $request->user()->polls()->find($id);

        if (!$poll) {
            return response()->json(['message' => 'Poll not found']);
        }

        $poll->delete();
        return response()->json(['message' => 'Poll deleted'], 200);
    }
}
