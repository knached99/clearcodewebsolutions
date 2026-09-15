@extends('layouts.home_layout')

@section('title', 'Clear Code Web Solutions')

@section('description',
    'Clear Code Web Solutions creates modern, high-performance websites and digital solutions that
    help businesses stand out and grow.')

@section('content')

    <main>

        <x-home.intro />

        <x-home.services id="services" />

        <x-home.about id="about" />

        <x-home.work id="work" />

        <x-home.team id="team" />

        <x-home.process id="process" />

        <x-home.pricing-model id="pricing" />

        <livewire:home.testimonials.slider id="testimonials" />

        <livewire:home.testimonials.form id="testimonial-form" />

        <livewire:home.contact />

    </main>

    {{-- <x-home.subscribe /> --}}

@endsection
