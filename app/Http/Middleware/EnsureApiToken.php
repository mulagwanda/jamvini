<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EnsureApiToken
{
    public function handle(Request $request, Closure $next, ?string $ability = null)
    {
        $plain = $request->bearerToken() ?: (string) $request->header('X-API-Token');

        if ($plain === '') {
            return response()->json(['success' => false, 'message' => 'API token is required.'], 401);
        }

        $token = ApiToken::where('token_hash', hash('sha256', $plain))
            ->where('is_active', true)
            ->first();

        if (!$token || ($token->expires_at && $token->expires_at->isPast())) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired API token.'], 401);
        }

        if ($ability && !$token->allows($ability)) {
            return response()->json(['success' => false, 'message' => 'API token does not have this permission.'], 403);
        }

        $request->attributes->set('api_token', $token);
        $token->update(['last_used_at' => now()]);
        $startedAt = microtime(true);

        $response = $next($request);

        if (DB::getSchemaBuilder()->hasTable('api_logs')) {
            DB::table('api_logs')->insert([
                'api_token_id' => $token->id,
                'method' => $request->method(),
                'path' => Str::limit($request->path(), 250, ''),
                'status' => method_exists($response, 'getStatusCode') ? $response->getStatusCode() : 200,
                'ip_address' => $request->ip(),
                'request_payload' => json_encode($request->except(['password', 'token', 'api_token'])),
                'response_payload' => null,
                'duration_ms' => (int) ((microtime(true) - $startedAt) * 1000),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $response;
    }
}
