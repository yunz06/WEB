/* Chỉ bổ sung hành vi Dashboard. Gộp vào admin.js chung nếu file đó đã tồn tại. */
document.addEventListener('DOMContentLoaded', function () {
    var refreshButton = document.querySelector('[data-dashboard-refresh]');

    if (refreshButton === null) {
        return;
    }

    refreshButton.addEventListener('click', function () {
        window.location.reload();
    });
});
