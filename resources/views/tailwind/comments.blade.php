@php
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;

/** @var \Illuminate\Database\Eloquent\Model $model */
$allComments = (isset($approved) && $approved === true)
    ? $model->approvedComments()->get()
    : $model->comments()->get();
@endphp

@if($allComments->isEmpty())
    <div class="rounded-md border border-yellow-300 bg-yellow-50 px-4 py-3 text-yellow-800 text-sm">
        @lang('comments::comments.there_are_no_comments')
    </div>
@endif

<div id="comments-section" class="space-y-4">
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
                @include('comments::tailwind._comment', [
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
    <div class="mt-4">
        {{ $grouped_comments->links() }}
    </div>
@endisset

<div class="mt-6">
    @auth
        @include('comments::tailwind._form')
    @elseif(Config::get('comments.guest_commenting'))
        @include('comments::tailwind._form', ['guest_commenting' => true])
    @else
        <div class="rounded-lg border border-gray-200 bg-gray-50 px-6 py-5 text-center">
            <h3 class="text-base font-semibold text-gray-800">@lang('comments::comments.authentication_required')</h3>
            <p class="mt-1 text-sm text-gray-500">@lang('comments::comments.you_must_login_to_post_a_comment')</p>
            <a href="{{ route('login') }}"
               class="mt-3 inline-block rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @lang('comments::comments.log_in')
            </a>
        </div>
    @endauth
</div>
