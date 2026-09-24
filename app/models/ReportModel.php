<?php
/**
 * ReportModel
 * -------------------------------------------------
 * SECURITY-CRITICAL: This model is the single gateway moderators use to read
 * report/post data. It NEVER selects, joins, or returns any column from
 * `post_authors` or `users` for the original poster. Only a masked,
 * non-reversible reporter_display_id (e.g. "R-2291") is exposed - that ID is
 * generated at report-creation time and stored as a plain string; it has no
 * queryable relationship back to a real user_id.
 *
 * Even a System Administrator's RBAC/analytics screens must go through a
 * separate, explicitly-audited path if real identities are ever needed
 * (e.g. a legal/Title-IX request) - never through this moderation model.
 */
class ReportModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Moderation queue. Notice: no JOIN to post_authors or users for authorship -
     * only report + post content fields that are safe for moderator eyes.
     */
    public function getQueue(string $statusFilter = 'pending'): array
    {
        $allowed = ['pending', 'approved', 'hidden', 'deleted', 'all'];
        if (!in_array($statusFilter, $allowed, true)) {
            $statusFilter = 'pending';
        }

        $sql = 'SELECT r.id, r.reporter_display_id, r.reason, r.ai_flagged, r.status,
                       r.created_at, p.id AS post_id, p.content, p.is_anonymous
                FROM reports r
                INNER JOIN posts p ON p.id = r.post_id';
        if ($statusFilter !== 'all') {
            $sql .= ' WHERE r.status = :status';
        }
        $sql .= ' ORDER BY r.ai_flagged DESC, r.created_at ASC';

        $this->db->query($sql);
        if ($statusFilter !== 'all') {
            $this->db->bind(':status', $statusFilter);
        }
        return $this->db->resultSet();
    }

    public function countByStatus(string $status): int
    {
        $this->db->query('SELECT COUNT(*) AS c FROM reports WHERE status = :status');
        $this->db->bind(':status', $status);
        return (int) ($this->db->single()['c'] ?? 0);
    }

    /** Approve = report reviewed, post stays visible. */
    public function approve(int $reportId, int $actorId): bool
    {
        return $this->setStatus($reportId, 'approved', $actorId, 'approve');
    }

    /** Hide = post content is hidden from public view but retained for audit. */
    public function hide(int $reportId, int $actorId): bool
    {
        return $this->setStatus($reportId, 'hidden', $actorId, 'hide', hidePost: true);
    }

    /** Delete = soft-delete; content is never truly purged so audit trail survives. */
    public function delete(int $reportId, int $actorId): bool
    {
        return $this->setStatus($reportId, 'deleted', $actorId, 'delete', hidePost: true, deletePost: true);
    }

    private function setStatus(
        int $reportId,
        string $status,
        int $actorId,
        string $action,
        bool $hidePost = false,
        bool $deletePost = false
    ): bool {
        try {
            $this->db->beginTransaction();

            $this->db->query('UPDATE reports SET status = :status, resolved_by = :actor,
                               resolved_at = NOW() WHERE id = :id');
            $this->db->bind(':status', $status);
            $this->db->bind(':actor', $actorId);
            $this->db->bind(':id', $reportId);
            $this->db->execute();

            if ($hidePost || $deletePost) {
                $postStatus = $deletePost ? 'deleted' : 'hidden';
                $this->db->query('UPDATE posts p
                                   INNER JOIN reports r ON r.post_id = p.id
                                   SET p.status = :postStatus WHERE r.id = :id');
                $this->db->bind(':postStatus', $postStatus);
                $this->db->bind(':id', $reportId);
                $this->db->execute();
            }

            $this->db->query('INSERT INTO moderation_logs (actor_user_id, action, report_id)
                               VALUES (:actor, :action, :report_id)');
            $this->db->bind(':actor', $actorId);
            $this->db->bind(':action', $action);
            $this->db->bind(':report_id', $reportId);
            $this->db->execute();

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('[CampusConnect] Moderation action failed: ' . $e->getMessage());
            return false;
        }
    }
}
