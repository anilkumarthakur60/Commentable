{{-- Tailwind CSS comment form (no JS framework required) --}}
<div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    <h3 class="mb-4 text-base font-semibold text-gray-800">@lang('comments::comments.leave_a_comment')</h3>

    @if($errors->has('commentable_type') || $errors->has('commentable_id'))
        <div class="mb-4 rounded-md border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $errors->first('commentable_type') }}
            {{ $errors->first('commentable_id') }}
        </div>
    @endif

    @if(session('success'))
        <div class="mb-4 rounded-md border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('comments.store') }}" class="space-y-4">
        @csrf
        @honeypot
        <input type="hidden" name="commentable_type" value="{{ get_class($model) }}" />
        <input type="hidden" name="commentable_id"   value="{{ $model->getKey() }}" />

        {{-- Guest fields --}}
        @if(isset($guest_commenting) && $guest_commenting)
            <div>
                <label for="guest_name" class="block text-sm font-medium text-gray-700">
                    @lang('comments::comments.enter_your_name_here')
                </label>
                <input type="text"
                       id="guest_name"
                       name="guest_name"
                       value="{{ old('guest_name') }}"
                       required
                       class="mt-1 block w-full rounded-md border {{ $errors->has('guest_name') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                @error('guest_name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="guest_email" class="block text-sm font-medium text-gray-700">
                    @lang('comments::comments.enter_your_email_here')
                </label>
                <input type="email"
                       id="guest_email"
                       name="guest_email"
                       value="{{ old('guest_email') }}"
                       required
                       class="mt-1 block w-full rounded-md border {{ $errors->has('guest_email') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                @error('guest_email')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        @endif

        <div>
            <label for="comment-message" class="block text-sm font-medium text-gray-700">
                @lang('comments::comments.enter_your_message_here')
            </label>
            <textarea id="comment-message"
                      name="message"
                      rows="4"
                      required
                      class="mt-1 block w-full rounded-md border {{ $errors->has('message') ? 'border-red-500' : 'border-gray-300' }} px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">{{ old('message') }}</textarea>
            @error('message')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-gray-500">
                @lang('comments::comments.markdown_cheatsheet', ['url' => 'https://docs.github.com/en/get-started/writing-on-github/getting-started-with-writing-and-formatting-on-github/basic-writing-and-formatting-syntax'])
            </p>
        </div>

        <div>
            <button type="submit"
                    class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium uppercase tracking-wide text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                @lang('comments::comments.submit')
            </button>
        </div>
    </form>
</div>
