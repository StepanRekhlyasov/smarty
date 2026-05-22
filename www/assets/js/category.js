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

    document.addEventListener('DOMContentLoaded', function () {
        var btnDelete = document.getElementById('btn-delete-category');
        if (!btnDelete) return;

        btnDelete.addEventListener('click', function () {
            openModal('modal-delete-confirm');
        });

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
