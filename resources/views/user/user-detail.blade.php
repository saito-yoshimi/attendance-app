@extends('layouts.app')

@section('css')
    @vite('resources/css/user/user-detail.css')
@endsection

@section('content')

<div class="detail__content">

    <div class="detail__header">
        <h1 class="content__header--item">勤怠詳細</h1>
    </div>

    <form
    class="form"
        method="POST"
        action="{{ route('stamp_correction_request.store') }}"
    >
    @csrf

        <input
            type="hidden"
            name="attendance_record_id"
            value="{{ $data['id'] }}"
        >

        <div class="form__content">

            {{-- 名前 --}}
            <div class="form__group">
                <label class="form__header">名前</label>

                <div class="form__input-group">
                    <input
                        class="form__input form__input--name"
                        type="text"
                        value="{{ $user->name }}"
                        readonly
                    >
                </div>
            </div>

            {{-- 日付 --}}
            <div class="form__group">
                <label class="form__header">日付</label>

                <div class="form__input-group">
                    <input
                        class="form__input form__input--date"
                        type="text"
                        value="{{ $data['year'] }}"
                        readonly
                    >

                    <input
                        class="form__input form__input--date"
                        type="text"
                        value="{{ $data['date'] }}"
                        readonly
                    >
                </div>
            </div>

            {{-- 出勤・退勤 --}}
            <div class="form__group">
                <label class="form__header">
                    出勤・退勤
                </label>

                <div class="form__input-group">

                    <input
                        class="form__input"
                        type="text"
                        name="new_clock_in"
                        value="{{ old('new_clock_in', $data['clock_in']) }}"
                        
                    >

                    <p>〜</p>

                    <input
                        class="form__input"
                        type="text"
                        name="new_clock_out"
                        value="{{ old('new_clock_out', $data['clock_out']) }}"
                    >

                </div>
            </div>

            <div class="error-message">
                <div></div>

                <div class="error-message__item">

                    @error('new_clock_in')
                        <p>{{ $message }}</p>
                    @enderror

                    @error('new_clock_out')
                        <p>{{ $message }}</p>
                    @enderror

                </div>
            </div>

            {{-- 休憩 --}}
            @foreach($data['breaks'] as $index => $break)

                <div class="form__group">

                    <label class="form__header">
                        {{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}
                    </label>

                    <div class="form__input-group">

                        <input
                            class="form__input"
                            type="text"
                            name="new_break_in[{{ $index }}]"
                            value="{{ old('new_break_in.' . $index, $break['break_in']) }}"
                        >

                        <p>〜</p>

                        <input
                            class="form__input"
                            type="text"
                            name="new_break_out[{{ $index }}]"
                            value="{{ old('new_break_out.' . $index, $break['break_out']) }}"
                        >

                    </div>

                </div>

                <div class="error-message">
                    <div></div>

                    <div class="error-message__item">

                        @error('new_break_in.' . $index)
                            <p>{{ $message }}</p>
                        @enderror

                        @error('new_break_out.' . $index)
                            <p>{{ $message }}</p>
                        @enderror

                    </div>

                </div>

            @endforeach

            @php
                $newBreakIndex = count($data['breaks']);
            @endphp

            {{-- 追加休憩 --}}
            <div class="form__group">

                <label class="form__header">
                    {{ $newBreakIndex === 0 ? '休憩' : '休憩' . ($newBreakIndex + 1) }}
                </label>

                <div class="form__input-group">

                    <input
                        class="form__input"
                        type="text"
                        name="new_break_in[{{ $newBreakIndex }}]"
                        value=""
                    >

                    <p>〜</p>

                    <input
                        class="form__input"
                        type="text"
                        name="new_break_out[{{ $newBreakIndex }}]"
                        value=""
                    >

                </div>

            </div>

            {{-- 備考 --}}
            <div class="form__group">

                <label class="form__header">
                    備考
                </label>

                <div class="form__input-group">
                    <textarea
                        class="form__textarea"
                        name="comment"
                        >{{ old('comment', $data['comment']) }}
                    </textarea>
                </div>

            </div>

            <div class="error-message">
                <div></div>

                <div class="error-message__item">

                    @error('comment')
                        <p>{{ $message }}</p>
                    @enderror

                </div>
            </div>

        </div>

        <div class="form__button">

            @if($hasPendingRequest)

                <p class="readonly-message">
                    承認待ちのため修正はできません。
                </p>

            @else

                <button
                    class="form__button--submit"
                    type="submit"
                >
                    修正
                </button>

            @endif

        </div>

    </form>

</div>

@endsection