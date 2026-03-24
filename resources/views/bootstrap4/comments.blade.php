@php
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;

/** @var \Illuminate\Database\Eloquent\Model $model */
$allComments = (isset($approved) && $approved === true)
    ? $model->approvedComments()->get()
    : $model->comments()->get();
@endphp

@if($allComments->isEmpty())
    <div class="alert alert-warning">@lang('comments::comments.there_are_no_comments')</div>
@endif

<div id="comments-section">
    @php
    $allComments = $allComments->sortBy('created_at');

    if (isset($perPage)) {
        $page = (int) request()->query('page', 1) - 1;
        $parentComments = $allComments->whereStrict('child_id', null);
        $slicedParents  = $parentComments->slice($page * $perPage, $perPage);
        $replies        = $allComments->whereNotNull('child_id');

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

    @foreach($grouped_comments as $comment_id => $comments)
        @if(empty($comment_id))
            @foreach($comments as $comment)
                @include('comments::bootstrap4._comment', [
                    'comment'             => $comment,
                    'grouped_comments'    => $grouped_comments,
                    'indentationLevel'    => 0,
                    'maxIndentationLevel' => $maxIndentationLevel ?? 3,
                ])
            @endforeach
        @endif
    @endforeach
</div>

@isset($perPage)
    {{ $grouped_comments->links() }}
@endisset

<div class="mt-4">
    @auth
        @include('comments::bootstrap4._form')
    @elseif(Config::get('comments.guest_commenting'))
        @include('comments::bootstrap4._form', ['guest_commenting' => true])
    @else
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title">@lang('comments::comments.authentication_required')</h5>
                <p class="card-text text-muted">@lang('comments::comments.you_must_login_to_post_a_comment')</p>
                <a href="{{ route('login') }}" class="btn btn-primary btn-sm">@lang('comments::comments.log_in')</a>
            </div>
        </div>
    @endauth
</div>
