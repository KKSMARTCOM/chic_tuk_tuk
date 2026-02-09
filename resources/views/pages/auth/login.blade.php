@extends('layouts.auth')

@section('title', 'Connexion')

@section('form')
    <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Adresse e-mail</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required
                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500">
            @error('email')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="relative">
            <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe</label>
            <input id="password" type="password" name="password" required
                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500 pr-12">
            <button type="button" data-target="#password"
                class="toggle-password absolute inset-y-0 right-2 top-6 inline-flex items-center px-3 text-gray-500 hover:text-gray-700"
                aria-label="Afficher le mot de passe">
                <i class="fas fa-eye" aria-hidden="true"></i>
            </button>
            @error('password')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- <div class="flex items-center">
            <input id="terms" type="checkbox" name="terms" class="h-4 w-4 text-emerald-600 border-gray-300 rounded">
            <label for="terms" class="ml-2 text-sm text-gray-700">J'accepte les <a href="#"
                    class="text-emerald-600 underline">conditions</a></label>
        </div> --}}

        <div>
            <button type="submit"
                class="w-full py-2 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md font-semibold">Se
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
                <a href="#" aria-label="Twitter"
                    class="inline-flex items-center justify-center w-10 h-10 bg-gray-100 rounded-full text-gray-600"><i
                        class="fab fa-twitter"></i></a>
            </div>
        </div>
    </form>
@endsection
