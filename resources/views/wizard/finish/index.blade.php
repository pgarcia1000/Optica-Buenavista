@extends('layouts.wizard')

@section('title', trans('general.wizard'))

@section('content')
    <div class="card">
        @include('partials.wizard.steps')

        <div class="card-body bg-default">
            <div class="row">
                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                    <div class="content-header">
                        <h3 class="text-white">{{ trans('modules.recommended_apps') }}</h3>
                    </div>

                    @if ($modules)
                        <div class="row">
                            @foreach ($modules->data as $module)
                                @include('partials.modules.item')
                            @endforeach
                        </div>

                        <div class="col-md-12">
                            <ul>
                                @if ($modules->current_page < $modules->last_page)
                                    <li class="next"><a href="{{ url(request()->path()) }}?page={{ $modules->current_page + 1 }}" class="btn btn-default btn-sm">{!! trans('pagination.next') !!}</a></li>
                                @endif
                                @if ($modules->current_page > 1)
                                    <li class="previous"><a href="{{ url(request()->path()) }}?page={{ $modules->current_page - 1 }}" class="btn btn-default btn-sm">{{ trans('pagination.previous') }}</a></li>
                                @endif
                            </ul>
                        </div>
                    @else
                        <div class="card">
                            <div class="card-body">
                                <p class="col-md-12">
                                    {{ trans('modules.no_apps') }}
                                </p>

                                <p class="col-md-12">
                                    <small>{!! trans('modules.developer') !!}</small>
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="card-footer">
            <div class="row">
                <div class="col-sm-6">
                    <a href="{{ route('wizard.taxes.index') }}" class="btn btn-icon btn-white">
                        <span class="btn-inner--text">{{ trans('pagination.previous') }}</span>
                    </a>
                </div>

                <div class="col-sm-6 text-right">
                    <a href="{{ route('dashboard') }}" id="wizard-skip" class="btn btn-icon btn-success">
                        <span class="btn-inner--text">{{ trans('general.go_to_dashboard') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts_start')
    <script src="{{ asset('public/js/wizard/finish.js?v=' . version('short')) }}"></script>
@endpush
