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

        var btnEdit = document.getElementById('btn-edit-article');
        if (btnEdit) {
            btnEdit.addEventListener('click', function () {
                openModal('modal-edit-article');
            });
        }

        // Image type toggle inside edit modal
        var editImageRadios     = document.querySelectorAll('#modal-edit-article input[name="image_type"]');
        var editUrlSection      = document.getElementById('edit-image-url-section');
        var editFileSection     = document.getElementById('edit-image-file-section');
        var editCurrentPreview  = document.getElementById('edit-current-image');

        editImageRadios.forEach(function (radio) {
            radio.addEventListener('change', function () {
                var isKeep = this.value === 'keep';
                var isUrl  = this.value === 'url';
                var isFile = this.value === 'file';

                if (editUrlSection)     editUrlSection.style.display     = isUrl  ? '' : 'none';
                if (editFileSection)    editFileSection.style.display    = isFile ? '' : 'none';
                if (editCurrentPreview) editCurrentPreview.style.display = isKeep ? '' : 'none';
            });
        });

        // Tooltip for URL input
        document.querySelectorAll('[data-tooltip]').forEach(function (trigger) {
            var tip = document.createElement('div');
            tip.className   = 'tooltip-box';
            tip.textContent = trigger.dataset.tooltip;
            trigger.parentNode.appendChild(tip);

            trigger.addEventListener('mouseenter', function () { tip.classList.add('tooltip-box--visible'); });
            trigger.addEventListener('mouseleave',  function () { tip.classList.remove('tooltip-box--visible'); });
            trigger.addEventListener('focus',       function () { tip.classList.add('tooltip-box--visible'); });
            trigger.addEventListener('blur',        function () { tip.classList.remove('tooltip-box--visible'); });
        });

        // Edit form submit
        var formEdit = document.getElementById('form-edit-article');
        if (formEdit) {
            formEdit.addEventListener('submit', async function (e) {
                e.preventDefault();

                var checked = this.querySelectorAll('input[name="categories[]"]:checked');
                if (checked.length === 0) {
                    showError(this, 'Выберите хотя бы одну категорию');
                    return;
                }

                var btn = this.querySelector('[type="submit"]');
                setLoading(btn, true);

                try {
                    var formData = new FormData(this);
                    var res      = await fetch('/api/article/update/', { method: 'POST', body: formData });
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

        var btnDelete = document.getElementById('btn-delete-article');
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
                    var articleId = btnDelete.dataset.articleId;
                    var formData  = new FormData();
                    formData.append('id', articleId);

                    var res  = await fetch('/api/article/delete/', { method: 'POST', body: formData });
                    var data = await res.json();

                    if (data.success) {
                        window.location.href = '/';
                    } else {
                        closeModal(document.getElementById('modal-delete-confirm'));
                        alert(data.error || 'Не удалось удалить статью');
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
