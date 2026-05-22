'use strict';

(function () {
    // ── Modal helpers ─────────────────────────────────────────────────────────

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
        el.className = 'form-error';
        el.textContent = message;
        formEl.prepend(el);
    }

    function setLoading(btn, loading) {
        btn.disabled = loading;
        btn.dataset.originalText = btn.dataset.originalText || btn.textContent;
        btn.textContent = loading ? 'Сохранение…' : btn.dataset.originalText;
    }

    // ── Bootstrap ─────────────────────────────────────────────────────────────

    document.addEventListener('DOMContentLoaded', function () {

        // Open buttons
        var btnArticle  = document.getElementById('btn-create-article');
        var btnCategory = document.getElementById('btn-create-category');

        if (btnArticle) {
            btnArticle.addEventListener('click', function () {
                openModal('modal-article');
            });
        }

        if (btnCategory) {
            btnCategory.addEventListener('click', function () {
                openModal('modal-category');
            });
        }

        // Close on overlay click / close button
        document.querySelectorAll('[data-modal-close]').forEach(function (el) {
            el.addEventListener('click', function () {
                closeModal(el.closest('.modal'));
            });
        });

        // Close on Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal--open').forEach(closeModal);
            }
        });

        // ── Image type toggle ─────────────────────────────────────────────────

        var imageTypeRadios = document.querySelectorAll('input[name="image_type"]');
        var urlSection      = document.getElementById('image-url-section');
        var fileSection     = document.getElementById('image-file-section');

        imageTypeRadios.forEach(function (radio) {
            radio.addEventListener('change', function () {
                if (urlSection)  urlSection.style.display  = this.value === 'url'  ? '' : 'none';
                if (fileSection) fileSection.style.display = this.value === 'file' ? '' : 'none';
            });
        });

        // ── Article form ──────────────────────────────────────────────────────

        var formArticle = document.getElementById('form-article');
        if (formArticle) {
            formArticle.addEventListener('submit', async function (e) {
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
                    var res      = await fetch('/api/article/create/', { method: 'POST', body: formData });
                    var data     = await res.json();

                    if (data.success) {
                        closeModal(document.getElementById('modal-article'));
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

        // ── Category form ─────────────────────────────────────────────────────

        var formCategory = document.getElementById('form-category');
        if (formCategory) {
            formCategory.addEventListener('submit', async function (e) {
                e.preventDefault();
                var btn = this.querySelector('[type="submit"]');
                setLoading(btn, true);

                try {
                    var formData = new FormData(this);
                    var res      = await fetch('/api/category/create/', { method: 'POST', body: formData });
                    var data     = await res.json();

                    if (data.success) {
                        closeModal(document.getElementById('modal-category'));
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

        // ── Tooltip ───────────────────────────────────────────────────────────

        var tooltipTriggers = document.querySelectorAll('[data-tooltip]');
        tooltipTriggers.forEach(function (trigger) {
            var tip = document.createElement('div');
            tip.className   = 'tooltip-box';
            tip.textContent = trigger.dataset.tooltip;
            trigger.parentNode.appendChild(tip);

            trigger.addEventListener('mouseenter', function () {
                tip.classList.add('tooltip-box--visible');
            });
            trigger.addEventListener('mouseleave', function () {
                tip.classList.remove('tooltip-box--visible');
            });
            trigger.addEventListener('focus', function () {
                tip.classList.add('tooltip-box--visible');
            });
            trigger.addEventListener('blur', function () {
                tip.classList.remove('tooltip-box--visible');
            });
        });
    });
}());
