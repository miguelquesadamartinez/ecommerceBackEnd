@extends('errors::minimal')

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-danger">{{ __('Error occurred in the application') }}</h6>
        </div>
        <div class="card-body">
            <p>{{ __('An email has been sent to IT for solving the issue') }}.</p>
        </div>
    </div>

