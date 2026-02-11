<?php

namespace App\Http\Middleware;

use App\Models\Application;
use App\Models\Document;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyTenantScope
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = Filament::getTenant();

        if ($tenant) {
            // Set global scope for all queries in this request
            Application::addGlobalScope('tenant', function ($query) use ($tenant) {
                $query->where('school_id', $tenant->id);
            });

            Document::addGlobalScope('tenant', function ($query) use ($tenant) {
                $query->whereHas('application', function ($q) use ($tenant) {
                    $q->where('school_id', $tenant->id);
                });
            });

            // Add for other models...
        }

        return $next($request);
    }
}
