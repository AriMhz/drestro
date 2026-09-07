<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AakashSmsService
{
    /**
     * Send SMS via Aakash SMS API Gateway.
     * 
     * @param string $to
     * @param string $message
     * @return bool
     */
    public static function send($to, $message)
    {
        $token = env('AAKASH_SMS_AUTH_TOKEN');
        
        if (empty($token)) {
            Log::error("Aakash SMS: Auth Token is not set in .env.");
            return false;
        }

        // Clean up phone number (remove spaces, dashes, ensure correct country prefix if needed)
        $to = preg_replace('/[^0-9]/', '', $to);

        // Aakash SMS expects standard 10-digit mobile number for Nepal (98xxxxxxxx)
        if (strlen($to) > 10) {
            // Remove leading country codes like 977
            if (str_starts_with($to, '977')) {
                $to = substr($to, 3);
            }
        }

        try {
            $response = Http::asForm()->post('https://sms.aakashsms.com/sms/v3/send', [
                'auth_token' => $token,
                'to' => $to,
                'text' => $message,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Check Aakash SMS specific error format (typically 'error' => true/false or similar)
                if (isset($data['error']) && $data['error'] === true) {
                    Log::error("Aakash SMS Gateway Error: " . ($data['message'] ?? 'Unknown gateway error'));
                    return false;
                }
                
                Log::info("Aakash SMS sent successfully to {$to}.");
                return true;
            } else {
                Log::error("Aakash SMS Request failed with status " . $response->status() . ": " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Aakash SMS Gateway Exception: " . $e->getMessage());
        }

        return false;
    }
}
