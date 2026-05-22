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
        var btnUpload   = document.getElementById('btn-upload-data');

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

        if (btnUpload) {
            btnUpload.addEventListener('click', function () {
                document.getElementById('upload-result').style.display = 'none';
                document.getElementById('form-upload').style.display   = '';
                openModal('modal-upload');
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

        // ── Upload form ───────────────────────────────────────────────────────

        var formUpload = document.getElementById('form-upload');
        if (formUpload) {
            formUpload.addEventListener('submit', async function (e) {
                e.preventDefault();

                var fileInput = this.querySelector('input[type="file"]');
                if (!fileInput.files.length) {
                    showError(this, 'Выберите файл');
                    return;
                }

                var btn = this.querySelector('[type="submit"]');
                setLoading(btn, true);

                try {
                    var formData = new FormData(this);
                    var res      = await fetch('/api/upload/', { method: 'POST', body: formData });
                    var data     = await res.json();

                    var resultEl = document.getElementById('upload-result');
                    formUpload.style.display = 'none';
                    resultEl.style.display   = '';

                    if (data.success) {
                        resultEl.innerHTML =
                            '<div class="upload-success">' +
                            '<div class="upload-success__icon">✅</div>' +
                            '<strong>Данные загружены успешно!</strong><br><br>' +
                            'Создано категорий: <strong>' + data.created_categories + '</strong><br>' +
                            'Создано статей: <strong>' + data.created_articles + '</strong><br><br>' +
                            '<button class="btn btn-primary" onclick="window.location.reload()">Обновить страницу</button>' +
                            '</div>';
                    } else {
                        var details = '';
                        if (data.details && data.details.length) {
                            details = '<ul class="upload-error-list">' +
                                data.details.map(function (d) { return '<li>' + d + '</li>'; }).join('') +
                                '</ul>';
                        }
                        resultEl.innerHTML =
                            '<div class="form-error" style="margin:0 0 16px;">' +
                            '<strong>' + (data.error || 'Ошибка') + '</strong>' +
                            details +
                            '</div>' +
                            '<button class="btn btn-outline" onclick="' +
                            'document.getElementById(\'upload-result\').style.display=\'none\';' +
                            'document.getElementById(\'form-upload\').style.display=\'\';' +
                            '">← Назад</button>';
                        setLoading(btn, false);
                    }
                } catch (err) {
                    showError(this, 'Ошибка соединения с сервером');
                    setLoading(btn, false);
                }
            });
        }

        // ── Tooltip ───────────────────────────────────────────────────────

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
