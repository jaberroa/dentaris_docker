<?php

if (!function_exists('getPatientSortUrl')) {
    function getPatientSortUrl($field, $currentSort, $currentDirection) {
        $params = request()->query();
        
        if ($currentSort === $field && $currentDirection === 'asc') {
            $params['direction'] = 'desc';
        } else {
            $params['direction'] = 'asc';
        }
        
        $params['sort'] = $field;
        
        return request()->url() . '?' . http_build_query($params);
    }
}

if (!function_exists('getPatientSortIcon')) {
    function getPatientSortIcon($field, $currentSort, $currentDirection) {
        if ($currentSort !== $field) {
            return '<i class="fas fa-sort sort-icon"></i>';
        }
        
        if ($currentDirection === 'asc') {
            return '<i class="fas fa-sort-up sort-icon active"></i>';
        } else {
            return '<i class="fas fa-sort-down sort-icon active"></i>';
        }
    }
}
