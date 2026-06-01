@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8 flex items-center justify-between">
            <h1 class="text-3xl font-bold text-gray-900">Détails du Rôle: {{ $role->name }}</h1>
            <a href="{{ route('admin.roles.index') }}" class="text-blue-600 hover:text-blue-800">
                ← Retour
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Permissions</h2>
                    @if ($role->permissions->count() > 0)
                        <div class="space-y-2">
                            @foreach ($role->permissions as $permission)
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-gray-700">{{ $permission->name }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            Aucune permission assignée à ce rôle.
                        </div>
                    @endif
                </div>
            </div>

            <div>
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Utilisateurs</h2>
                    @php $users = $role->users()->get(); @endphp
                    @if ($users->count() > 0)
                        <ul class="space-y-3">
                            @foreach ($users as $user)
                                <li class="p-3 bg-gray-50 rounded-lg">
                                    <a href="#" class="font-medium text-blue-600 hover:text-blue-800">
                                        {{ $user->name }}
                                    </a>
                                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            Aucun utilisateur avec ce rôle.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
