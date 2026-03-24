@php
    use Illuminate\Database\Eloquent\Model;use Illuminate\Pagination\LengthAwarePaginator;
    use Illuminate\Support\Facades\Config;

    /** @var Model $model */
    $allComments = (isset($approved) && $approved === true)
        ? $model->approvedComments()->get()
        : $model->comments()->get();

    // Resolve config-driven options (can also be overridden per-include via Blade variables)
    $reactionsEnabled = $reactionsEnabled ?? Config::get('comments.reactions.enabled', true);
    $reactionTypes    = $reactionTypes    ?? Config::get('comments.reactions.types', ['like', 'dislike']);
    $configMaxDepth   = $maxIndentationLevel ?? Config::get('comments.max_depth', 3);
    $configSort       = $sort ?? Config::get('comments.sort', 'latest');

    // Eager-load reactions once to avoid N+1 per comment — only when feature is on
    if ($reactionsEnabled) {
        $allComments->load('reactions');
    }
@endphp

<style>
    /* ── Comment Component — Framework-Agnostic ─────────────────────────────── */
    .cc-wrap *,
    .cc-wrap *::before,
    .cc-wrap *::after {
        box-sizing: border-box;
    }

    .cc-wrap {
        background: #ffffff;
        border-radius: 14px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, .07), 0 4px 16px rgba(0, 0, 0, .06);
        padding: 36px 44px;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        font-size: 14px;
        color: #1a1a1a;
        line-height: 1.5;
        max-width: 100%;
    }

    /* ── Form ── */
    .cc-form-box {
        background: #f6f6f6;
        border-radius: 10px;
        padding: 16px 20px 14px;
    }

    .cc-guest-fields {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 14px;
    }

    .cc-input {
        width: 100%;
        border: none;
        border-bottom: 1px solid #ddd;
        background: transparent;
        outline: none;
        padding: 6px 2px;
        font-size: 14px;
        font-family: inherit;
        color: #333;
    }

    .cc-input::placeholder {
        color: #aaa;
    }

    .cc-textarea {
        width: 100%;
        border: none;
        background: transparent;
        outline: none;
        resize: none;
        font-size: 15px;
        color: #333;
        font-family: inherit;
        padding: 2px 0 10px;
        min-height: 52px;
    }

    .cc-textarea::placeholder {
        color: #aaa;
    }

    .cc-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-top: 1px solid #e8e8e8;
        padding-top: 10px;
    }

    .cc-toolbar-left {
        display: flex;
        align-items: center;
        gap: 2px;
    }

    .cc-tool-btn {
        background: none;
        border: none;
        cursor: pointer;
        color: #666;
        padding: 5px 7px;
        border-radius: 5px;
        font-size: 13px;
        font-weight: 700;
        line-height: 1;
        transition: background .15s, color .15s;
        font-family: inherit;
        display: inline-flex;
        align-items: center;
    }

    .cc-tool-btn:hover {
        background: #e0e0e0;
        color: #333;
    }

    .cc-tool-sep {
        width: 1px;
        height: 16px;
        background: #d8d8d8;
        margin: 0 8px;
        flex-shrink: 0;
    }

    .cc-submit-btn {
        background: #ea580c;
        color: #fff;
        border: none;
        border-radius: 20px;
        padding: 7px 22px;
        font-size: 13.5px;
        font-weight: 600;
        cursor: pointer;
        font-family: inherit;
        transition: background .15s, transform .1s;
        white-space: nowrap;
    }

    .cc-submit-btn:hover {
        background: #d24d0b;
    }

    .cc-submit-btn:active {
        transform: scale(.97);
    }

    .cc-error {
        color: #dc2626;
        font-size: 12px;
        margin-top: 5px;
    }

    /* ── Auth banner ── */
    .cc-auth-banner {
        background: #f6f6f6;
        border-radius: 10px;
        padding: 28px 24px;
        text-align: center;
    }

    .cc-auth-title {
        font-size: 15px;
        font-weight: 700;
        margin: 0 0 6px;
        color: #111;
    }

    .cc-auth-sub {
        font-size: 13px;
        color: #777;
        margin: 0 0 16px;
    }

    .cc-auth-link {
        display: inline-block;
        background: #ea580c;
        color: #fff;
        border-radius: 20px;
        padding: 7px 22px;
        font-size: 13.5px;
        font-weight: 600;
        text-decoration: none;
        transition: background .15s;
    }

    .cc-auth-link:hover {
        background: #d24d0b;
        color: #fff;
    }

    /* ── Divider ── */
    .cc-divider {
        border: none;
        border-top: 1px solid #ebebeb;
        margin: 28px 0;
    }

    /* ── Header ── */
    .cc-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
    }

    .cc-header-left {
        display: flex;
        align-items: center;
        gap: 9px;
    }

    .cc-title {
        font-size: 17px;
        font-weight: 700;
        margin: 0;
        color: #111;
    }

    .cc-count-badge {
        background: #ea580c;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        padding: 2px 9px;
        border-radius: 10px;
        line-height: 1.6;
    }

    .cc-sort-btn {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: none;
        border: none;
        cursor: pointer;
        color: #444;
        font-size: 13px;
        font-weight: 500;
        font-family: inherit;
        padding: 0;
    }

    .cc-sort-btn:hover {
        color: #111;
    }

    /* ── Comments list ── */
    .cc-list {
        display: flex;
        flex-direction: column;
        gap: 22px;
    }

    .cc-empty {
        text-align: center;
        padding: 40px 0;
        color: #aaa;
        font-size: 14px;
    }

    /* ── Comment item ── */
    .cc-comment {
        position: relative;
    }

    .cc-comment-inner {
        display: flex;
        gap: 14px;
    }

    /* ── Avatar ── */
    .cc-avatar-wrap {
        position: relative;
        flex-shrink: 0;
        width: 40px;
        height: 40px;
    }

    .cc-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        background: #eee;
        display: block;
    }

    .cc-avatar-init {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 15px;
        flex-shrink: 0;
        letter-spacing: .5px;
    }

    /* ── Comment body ── */
    .cc-body {
        flex: 1;
        min-width: 0;
    }

    .cc-meta {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 5px;
    }

    .cc-author {
        font-weight: 700;
        font-size: 14px;
        color: #111;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .cc-verified {
        color: #3b82f6;
        display: inline-flex;
    }

    .cc-time {
        font-size: 12.5px;
        color: #999;
    }

    .cc-pending-badge {
        background: #fef9c3;
        color: #854d0e;
        font-size: 10px;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 10px;
        line-height: 1.6;
    }

    .cc-text {
        font-size: 14px;
        color: #333;
        line-height: 1.6;
        word-break: break-word;
        white-space: pre-wrap;
        margin: 0 0 10px;
    }

    /* ── Action row ── */
    .cc-actions {
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
    }

    .cc-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: none;
        border: none;
        cursor: pointer;
        color: #777;
        font-size: 13px;
        font-family: inherit;
        padding: 0;
        transition: color .15s;
        line-height: 1;
    }

    .cc-action-btn:hover {
        color: #ea580c;
    }

    .cc-action-btn.cc-reaction-active {
        color: #ea580c;
        font-weight: 600;
    }

    .cc-action-btn[data-type="dislike"].cc-reaction-active {
        color: #64748b;
    }

    /* ── Dropdown ── */
    .cc-dropdown {
        position: relative;
    }

    .cc-more-btn {
        background: none;
        border: none;
        cursor: pointer;
        color: #aaa;
        padding: 0 3px;
        font-size: 16px;
        letter-spacing: 1.5px;
        font-family: inherit;
        line-height: 1;
        transition: color .15s;
    }

    .cc-more-btn:hover {
        color: #555;
    }

    .cc-dropdown-menu {
        display: none;
        position: absolute;
        top: calc(100% + 4px);
        right: 0;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, .13);
        min-width: 120px;
        z-index: 200;
        padding: 4px 0;
        border: 1px solid rgba(0, 0, 0, .06);
    }

    .cc-dropdown.cc-open .cc-dropdown-menu {
        display: block;
    }

    .cc-dropdown-item {
        display: block;
        width: 100%;
        text-align: left;
        background: none;
        border: none;
        padding: 8px 16px;
        font-size: 13px;
        font-family: inherit;
        cursor: pointer;
        color: #333;
        transition: background .12s;
        white-space: nowrap;
    }

    .cc-dropdown-item:hover {
        background: #f5f5f5;
    }

    .cc-dropdown-item.cc-danger {
        color: #dc2626;
    }

    /* ── Inline forms (reply / edit) ── */
    .cc-inline-form {
        display: none;
        margin-top: 12px;
        background: #f6f6f6;
        border-radius: 8px;
        padding: 12px 16px 12px;
    }

    .cc-inline-form.cc-open {
        display: block;
    }

    .cc-inline-textarea {
        width: 100%;
        border: none;
        background: transparent;
        outline: none;
        resize: none;
        font-size: 14px;
        color: #333;
        font-family: inherit;
        padding: 0;
        margin-bottom: 10px;
        min-height: 60px;
    }

    .cc-inline-textarea::placeholder {
        color: #aaa;
    }

    .cc-inline-footer {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
    }

    .cc-cancel-btn {
        background: none;
        border: 1px solid #ddd;
        border-radius: 20px;
        padding: 5px 16px;
        font-size: 13px;
        cursor: pointer;
        font-family: inherit;
        color: #555;
        transition: background .12s;
    }

    .cc-cancel-btn:hover {
        background: #ececec;
    }

    /* ── Nested replies ── */
    .cc-replies {
        margin-left: 54px;
        margin-top: 18px;
        padding-left: 30px; /* space for the connector arm */
        display: flex;
        flex-direction: column;
        gap: 16px;
        position: relative;
    }

    /* Vertical backbone — runs full height; last-child mask hides the overshoot */
    .cc-replies::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e8e8e8;
        border-radius: 1px;
        z-index: 0;
    }

    /* White mask on the last child hides the backbone below its avatar centre */
    .cc-replies > .cc-comment:last-child {
        z-index: 1;
    }

    .cc-replies > .cc-comment:last-child::after {
        content: '';
        position: absolute;
        left: -32px; /* 2px left of backbone start (-30px) for full coverage */
        top: 0px; /* just below avatar centre */
        bottom: 0;
        width: 6px;
        background: #ffffff; /* matches .cc-wrap */
        z-index: 2;
        pointer-events: none;
    }

    /* L-shaped elbow: vertical segment + curved horizontal arm */
    .cc-elbow {
        position: absolute;
        left: -30px; /* aligns with the backbone left edge */
        top: -18px; /* reach up into the gap above this child */
        width: 22px; /* horizontal arm length */
        height: 38px; /* gap (16px) + half avatar (20px) + small overlap (2px) */
        border-left: 2px solid #e8e8e8;
        border-bottom: 2px solid #e8e8e8;
        border-bottom-left-radius: 10px;
        pointer-events: none;
        z-index: 3; /* above the mask so elbow itself stays visible */
    }

    /* ── Show more ── */
    .cc-show-more {
        text-align: center;
        margin-top: 28px;
    }

    .cc-show-more-link {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        color: #ea580c;
        font-weight: 600;
        font-size: 14px;
        text-decoration: none;
        transition: color .15s;
    }

    .cc-show-more-link:hover {
        color: #c2410c;
    }

    /* ── Responsive ── */
    @media (max-width: 520px) {
        .cc-wrap {
            padding: 20px 16px;
        }

        .cc-guest-fields {
            grid-template-columns: 1fr;
        }

        .cc-replies {
            margin-left: 36px;
            padding-left: 24px;
        }

        .cc-elbow {
            left: -24px;
            width: 18px;
        }

        .cc-replies > .cc-comment:last-child::after {
            left: -26px;
        }
    }
