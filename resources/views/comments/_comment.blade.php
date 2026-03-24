@php
    /** @var Comment $comment */
    use Anil\Comments\Comment;$markdownParser = new Parsedown();
    $markdownParser->setSafeMode(true);

    $currentLevel = $indentationLevel ?? 0;
    $maxLevel     = $maxIndentationLevel ?? 3;
    $nextLevel    = $currentLevel + 1;
    $commentKey   = $comment->getKey();

    // Reactions — counts and current user's choice, from the eager-loaded collection
    $reactionsEnabled = $reactionsEnabled ?? Config::get('comments.reactions.enabled', true);
    $reactionTypes    = $reactionTypes    ?? Config::get('comments.reactions.types', ['like', 'dislike']);
    $reactionsLoaded  = $comment->relationLoaded('reactions');
    $reactionCounts   = [];
    $userReaction     = null;

    if ($reactionsEnabled && $reactionsLoaded) {
        $reactionCounts = $comment->reactions
            ->groupBy('type')
            ->map(fn ($g) => $g->count())
            ->toArray();

        if (auth()->check()) {
            $found = $comment->reactions
                ->where('reactor_id', auth()->id())
                ->where('reactor_type', auth()->user()->getMorphClass())
                ->first();
            $userReaction = $found?->type ?? null;
        }
    }

    // Icons for built-in reaction types; add entries here for any custom types
    $reactionIcons = [
        'like'    => '<svg width="15" height="15" fill="currentColor" viewBox="0 0 20 20"><path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"/></svg>',
        'dislike' => '<svg width="15" height="15" fill="currentColor" viewBox="0 0 20 20"><path d="M18 9.5a1.5 1.5 0 11-3 0v-6a1.5 1.5 0 013 0v6zM14 9.667v-5.43a2 2 0 00-1.105-1.79l-.05-.025A4 4 0 0011.055 2H5.64a2 2 0 00-1.962 1.608l-1.2 6A2 2 0 004.44 12H8v4a2 2 0 002 2 1 1 0 001-1v-.667a4 4 0 00-.8-2.4l-1.4 1.866a4 4 0 00-.8 2.4z"/></svg>',
    ];

    // Avatar initials + color
    $authorName    = $comment->getAuthorName();
    $nameParts     = explode(' ', trim($authorName));
    $initials      = strtoupper(mb_substr($nameParts[0], 0, 1));
    if (count($nameParts) > 1) {
        $initials .= strtoupper(mb_substr(end($nameParts), 0, 1));
    }
    $avatarPalette = ['#ea580c','#3b82f6','#10b981','#8b5cf6','#f59e0b','#ec4899','#06b6d4'];
    $avatarBg      = $avatarPalette[abs(crc32($authorName)) % count($avatarPalette)];
@endphp

