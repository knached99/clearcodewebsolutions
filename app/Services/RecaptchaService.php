<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class RecaptchaService
{

    private const RECAPTCHA_MAX_LENGTH = 4096; 
    private const RECAPTCHA_MIN_SCORE = 0.5; 
    private const RECAPTCHA_MAX_SCORE = 1.0; 
    
    public function verifyRecaptcha(
        string $token,
        string $expectedAction,
    ): array {
        if ($token === '' || strlen($token) > self::RECAPTCHA_MAX_LENGTH) {
            return [
                'success' => false,
                'message' => 'The security verification failed, please try again.',
            ];
        }

        $secret = config('services.google_recaptcha.secret_key');

        if (blank($secret)) {
            report(
                new RuntimeException(
                    'Google reCAPTCHA secret key is not configured'
                )
            );

            return [
                'success' => false,
                'message' => 'Security verification is currently unavailable, please try again.',
            ];
        }

        try {
            $response = Http::asForm()
                ->connectTimeout(3)
                ->timeout(5)
                ->post(
                    'https://www.google.com/recaptcha/api/siteverify',
                    [
                        'secret' => $secret,
                        'response' => $token,
                        'remoteip' => request()->ip(),
                    ]
                );
        } catch (Throwable $e) {
            report($e);

            return [
                'success' => false,
                'message' => 'We could not complete the security check, please try again.',
            ];
        }

        if (! $response->successful()) {
            report(
                new RuntimeException(
                    'Google reCAPTCHA verification request failed with HTTP status '
                    . $response->status(),
                )
            );

            return [
                'success' => false,
                'message' => 'We could not complete the security check, please try again later.',
            ];
        }

        $data = $response->json();

        if (! is_array($data)) {
            return [
                'success' => false,
                'message' => 'The security verification failed, please try again.',
            ];
        }

        /*
         * Google verification failed.
         */
        if (($data['success'] ?? false) !== true) {
            return [
                'success' => false,
                'message' => 'The security verification failed, please try again.',
            ];
        }

        /*
         * Make sure the token was generated for this action.
         */
        if (($data['action'] ?? null) !== $expectedAction) {
            report(
                new RuntimeException(
                    'Unexpected reCAPTCHA action.'
                )
            );

            return [
                'success' => false,
                'message' => 'The security verification failed, please try again.',
            ];
        }


        $score = $data['score'] ?? null;

        if (! is_numeric($score)) {
            return [
                'success' => false,
                'message' => 'The security verification failed, please try again.',
            ];
        }

        $score = (float) $score;

        if ($score < self::RECAPTCHA_MIN_SCORE || $score > self::RECAPTCHA_MAX_SCORE) {
            return [
                'success' => false,
                'message' => 'We could not verify that this request came from a real visitor, please try again.',
            ];
        }

        $expectedHostname = config(
            'services.google_recaptcha.hostname'
        );

        if (
            filled($expectedHostname)
            && ($data['hostname'] ?? null) !== $expectedHostname
        ) {
            report(
                new RuntimeException(
                    'Unexpected reCAPTCHA hostname.'
                )
            );

            return [
                'success' => false,
                'message' => 'The security verification failed, please try again.',
            ];
        }

        return [
            'success' => true,
            'score' => $score,
        ];
    }
}