</style>

<div class="cc-wrap" data-csrf="{{ csrf_token() }}">
    {{-- Comment form --}}
    <div style="margin-bottom: 4px;">
        @auth
            @include('comments::comments._form')
        @elseif(Config::get('comments.guest_commenting'))
            @include('comments::comments._form', ['guest_commenting' => true])
        @else
            <div class="cc-auth-banner">
                <p class="cc-auth-title">@lang('comments::comments.authentication_required')</p>
                <p class="cc-auth-sub">@lang('comments::comments.you_must_login_to_post_a_comment')</p>
                <a href="{{ route('login') }}" class="cc-auth-link">@lang('comments::comments.log_in')</a>
            </div>
        @endauth
    </div>

    <hr class="cc-divider">

    {{-- Header --}}
    <div class="cc-header">
        <div class="cc-header-left">
            <h2 class="cc-title">Comments</h2>
            <span class="cc-count-badge">{{ $allComments->count() }}</span>
        </div>
        <button type="button" class="cc-sort-btn" aria-label="Sort comments">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
            </svg>
            {{ $configSort === 'oldest' ? 'Oldest first' : 'Most recent' }}
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
    </div>

    @if($allComments->isEmpty())
        <div class="cc-empty">@lang('comments::comments.there_are_no_comments')</div>
    @endif

    @php
        $allComments = $configSort === 'oldest'
            ? $allComments->sortBy('created_at')
            : $allComments->sortByDesc('created_at');

        if (isset($perPage)) {
            $page             = (int) request()->query('page', 1) - 1;
            $parentComments   = $allComments->whereStrict('child_id', null);
            $slicedParents    = $parentComments->slice($page * $perPage, $perPage);
            $replies          = $allComments->whereNotNull('child_id');

            $grouped_comments = new LengthAwarePaginator(
                $slicedParents->merge($replies)->groupBy('child_id'),
                $parentComments->count(),
                $perPage
            );
            $grouped_comments->withPath(request()->url());
        } else {
            $grouped_comments = $allComments->groupBy('child_id');
        }
    @endphp

    <div class="cc-list">
        @foreach($grouped_comments as $comment_id => $comments)
            @if(empty($comment_id))
                @foreach($comments as $comment)
                    @include('comments::comments._comment', [
                        'comment'             => $comment,
                        'grouped_comments'    => $grouped_comments,
                        'indentationLevel'    => 0,
                        'maxIndentationLevel' => $configMaxDepth,
                        'reactionsEnabled'    => $reactionsEnabled,
                        'reactionTypes'       => $reactionTypes,
                    ])
                @endforeach
            @endif
        @endforeach
    </div>

    @isset($perPage)
        @if($grouped_comments->hasPages() && $grouped_comments->hasMorePages())
            <div class="cc-show-more">
                <a href="{{ $grouped_comments->nextPageUrl() }}" class="cc-show-more-link">
                    Show more
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </a>
            </div>
        @endif
    @endisset
