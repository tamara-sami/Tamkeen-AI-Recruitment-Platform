<?php

if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('initials')) {
    function initials($name) {

        $name = trim((string)$name);

        if ($name === '') {
            return 'U';
        }

        $parts = preg_split('/\s+/', $name);

        $first = strtoupper(substr($parts[0] ?? 'U', 0, 1));
        $second = strtoupper(substr($parts[1] ?? '', 0, 1));

        return $first . $second;
    }
}

if (!function_exists('candidateScore')) {
    function candidateScore($earnedPoints, $submittedTasks) {

        $score = ((int)$earnedPoints) + (((int)$submittedTasks) * 10);

        if ($score <= 0) {
            $score = 50;
        }

        return min($score, 100);
    }
}

if (!function_exists('fitLabel')) {
    function fitLabel($score) {

        if ($score >= 85) {
            return 'Excellent Match';
        }

        if ($score >= 70) {
            return 'Good Potential';
        }

        return 'Needs Review';
    }
}

if (!function_exists('fitClass')) {
    function fitClass($score) {

        if ($score >= 85) {
            return 'excellent';
        }

        if ($score >= 70) {
            return 'strong';
        }

        return 'normal';
    }
}

if (!function_exists('formatInterviewDateTime')) {
    function formatInterviewDateTime($date, $time) {

        $timestamp = strtotime($date . ' ' . $time);

        if (!$timestamp) {
            return 'Date not set';
        }

        return date('M d, Y · h:i A', $timestamp);
    }
}


if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}


if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('initials')) {
    function initials($name) {
        $name = trim((string)$name);

        if ($name === '') {
            return 'U';
        }

        $parts = preg_split('/\s+/', $name);

        $first = strtoupper(substr($parts[0] ?? 'U', 0, 1));
        $second = strtoupper(substr($parts[1] ?? '', 0, 1));

        return $first . $second;
    }
}

if (!function_exists('formatInterviewDateTime')) {
    function formatInterviewDateTime($date, $time) {
        $timestamp = strtotime($date . ' ' . $time);

        if (!$timestamp) {
            return 'Date not set';
        }

        return date('M d, Y · h:i A', $timestamp);
    }
}
