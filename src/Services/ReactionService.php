<?php

namespace Anil\Comments\Services;

use Anil\Comments\Contracts\ReactionServiceContract;
use Anil\Comments\Http\Requests\ReactRequest;
use Anil\Comments\Models\Comment;
use Anil\Comments\Models\CommentReaction;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Config;

class ReactionService implements ReactionServiceContract
{
    /**
     * Toggles a reaction on a comment for the authenticated user.
     *
     * Behaviour:
     *  - Same type again  → removes the reaction (toggle off).
     *  - Different type   → switches to the new reaction.
     *  - No prior reaction → creates a new one.
     *
     * @return array{reactions: array<string, int>, user_reaction: string|null}
     */
    public function react(ReactRequest $request, Comment $comment): array
    {
        /** @var User $user */
        $user = $request->user();
        $reactorId = $user->getKey();
        $reactorType = $user->getMorphClass();
        $type = $request->string('type')->toString();

        /** @var class-string<CommentReaction> $reactionClass */
        $reactionClass = Config::get('comments.reaction_model', CommentReaction::class);

        $existing = $comment->reactions()
            ->where('reactor_id', $reactorId)
            ->where('reactor_type', $reactorType)
            ->first();

        $userReaction = null;

        if ($existing !== null) {
            if ($existing->type === $type) {
                $existing->delete();
            } else {
                $existing->update(['type' => $type]);
                $userReaction = $type;
            }
        } else {
            $reactionClass::create([
                'comment_id' => $comment->getKey(),
                'reactor_id' => $reactorId,
                'reactor_type' => $reactorType,
                'type' => $type,
            ]);
            $userReaction = $type;
        }

        $comment->load('reactions');

        /** @var list<string> $allowedTypes */
        $allowedTypes = Config::get('comments.reactions.types', ['like', 'dislike']);

        /** @var array<string, int> $counts */
        $counts = $comment->reactions
            ->groupBy('type')
            ->map(fn ($group) => $group->count())
            ->toArray();

        foreach ($allowedTypes as $allowedType) {
            $counts[$allowedType] ??= 0;
        }

        return [
            'reactions' => $counts,
            'user_reaction' => $userReaction,
        ];
    }
}
