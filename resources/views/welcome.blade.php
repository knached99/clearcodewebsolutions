@extends('layouts.home_layout')

@section('title', 'Clear Code Web Solutions')

@section('description', 'Clear Code Web Solutions creates modern, high-performance websites and digital solutions that help businesses stand out and grow.')

@section('content')

    <x-home.navbar />

    <main>

        <x-home.intro />

        <x-home.services id="services" />

        <x-home.work id="work" />

        <x-home.team id="team" />

        <x-home.process id="process" />

        <x-home.testimonials id="testimonials" />

        <x-home.about id="about" />

        <livewire:home.contact />

    </main>

    <x-home.subscribe />

    <x-home.footer />

    <x-home.to-top />

@endsection