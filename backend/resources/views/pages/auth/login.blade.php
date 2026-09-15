@extends('layouts.auth')

@section('title', 'Connexion')

@section('form')
    <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
        @csrf

        {{-- Sélection du profil --}}
        <div>
            <div class="grid grid-cols-3 gap-3">
                @foreach ([['value' => 'client', 'label' => 'Client', 'icon' => 'fa-user'], ['value' => 'driver', 'label' => 'Agent', 'icon' => 'fa-car'], ['value' => 'admin', 'label' => 'Administrateur', 'icon' => 'fa-user-shield']] as $profil)
                    <label for="profil_{{ $profil['value'] }}"
                        class="profil-card cursor-pointer rounded-xl border-2 p-3 flex flex-col items-center gap-2 transition-all duration-200 select-none
                              {{ old('profil', 'client') == $profil['value'] ? 'border-[#286b41] bg-[#286b41]/10 text-[#286b41]' : 'border-gray-200 bg-white text-gray-500 hover:border-[#286b41]/40' }}">

                        <input type="radio" name="profil" id="profil_{{ $profil['value'] }}" value="{{ $profil['value'] }}"
                            class="sr-only" {{ old('profil', 'client') == $profil['value'] ? 'checked' : '' }} required>

                        <div
                            class="w-10 h-10 rounded-full flex items-center justify-center transition-colors duration-200
                                {{ old('profil', 'client') == $profil['value'] ? 'bg-[#286b41] text-white' : 'bg-gray-100 text-gray-400' }}">
                            <i class="fas {{ $profil['icon'] }} text-base"></i>
                        </div>

                        <span class="text-xs font-semibold text-center leading-tight">{{ $profil['label'] }}</span>

                        {{-- Indicateur de sélection --}}
                        {{-- <div
                            class="w-4 h-4 rounded-full border-2 flex items-center justify-center transition-colors duration-200
                                {{ old('profil', 'client') == $profil['value'] ? 'border-[#286b41]' : 'border-gray-300' }}">
                            @if (old('profil', 'client') == $profil['value'])
                                <div class="w-2 h-2 rounded-full bg-[#286b41]"></div>
                            @endif
                        </div> --}}
                    </label>
                @endforeach
            </div>

            @error('profil')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-lg font-medium text-gray-700">E-mail</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                placeholder="Votre adresse mail" required
                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#286b41]">
            @error('email')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="relative">
            <label for="password" class="block text-lg font-medium text-gray-700">Mot de passe</label>
            <input id="password" type="password" name="password" placeholder="Votre mot de passe" required
                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#286b41] pr-12">
            <button type="button" data-target="#password"
                class="toggle-password absolute inset-y-0 right-2 top-6 inline-flex items-center px-3 text-gray-500 hover:text-gray-700"
                aria-label="Afficher le mot de passe">
                <i class="fas fa-eye" aria-hidden="true"></i>
            </button>
        </div>
        @error('password')
            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
        @enderror

        {{-- <div class="flex items-center">
            <input id="terms" type="checkbox" name="terms" class="h-4 w-4 text-emerald-600 border-gray-300 rounded">
            <label for="terms" class="ml-2 text-sm text-gray-700">J'accepte les <a href="#"
                    class="text-emerald-600 underline">conditions</a></label>
        </div> --}}

        <div>
            <button type="submit"
                class="w-full py-2 px-4 bg-[#286b41] hover:opacity-90 text-white rounded-md font-semibold">Se
                connecter</button>
        </div>

        {{-- <div class="text-center text-sm text-gray-600">
            <p>Déjà un compte ? <a href="{{ route('login') }}" class="text-emerald-600 underline">Se
                    connecter</a></p>
        </div> --}}

        <div class="mt-4">
            <div class="text-center text-sm text-gray-500 mb-3">Ou connectez-vous avec</div>
            <div class="flex justify-center space-x-3">
                <a href="#" aria-label="Google"
                    class="inline-flex items-center justify-center w-10 h-10 bg-gray-100 rounded-full text-gray-600"><i
                        class="fab fa-google"></i></a>
                <a href="#" aria-label="Facebook"
                    class="inline-flex items-center justify-center w-10 h-10 bg-gray-100 rounded-full text-gray-600"><i
                        class="fab fa-facebook"></i></a>
            </div>
        </div>
    </form>

@endsection
