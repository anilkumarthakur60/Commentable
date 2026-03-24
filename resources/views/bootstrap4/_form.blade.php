{{-- Bootstrap 4 comment form --}}
<div class="card">
    <div class="card-body">
        <h5 class="card-title">@lang('comments::comments.leave_a_comment')</h5>

        @if($errors->has('commentable_type') || $errors->has('commentable_id'))
            <div class="alert alert-danger">
                {{ $errors->first('commentable_type') }}
                {{ $errors->first('commentable_id') }}
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('comments.store') }}">
            @csrf
            @honeypot
            <input type="hidden" name="commentable_type" value="{{ get_class($model) }}" />
            <input type="hidden" name="commentable_id"   value="{{ $model->getKey() }}" />

            {{-- Guest fields --}}
            @if(isset($guest_commenting) && $guest_commenting)
                <div class="form-group">
                    <label for="guest_name">@lang('comments::comments.enter_your_name_here')</label>
                    <input type="text"
                           id="guest_name"
                           name="guest_name"
                           value="{{ old('guest_name') }}"
                           class="form-control{{ $errors->has('guest_name') ? ' is-invalid' : '' }}"
                           required />
                    @error('guest_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="guest_email">@lang('comments::comments.enter_your_email_here')</label>
                    <input type="email"
                           id="guest_email"
                           name="guest_email"
                           value="{{ old('guest_email') }}"
                           class="form-control{{ $errors->has('guest_email') ? ' is-invalid' : '' }}"
                           required />
                    @error('guest_email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            @endif

            <div class="form-group">
                <label for="comment-message">@lang('comments::comments.enter_your_message_here')</label>
                <textarea id="comment-message"
                          name="message"
                          rows="4"
                          class="form-control{{ $errors->has('message') ? ' is-invalid' : '' }}"
                          required>{{ old('message') }}</textarea>
                @error('message')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">
                    @lang('comments::comments.markdown_cheatsheet', ['url' => 'https://docs.github.com/en/get-started/writing-on-github/getting-started-with-writing-and-formatting-on-github/basic-writing-and-formatting-syntax'])
                </small>
            </div>

            <button type="submit" class="btn btn-primary btn-sm text-uppercase">
                @lang('comments::comments.submit')
            </button>
        </form>
    </div>
</div>
