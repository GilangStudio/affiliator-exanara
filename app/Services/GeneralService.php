<?php

namespace App\Services;

use App\Models\User;

class GeneralService
{
    /**
     * Process username
     */
    public static function processUsername($email)
    {
        $username = strtolower(substr(explode('@', $email)[0], 0, 20));
        
        while (User::where('username', $username)->exists()) {
            $username .= rand(100, 999);
        }
        return $username;
    }
    
    /**
     * Format phone number to consistent format
     */
    public static function formatPhoneNumber($phone)
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // // Handle different formats
        // if (substr($phone, 0, 2) === '62') {
        //     // Already has country code 62
        //     return $phone;
        // } elseif (substr($phone, 0, 1) === '0') {
        //     // Remove leading 0 and add country code
        //     return '62' . substr($phone, 1);
        // } else {
        //     // Add country code
        //     return '62' . $phone;
        // }

        // if start with 62, remove it
        if (substr($phone, 0, 2) === '62') {
            $phone = substr($phone, 2);
        }

        // if start with 0, remove it
        if (substr($phone, 0, 1) === '0') {
            $phone = substr($phone, 1);
        }

        return $phone;
    }
}