<div class="cc-form-box">
    <form method="POST" action="{{ route('comments.store') }}" style="margin:0;">
        @csrf
        @honeypot
        <input type="hidden" name="commentable_type" value="\{{ get_class($model) }}">
        <input type="hidden" name="commentable_id"   value="{{ $model->getKey() }}">

        @if(isset($guest_commenting) && $guest_commenting === true)
            <div class="cc-guest-fields">
                <input
                    type="text"
                    class="cc-input"
                    name="guest_name"
                    value="{{ old('guest_name') }}"
                    placeholder="Name"
                    required
                >
                <input
                    type="email"
                    class="cc-input"
                    name="guest_email"
                    value="{{ old('guest_email') }}"
                    placeholder="Email"
                    required
                >
            </div>
            @if($errors->has('guest_name') || $errors->has('guest_email'))
                <div class="cc-error" style="margin-bottom:10px;">
                    {{ $errors->first('guest_name') }} {{ $errors->first('guest_email') }}
                </div>
            @endif
        @endif

        <textarea
            class="cc-textarea"
            name="message"
            rows="2"
            placeholder="Add comment..."
            required
        ></textarea>

        @error('message')
            <div class="cc-error" style="margin-bottom:8px;">{{ $message }}</div>
        @enderror

        <div class="cc-toolbar">
            <div class="cc-toolbar-left">
                <button type="button" class="cc-tool-btn" title="Attach file">
                    <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                </button>
                <button type="button" class="cc-tool-btn" title="Insert image">
                    <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </button>
                <button type="button" class="cc-tool-btn" title="Emoji">
                    <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </button>
            </div>

            <button type="submit" class="cc-submit-btn">Submit</button>
        </div>
    </form>
</div>
