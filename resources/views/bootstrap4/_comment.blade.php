@php
/** @var \Anil\Comments\Comment $comment */
$markdownParser = new Parsedown();
$markdownParser->setSafeMode(true);

$currentLevel = $indentationLevel ?? 0;
$maxLevel     = $maxIndentationLevel ?? 3;
$nextLevel    = $currentLevel + 1;
$commentKey   = $comment->getKey();
@endphp

<div id="comment-{{ $commentKey }}" class="media mb-3">
    <img class="mr-3 rounded-circle"
         src="{{ $comment->getAvatarUrl(48) }}"
         alt="{{ $comment->getAuthorName() }}"
         width="48"
         height="48">
    <div class="media-body">
        <div class="card">
            <div class="card-body py-2 px-3">
                <h6 class="card-subtitle mb-1 text-muted">
                    <strong class="text-dark">{{ $comment->getAuthorName() }}</strong>
                    &nbsp;&middot;&nbsp;
                    <small>{{ $comment->created_at->diffForHumans() }}</small>
                    @if(!$comment->approved)
                        <span class="badge badge-warning ml-1">@lang('comments::comments.pending_approval')</span>
                    @endif
                </h6>
                <div class="card-text" style="white-space: pre-wrap;">{!! $markdownParser->line($comment->comment) !!}</div>

                <div class="mt-2">
                    @can('reply-to-comment', $comment)
                        <button type="button"
                                class="btn btn-link btn-sm p-0 mr-2 text-uppercase"
                                data-toggle="modal"
                                data-target="#reply-modal-{{ $commentKey }}">
                            @lang('comments::comments.reply')
                        </button>
                    @endcan
                    @can('edit-comment', $comment)
                        <button type="button"
                                class="btn btn-link btn-sm p-0 mr-2 text-uppercase"
                                data-toggle="modal"
                                data-target="#edit-modal-{{ $commentKey }}">
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
            <div class="{{ $currentLevel < $maxLevel ? 'ml-4 pl-2 border-left' : '' }} mt-2">
                @foreach($grouped_comments[$commentKey] as $child)
                    @include('comments::bootstrap4._comment', [
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

{{-- Edit modal (Bootstrap 4) --}}
@can('edit-comment', $comment)
<div class="modal fade" id="edit-modal-{{ $commentKey }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('comments.update', $commentKey) }}">
                @method('PUT')
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">@lang('comments::comments.edit_comment')</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>@lang('comments::comments.update_your_message_here')</label>
                        <textarea class="form-control" name="message" rows="4" required>{{ $comment->comment }}</textarea>
                        <small class="form-text text-muted">
                            @lang('comments::comments.markdown_cheatsheet', ['url' => 'https://docs.github.com/en/get-started/writing-on-github/getting-started-with-writing-and-formatting-on-github/basic-writing-and-formatting-syntax'])
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">
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

{{-- Reply modal (Bootstrap 4) --}}
@can('reply-to-comment', $comment)
<div class="modal fade" id="reply-modal-{{ $commentKey }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('comments.reply', $commentKey) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">@lang('comments::comments.reply_to_comment')</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">
                        @lang('comments::comments.replying_to', ['name' => $comment->getAuthorName()])
                    </p>
                    <div class="form-group">
                        <label>@lang('comments::comments.enter_your_message_here')</label>
                        <textarea class="form-control" name="message" rows="4" required></textarea>
                        <small class="form-text text-muted">
                            @lang('comments::comments.markdown_cheatsheet', ['url' => 'https://docs.github.com/en/get-started/writing-on-github/getting-started-with-writing-and-formatting-on-github/basic-writing-and-formatting-syntax'])
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">
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