<div id="comment-{{ $commentKey }}" class="cc-comment" style="position:relative;">
    @if($currentLevel > 0)
        <div class="cc-elbow"></div>
    @endif
    <div class="cc-comment-inner">

        {{-- Avatar --}}
        <div class="cc-avatar-wrap">
            <img
                src="{{ $comment->getAvatarUrl(40) }}"
                alt="{{ $authorName }}"
                class="cc-avatar"
                onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
            >
            <div class="cc-avatar-init" style="display:none;background:{{ $avatarBg }};">{{ $initials }}</div>
        </div>

        {{-- Body --}}
        <div class="cc-body">
            <div class="cc-meta">
                <span class="cc-author">
                    {{ $authorName }}
                    @if($comment->approved)
                        <span class="cc-verified" title="Verified">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="#3b82f6"><path
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                        </span>
                    @endif
                </span>
                <span class="cc-time">{{ $comment->created_at->diffForHumans() }}</span>
                @if(!$comment->approved)
                    <span class="cc-pending-badge">@lang('comments::comments.pending_approval')</span>
                @endif
            </div>

            <p class="cc-text">{!! $markdownParser->line($comment->comment) !!}</p>

            {{-- Actions --}}
            <div class="cc-actions">
                {{-- Reaction buttons — rendered for each configured type --}}
                @if($reactionsEnabled)
                    @foreach($reactionTypes as $reactionType)
                        <button
                            type="button"
                            class="cc-action-btn cc-react-btn {{ $userReaction === $reactionType ? 'cc-reaction-active' : '' }}"
                            data-comment="{{ $commentKey }}"
                            data-type="{{ $reactionType }}"
                            title="{{ ucfirst($reactionType) }}"
                        >
                            {!! $reactionIcons[$reactionType] ?? '<span style="font-size:11px;font-weight:600;">'.e(ucfirst($reactionType)).'</span>' !!}
                            <span class="cc-reaction-count">{{ $reactionCounts[$reactionType] ?? 0 }}</span>
                        </button>
                    @endforeach
                @endif

                {{-- Reply --}}
                @can('reply-to-comment', $comment)
                    <button
                        type="button"
                        class="cc-action-btn"
                        data-cc-toggle="cc-reply-{{ $commentKey }}"
                        title="Reply"
                    >
                        <svg width="15" height="15" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/>
                        </svg>
                        Reply
                    </button>
                @endcan

                {{-- More (edit / delete) --}}
                @canany(['edit-comment', 'delete-comment'], $comment)
                    <div class="cc-dropdown">
                        <button type="button" class="cc-more-btn" title="More options">•••</button>
                        <div class="cc-dropdown-menu">
                            @can('edit-comment', $comment)
                                <button
                                    type="button"
                                    class="cc-dropdown-item"
                                    data-cc-toggle="cc-edit-{{ $commentKey }}"
                                >@lang('comments::comments.edit')</button>
                            @endcan
                            @can('delete-comment', $comment)
                                <form
                                    method="POST"
                                    action="{{ route('comments.destroy', $commentKey) }}"
                                    onsubmit="return confirm('@lang('comments::comments.confirm_delete')')"
                                    style="margin:0;"
                                >
                                    @method('DELETE')
                                    @csrf
                                    <button type="submit"
                                            class="cc-dropdown-item cc-danger">@lang('comments::comments.delete')</button>
                                </form>
                            @endcan
                        </div>
                    </div>
                @endcanany
            </div>

            {{-- Inline reply form --}}
            @can('reply-to-comment', $comment)
                <div id="cc-reply-{{ $commentKey }}" class="cc-inline-form">
                    <form method="POST" action="{{ route('comments.reply', $commentKey) }}" style="margin:0;">
                        @csrf
                        <textarea
                            class="cc-inline-textarea"
                            name="message"
                            rows="3"
                            placeholder="Write your reply to {{ $authorName }}…"
                            required
                        ></textarea>
                        <div class="cc-inline-footer">
                            <button type="button" class="cc-cancel-btn" data-cc-close>Cancel</button>
                            <button type="submit" class="cc-submit-btn">Post Reply</button>
                        </div>
                    </form>
                </div>
            @endcan

            {{-- Inline edit form --}}
            @can('edit-comment', $comment)
                <div id="cc-edit-{{ $commentKey }}" class="cc-inline-form">
                    <form method="POST" action="{{ route('comments.update', $commentKey) }}" style="margin:0;">
                        @method('PUT')
                        @csrf
                        <textarea
                            class="cc-inline-textarea"
                            name="message"
                            rows="3"
                            required
                        >{{ $comment->comment }}</textarea>
                        <div class="cc-inline-footer">
                            <button type="button" class="cc-cancel-btn" data-cc-close>Cancel</button>
                            <button type="submit" class="cc-submit-btn">Update</button>
                        </div>
                    </form>
                </div>
            @endcan
        </div>
    </div>

    {{-- Nested replies --}}
    @if($grouped_comments->has($commentKey) && $currentLevel < $maxLevel)
        <div class="cc-replies">
            @foreach($grouped_comments[$commentKey] as $child)
                @include('comments::comments._comment', [
                    'comment'             => $child,
                    'grouped_comments'    => $grouped_comments,
                    'indentationLevel'    => $nextLevel,
                    'maxIndentationLevel' => $maxLevel,
                    'reactionsEnabled'    => $reactionsEnabled,
                    'reactionTypes'       => $reactionTypes,
                ])
            @endforeach
        </div>
    @endif
</div>
