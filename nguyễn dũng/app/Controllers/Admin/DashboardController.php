<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Dashboard;
use Closure;
use RuntimeException;

/**
 * Trang quản trị. Cần được Router của TV1 gắn vào route Admin.
 * Hàm kiểm tra quyền bắt buộc do module Auth của TV1 truyền vào.
 */
final class DashboardController
{
    private Dashboard $dashboard;
    private Closure $hasAdminPermission;

    public function __construct(Dashboard $dashboard, callable $hasAdminPermission)
    {
        $this->dashboard = $dashboard;
        $this->hasAdminPermission = Closure::fromCallable($hasAdminPermission);
    }

    /**
     * Trả về HTML nội dung để Admin Layout chung của nhóm bao ngoài.
     */
    public function index(): string
    {
        $authorized = ($this->hasAdminPermission)();

        if ($authorized !== true) {
            throw new RuntimeException('Không có quyền truy cập Dashboard Admin.', 403);
        }

        $data = $this->dashboard->getOverview(5);
        $viewFile = dirname(__DIR__, 2) . '/Views/admin/dashboard/index.php';

        if (!is_file($viewFile)) {
            throw new RuntimeException('Thiếu file view Dashboard.');
        }

        ob_start();

        try {
            require $viewFile;
            return (string) ob_get_clean();
        } catch (\Throwable $error) {
            ob_end_clean();
            throw $error;
        }
    }
}
