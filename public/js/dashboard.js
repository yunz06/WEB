/* Chỉ bổ sung hành vi Dashboard. Giữ riêng dashboard.js để tránh ghi đè admin.js chung của nhóm. */
document.addEventListener('DOMContentLoaded', function () {
    var refreshButton = document.querySelector('[data-dashboard-refresh]');

    if (refreshButton === null) {
        return;
    }

    refreshButton.addEventListener('click', function () {
        window.location.reload();
    });
});
