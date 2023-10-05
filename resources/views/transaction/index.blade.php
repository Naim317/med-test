@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="text-center">Transaction History</h2>
        <div class="my-2">
            <btn class="btn btn-secondary">Current Balance: ${{ $currentBalance }}</btn>
            <a href="{{ route('transaction.deposit') }}" class="btn btn-primary">Deposit</a>
            <a href="{{ route('transaction.withdrawal') }}" class="btn btn-primary">Withdraw</a>
        </div>
        <table class="table" id="transactions-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Transaction Type</th>
                    <th>Amount</th>
                    <th>Fee</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->date }}</td>
                        <td>{{ $transaction->transaction_type }}</td>
                        <td>${{ $transaction->amount }}</td>
                        @if($transaction->fee && $transaction->fee != 0)
                        <td>${{ $transaction->fee }}</td>
                        @else
                        <th>&#10060; No Fees</th
                        @endif
                        
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <script>
        $(document).ready(function () {
            $('#transactions-table').DataTable({
                paging: true,
            });
        });
    </script>
@endsection
