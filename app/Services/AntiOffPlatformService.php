<?php

namespace App\Services;

class AntiOffPlatformService
{
    /**
     * Inspect and sanitize message content against off-platform contact leaks (PRD §13.2).
     *
     * @return array{clean_body: string, is_flagged: bool, flagged_reasons: array<string>, matches: array<string>}
     */
    public function inspect(string $text): array
    {
        $flaggedReasons = [];
        $matchesFound = [];
        $clean = $text;

        $maskNotice = '[Kontak disembunyikan demi keamanan transaksi Turget.id]';

        // 1. WhatsApp links (wa.me, api.whatsapp.com)
        $waRegex = '/(?:https?:\/\/)?(?:www\.)?(?:wa\.me|api\.whatsapp\.com|chat\.whatsapp\.com)\/[a-zA-Z0-9_\-\+\.\/]+/i';
        if (preg_match_all($waRegex, $clean, $matches)) {
            $flaggedReasons[] = 'whatsapp_link';
            $matchesFound = array_merge($matchesFound, $matches[0]);
            $clean = preg_replace($waRegex, $maskNotice, $clean);
        }

        // 2. Telegram links (t.me)
        $tgRegex = '/(?:https?:\/\/)?(?:www\.)?t\.me\/[a-zA-Z0-9_\-]+/i';
        if (preg_match_all($tgRegex, $clean, $matches)) {
            $flaggedReasons[] = 'telegram_link';
            $matchesFound = array_merge($matchesFound, $matches[0]);
            $clean = preg_replace($tgRegex, $maskNotice, $clean);
        }

        // 3. Email addresses
        $emailRegex = '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/';
        if (preg_match_all($emailRegex, $clean, $matches)) {
            $flaggedReasons[] = 'email_address';
            $matchesFound = array_merge($matchesFound, $matches[0]);
            $clean = preg_replace($emailRegex, $maskNotice, $clean);
        }

        // 4. Phone number patterns (Indonesian 08xx, +628xx, with optional spaces/dashes/dots)
        // Matches e.g.: 081234567890, +62 812-3456-7890, 0857.1234.5678, 081 234 567 890
        $phoneRegex = '/(?:\+?62|0)8[0-9]{1,3}(?:[\s\.\-]?[0-9]{3,4}){2,3}/';
        if (preg_match_all($phoneRegex, $clean, $matches)) {
            $flaggedReasons[] = 'phone_number';
            $matchesFound = array_merge($matchesFound, $matches[0]);
            $clean = preg_replace($phoneRegex, $maskNotice, $clean);
        }

        // 5. Explicit off-platform keywords with digits (e.g., "wa saya 08...", "transfer bca 8830...")
        $keywordRegex = '/(?:wa\s*(?:saya|ke|di)?|whatsapp|rekening|rek\s*bca|rek\s*mandiri)\s*:?\s*[0-9\s\.\-]{6,}/i';
        if (preg_match_all($keywordRegex, $clean, $matches)) {
            if (! in_array('direct_contact_keyword', $flaggedReasons, true)) {
                $flaggedReasons[] = 'direct_contact_keyword';
            }
            $matchesFound = array_merge($matchesFound, $matches[0]);
            $clean = preg_replace($keywordRegex, $maskNotice, $clean);
        }

        return [
            'clean_body' => $clean,
            'is_flagged' => count($flaggedReasons) > 0,
            'flagged_reasons' => array_values(array_unique($flaggedReasons)),
            'matches' => array_values(array_unique($matchesFound)),
        ];
    }
}
