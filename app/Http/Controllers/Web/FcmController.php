<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FcmToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FcmController extends Controller
{
    public function store(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        FcmToken::updateOrCreate(
            ['token' => $request->token],
            ['user_id' => Auth::id()]
        );

        return response()->json(['success' => true]);
    }
}
