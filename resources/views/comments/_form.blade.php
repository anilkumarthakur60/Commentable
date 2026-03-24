<div class="cc-form-box">
    <form method="POST" action="{{ route('comments.store') }}" style="margin:0;">
        @csrf
        @honeypot
        <input type="hidden" name="commentable_type" value="{{ get_class($model) }}">
        <input type="hidden" name="commentable_id" value="{{ $model->getKey() }}">

        @if (isset($guest_commenting) && $guest_commenting === true)
            <div class="cc-guest-fields">
                <input type="text" class="cc-input" name="guest_name" value="{{ old('guest_name') }}"
                    placeholder="Name" required>
                <input type="email" class="cc-input" name="guest_email" value="{{ old('guest_email') }}"
                    placeholder="Email" required>
            </div>
            @if ($errors->has('guest_name') || $errors->has('guest_email'))
                <div class="cc-error" style="margin-bottom:10px;">
                    {{ $errors->first('guest_name') }} {{ $errors->first('guest_email') }}
                </div>
            @endif
        @endif

        <textarea class="cc-textarea" name="message" rows="2" placeholder="Add comment..." required></textarea>

        @error('message')
            <div class="cc-error" style="margin-bottom:8px;">{{ $message }}</div>
        @enderror

        <div class="cc-toolbar">
            <button type="submit" class="cc-submit-btn">Submit</button>
        </div>
    </form>
</div>
