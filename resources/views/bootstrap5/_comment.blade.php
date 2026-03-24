@php
/** @var \Anil\Comments\Comment $comment */
$markdownParser = new Parsedown();
$markdownParser->setSafeMode(true);

$currentLevel = $indentationLevel ?? 0;
$maxLevel     = $maxIndentationLevel ?? 3;
$nextLevel    = $currentLevel + 1;
$commentKey   = $comment->getKey();
@endphp

<div id="comment-{{ $commentKey }}" class="d-flex mb-3">
    <img class="rounded-circle flex-shrink-0 me-3"
         src="{{ $comment->getAvatarUrl(48) }}"
         alt="{{ $comment->getAuthorName() }}"
         width="48"
         height="48">
    <div class="flex-grow-1">
        <div class="card">
            <div class="card-body py-2 px-3">
                <p class="card-subtitle mb-1 text-muted small">
                    <strong class="text-dark">{{ $comment->getAuthorName() }}</strong>
                    &nbsp;&middot;&nbsp;
                    {{ $comment->created_at->diffForHumans() }}
                    @if(!$comment->approved)
                        <span class="badge bg-warning text-dark ms-1">@lang('comments::comments.pending_approval')</span>
                    @endif
                </p>
                <div class="card-text" style="white-space: pre-wrap;">{!! $markdownParser->line($comment->comment) !!}</div>

                <div class="mt-2 d-flex gap-2">
                    @can('reply-to-comment', $comment)
                        <button type="button"
                                class="btn btn-link btn-sm p-0 text-uppercase"
                                data-bs-toggle="modal"
                                data-bs-target="#reply-modal-{{ $commentKey }}">
                            @lang('comments::comments.reply')
                        </button>
                    @endcan
                    @can('edit-comment', $comment)
                        <button type="button"
                                class="btn btn-link btn-sm p-0 text-uppercase"
                                data-bs-toggle="modal"
                                data-bs-target="#edit-modal-{{ $commentKey }}">
                            @lang('comments::comments.edit')
                        </button>
                    @endcan
                    @can('delete-comment', $comment)
                        <form action="{{ route('comments.destroy', $commentKey) }}"
                              method="POST"
                              class="d-inline"
                              onsubmit="return confirm('@lang('comments::comments.confirm_delete')')">
                            @method('DELETE')
                            @csrf
                            <button type="submit" class="btn btn-link btn-sm p-0 text-danger text-uppercase">
                                @lang('comments::comments.delete')
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>

        {{-- Nested replies --}}
        @if($grouped_comments->has($commentKey))
            <div class="{{ $currentLevel < $maxLevel ? 'ms-4 ps-2 border-start' : '' }} mt-2">
                @foreach($grouped_comments[$commentKey] as $child)
                    @include('comments::bootstrap5._comment', [
                        'comment'             => $child,
                        'grouped_comments'    => $grouped_comments,
                        'indentationLevel'    => $nextLevel,
                        'maxIndentationLevel' => $maxLevel,
                    ])
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- Edit modal (Bootstrap 5) --}}
@can('edit-comment', $comment)
<div class="modal fade" id="edit-modal-{{ $commentKey }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('comments.update', $commentKey) }}">
                @method('PUT')
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">@lang('comments::comments.edit_comment')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">@lang('comments::comments.update_your_message_here')</label>
                        <textarea class="form-control" name="message" rows="4" required>{{ $comment->comment }}</textarea>
                        <div class="form-text">
                            @lang('comments::comments.markdown_cheatsheet', ['url' => 'https://docs.github.com/en/get-started/writing-on-github/getting-started-with-writing-and-formatting-on-github/basic-writing-and-formatting-syntax'])
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                        @lang('comments::comments.cancel')
                    </button>
                    <button type="submit" class="btn btn-sm btn-success">
                        @lang('comments::comments.update')
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

{{-- Reply modal (Bootstrap 5) --}}
@can('reply-to-comment', $comment)
<div class="modal fade" id="reply-modal-{{ $commentKey }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('comments.reply', $commentKey) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">@lang('comments::comments.reply_to_comment')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">
                        @lang('comments::comments.replying_to', ['name' => $comment->getAuthorName()])
                    </p>
                    <div class="mb-3">
                        <label class="form-label">@lang('comments::comments.enter_your_message_here')</label>
                        <textarea class="form-control" name="message" rows="4" required></textarea>
                        <div class="form-text">
                            @lang('comments::comments.markdown_cheatsheet', ['url' => 'https://docs.github.com/en/get-started/writing-on-github/getting-started-with-writing-and-formatting-on-github/basic-writing-and-formatting-syntax'])
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                        @lang('comments::comments.cancel')
                    </button>
                    <button type="submit" class="btn btn-sm btn-success">
                        @lang('comments::comments.reply')
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
