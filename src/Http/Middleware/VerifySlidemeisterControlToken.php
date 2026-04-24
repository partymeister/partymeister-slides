<?php

namespace Partymeister\Slides\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Shared-token middleware for the external slidemeister control API.
 *
 * Mirrors the shader-showdown / export-token pattern: a single env-var
 * secret configured on the server is matched against an X-Slidemeister-Token
 * header using hash_equals. Intended for trusted external callers that
 * need to drive the slidemeister-web viewer without an admin session.
 */
class VerifySlidemeisterControlToken
{
    public function handle(Request $request, Closure $next)
    {
        $expected = config('partymeister-slides.control_token');

        if (empty($expected)) {
            return response()->json(
                ['message' => 'Slidemeister control token not configured on server'],
                503,
            );
        }

        $provided = (string) $request->header('X-Slidemeister-Token', '');

        if (! hash_equals($expected, $provided)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
