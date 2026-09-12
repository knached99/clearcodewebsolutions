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

    private const CONNECT_TIMEOUT_SECONDS = 3;

    private const REQUEST_TIMEOUT_SECONDS = 5;

    /**
     * Verify a reCAPTCHA v3 token with Google.
     *
     * Security checks performed:
     *
     * 1. Local token validation
     * 2. Secret configuration validation
     * 3. Google verification
     * 4. Google success validation
     * 5. Expected action validation
     * 6. Score validation
     * 7. Optional hostname validation
     *
     * IMPORTANT:
     * This method never logs or persists the reCAPTCHA token.
     */
    public function verifyRecaptcha(
        string $token,
        string $expectedAction,
    ): array {
        /*
         * ---------------------------------------------------------
         * 1. CHEAP LOCAL TOKEN VALIDATION
         * ---------------------------------------------------------
         */

        if (
            $token === ''
            || strlen($token) > self::RECAPTCHA_MAX_LENGTH
        ) {
            return $this->failure(
                'The security verification failed, please try again.',
            );
        }

        /*
         * The action is controlled by the application code.
         *
         * Reject an empty expected action rather than making an
         * ambiguous request to Google.
         */

        if ($expectedAction === '') {
            report(
                new RuntimeException(
                    'reCAPTCHA expected action is empty.',
                ),
            );

            return $this->failure(
                'Security verification is currently unavailable, please try again.',
            );
        }

        /*
         * ---------------------------------------------------------
         * 2. CONFIGURATION VALIDATION
         * ---------------------------------------------------------
         */

        $secret = config(
            'services.google_recaptcha.secret_key',
        );

        if (blank($secret)) {
            report(
                new RuntimeException(
                    'Google reCAPTCHA secret key is not configured.',
                ),
            );

            return $this->failure(
                'Security verification is currently unavailable, please try again.',
            );
        }

        /*
         * ---------------------------------------------------------
         * 3. GOOGLE VERIFICATION
         * ---------------------------------------------------------
         *
         * No automatic retry.
         *
         * Retrying security verification requests can multiply
         * external requests during an attack or outage.
         */

        try {
            $response = Http::asForm()
                ->connectTimeout(
                    self::CONNECT_TIMEOUT_SECONDS,
                )
                ->timeout(
                    self::REQUEST_TIMEOUT_SECONDS,
                )
                ->post(
                    'https://www.google.com/recaptcha/api/siteverify',
                    [
                        'secret' => $secret,
                        'response' => $token,
                        'remoteip' => request()->ip(),
                    ],
                );
        } catch (Throwable $e) {
            report($e);

            return $this->failure(
                'We could not complete the security check, please try again.',
            );
        }

        /*
         * ---------------------------------------------------------
         * 4. HTTP RESPONSE VALIDATION
         * ---------------------------------------------------------
         */

        if (! $response->successful()) {
            report(
                new RuntimeException(
                    'Google reCAPTCHA verification request failed with HTTP status '
                    . $response->status(),
                ),
            );

            return $this->failure(
                'We could not complete the security check, please try again later.',
            );
        }

        $data = $response->json();

        if (! is_array($data)) {
            return $this->failure(
                'The security verification failed, please try again.',
            );
        }

        /*
         * ---------------------------------------------------------
         * 5. GOOGLE SUCCESS VALIDATION
         * ---------------------------------------------------------
         */

        if (($data['success'] ?? false) !== true) {
            return $this->failure(
                'The security verification failed, please try again.',
            );
        }

        /*
         * ---------------------------------------------------------
         * 6. ACTION VALIDATION
         * ---------------------------------------------------------
         *
         * Prevent a valid token generated for one action from
         * being reused against another endpoint.
         */

        if (($data['action'] ?? null) !== $expectedAction) {
            report(
                new RuntimeException(
                    'Unexpected reCAPTCHA action.',
                ),
            );

            return $this->failure(
                'The security verification failed, please try again.',
            );
        }

        /*
         * ---------------------------------------------------------
         * 7. SCORE VALIDATION
         * ---------------------------------------------------------
         */

        $score = $data['score'] ?? null;

        if (! is_numeric($score)) {
            return $this->failure(
                'The security verification failed, please try again.',
            );
        }

        $score = (float) $score;

        if (
            $score < self::RECAPTCHA_MIN_SCORE
            || $score > self::RECAPTCHA_MAX_SCORE
        ) {
            return $this->failure(
                'We could not verify that this request came from a real visitor, please try again.',
            );
        }

        /*
         * ---------------------------------------------------------
         * 8. OPTIONAL HOSTNAME VALIDATION
         * ---------------------------------------------------------
         *
         * Configure:
         *
         * GOOGLE_RECAPTCHA_HOSTNAME=yourdomain.com
         *
         * Leave it null/empty if hostname validation is not
         * configured.
         */

        $expectedHostname = config(
            'services.google_recaptcha.hostname',
        );

        if (
            filled($expectedHostname)
            && ($data['hostname'] ?? null) !== $expectedHostname
        ) {
            report(
                new RuntimeException(
                    'Unexpected reCAPTCHA hostname.',
                ),
            );

            return $this->failure(
                'The security verification failed, please try again.',
            );
        }

        /*
         * ---------------------------------------------------------
         * SUCCESS
         * ---------------------------------------------------------
         */

        return [
            'success' => true,
            'score' => $score,
        ];
    }

    protected function failure(string $message): array
    {
        return [
            'success' => false,
            'message' => $message,
        ];
    }
}