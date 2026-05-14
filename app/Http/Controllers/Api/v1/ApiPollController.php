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
            'question'               => 'required|string|max:255',
            'title'                  => 'nullable|string|max:255',
            'options'                => 'required|array|min:2',
            'options.*'              => 'required|string|max:255',
            'allow_multiple_choices' => 'boolean',
            'results_public'         => 'boolean',
            'duration'               => 'nullable|integer|min:1',
            'start_now'              => 'boolean',
        ]);

        $poll = new Poll();
        $poll->user_id = $request->user()->id;
        $poll->question = $validated['question'];
        $poll->title = $validated['title'] ?? null;
        $poll->secret_token = Str::uuid();
        $poll->allow_multiple_choices = $validated['allow_multiple_choices'] ?? false;
        $poll->results_public = $validated['results_public'] ?? false;
        $poll->is_draft = true;

        if (!empty($validated['duration'])) {
            $poll->duration = $validated['duration'] * 60;
        }

        if (!empty($validated['start_now'])) {
            $poll->is_draft = false;
            $poll->started_at = now();
            if ($poll->duration) {
                $poll->ends_at = now()->addSeconds($poll->duration);
            }
        }

        $poll->save();

        foreach ($validated['options'] as $label) {
            $poll->options()->create(['label' => trim($label)]);
        }

        return response()->json($poll->load('options'), 201);
    }

    public function start(Request $request, int $id)
    {
        $poll = $request->user()->polls()->find($id);

        if (!$poll) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        if (!$poll->is_draft) {
            return response()->json(['message' => 'Poll is already started.'], 422);
        }

        $poll->is_draft = false;
        $poll->started_at = now();
        if ($poll->duration) {
            $poll->ends_at = now()->addSeconds($poll->duration);
        }
        $poll->save();

        return response()->json($poll->load('options'));
    }

    public function vote(Request $request, string $token)
    {
        $poll = Poll::where('secret_token', $token)->first();

        if (!$poll) {
            return response()->json(['message' => 'Sondage introuvable.'], 404);
        }
        if ($poll->is_draft) {
            return response()->json(['message' => 'Ce sondage n\'est pas encore démarré.'], 422);
        }
        if ($poll->ends_at && $poll->ends_at->isPast()) {
            return response()->json(['message' => 'Ce sondage est terminé.'], 422);
        }

        $validated = $request->validate([
            'option_ids'   => 'required|array|min:1',
            'option_ids.*' => 'integer',
        ]);

        $validOptionIds = $poll->options()->pluck('id')->toArray();
        foreach ($validated['option_ids'] as $optionId) {
            if (!in_array($optionId, $validOptionIds)) {
                return response()->json(['message' => 'Option invalide.'], 422);
            }
        }

        if (!$poll->allow_multiple_choices && count($validated['option_ids']) > 1) {
            return response()->json(['message' => 'Ce sondage n\'accepte qu\'un seul choix.'], 422);
        }

        $poll->votes()->where('user_id', $request->user()->id)->delete();

        foreach ($validated['option_ids'] as $optionId) {
            $poll->votes()->create([
                'user_id'        => $request->user()->id,
                'poll_option_id' => $optionId,
            ]);
        }

        return response()->json(['message' => 'Vote enregistré.']);
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
