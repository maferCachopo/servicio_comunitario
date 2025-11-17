@extends('layouts.app')

@section('content')
<div class="container">
    <h1>🔍 Loan Section Debug Test</h1>
    
    @if(Auth::check())
        <div class="alert alert-info">
            <h5>Current User Information:</h5>
            <p><strong>Name:</strong> {{ Auth::user()->name }}</p>
            <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
            <p><strong>Role:</strong> {{ Auth::user()->role }}</p>
            <p><strong>ID:</strong> {{ Auth::user()->id }}</p>
        </div>
        
        <div class="alert {{ (Auth::user()->role == 'loan_user' && Auth::user()->role != 'admin') ? 'alert-success' : 'alert-warning' }}">
            <h5>Loan Section Visibility:</h5>
            <p><strong>Is Loan User:</strong> {{ Auth::user()->role == 'loan_user' ? '✅ Yes' : '❌ No' }}</p>
            <p><strong>Is NOT Admin:</strong> {{ Auth::user()->role != 'admin' ? '✅ Yes' : '❌ No' }}</p>
            <p><strong>Should Show Loan Section:</strong> 
                <span class="badge {{ (Auth::user()->role == 'loan_user' && Auth::user()->role != 'admin') ? 'bg-success' : 'bg-danger' }}">
                    {{ (Auth::user()->role == 'loan_user' && Auth::user()->role != 'admin') ? '✅ YES' : '❌ NO' }}
                </span>
            </p>
        </div>
        
        @if(Auth::user()->role == 'loan_user' && Auth::user()->role != 'admin')
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5>✅ Loan Request Section (Should be visible)</h5>
                </div>
                <div class="card-body">
                    <p>This section should appear for loan users who are not administrators.</p>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#loanRequestModal">
                        <i class="fas fa-hand-paper me-2"></i>Test Loan Request Modal
                    </button>
                </div>
            </div>
            
            <!-- Include the loan request modal -->
            @include('profile.partials.loan_request_modal')
        @else
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5>⚠️ Loan Section Hidden</h5>
                </div>
                <div class="card-body">
                    <p>This section is hidden because:</p>
                    <ul>
                        @if(Auth::user()->role != 'loan_user')
                            <li>User role is not 'loan_user' (current: {{ Auth::user()->role }})</li>
                        @endif
                        @if(Auth::user()->role == 'admin')
                            <li>User is an administrator</li>
                        @endif
                    </ul>
                </div>
            </div>
        @endif
        
    @else
        <div class="alert alert-danger">
            <h5>❌ Not Authenticated</h5>
            <p>Please <a href="{{ route('login') }}">login</a> to test the loan features.</p>
        </div>
    @endif
    
    <div class="mt-4">
        <h5>Quick Actions:</h5>
        <a href="{{ route('profile.show') }}" class="btn btn-info">
            <i class="fas fa-user me-2"></i>Go to Profile Page
        </a>
        <a href="{{ route('home') }}" class="btn btn-secondary">
            <i class="fas fa-home me-2"></i>Go to Dashboard
        </a>
    </div>
</div>
@endsection