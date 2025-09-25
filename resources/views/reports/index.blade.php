@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Reports for {{ $date ?? now()->toDateString() }}</h1>

    {{-- Search + Print Row --}}
    <div class="row mb-4 align-items-center justify-content-between">
        <div class="col-md-6">
            <form action="{{ route('reports.index') }}" method="GET" class="d-flex">
                <input type="date" name="date" value="{{ $date ?? now()->toDateString() }}" class="form-control me-2">
                <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control me-2" placeholder="Search by product, batch, type...">
                <button type="submit" class="btn btn-outline-primary">Filter</button>
            </form>
        </div>
        <div class="col-md-3 text-end">
            <a href="{{ route('reports.print', ['date' => $date ?? now()->toDateString()]) }}" target="_blank" class="btn btn-primary">
                <i class="bi bi-printer"></i> Print Report
            </a>
        </div>
    </div>

    {{-- Totals cards --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-success shadow-sm">
                <div class="card-body text-center">
                    <h5>Stocked In</h5>
                    <h2>{{ $totalStockIn }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-warning shadow-sm">
                <div class="card-body text-center">
                    <h5>Pulled Out</h5>
                    <h2>{{ $totalPulledOut }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-danger shadow-sm">
                <div class="card-body text-center">
                    <h5>Expired</h5>
                    <h2>{{ $totalExpired }}</h2>
                </div>
            </div>
        </div>
    </div>

    {{-- Tables --}}
    @foreach (['validReports' => 'Stocked In', 'pulledOutReports' => 'Pulled Out', 'expiredReports' => 'Expired'] as $var => $title)
        <div class="card shadow-sm mb-4">
            <div class="card-header
                @if($var == 'validReports') bg-success text-white
                @elseif($var == 'pulledOutReports') bg-warning text-dark
                @else bg-danger  @endif">
                {{ $title }}
            </div>
            <div class="card-body table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            @if($var == 'validReports')<th>Value</th>@endif
                            @if($var == 'pulledOutReports')<th>Reason</th>@endif
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($$var as $report)
                            <tr>
                                <td>{{ $report->product->productName }}</td>
                                <td>{{ $report->quantity }}</td>
                                @if($var == 'validReports')
                                    <td>₱{{ number_format($report->quantity * $report->product->price, 2) }}</td>
                                @endif
                                @if($var == 'pulledOutReports')
                                    {{-- Format reason nicely for display --}}
                                    <td>{{ ucwords(str_replace(['pulled_out_', '_'], ['Pulled Out - ', ' '], $report->reason)) }}</td>
                                @endif
                                <td>{{ $report->created_at->timezone('Asia/Manila')->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="@if($var == 'pulledOutReports')4 @elseif($var == 'validReports')4 @else 3 @endif" class="text-center text-muted">
                                    No items for this category today.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
</div>
@endsection
