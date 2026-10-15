<?php

namespace App\Enums;

/**
 * Publication workflow (PRD §6.2): Draft → In Review → Approved → Published → Archived.
 */
enum ContentStatus: string
{
    case Draft = 'draft';
    case InReview = 'in_review';
    case Approved = 'approved';
    case Published = 'published';
    case Archived = 'archived';

    /**
     * Allowed transitions: action => [from statuses, to status, required permission].
     *
     * @return array<string, array{from: list<self>, to: self, permission: string}>
     */
    public static function actions(): array
    {
        return [
            'submit' => ['from' => [self::Draft], 'to' => self::InReview, 'permission' => 'content.edit'],
            'approve' => ['from' => [self::InReview], 'to' => self::Approved, 'permission' => 'content.review'],
            'request_changes' => ['from' => [self::InReview, self::Approved], 'to' => self::Draft, 'permission' => 'content.review'],
            'publish' => ['from' => [self::Approved], 'to' => self::Published, 'permission' => 'content.publish'],
            'unpublish' => ['from' => [self::Published], 'to' => self::Draft, 'permission' => 'content.publish'],
            'archive' => ['from' => [self::Published, self::Draft], 'to' => self::Archived, 'permission' => 'content.publish'],
            'restore' => ['from' => [self::Archived], 'to' => self::Draft, 'permission' => 'content.publish'],
        ];
    }

    /** @return list<string> */
    public function availableActions(): array
    {
        return array_keys(array_filter(
            self::actions(),
            fn (array $rule) => in_array($this, $rule['from'], true),
        ));
    }

    public function label(string $locale = 'id'): string
    {
        $labels = [
            'id' => [
                'draft' => 'Draf', 'in_review' => 'Ditinjau', 'approved' => 'Disetujui',
                'published' => 'Terbit', 'archived' => 'Diarsipkan',
            ],
            'en' => [
                'draft' => 'Draft', 'in_review' => 'In review', 'approved' => 'Approved',
                'published' => 'Published', 'archived' => 'Archived',
            ],
        ];

        return $labels[$locale][$this->value] ?? $this->value;
    }
}
