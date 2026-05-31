<?php

namespace App\Http\Controllers\PWA;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PushController extends Controller
{
    public function store(Request $request) {
      $request->validate([
          'endpoint' => 'required',
          'keys.auth' => 'required',
          'keys.p256dh' => 'required'
      ]);

      $user = auth()->user();

      if (!$user) {
          return response()->json(['error' => 'Unauthorized'], 401);
      }

      $user->updatePushSubscription(
          $request->endpoint, 
          $request->keys['p256dh'], 
          $request->keys['auth']
      );

      return response()->json(['success' => true]);
  }
}
