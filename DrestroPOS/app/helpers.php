<?php

if (!function_exists('current_restaurant')) {
    function current_restaurant() {
        if (app()->bound('restaurant')) {
            return app('restaurant');
        }
        return \App\Models\Restaurant::first();
    }
}

if (!function_exists('get_tenant_menu_url')) {
    function get_tenant_menu_url($tableId = null) {
        $restaurant = current_restaurant();
        $slug = $restaurant ? $restaurant->slug : 'demo';
        $host = request()->getHost();
        $scheme = request()->getScheme();
        
        $cleanHost = $host;
        if (str_contains($host, 'portal.drestro.com')) {
            $url = $scheme . '://portal.drestro.com/menu?slug=' . $slug;
            if ($tableId) {
                $url .= '&table=' . $tableId;
            }
            return $url;
        } elseif (str_contains($host, 'localhost')) {
            $parts = explode(':', $host);
            $domainPart = $parts[0];
            $portPart = $parts[1] ?? '8000';
            
            $subparts = explode('.', $domainPart);
            if (count($subparts) > 1) {
                // If it is 'tenant.localhost', slice off the tenant prefix
                $domainPart = 'localhost';
            }
            $cleanHost = $domainPart . ':' . $portPart;
        } else {
            $parts = explode('.', $host);
            if (count($parts) > 2) {
                $cleanHost = implode('.', array_slice($parts, -2));
            }
        }
        
        $url = $scheme . '://' . $slug . '.' . $cleanHost . '/menu';
        if ($tableId) {
            $url .= '?table=' . $tableId;
        }
        return $url;
    }
}
