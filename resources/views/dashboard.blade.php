@extends('layouts.app')

@section('content')

<h2>Dashboard</h2>

<p>Logged in as role: {{ $userRole ?? 'guest' }}</p>

@if($userRole === 'gold')
    <p>Gold Member Benefits</p>
@endif

@if($userRole === 'silver')
    <p>Silver Member Discounts</p>
@endif

@if($userRole === 'customer')
    <p>Standard Customer View</p>
@endif

@if(!$userRole)
    <p>Guest View</p>
@endif

@endsection