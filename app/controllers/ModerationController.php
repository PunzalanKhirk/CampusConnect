<?php
/**
 * ModerationController
 * -------------------------------------------------
 * Accessible to: student_moderator, system_admin (see Middleware::requireModerator).
 * Handles the moderation queue, AI-flagged content review, and enforcement
 * (approve/hide/delete). Never touches author-identity data - see ReportModel.
 */
class ModerationController extends Controller
{
    private ReportModel $reportModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        Middleware::requireModerator(); // RBAC gate - runs before anything else
        $this->reportModel = $this->model('ReportModel');
    }

    /** GET /moderation/dashboard */
    public function dashboard(): void
    {
        $status = $_GET['status'] ?? 'pending';
        $queue = $this->reportModel->getQueue($status);

        $this->view('moderation/dashboard', [
            'title' => 'Moderation Queue',
            'queue' => $queue,
            'activeFilter' => $status,
            'pendingCount' => $this->reportModel->countByStatus('pending'),
            'csrf_token' => Middleware::csrfToken(),
            'userName' => $_SESSION['full_name'] ?? 'Moderator',
            'userRole' => $_SESSION['role'] ?? '',
        ]);
    }

    /** POST /moderation/approve/{reportId}  (also callable as JSON REST endpoint) */
    public function approve(?string $reportId = null): void
    {
        $this->handleAction($reportId, 'approve');
    }

    /** POST /moderation/hide/{reportId} */
    public function hide(?string $reportId = null): void
    {
        $this->handleAction($reportId, 'hide');
    }

    /** POST /moderation/delete/{reportId} */
    public function delete(?string $reportId = null): void
    {
        $this->handleAction($reportId, 'delete');
    }

    private function handleAction(?string $reportId, string $action): void
    {
        Middleware::verifyCsrf($_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null));

        $id = filter_var($reportId, FILTER_VALIDATE_INT);
        if (!$id) {
            $this->json(['success' => false, 'message' => 'Invalid report ID.'], 422);
        }

        $actorId = (int) $_SESSION['user_id'];
        $ok = match ($action) {
            'approve' => $this->reportModel->approve($id, $actorId),
            'hide' => $this->reportModel->hide($id, $actorId),
            'delete' => $this->reportModel->delete($id, $actorId),
            default => false,
        };

        $this->json([
            'success' => $ok,
            'message' => $ok ? ucfirst($action) . 'd successfully.' : 'Action failed.',
            'reportId' => $id,
        ], $ok ? 200 : 500);
    }
}