</div>

<script>
    (function () {
        'use strict';

        // ── Dropdown toggle ───────────────────────────────────────────────────
        function closeAllDropdowns(except) {
            document.querySelectorAll('.cc-dropdown.cc-open').forEach(function (d) {
                if (d !== except) d.classList.remove('cc-open');
            });
        }

        document.addEventListener('click', function (e) {
            var moreBtn = e.target.closest('.cc-more-btn');
            if (moreBtn) {
                var dropdown = moreBtn.closest('.cc-dropdown');
                var isOpen = dropdown.classList.contains('cc-open');
                closeAllDropdowns(null);
                if (!isOpen) dropdown.classList.add('cc-open');
                e.stopPropagation();
                return;
            }
            closeAllDropdowns(null);
        });

        // ── Inline form toggle (reply / edit) ─────────────────────────────────
        document.addEventListener('click', function (e) {
            var trigger = e.target.closest('[data-cc-toggle]');
            if (!trigger) return;

            var targetId = trigger.getAttribute('data-cc-toggle');
            var form = document.getElementById(targetId);
            if (!form) return;

            var isOpen = form.classList.contains('cc-open');

            document.querySelectorAll('.cc-inline-form.cc-open').forEach(function (f) {
                if (f !== form) f.classList.remove('cc-open');
            });

            if (isOpen) {
                form.classList.remove('cc-open');
            } else {
                form.classList.add('cc-open');
                var ta = form.querySelector('textarea');
                if (ta) {
                    ta.focus();
                }
            }
        });

        // ── Cancel button ─────────────────────────────────────────────────────
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-cc-close]');
            if (!btn) return;
            btn.closest('.cc-inline-form').classList.remove('cc-open');
        });

        // ── Like / Dislike reactions ───────────────────────────────────────────
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.cc-react-btn');
            if (!btn) return;

            var commentId = btn.dataset.comment;
            var type = btn.dataset.type;
            var wrap = document.querySelector('.cc-wrap');
            var csrf = wrap ? wrap.dataset.csrf : '';

            // Optimistic UI — disable buttons while request is in flight
            var commentEl = document.getElementById('comment-' + commentId);
            var allReactBtns = commentEl
                ? commentEl.querySelectorAll('.cc-react-btn')
                : [];
            allReactBtns.forEach(function (b) {
                b.disabled = true;
            });

            fetch('/comments/' + commentId + '/react', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({type: type}),
            })
                .then(function (res) {
                    if (res.status === 401) {
                        window.location.href = '/login';
                        return null;
                    }
                    return res.json();
                })
                .then(function (data) {
                    if (!data || !commentEl) return;

                    // Update every reaction button dynamically — works for any configured types
                    commentEl.querySelectorAll('.cc-react-btn').forEach(function (btn) {
                        var t = btn.dataset.type;
                        btn.querySelector('.cc-reaction-count').textContent =
                            (data.reactions && data.reactions[t] !== undefined) ? data.reactions[t] : 0;
                        btn.classList.toggle('cc-reaction-active', data.user_reaction === t);
                    });
                })
                .catch(function () { /* silently ignore network errors */
                })
                .finally(function () {
                    allReactBtns.forEach(function (b) {
                        b.disabled = false;
                    });
                });
        });
    })();
</script>
