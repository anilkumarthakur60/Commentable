@php
/** @var \Anil\Comments\Comment $comment */
$markdownParser = new Parsedown();
$markdownParser->setSafeMode(true);

$currentLevel = $indentationLevel ?? 0;
$maxLevel     = $maxIndentationLevel ?? 3;
$nextLevel    = $currentLevel + 1;
$commentKey   = $comment->getKey();
@endphp

{{-- Comment card --}}
<div id="comment-{{ $commentKey }}" class="flex gap-3">
    <img src="{{ $comment->getAvatarUrl(40) }}"
         alt="{{ $comment->getAuthorName() }}"
         class="h-10 w-10 flex-shrink-0 rounded-full object-cover"
         width="40"
         height="40">

    <div class="flex-1 min-w-0">
        <div class="rounded-lg border border-gray-200 bg-white px-4 py-3 shadow-sm">
            <div class="flex items-center gap-2 text-sm">
                <span class="font-semibold text-gray-800">{{ $comment->getAuthorName() }}</span>
                <span class="text-gray-400">&middot;</span>
                <span class="text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                @if(!$comment->approved)
                    <span class="rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-800">
                        @lang('comments::comments.pending_approval')
                    </span>
                @endif
            </div>

            <div class="mt-1 text-sm text-gray-700" style="white-space: pre-wrap;">
                {!! $markdownParser->line($comment->comment) !!}
            </div>

            <div class="mt-2 flex items-center gap-3">
                @can('reply-to-comment', $comment)
                    <button type="button"
                            onclick="document.getElementById('reply-dialog-{{ $commentKey }}').showModal()"
                            class="text-xs font-medium uppercase tracking-wide text-blue-600 hover:text-blue-800 focus:outline-none">
                        @lang('comments::comments.reply')
                    </button>
                @endcan
                @can('edit-comment', $comment)
                    <button type="button"
                            onclick="document.getElementById('edit-dialog-{{ $commentKey }}').showModal()"
                            class="text-xs font-medium uppercase tracking-wide text-gray-500 hover:text-gray-700 focus:outline-none">
                        @lang('comments::comments.edit')
                    </button>
                @endcan
                @can('delete-comment', $comment)
                    <form action="{{ route('comments.destroy', $commentKey) }}"
                          method="POST"
                          class="inline"
                          onsubmit="return confirm('@lang('comments::comments.confirm_delete')')">
                        @method('DELETE')
                        @csrf
                        <button type="submit"
                                class="text-xs font-medium uppercase tracking-wide text-red-500 hover:text-red-700 focus:outline-none">
                            @lang('comments::comments.delete')
                        </button>
                    </form>
                @endcan
            </div>
        </div>

        {{-- Nested replies --}}
        @if($grouped_comments->has($commentKey))
            <div class="mt-3 space-y-3 {{ $currentLevel < $maxLevel ? 'border-l-2 border-gray-100 pl-4' : '' }}">
                @foreach($grouped_comments[$commentKey] as $child)
                    @include('comments::tailwind._comment', [
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

{{--
  Modals use native HTML5 <dialog> — no JavaScript framework needed.
  The browser provides scroll-lock and backdrop automatically.
--}}

{{-- Edit dialog --}}
@can('edit-comment', $comment)
<dialog id="edit-dialog-{{ $commentKey }}"
        class="w-full max-w-lg rounded-xl p-0 shadow-xl backdrop:bg-black/40 open:flex open:flex-col">
    <form method="POST" action="{{ route('comments.update', $commentKey) }}" class="flex flex-col">
        @method('PUT')
        @csrf
        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
            <h2 class="text-base font-semibold text-gray-800">@lang('comments::comments.edit_comment')</h2>
            <button type="button"
                    onclick="this.closest('dialog').close()"
                    class="rounded-md p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 focus:outline-none">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
        <div class="px-5 py-4">
            <label class="block text-sm font-medium text-gray-700">
                @lang('comments::comments.update_your_message_here')
            </label>
            <textarea name="message"
                      rows="5"
                      required
                      class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">{{ $comment->comment }}</textarea>
            <p class="mt-1 text-xs text-gray-500">
                @lang('comments::comments.markdown_cheatsheet', ['url' => 'https://docs.github.com/en/get-started/writing-on-github/getting-started-with-writing-and-formatting-on-github/basic-writing-and-formatting-syntax'])
            </p>
        </div>
        <div class="flex justify-end gap-2 border-t border-gray-200 px-5 py-3">
            <button type="button"
                    onclick="this.closest('dialog').close()"
                    class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400">
                @lang('comments::comments.cancel')
            </button>
            <button type="submit"
                    class="rounded-md bg-green-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                @lang('comments::comments.update')
            </button>
        </div>
    </form>
</dialog>
@endcan

{{-- Reply dialog --}}
@can('reply-to-comment', $comment)
<dialog id="reply-dialog-{{ $commentKey }}"
        class="w-full max-w-lg rounded-xl p-0 shadow-xl backdrop:bg-black/40 open:flex open:flex-col">
    <form method="POST" action="{{ route('comments.reply', $commentKey) }}" class="flex flex-col">
        @csrf
        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
            <h2 class="text-base font-semibold text-gray-800">@lang('comments::comments.reply_to_comment')</h2>
            <button type="button"
                    onclick="this.closest('dialog').close()"
                    class="rounded-md p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 focus:outline-none">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
        <div class="px-5 py-4">
            <p class="mb-2 text-xs text-gray-500">
                @lang('comments::comments.replying_to', ['name' => $comment->getAuthorName()])
            </p>
            <label class="block text-sm font-medium text-gray-700">
                @lang('comments::comments.enter_your_message_here')
            </label>
            <textarea name="message"
                      rows="5"
                      required
                      class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"></textarea>
            <p class="mt-1 text-xs text-gray-500">
                @lang('comments::comments.markdown_cheatsheet', ['url' => 'https://docs.github.com/en/get-started/writing-on-github/getting-started-with-writing-and-formatting-on-github/basic-writing-and-formatting-syntax'])
            </p>
        </div>
        <div class="flex justify-end gap-2 border-t border-gray-200 px-5 py-3">
            <button type="button"
                    onclick="this.closest('dialog').close()"
                    class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400">
                @lang('comments::comments.cancel')
            </button>
            <button type="submit"
                    class="rounded-md bg-green-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                @lang('comments::comments.reply')
            </button>
        </div>
    </form>
</dialog>
@endcan
