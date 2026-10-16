<?php

namespace App\Services;

class AntiOffPlatformService
{
    /**
     * Inspect and sanitize message content against off-platform contact leaks (PRD §13.2).
     * Follows Tokopedia & Shopee escrow anti-disintermediation standards.
     *
     * @return array{clean_body: string, is_flagged: bool, flagged_reasons: array<string>, matches: array<string>}
     */
    public function inspect(string $text): array
    {
        $flaggedReasons = [];
        $matchesFound = [];
        $clean = $text;

        $maskNotice = '[Kontak disembunyikan demi keamanan transaksi Turget.id]';

        // 1. WhatsApp & Messaging Links (wa.me, api.whatsapp.com, chat.whatsapp.com)
        $waLinkRegex = '/(?:https?:\/\/)?(?:www\.)?(?:wa\.me|api\.whatsapp\.com|chat\.whatsapp\.com)\/[a-zA-Z0-9_\-\+\.\/]+/i';
        if (preg_match_all($waLinkRegex, $clean, $matches)) {
            $flaggedReasons[] = 'whatsapp_link';
            $matchesFound = array_merge($matchesFound, $matches[0]);
            $clean = preg_replace($waLinkRegex, $maskNotice, $clean);
        }

        // 2. Telegram links & usernames (t.me)
        $tgRegex = '/(?:https?:\/\/)?(?:www\.)?t\.me\/[a-zA-Z0-9_\-]+/i';
        if (preg_match_all($tgRegex, $clean, $matches)) {
            $flaggedReasons[] = 'telegram_link';
            $matchesFound = array_merge($matchesFound, $matches[0]);
            $clean = preg_replace($tgRegex, $maskNotice, $clean);
        }

        // 3. Email addresses (standard and spaced obfuscation like user (at) domain.com)
        $emailRegex = '/[a-zA-Z0-9._%+-]+(?:\s*@\s*|\s*\[at\]\s*|\s*\(at\)\s*)[a-zA-Z0-9.-]+\s*(?:\.|\s*\[dot\]\s*|\s*\(dot\)\s*)[a-zA-Z]{2,}/i';
        if (preg_match_all($emailRegex, $clean, $matches)) {
            $flaggedReasons[] = 'email_address';
            $matchesFound = array_merge($matchesFound, $matches[0]);
            $clean = preg_replace($emailRegex, $maskNotice, $clean);
        }

        // 4. Spelled-out phone numbers in Indonesian (e.g. "nol delapan satu dua...", "kosong delapan satu...")
        $spelledNumberRegex = '/\b(?:nol|kosong|zero)\s+(?:delapan|lapan|eight)(?:\s+(?:nol|kosong|satu|dua|tiga|empat|lima|enam|tujuh|delapan|lapan|sembilan|zero|one|two|three|four|five|six|seven|eight|nine)){6,}\b/i';
        if (preg_match_all($spelledNumberRegex, $clean, $matches)) {
            $flaggedReasons[] = 'phone_number';
            $matchesFound = array_merge($matchesFound, $matches[0]);
            $clean = preg_replace($spelledNumberRegex, $maskNotice, $clean);
        }

        // 5. Phone number patterns:
        // Matches Indonesian 08xx, +628xx, 628xx, or starting with 'o8' / 'O8'
        // Supports spaces, dots, dashes, parentheses between digits (e.g., 081234567890, 081 234 567 890, +62-812-3456-7890)
        $phoneRegex = '/(?:\+?62|0|[oO])\s*8[0-9oO\s\.\-\(\)]{7,18}[0-9oO]/';
        if (preg_match_all($phoneRegex, $clean, $matches)) {
            foreach ($matches[0] as $matchCandidate) {
                // Verify that candidate has at least 9 digit-equivalents
                $digitsOnly = preg_replace('/[^0-9oO]/', '', $matchCandidate);
                if (strlen($digitsOnly) >= 9 && strlen($digitsOnly) <= 15) {
                    $flaggedReasons[] = 'phone_number';
                    $matchesFound[] = $matchCandidate;
                    $clean = str_replace($matchCandidate, $maskNotice, $clean);
                }
            }
        }

        // 6. Direct Bank Account & Off-Platform Payment keywords
        // e.g. "transfer bca 12345678", "rek mandiri: 8830...", "no rek 5410..."
        $bankRegex = '/\b(?:transfer|tf|rek|rekening|bca|mandiri|bri|bni|bsi|cimb|dana|ovo|gopay)\s*(?:(?:bca|mandiri|bri|bni|bsi|cimb|dana|ovo|gopay|bank|langsung|pribadi|manual|ke|di|no|nomor)\s*)*:?\s*[0-9\s\.\-]{6,20}\b/i';
        if (preg_match_all($bankRegex, $clean, $matches)) {
            if (! in_array('bank_payment_bypass', $flaggedReasons, true)) {
                $flaggedReasons[] = 'bank_payment_bypass';
            }
            $matchesFound = array_merge($matchesFound, $matches[0]);
            $clean = preg_replace($bankRegex, $maskNotice, $clean);
        }

        // 7. Social Media Handles & Off-Platform Messengers (Instagram, Line, Telegram, TikTok handles)
        // e.g. "ig: @traveler_bali", "dm ig saya", "line id: bali_guide"
        $socialRegex = '/\b(?:ig|instagram|dm\s*ig|line\s*id|line|tele|telegram|tiktok)\s*[:=]?\s*@?[a-zA-Z0-9_\.]{3,30}\b/i';
        if (preg_match_all($socialRegex, $clean, $matches)) {
            if (! in_array('social_media_contact', $flaggedReasons, true)) {
                $flaggedReasons[] = 'social_media_contact';
            }
            $matchesFound = array_merge($matchesFound, $matches[0]);
            $clean = preg_replace($socialRegex, $maskNotice, $clean);
        }

        // 8. Explicit off-platform keywords with digits or invitations
        // e.g., "wa saya 08...", "hubungi wa", "japri saya", "chat wa", "no wa"
        $keywordRegex = '/\b(?:wa\s*(?:saya|ke|di)?|whatsapp|watsap|wea|w\.a|w\/a|japri|hubungi\s*wa|chat\s*wa|no\s*wa|nomor\s*wa)\s*:?\s*[0-9\s\.\-]{5,}\b/i';
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
