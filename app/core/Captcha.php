<?php
namespace App\Core;

/**
 * A minimal, self-hosted CAPTCHA: a simple arithmetic question,
 * answer stored server-side in the session. This avoids depending
 * on an external service (reCAPTCHA/hCaptcha) that would need API
 * keys we don't have — same reasoning as the "manual" shipping
 * provider. Good enough to stop unsophisticated bots from hammering
 * login/register forms; not a defense against a determined attacker
 * running a real solver, which no CAPTCHA fully is anyway.
 *
 * Swap-in path: if real reCAPTCHA credentials become available,
 * this can become a CaptchaInterface + provider pair following the
 * same pattern as Payment/SMS/Email — kept simple for now since
 * there's only one implementation.
 */
class Captcha
{
    public static function generate(): array
    {
        $a = random_int(1, 9);
        $b = random_int(1, 9);
        $_SESSION['captcha_answer'] = $a + $b;
        return ['a' => $a, 'b' => $b];
    }

    public static function verify(?string $submitted): bool
    {
        $expected = $_SESSION['captcha_answer'] ?? null;
        unset($_SESSION['captcha_answer']); // one-time use — always regenerate after a check

        if ($expected === null || $submitted === null || $submitted === '') return false;
        return (int) $submitted === (int) $expected;
    }

    /** Renders the challenge as a form field (question + input). Caller provides the field name. */
    public static function field(string $name = 'captcha_answer'): string
    {
        $c = self::generate();
        return "<label>What is {$c['a']} + {$c['b']}?</label> <input type=\"text\" name=\"{$name}\" inputmode=\"numeric\" autocomplete=\"off\" required>";
    }
}
