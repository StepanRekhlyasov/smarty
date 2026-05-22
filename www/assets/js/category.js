'use strict';

(function () {
    function openModal(id) {
        var modal = document.getElementById(id);
        if (!modal) return;
        modal.setAttribute('aria-hidden', 'false');
        modal.classList.add('modal--open');
        document.body.classList.add('modal-open');
    }

    function closeModal(modal) {
        if (!modal) return;
        modal.setAttribute('aria-hidden', 'true');
        modal.classList.remove('modal--open');
        document.body.classList.remove('modal-open');
    }

    function showError(formEl, message) {
        var existing = formEl.querySelector('.form-error');
        if (existing) existing.remove();
        var el = document.createElement('div');
        el.className   = 'form-error';
        el.textContent = message;
        formEl.prepend(el);
    }

    function setLoading(btn, loading) {
        btn.disabled = loading;
        btn.dataset.originalText = btn.dataset.originalText || btn.textContent;
        btn.textContent = loading ? 'Сохранение…' : btn.dataset.originalText;
    }

    document.addEventListener('DOMContentLoaded', function () {

        // ── Close handlers ────────────────────────────────────────────────────

        document.querySelectorAll('[data-modal-close]').forEach(function (el) {
            el.addEventListener('click', function () {
                closeModal(el.closest('.modal'));
            });
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal--open').forEach(closeModal);
            }
        });

        // ── Edit modal ────────────────────────────────────────────────────────

        var btnEdit = document.getElementById('btn-edit-category');
        if (btnEdit) {
            btnEdit.addEventListener('click', function () {
                openModal('modal-edit-category');
            });
        }

        var formEdit = document.getElementById('form-edit-category');
        if (formEdit) {
            formEdit.addEventListener('submit', async function (e) {
                e.preventDefault();
                var btn = this.querySelector('[type="submit"]');
                setLoading(btn, true);

                try {
                    var formData = new FormData(this);
                    var res      = await fetch('/api/category/update/', { method: 'POST', body: formData });
                    var data     = await res.json();

                    if (data.success) {
                        window.location.reload();
                    } else {
                        showError(this, data.error || 'Неизвестная ошибка');
                        setLoading(btn, false);
                    }
                } catch (err) {
                    showError(this, 'Ошибка соединения с сервером');
                    setLoading(btn, false);
                }
            });
        }

        // ── Delete modal ──────────────────────────────────────────────────────

        var btnDelete = document.getElementById('btn-delete-category');
        if (btnDelete) {
            btnDelete.addEventListener('click', function () {
                openModal('modal-delete-confirm');
            });
        }

        var btnConfirm = document.getElementById('btn-confirm-delete');
        if (btnConfirm) {
            btnConfirm.addEventListener('click', async function () {
                this.disabled    = true;
                this.textContent = 'Удаление…';

                try {
                    var categoryId = btnDelete.dataset.categoryId;
                    var formData   = new FormData();
                    formData.append('id', categoryId);

                    var res  = await fetch('/api/category/delete/', { method: 'POST', body: formData });
                    var data = await res.json();

                    if (data.success) {
                        window.location.href = '/';
                    } else {
                        closeModal(document.getElementById('modal-delete-confirm'));
                        alert(data.error || 'Не удалось удалить категорию');
                        this.disabled    = false;
                        this.textContent = 'Да, удалить';
                    }
                } catch (err) {
                    closeModal(document.getElementById('modal-delete-confirm'));
                    alert('Ошибка соединения с сервером');
                    this.disabled    = false;
                    this.textContent = 'Да, удалить';
                }
            });
        }
    });
}());
