<?php
namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        // Untuk API, tidak melakukan redirect dan hanya mengembalikan null
        if (! $request->expectsJson()) {
            return null;
        }
    }

    /**
     * Handle unauthenticated users.
     *
     * @param \Illuminate\Http\Request $request
     * @param array $guards
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function unauthenticated($request, array $guards)
    {
        // Kembalikan respons JSON jika pengguna tidak terautentikasi
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401)
        );
    }
}
