/**
 * CampusConnect - Moderation Dashboard interactions
 * Vanilla JS. Talks to the REST-style endpoints exposed by ModerationController
 * (POST /moderation/{approve|hide|delete}/{reportId}), which return JSON.
 */
(function () {
    'use strict';

    const CC = window.CampusConnect || {};
    const modal = document.getElementById('confirmModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalSubtitle = document.getElementById('modalSubtitle');
    const modalConfirmBtn = document.getElementById('modalConfirm');
    const modalCancelBtn = document.getElementById('modalCancel');
    const toastEl = document.getElementById('toast');
    const queueList = document.getElementById('queueList');
    const searchInput = document.getElementById('queueSearch');

    let pendingAction = null; // { action, id, row }

    const ACTION_COPY = {
        approve: { title: 'Approve this post?', subtitle: 'The reported content will remain visible and the report will be marked resolved.' },
        hide: { title: 'Hide this post?', subtitle: 'The content will be hidden from public view but retained for audit purposes.' },
        delete: { title: 'Delete this post?', subtitle: 'This soft-deletes the content. This action is permanent for end users and is fully logged.' },
    };

    function openModal(action, id, row) {
        pendingAction = { action, id, row };
        const copy = ACTION_COPY[action] || { title: 'Confirm action?', subtitle: 'This action will be logged.' };
        modalTitle.textContent = copy.title;
        modalSubtitle.textContent = copy.subtitle;
        modal.classList.add('open');
    }

    function closeModal() {
        modal.classList.remove('open');
        pendingAction = null;
    }

    function showToast(message, isError) {
        toastEl.textContent = message;
        toastEl.className = 'toast show' + (isError ? ' error' : '');
        setTimeout(() => { toastEl.classList.remove('show'); }, 3000);
    }

    async function submitAction(action, id) {
        try {
            const res = await fetch(`${CC.urlRoot}/moderation/${action}/${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-Token': CC.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new URLSearchParams({ csrf_token: CC.csrfToken }),
            });
            const data = await res.json();

            if (data.success) {
                showToast(data.message || 'Done.');
                // Remove the row from the queue visually (it's been resolved)
                if (pendingAction && pendingAction.row) {
                    pendingAction.row.style.transition = 'opacity 0.2s ease';
                    pendingAction.row.style.opacity = '0';
                    setTimeout(() => pendingAction.row.remove(), 200);
                }
            } else {
                showToast(data.message || 'Action failed.', true);
            }
        } catch (err) {
            console.error(err);
            showToast('Network error. Please try again.', true);
        }
    }

    // Delegate clicks on action buttons within the queue list
    queueList && queueList.addEventListener('click', function (e) {
        const btn = e.target.closest('button[data-action]');
        if (!btn) return;

        const action = btn.dataset.action;
        const id = btn.dataset.id;
        const row = btn.closest('.queue-row');

        if (action === 'more') {
            // Placeholder for an options menu (view full post, view AI flag reasons, etc.)
            showToast('More options coming soon.');
            return;
        }

        openModal(action, id, row);
    });

    modalConfirmBtn && modalConfirmBtn.addEventListener('click', function () {
        if (!pendingAction) return;
        const { action, id } = pendingAction;
        closeModal();
        submitAction(action, id);
    });

    modalCancelBtn && modalCancelBtn.addEventListener('click', closeModal);
    modal && modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeModal();
    });

    // Lightweight client-side filter (server already paginates/filters by status)
    searchInput && searchInput.addEventListener('input', function () {
        const term = this.value.trim().toLowerCase();
        document.querySelectorAll('.queue-row').forEach((row) => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(term) ? '' : 'none';
        });
    });
})();
